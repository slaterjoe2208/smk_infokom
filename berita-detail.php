<?php
require_once __DIR__ . '/includes/session-init.php';
require_once __DIR__ . '/includes/session-widget.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/settings-helper.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM berita WHERE id = ?');
$stmt->execute([$id]);
$berita = $stmt->fetch();

$imgUrl = ($berita && $berita['foto']) ? 'uploads/berita/' . htmlspecialchars($berita['foto']) : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title><?php echo $berita ? htmlspecialchars($berita['judul']) . ' — SMK Infokom' : 'Berita Tidak Ditemukan — SMK Infokom'; ?></title>
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
      <a href="bkk.php">BKK</a>
      <a href="pkl.php">PKL</a>
      <a href="berita.php" class="active">Info &amp; Berita</a>
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
    <a href="berita.php">Info &amp; Berita</a>
    <a href="kontak.php">Kontak</a>
    <a href="ppdb-daftar.php" class="btn btn-accent mobile-cta" style="width:100%;text-align:center;margin-top:12px;">Daftar PPDB</a>
    <?php echo smkn1_navbar_session_html('mobile'); ?>
  </div>
</div>

<main id="main">
  <?php if (!$berita): ?>
  <section class="section">
    <div class="container" style="text-align:center;padding:60px 0;">
      <h1>Berita Tidak Ditemukan</h1>
      <p style="color:var(--muted);">Berita yang Anda cari tidak tersedia atau sudah dihapus.</p>
      <a href="berita.php" class="btn btn-primary" style="margin-top:16px;">Kembali ke Berita</a>
    </div>
  </section>
  <?php else: ?>
  <section class="section">
    <div class="container" style="max-width:820px;">
      <p class="breadcrumb"><a href="index.php">Beranda</a> / <a href="berita.php">Berita</a> / <?php echo htmlspecialchars($berita['judul']); ?></p>
      <h1 style="margin-top:12px;"><?php echo htmlspecialchars($berita['judul']); ?></h1>
      <div class="news-meta" style="margin:14px 0 24px;">
        <span>📅 <?php echo date('d F Y', strtotime($berita['tanggal'])); ?></span>
        <span>👤 Admin</span>
      </div>
      <?php if ($imgUrl): ?>
      <div class="editorial-media" style="margin-top:0;margin-bottom:28px;">
        <img src="<?php echo $imgUrl; ?>" alt="<?php echo htmlspecialchars($berita['judul']); ?>" loading="lazy">
      </div>
      <?php endif; ?>
      <div style="color:var(--text);line-height:1.85;text-align:justify;white-space:pre-line;">
        <?php echo nl2br(htmlspecialchars($berita['isi'])); ?>
      </div>
      <div style="margin-top:40px;">
        <a href="berita.php" class="btn btn-ghost">← Kembali ke Semua Berita</a>
      </div>
    </div>
  </section>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/includes/cta-band.php'; ?>
<footer class="site-footer">
  <div class="container footer-top">
    <div class="footer-brand">
      <a href="index.php" class="brand" style="color:var(--white);">
        <span class="brand-mark"><img src="assets/images/logo-infokom.png" alt="Logo SMK Infokom" loading="lazy"></span>
        <span class="brand-text"><strong>SMK INFOKOM</strong><span>KOTA BOGOR</span></span>
      </a>
      <p>Sekolah menengah kejuruan yang membentuk generasi unggul, berkarakter, dan siap bersaing di dunia kerja maupun dunia industri.</p>
    </div>
    <div class="footer-col">
      <h4>Tentang Sekolah</h4>
      <ul>
        <li><a href="profil.php">Profil</a></li>
        <li><a href="akademik-info.php">Akademik</a></li>
        <li><a href="berita.php">Berita</a></li>
        <li><a href="ppdb.php">PPDB</a></li>
        <li><a href="kontak.php">Kontak</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Link Penting</h4>
      <ul>
        <li><a href="akademik-info.php#panel-kurikulum">Kurikulum</a></li>
        <li><a href="akademik-info.php#panel-tatatertib">Peraturan Sekolah</a></li>
      </ul>
    </div>
    <div class="footer-col footer-contact">
      <h4>Kontak</h4>
      <ul>
        <li>📍Jl. Letjen Ibrahim Adjie No.178, RT.03/RW.08, Sindangbarang, Kec. Bogor Barat., Kota Bogor, Jawa Barat 16117</li>
        <li>📞 0878-7307-1400 (Humas SMK INFOKOM)</li>
        <li>✉️ smk.infokom@gmail.com</li>
        <li>🕐 Senin – Jumat, 06.30 – 15.30</li>
      </ul>
    </div>
    <div class="footer-col footer-social">
      <h4>Ikuti Kami</h4>
      <div class="social-icons" style="display:flex;gap:10px;margin-top:4px;">
        <?php echo smkn1_social_icons_html($pdo, 'display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:50%;background:rgba(255,255,255,0.08);color:var(--white);'); ?>
      </div>
    </div>
  </div>
  <?php require __DIR__ . '/includes/footer-map.php'; ?>
  <div class="container footer-bottom">
    <span>© 2026 SMK INFOKOM Kota Bogor. All rights reserved.</span>
    <span>Sistem Informasi Sekolah</span>
  </div>
</footer>

<button class="back-to-top" aria-label="Kembali ke atas">↑</button>
<script src="js/script.js"></script>
</body>
</html>
