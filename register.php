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

if (!empty($_SESSION['user'])) {
    header('Location: ' . ($ROLE_DASHBOARD[$_SESSION['user']['role']] ?? 'index.php'));
    exit;
}

$error = '';
$old = ['name' => '', 'email' => '', 'role' => 'siswa'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    $role = $_POST['role'] ?? '';

    $old = ['name' => $name, 'email' => $email, 'role' => $role ?: 'siswa'];

    if (!csrf_valid()) {
        $error = 'Sesi formulir kedaluwarsa. Silakan muat ulang halaman lalu coba lagi.';
    } elseif ($name === '' || $email === '' || $password === '' || $role === '') {
        $error = 'Semua kolom wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi password tidak sama.';
    } elseif (!in_array($role, ['siswa', 'ortu'], true)) {
        $error = 'Silakan pilih peran sebagai Siswa atau Orang Tua.';
    } else {
        $check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = 'Email tersebut sudah terdaftar. Silakan login atau pakai email lain.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);

            // Akun baru harus selalu terpisah total dari akun mana pun yang mungkin
            // pernah login di browser ini: ganti ID sesi dan buang seluruh isi
            // $_SESSION lama sebelum menulis data akun yang baru saja dibuat.
            $newId = (int)$pdo->lastInsertId();
            session_regenerate_id(true);
            $_SESSION = [];
            $_SESSION['user'] = ['id' => $newId, 'name' => $name, 'email' => $email, 'role' => $role];
            header('Location: ' . $ROLE_DASHBOARD[$role]);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Buat Akun — SMK Infokom</title>
<meta name="description" content="Buat akun baru sebagai siswa atau orang tua di Sistem Informasi SMK Infokom.">
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
        "Website ini membantu siswa dan orang tua memantau nilai, absensi, dan informasi sekolah kapan saja."
      </blockquote>
    </div>
  </div>

  <div class="login-panel">
    <div class="login-form-wrap">
      <a href="login.php" class="login-back">← Kembali ke Login</a>
      <div class="brand" style="margin-bottom:28px;">
        <span class="brand-mark"><img src="assets/images/logo-infokom.png" alt="Logo SMK Infokom" loading="lazy"></span>
        <span class="brand-text"><strong>SMK INFOKOM</strong><span>BOGOR</span></span>
      </div>
      <h1>Buat Akun Baru</h1>
      <p>Daftar sebagai siswa atau orang tua untuk mengakses dashboard sekolah.</p>

      <?php if ($error): ?>
        <div class="login-disclaimer" style="background:#fdecea;color:#b3261e;margin-bottom:20px;">
          <span>⚠️</span>
          <span><?php echo htmlspecialchars($error); ?></span>
        </div>
      <?php endif; ?>

      <div class="filter-chips" id="registerRoleTabs" style="margin-bottom:24px;">
        <button type="button" class="chip <?php echo $old['role'] === 'siswa' ? 'is-active' : ''; ?>" data-role="siswa">Siswa</button>
        <button type="button" class="chip <?php echo $old['role'] === 'ortu' ? 'is-active' : ''; ?>" data-role="ortu">Orang Tua</button>
      </div>

      <form id="registerForm" method="POST" action="register.php" novalidate><?php echo csrf_field(); ?>
        <input type="hidden" name="role" id="registerRole" value="<?php echo htmlspecialchars($old['role']); ?>">

        <div class="form-group">
          <label for="regName">Nama Lengkap</label>
          <input type="text" name="name" id="regName" class="form-control" placeholder="Nama lengkap" value="<?php echo htmlspecialchars($old['name']); ?>" required>
        </div>
        <div class="form-group">
          <label for="regEmail">Email</label>
          <input type="email" name="email" id="regEmail" class="form-control" placeholder="nama@email.com" autocomplete="username" value="<?php echo htmlspecialchars($old['email']); ?>" required>
        </div>
        <div class="form-group">
          <label for="regPassword">Password</label>
          <div class="password-field">
            <input type="password" name="password" id="regPassword" class="form-control" placeholder="Minimal 6 karakter" minlength="6" autocomplete="new-password" required>
            <button type="button" class="password-toggle">Tampilkan</button>
          </div>
        </div>
        <div class="form-group">
          <label for="regConfirm">Konfirmasi Password</label>
          <input type="password" name="confirm" id="regConfirm" class="form-control" placeholder="Ulangi password" minlength="6" autocomplete="new-password" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Buat Akun</button>
      </form>

      <div class="login-disclaimer">
        <span>ℹ️</span>
        <span>Sudah punya akun? <a href="login.php">Login di sini</a>.</span>
      </div>
    </div>
  </div>
</div>

<script src="js/script.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    var roleTabs = document.querySelectorAll("#registerRoleTabs .chip");
    var roleInput = document.getElementById("registerRole");
    roleTabs.forEach(function (tab) {
      tab.addEventListener("click", function () {
        roleTabs.forEach(function (t) { t.classList.remove("is-active"); });
        tab.classList.add("is-active");
        roleInput.value = tab.getAttribute("data-role");
      });
    });

    var form = document.getElementById("registerForm");
    form.addEventListener("submit", function (e) {
      var pw = document.getElementById("regPassword").value;
      var confirm = document.getElementById("regConfirm").value;
      if (pw !== confirm) {
        e.preventDefault();
        alert("Konfirmasi password tidak sama.");
      }
    });
  });
</script>
</body>
</html>
