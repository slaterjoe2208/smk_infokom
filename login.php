<?php
require_once __DIR__ . '/includes/session-init.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session-widget.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$ROLE_DASHBOARD = [
    'admin' => 'admin/dashboard.php',
    'siswa' => 'siswa/dashboard-siswa.php',
    'ortu'  => 'orangtua/dashboard-ortu.php',
];

// Kalau di browser ini masih ada sesi login lain (mis. admin), JANGAN langsung dilempar ke dashboard:
// form login tetap ditampilkan supaya bisa masuk dengan akun lain (siswa / wali). Login baru menimpa sesi lama.
$sesiAktif = $_SESSION['user'] ?? null;
$sesiDashboard = $sesiAktif ? ($ROLE_DASHBOARD[$sesiAktif['role']] ?? 'index.php') : null;

$error = '';
$oldEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $oldEmail = $email;

    if (!csrf_valid()) {
        $error = 'Sesi formulir kedaluwarsa. Silakan muat ulang halaman lalu coba lagi.';
    } elseif ($email === '' || $password === '') {
        $error = 'Email/username dan password wajib diisi.';
    } else {
        // Bisa login memakai email ATAU username (akun calon siswa dari
        // pendaftaran PPDB otomatis memakai username).
        $stmt = $pdo->prepare('SELECT id, name, email, password, role, is_active FROM users WHERE email = ? OR username = ? LIMIT 1');
        $stmt->execute([$email, $email]);
        $user = $stmt->fetch();

        // Dukung password yang sudah di-hash (password_hash) maupun akun demo lama
        // yang passwordnya masih plain text — supaya akun lama tetap bisa login,
        // sekaligus otomatis meng-upgrade akun lama itu ke hash begitu berhasil login.
        $passwordOk = false;
        if ($user) {
            $looksHashed = (bool) preg_match('/^\$2y\$/', $user['password']);
            if ($looksHashed) {
                $passwordOk = password_verify($password, $user['password']);
            } else {
                $passwordOk = hash_equals($user['password'], $password);
                if ($passwordOk) {
                    $upgrade = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
                    $upgrade->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
                }
            }
        }

        if (!$user || !$passwordOk) {
            // Alasan sebenarnya hanya dicatat di log server (bukan ditampilkan) untuk membantu admin menelusuri masalah login.
            error_log('[LOGIN] gagal untuk "' . substr($email, 0, 60) . '": ' . (!$user ? 'akun tidak ditemukan' : 'password tidak cocok'));
            $error = 'Email/username atau password salah.';
        } elseif (!$user['is_active']) {
            error_log('[LOGIN] akun nonaktif: ' . substr($email, 0, 60));
            $error = 'Akun ini dinonaktifkan. Hubungi admin sekolah.';
        } else {
            // Login sukses — simpan session, JANGAN simpan password.
            // ID sesi lama dibuang total (delete_old_session) supaya akun yang baru
            // login tidak pernah mewarisi data sesi dari akun sebelumnya di browser ini.
            require_once __DIR__ . '/includes/ppdb-helper.php';
            ppdb_sync_login_data($pdo, (int)$user['id'], (string)$user['role']); // siswa/wali PPDB: data masuk ke dashboard admin saat login pertama
            session_regenerate_id(true);
            $_SESSION = [];
            $_SESSION['user'] = [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $user['role'],
            ];
            header('Location: ' . ($ROLE_DASHBOARD[$user['role']] ?? 'index.php'));
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Login — SMK Infokom</title>
<meta name="description" content="Masuk ke Sistem Informasi Sekolah SMK Infokom.">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<script src="js/page-transition.js"></script>
</head>
<body>

<div class="login-shell">
  <div class="login-media" style="background-image:url('https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1200&q=80');">
    <div class="login-media-content">
      <a href="index.php" class="brand" style="color:var(--white);">
        <span class="brand-mark"><img src="assets/images/logo-infokom.png" alt="Logo SMK Infokom" loading="lazy"></span>
        <span class="brand-text"><strong style="color:var(--white);">SMK INFOKOM</strong><span style="color:rgba(255,255,255,0.7);">BOGOR</span></span>
      </a>
      <blockquote>
        "Pendidikan adalah bekal terbaik untuk masa depan. Sistem ini membantu kami mengelola sekolah dengan lebih rapi dan efisien."
        <cite>— Dr. Bambang Wijaya, M.Pd., Kepala Sekolah</cite>
      </blockquote>
    </div>
  </div>

  <div class="login-panel">
    <div class="login-form-wrap">
      <a href="index.php" class="login-back">← Kembali ke Beranda</a>
      <div class="brand" style="margin-bottom:28px;">
        <span class="brand-mark"><img src="assets/images/logo-infokom.png" alt="Logo SMK Infokom" loading="lazy"></span>
        <span class="brand-text"><strong>SMK INFOKOM</strong><span>BOGOR</span></span>
      </div>
      <h1>Selamat Datang Kembali</h1>
      <p>Pilih peran Anda sebagai panduan, lalu masuk dengan email atau username beserta password akun Anda.</p>

      <?php if ($sesiAktif): ?>
        <div class="login-disclaimer" style="background:#fff8e1;color:#8a6100;margin-bottom:20px;display:block;">
          <strong>Anda sedang login sebagai <?php echo htmlspecialchars($sesiAktif['name']); ?> (<?php echo htmlspecialchars($sesiAktif['role']); ?>).</strong><br>
          <span style="font-size:13px;">Untuk masuk dengan akun lain, isi form di bawah — sesi sebelumnya akan diganti.</span><br>
          <a href="<?php echo htmlspecialchars($sesiDashboard); ?>" class="btn btn-ghost btn-sm" style="margin-top:8px;">Lanjut ke dashboard</a>
        </div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="login-disclaimer" style="background:#fdecea;color:#b3261e;margin-bottom:20px;">
          <span>⚠️</span>
          <span><?php echo htmlspecialchars($error); ?></span>
        </div>
      <?php endif; ?>

      <div class="filter-chips" id="loginRoleTabs" style="margin-bottom:24px;">
        <button type="button" class="chip is-active" data-role="admin">Admin</button>
        <button type="button" class="chip" data-role="siswa">Siswa</button>
        <button type="button" class="chip" data-role="ortu">Orang Tua</button>
      </div>

      <form id="loginForm" method="POST" action="login.php" novalidate><?php echo csrf_field(); ?>
        <div class="form-group">
          <label for="loginEmail">Email atau Username</label>
          <input type="text" name="email" id="loginEmail" class="form-control" placeholder="Email atau username" autocapitalize="none" spellcheck="false" autocomplete="username" value="<?php echo htmlspecialchars($oldEmail); ?>" required>
          <span class="form-error" id="loginEmailError">Masukkan email atau username Anda.</span>
        </div>
        <div class="form-group">
          <label for="loginPassword">Password</label>
          <div class="password-field">
            <input type="password" name="password" id="loginPassword" class="form-control" placeholder="Masukkan password" autocomplete="current-password" required>
            <button type="button" class="password-toggle">Tampilkan</button>
          </div>
          <span class="form-error" id="loginPasswordError">Password minimal 6 karakter.</span>
        </div>

        <div class="login-options">
          <label class="checkbox-row">
            <input type="checkbox" id="rememberMe">
            <span>Ingat saya</span>
          </label>
          <a href="lupa-password.php">Lupa Password?</a>
        </div>

        <button type="submit" class="btn btn-primary btn-block" id="loginSubmit">Login</button>

        <div style="text-align:center;margin-top:16px;color:var(--muted);font-size:13.5px;">
          Belum punya akun? <a href="register.php">Buat akun baru</a>
        </div>
      </form>

      <div class="login-disclaimer">
        <span>ℹ️</span>
        <span>Akun bisa dibuat sebagai <strong>Siswa</strong> atau <strong>Orang Tua</strong>. Untuk akun Admin, hubungi pihak sekolah.</span>
      </div>
    </div>
  </div>
</div>

<script src="js/script.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    var roleTabs = document.querySelectorAll("#loginRoleTabs .chip");
    var emailInput = document.getElementById("loginEmail");
    var placeholders = {
      admin: "admin@smkinfokom.sch.id",
      siswa: "jidan.iskandar@siswa.smkinfokom.sch.id",
      ortu: "iskandar@wali.smkinfokom.sch.id"
    };
    roleTabs.forEach(function (tab) {
      tab.addEventListener("click", function () {
        roleTabs.forEach(function (t) { t.classList.remove("is-active"); });
        tab.classList.add("is-active");
        var role = tab.getAttribute("data-role");
        emailInput.placeholder = placeholders[role];
      });
    });
  });
</script>
</body>
</html>
