<?php
// DINONAKTIFKAN: Program Sekolah dihapus dari menu BKK. Pendaftaran kini lewat halaman Lowongan Pekerjaan.
// Hapus 2 baris di bawah jika halaman ini ingin dipakai lagi.
header('Location: bkk.php', true, 301);
exit;

require_once __DIR__ . '/includes/session-init.php';
require_once __DIR__ . '/includes/session-widget.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/settings-helper.php';

$isLoggedIn = !empty($_SESSION['user']);
$errors = [];
$success = null;

$programOptions = ['Praktik Kerja Lapangan (PKL)', 'Magang Industri Mitra', 'Sertifikasi Kompetensi'];

if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $program = $_POST['program'] ?? '';
    $noHp = trim($_POST['no_hp'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');

    if ($nama === '' || !in_array($program, $programOptions, true)) {
        $errors[] = 'Nama dan pilihan program wajib diisi dengan benar.';
    } elseif (strlen(preg_replace('/\D/', '', $noHp)) < 10) {
        $errors[] = 'Nomor HP tidak valid (minimal 10 digit).';
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO program_magang (user_id, nama_lengkap, program_pilihan, no_hp, keterangan) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$_SESSION['user']['id'], $nama, $program, $noHp, $keterangan ?: null]);
        $newId = (int)$pdo->lastInsertId();
        $success = ['nama' => $nama, 'program' => $program, 'nomor' => 'MGG-' . date('Y') . '-' . str_pad((string)$newId, 4, '0', STR_PAD_LEFT)];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Daftar Program Magang — SMK Infokom</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<script src="js/page-transition.js"></script>
</head>
<body>
<a href="#main" class="skip-link">Lompat ke konten utama</a>

<?php require __DIR__ . '/includes/top-info.php'; ?>
<header class="site-header">
  <div class="container navbar">
    <a href="index.php" class="brand">
      <span class="brand-mark"><img src="assets/images/logo-infokom.png" alt="Logo SMK Infokom" loading="lazy"></span>
      <span class="brand-text"><strong>SMK INFOKOM</strong><span>KOTA BOGOR</span></span>
    </a>
    <nav class="nav-menu" aria-label="Navigasi utama">
      <a href="profil.php">Profil</a>
      <a href="akademik-info.php">Akademik</a>
      <a href="ppdb.php">PPDB</a>
      <a href="bkk.php" class="active">BKK</a>
      <a href="pkl.php">PKL</a>
      <a href="kontak.php">Kontak</a>
    </nav>
    <div class="navbar-actions">
      <a href="ppdb-daftar.php" class="btn btn-accent btn-sm nav-cta desktop-only">Daftar PPDB</a>
      <?php echo smkn1_navbar_session_html('desktop'); ?>
      <button class="hamburger" aria-label="Buka menu" aria-expanded="false"><span></span><span></span><span></span></button>
    </div>
  </div>
</header>

<div class="mobile-menu" aria-hidden="true">
  <div class="mobile-menu-overlay"></div>
  <div class="mobile-menu-panel">
    <button class="btn-icon mobile-menu-close" aria-label="Tutup menu">&#10005;</button>
    <a href="profil.php">Profil</a>
    <a href="akademik-info.php">Akademik</a>
    <a href="ppdb.php">PPDB</a>
    <a href="bkk.php">BKK</a>
    <a href="pkl.php">PKL</a>
    <a href="kontak.php">Kontak</a>
    <a href="ppdb-daftar.php" class="btn btn-accent mobile-cta" style="width:100%;text-align:center;margin-top:12px;">Daftar PPDB</a>
    <?php echo smkn1_navbar_session_html('mobile'); ?>
  </div>
</div>

<main id="main">
  <section class="page-hero">
    <div class="container">
      <h1>Formulir Program Sekolah / Magang</h1>
      <p class="breadcrumb"><a href="index.php">Beranda</a> / <a href="bkk.php#panel-kerjasama">Program Magang</a> / Daftar</p>
    </div>
  </section>

  <section class="section">
    <div class="container" style="max-width:680px;">

      <?php if (!$isLoggedIn): ?>
      <div class="card card-pad reveal" style="text-align:center;padding:var(--sp-12) var(--sp-6);">
        <div style="font-size:44px;margin-bottom:16px;">🔒</div>
        <h2 style="margin-bottom:8px;">Login Diperlukan</h2>
        <p class="text-muted" style="max-width:440px;margin:0 auto 24px;">Untuk mendaftar Program Sekolah / Magang, Anda harus login atau membuat akun terlebih dahulu supaya pendaftaran Anda tercatat.</p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
          <a href="register.php" class="btn btn-primary">Buat Akun</a>
          <a href="login.php" class="btn btn-ghost">Login</a>
        </div>
      </div>

      <?php elseif ($success): ?>
      <div class="card card-pad reveal" style="text-align:center;padding:var(--sp-12) var(--sp-6);">
        <div style="font-size:44px;margin-bottom:16px;">✅</div>
        <h2 style="margin-bottom:8px;">Pendaftaran Program Terkirim</h2>
        <p class="text-muted" style="max-width:440px;margin:0 auto 20px;">Terima kasih, <strong><?php echo htmlspecialchars($success['nama']); ?></strong>. Anda mendaftar program <strong><?php echo htmlspecialchars($success['program']); ?></strong>.</p>
        <p style="font-size:22px;font-weight:800;color:var(--primary);margin-bottom:20px;"><?php echo htmlspecialchars($success['nomor']); ?></p>
        <p class="text-muted" style="max-width:480px;margin:0 auto 28px;font-size:14px;">Data pendaftaran Anda sudah tersimpan menggunakan akun <strong><?php echo htmlspecialchars($_SESSION['user']['email']); ?></strong> dan akan diproses oleh pihak sekolah.</p>
        <a href="index.php" class="btn btn-primary">Kembali ke Beranda</a>
      </div>

      <?php else: ?>
      <div class="card card-pad reveal" style="margin-bottom:24px;">
        <p class="text-muted" style="font-size:14px;">Anda mengisi formulir sebagai <strong><?php echo htmlspecialchars($_SESSION['user']['name']); ?></strong> (<?php echo htmlspecialchars($_SESSION['user']['email']); ?>).</p>
      </div>

      <?php foreach ($errors as $err): ?>
        <div class="login-disclaimer" style="background:#fdecea;color:#b3261e;margin-bottom:20px;"><span>⚠️</span><span><?php echo htmlspecialchars($err); ?></span></div>
      <?php endforeach; ?>

      <form method="POST" class="card card-pad reveal"><?php echo csrf_field(); ?>
        <div class="form-group">
          <label for="mgNama">Nama Lengkap</label>
          <input type="text" name="nama" id="mgNama" class="form-control" value="<?php echo htmlspecialchars($_POST['nama'] ?? $_SESSION['user']['name']); ?>" required>
        </div>
        <div class="form-group">
          <label for="mgProgram">Program yang Dipilih</label>
          <select name="program" id="mgProgram" class="form-control" required>
            <option value="">Pilih program</option>
            <?php foreach ($programOptions as $opt): ?>
              <option value="<?php echo htmlspecialchars($opt); ?>"><?php echo htmlspecialchars($opt); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="mgHp">Nomor HP / WhatsApp</label>
          <input type="tel" name="no_hp" id="mgHp" class="form-control" placeholder="08xxxxxxxxxx" value="<?php echo htmlspecialchars($_POST['no_hp'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
          <label for="mgKeterangan">Keterangan Tambahan (opsional)</label>
          <textarea name="keterangan" id="mgKeterangan" class="form-control" rows="3" placeholder="mis. jurusan, minat bidang, atau catatan lain"><?php echo htmlspecialchars($_POST['keterangan'] ?? ''); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Kirim Pendaftaran</button>
      </form>
      <?php endif; ?>

    </div>
  </section>
</main>

<footer class="site-footer">
  <?php require __DIR__ . '/includes/footer-map.php'; ?>
  <div class="container footer-bottom">
    <span>© 2026 SMK INFOKOM Kota Bogor. All rights reserved.</span>
    <span>Sistem Informasi Sekolah</span>
  </div>
</footer>

<script src="js/script.js"></script>
</body>
</html>
