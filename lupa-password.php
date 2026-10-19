<?php
// Lupa password: pengunjung mengisi username + nama, lalu diarahkan ke WhatsApp admin sekolah dengan pesan
// yang sudah terisi. Admin kemudian menekan tombol "Password" di menu Siswa / Orang Tua untuk membuat
// password baru dan mengirimkannya lewat WhatsApp. Tidak ada data yang disimpan di halaman ini.
require_once __DIR__ . '/includes/session-init.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/settings-helper.php';
require_once __DIR__ . '/includes/ppdb-helper.php';

$error = '';
$waNumber = smkn1_whatsapp_number($pdo);
$waTarget = $waNumber ? normalize_wa($waNumber) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_text($_POST['username'] ?? '', 150);
    $nama = clean_text($_POST['nama'] ?? '', 150);
    $peran = ($_POST['peran'] ?? '') === 'ortu' ? 'orang tua/wali' : 'siswa';
    if (!csrf_valid()) {
        $error = 'Sesi formulir kedaluwarsa. Muat ulang halaman lalu coba lagi.';
    } elseif ($username === '' || $nama === '') {
        $error = 'Username dan nama lengkap wajib diisi.';
    } elseif (!$waTarget) {
        $error = 'Nomor WhatsApp sekolah belum diatur. Silakan datang langsung atau hubungi sekolah.';
    } else {
        $pesan = "Halo admin SMK Infokom, saya lupa password akun website sekolah.\n\n"
               . "Nama: " . $nama . "\nPeran: " . $peran . "\nUsername/email: " . $username . "\n\n"
               . "Mohon bantu reset password saya. Terima kasih.";
        header('Location: https://wa.me/' . $waTarget . '?text=' . rawurlencode($pesan));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Lupa Password — SMK Infokom</title>
<meta name="robots" content="noindex">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<script src="js/page-transition.js"></script>
</head>
<body>
<div class="login-shell" style="grid-template-columns:1fr;">
  <div class="login-panel">
    <div class="login-form-wrap">
      <a href="login.php" class="login-back">← Kembali ke Login</a>
      <div class="brand" style="margin-bottom:28px;">
        <span class="brand-mark"><img src="assets/images/logo-infokom.png" alt="Logo SMK Infokom" loading="lazy"></span>
        <span class="brand-text"><strong>SMK INFOKOM</strong><span>BOGOR</span></span>
      </div>
      <h1>Lupa Password</h1>
      <p>Isi data di bawah, lalu kirim permintaan ke admin sekolah lewat WhatsApp. Admin akan membuat password baru dan mengirimkannya kepada Anda.</p>

      <?php if ($error): ?>
        <div class="login-disclaimer" style="background:#fdecea;color:#b3261e;margin-bottom:20px;"><span>⚠️</span><span><?php echo htmlspecialchars($error); ?></span></div>
      <?php endif; ?>

      <form method="POST" action="lupa-password.php"><?php echo csrf_field(); ?>
        <div class="form-group">
          <label for="lpPeran">Saya adalah</label>
          <select name="peran" id="lpPeran" class="form-control"><option value="siswa">Siswa</option><option value="ortu">Orang Tua / Wali</option></select>
        </div>
        <div class="form-group">
          <label for="lpNama">Nama lengkap</label>
          <input type="text" name="nama" id="lpNama" class="form-control" maxlength="150" required>
        </div>
        <div class="form-group">
          <label for="lpUser">Username atau email akun</label>
          <input type="text" name="username" id="lpUser" class="form-control" maxlength="150" autocapitalize="none" spellcheck="false" required>
        </div>
        <button type="submit" class="btn btn-wa btn-block">💬 Kirim Permintaan lewat WhatsApp</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
