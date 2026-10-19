<?php
require_once __DIR__ . '/includes/session-init.php';
require_once __DIR__ . '/includes/session-widget.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/settings-helper.php';

$hideFooterMap = true; // peta sudah tampil di halaman ini
$kontakErrors = [];
$old = ['nama' => '', 'email' => '', 'subjek' => '', 'pesan' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'kirim_pesan_kontak') {
    foreach ($old as $k => $_) $old[$k] = trim($_POST[$k] ?? '');

    if (!csrf_valid()) {
        $kontakErrors[] = 'Sesi formulir kedaluwarsa. Silakan muat ulang halaman lalu coba lagi.';
    } elseif ($old['nama'] === '' || $old['subjek'] === '' || $old['pesan'] === '') {
        $kontakErrors[] = 'Nama, subjek, dan pesan wajib diisi.';
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $kontakErrors[] = 'Email tidak valid.';
    } else {
        $pdo->prepare('INSERT INTO pesan_kontak (nama, email, subjek, pesan) VALUES (?, ?, ?, ?)')
            ->execute([$old['nama'], $old['email'], $old['subjek'], $old['pesan']]);
        // Post-Redirect-Get: refresh tidak mengirim ulang pesan.
        header('Location: kontak.php?pesan_terkirim=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Kontak — SMK Infokom</title>
<meta name="description" content="Hubungi SMK Infokom Kota Bogor: kirim pesan dan lihat lokasi sekolah di peta.">
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
      <div class="nav-item">
        <a href="profil.php" class="nav-toggle">Profil <span class="nav-caret" aria-hidden="true">&#9662;</span></a>
        <div class="nav-dropdown">
          <div class="nav-dropdown-inner">
            <a href="profil.php#panel-sejarah">Sejarah Sekolah</a>
            <a href="profil.php#panel-visi">Visi &amp; Misi</a>
            <a href="profil.php#panel-sambutan">Sambutan Kepala Sekolah</a>
            <a href="profil.php#panel-struktur">Struktur Organisasi</a>
            <a href="profil.php#panel-guru">Guru &amp; Tenaga Kependidikan</a>
          </div>
        </div>
      </div>
      <div class="nav-item">
        <a href="akademik-info.php" class="nav-toggle">Akademik <span class="nav-caret" aria-hidden="true">&#9662;</span></a>
        <div class="nav-dropdown">
          <div class="nav-dropdown-inner">
            <a href="akademik-info.php#panel-tatatertib">Tata Tertib</a>
            <a href="akademik-info.php#panel-kurikulum">Kurikulum</a>
            <a href="akademik-info.php#panel-program">Jurusan</a>
            <a href="akademik-info.php#panel-kalender">Kalender Akademik</a>
            <a href="akademik-info.php#panel-ekskul">Ekstrakurikuler</a>
            <a href="akademik-info.php#panel-fasilitas">Fasilitas Sekolah</a>
          </div>
        </div>
      </div>
      <a href="ppdb.php">PPDB</a>
      <div class="nav-item">
        <a href="bkk.php" class="nav-toggle">BKK <span class="nav-caret" aria-hidden="true">&#9662;</span></a>
        <div class="nav-dropdown">
          <div class="nav-dropdown-inner">
            <a href="bkk.php">Lowongan Pekerjaan</a>
            <a href="bkk.php#panel-magang-jepang">Program Industri Magang Jepang</a>
            <a href="bkk.php#panel-kerjasama">Kerja Sama Sekolah</a>
          </div>
        </div>
      </div>
      <a href="pkl.php">PKL</a>
      <div class="nav-item">
        <a href="berita.php" class="nav-toggle">Info &amp; Berita <span class="nav-caret" aria-hidden="true">&#9662;</span></a>
        <div class="nav-dropdown">
          <div class="nav-dropdown-inner">
            <a href="berita.php">Semua Info</a>
            <a href="berita.php#panel-berita">Berita</a>
            <a href="berita.php#panel-galeri">Galeri Kegiatan</a>
            <a href="berita.php#panel-prestasi">Prestasi Siswa</a>
            <a href="berita.php#panel-konten">Konten Sekolah</a>
          </div>
        </div>
      </div>
      <a href="kontak.php" class="active">Kontak</a>
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
    <div class="mobile-dropdown">
      <button class="mobile-dropdown-toggle" aria-expanded="false">Profil <span class="caret" aria-hidden="true">&#9662;</span></button>
      <div class="mobile-submenu">
        <a href="profil.php#panel-sejarah">Sejarah Sekolah</a>
        <a href="profil.php#panel-visi">Visi &amp; Misi</a>
        <a href="profil.php#panel-sambutan">Sambutan Kepala Sekolah</a>
        <a href="profil.php#panel-struktur">Struktur Organisasi</a>
        <a href="profil.php#panel-guru">Guru &amp; Tenaga Kependidikan</a>
      </div>
    </div>
    <div class="mobile-dropdown">
      <button class="mobile-dropdown-toggle" aria-expanded="false">Akademik <span class="caret" aria-hidden="true">&#9662;</span></button>
      <div class="mobile-submenu">
        <a href="akademik-info.php#panel-tatatertib">Tata Tertib</a>
        <a href="akademik-info.php#panel-kurikulum">Kurikulum</a>
        <a href="akademik-info.php#panel-program">Jurusan</a>
        <a href="akademik-info.php#panel-kalender">Kalender Akademik</a>
        <a href="akademik-info.php#panel-ekskul">Ekstrakurikuler</a>
        <a href="akademik-info.php#panel-fasilitas">Fasilitas Sekolah</a>
      </div>
    </div>
    <a href="ppdb.php">PPDB</a>
    <div class="mobile-dropdown">
      <button class="mobile-dropdown-toggle" aria-expanded="false">BKK <span class="caret" aria-hidden="true">&#9662;</span></button>
      <div class="mobile-submenu">
        <a href="bkk.php">Lowongan Pekerjaan</a>
        <a href="bkk.php#panel-magang-jepang">Program Industri Magang Jepang</a>
        <a href="bkk.php#panel-kerjasama">Kerja Sama Sekolah</a>
      </div>
    </div>
    <a href="pkl.php">PKL</a>
    <div class="mobile-dropdown">
      <button class="mobile-dropdown-toggle" aria-expanded="false">Info &amp; Berita <span class="caret" aria-hidden="true">&#9662;</span></button>
      <div class="mobile-submenu">
        <a href="berita.php">Semua Info</a>
        <a href="berita.php#panel-berita">Berita</a>
        <a href="berita.php#panel-galeri">Galeri Kegiatan</a>
        <a href="berita.php#panel-prestasi">Prestasi Siswa</a>
        <a href="berita.php#panel-konten">Konten Sekolah</a>
      </div>
    </div>
    <a href="kontak.php" class="active">Kontak</a>
    <a href="ppdb-daftar.php" class="btn btn-accent mobile-cta" style="width:100%;text-align:center;margin-top:12px;">Daftar PPDB</a>
    <?php echo smkn1_navbar_session_html('mobile'); ?>
  </div>
</div>

<main id="main">

  <section class="bkk-hero">
    <div class="container">
      <h1>Hubungi Kami</h1>
      <p class="breadcrumb"><a href="index.php">Beranda</a> / <b>Kontak</b></p>
      <p class="lead">Ada pertanyaan seputar sekolah, PPDB, atau kerja sama? Kirim pesan kepada kami atau kunjungi langsung sekolah kami.</p>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <?php if (isset($_GET['pesan_terkirim'])): ?>
      <div class="login-disclaimer" style="background:#e8f5e9;color:#1b5e20;margin-bottom:24px;"><span>✅</span><span>Pesan Anda berhasil dikirim. Terima kasih, kami akan segera menghubungi Anda kembali.</span></div>
      <?php endif; ?>
      <?php foreach ($kontakErrors as $err): ?>
      <div class="login-disclaimer" style="background:#fdecea;color:#b3261e;margin-bottom:24px;"><span>⚠️</span><span><?php echo e($err); ?></span></div>
      <?php endforeach; ?>

      <div class="kontak-grid reveal">
        <div class="card card-pad">
          <h3 style="margin-bottom:16px;">Kirim Pesan</h3>
          <form method="POST" action="kontak.php"><?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="kirim_pesan_kontak">
            <div class="form-row">
              <div class="form-group"><label for="kontakNama">Nama</label><input type="text" id="kontakNama" name="nama" class="form-control" required value="<?php echo e($old['nama']); ?>"></div>
              <div class="form-group"><label for="kontakEmail">Email</label><input type="email" id="kontakEmail" name="email" class="form-control" required value="<?php echo e($old['email']); ?>"></div>
            </div>
            <div class="form-group"><label for="kontakSubjek">Subjek</label><input type="text" id="kontakSubjek" name="subjek" class="form-control" required value="<?php echo e($old['subjek']); ?>"></div>
            <div class="form-group"><label for="kontakPesan">Pesan</label><textarea id="kontakPesan" name="pesan" class="form-control" rows="7" required><?php echo e($old['pesan']); ?></textarea></div>
            <button type="submit" class="btn btn-primary">Kirim Pesan</button>
          </form>
        </div>
        <div class="kontak-map">
          <iframe title="Lokasi SMK Infokom" src="<?php echo htmlspecialchars(smkn1_map_embed_url($pdo)); ?>" loading="lazy"></iframe>
        </div>
      </div>
    </div>
  </section>

</main>

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
        <li>📍Jl. Letjen Ibrahim Adjie No.178, RT.03/RW.08, Sindangbarang, Kec. Bogor Barat.,
Kota Bogor, Jawa Barat 16117</li>
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
