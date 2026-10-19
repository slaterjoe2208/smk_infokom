<?php
require_once __DIR__ . '/includes/session-init.php';
require_once __DIR__ . '/includes/session-widget.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/settings-helper.php';
// Halaman detail jurusan — menampilkan SATU jurusan per halaman (lewat ?j=kode),
// datanya diambil dari database (dikelola admin lewat admin/jurusan.php).

$rows = $pdo->query('SELECT * FROM jurusan ORDER BY nama')->fetchAll();
$jurusanData = [];
foreach ($rows as $row) {
    $key = strtolower($row['kode']);
    $jurusanData[$key] = [
        'nama' => $row['nama'],
        'singkatan' => $row['kode'],
        'gambar' => $row['gambar'] ? 'uploads/jurusan/' . $row['gambar'] : 'https://ui-avatars.com/api/?name=' . urlencode($row['kode']) . '&background=062B5C&color=fff&size=512',
        'deskripsi' => $row['deskripsi'],
        'mapel' => array_values(array_filter(array_map('trim', explode("\n", (string)$row['mapel'])))),
        'prospek' => array_values(array_filter(array_map('trim', explode("\n", (string)$row['prospek'])))),
        'keunggulan' => array_values(array_filter(array_map('trim', explode("\n", (string)($row['keunggulan'] ?? ''))))),
        'kegiatan' => array_values(array_filter(array_map('trim', explode("\n", (string)($row['kegiatan'] ?? ''))))),
        'fasilitas' => array_values(array_filter(array_map('trim', explode("\n", (string)($row['fasilitas'] ?? ''))))),
    ];
}

if (!$jurusanData) {
    // belum ada data jurusan sama sekali di database
    $slug = null;
} else {
    $slug = $_GET['j'] ?? array_key_first($jurusanData);
    if (!isset($jurusanData[$slug])) {
        $slug = array_key_first($jurusanData);
    }
}
$j = $slug ? $jurusanData[$slug] : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($j['nama']); ?> — SMK Infokom</title>
<meta name="description" content="Informasi jurusan <?php echo htmlspecialchars($j['nama']); ?> di SMK Infokom.">
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
        <a href="akademik-info.php" class="nav-toggle active">Akademik <span class="nav-caret" aria-hidden="true">&#9662;</span></a>
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
    <a href="kontak.php">Kontak</a>
    <a href="ppdb-daftar.php" class="btn btn-accent mobile-cta" style="width:100%;text-align:center;margin-top:12px;">Daftar PPDB</a>
    <?php echo smkn1_navbar_session_html('mobile'); ?>
  </div>
</div>

<main id="main">
  <?php if (!$j): ?>
  <section class="section">
    <div class="container" style="text-align:center;padding:60px 0;">
      <h1>Data Jurusan Belum Tersedia</h1>
      <p style="color:var(--muted);">Admin belum menambahkan data jurusan. Silakan cek kembali nanti.</p>
    </div>
  </section>
  <?php else: ?>
  <section class="page-hero">
    <div class="container">
      <h1><?php echo htmlspecialchars($j['nama']); ?></h1>
      <p class="breadcrumb"><a href="index.php">Beranda</a> / <a href="akademik-info.php#panel-program">Jurusan</a> / <?php echo htmlspecialchars($j['singkatan']); ?></p>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <!-- Pilih jurusan lain -->
      <nav class="jurusan-switch" aria-label="Pilih jurusan">
        <?php foreach ($jurusanData as $key => $item): ?>
          <a href="jurusan.php?j=<?php echo urlencode($key); ?>" class="<?php echo $key === $slug ? 'is-active' : ''; ?>"><?php echo htmlspecialchars($item['singkatan']); ?></a>
        <?php endforeach; ?>
      </nav>

      <div class="editorial-flex">
        <div>
          <span class="badge badge-info program-kode-badge"><?php echo htmlspecialchars($j['singkatan']); ?></span>
          <h3><?php echo htmlspecialchars($j['nama']); ?></h3>
          <p><?php echo htmlspecialchars($j['deskripsi']); ?></p>

          <?php if ($j['keunggulan']): ?>
          <h3 style="margin-top:32px;">Keunggulan Program</h3>
          <ul class="check-list">
            <?php foreach ($j['keunggulan'] as $poin): ?>
              <li><span class="check-ic" aria-hidden="true">✓</span><?php echo htmlspecialchars($poin); ?></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>

          <?php if ($j['mapel']): ?>
          <h3 style="margin-top:32px;">Mata Pelajaran Produktif</h3>
          <ul style="margin-bottom:var(--sp-4);line-height:1.9;color:var(--muted);padding-left:20px;">
            <?php foreach ($j['mapel'] as $mapel): ?>
              <li><?php echo htmlspecialchars($mapel); ?></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>

          <?php if ($j['kegiatan']): ?>
          <h3 style="margin-top:32px;">Kegiatan Siswa</h3>
          <ul style="margin-bottom:var(--sp-4);line-height:1.9;color:var(--muted);padding-left:20px;">
            <?php foreach ($j['kegiatan'] as $keg): ?>
              <li><?php echo htmlspecialchars($keg); ?></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>

          <?php if ($j['fasilitas']): ?>
          <h3 style="margin-top:32px;">Fasilitas Pendukung</h3>
          <ul style="margin-bottom:var(--sp-4);line-height:1.9;color:var(--muted);padding-left:20px;">
            <?php foreach ($j['fasilitas'] as $fas): ?>
              <li><?php echo htmlspecialchars($fas); ?></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>

          <?php if ($j['prospek']): ?>
          <h3 style="margin-top:32px;">Prospek Karir</h3>
          <ul style="margin-bottom:var(--sp-4);line-height:1.9;color:var(--muted);padding-left:20px;">
            <?php foreach ($j['prospek'] as $prospek): ?>
              <li><?php echo htmlspecialchars($prospek); ?></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </div>
        <div class="editorial-media program-media" style="margin-top:0;">
          <img src="<?php echo htmlspecialchars($j['gambar']); ?>" alt="<?php echo htmlspecialchars($j['nama']); ?>" loading="lazy">
        </div>
      </div>

      <div class="program-cta reveal">
        <div>
          <strong>Tertarik dengan program <?php echo htmlspecialchars($j['singkatan']); ?>?</strong>
          <span>Daftar sekarang, tanpa perlu login — formulir hanya butuh beberapa menit.</span>
        </div>
        <a href="ppdb-daftar.php?jurusan=<?php echo urlencode($j['singkatan']); ?>" class="btn btn-accent">Daftar Sekarang →</a>
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
