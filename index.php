<?php
require_once __DIR__ . '/includes/session-init.php';
require_once __DIR__ . '/includes/session-widget.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/settings-helper.php';

$jurusanList = $pdo->query('SELECT * FROM jurusan ORDER BY nama')->fetchAll();
$beritaList = $pdo->query('SELECT * FROM berita ORDER BY tanggal DESC, id DESC LIMIT 4')->fetchAll();
$galeriList = $pdo->query('SELECT * FROM galeri ORDER BY created_at DESC LIMIT 6')->fetchAll();
$infoPengumuman = (smkn1_has_column($pdo, 'berita', 'kategori') ? $pdo->query("SELECT judul, tanggal FROM berita WHERE kategori = 'Pengumuman' ORDER BY tanggal DESC, id DESC LIMIT 1")->fetch() : false)
    ?: $pdo->query('SELECT judul, tanggal FROM berita ORDER BY tanggal DESC, id DESC LIMIT 1')->fetch();
$infoAgenda = $pdo->query("SELECT nama_kegiatan, tanggal_mulai FROM kalender_akademik WHERE status = 'Aktif' AND COALESCE(tanggal_selesai, tanggal_mulai) >= CURDATE() ORDER BY tanggal_mulai LIMIT 1")->fetch();
if (!$infoAgenda) $infoAgenda = $pdo->query("SELECT nama_kegiatan, tanggal_mulai FROM kalender_akademik WHERE status = 'Aktif' ORDER BY tanggal_mulai DESC LIMIT 1")->fetch();
$infoPrestasi = $pdo->query('SELECT nama_prestasi, tahun FROM prestasi ORDER BY tahun DESC, id DESC LIMIT 1')->fetch();
$infoGaleri = $pdo->query('SELECT judul, created_at FROM galeri ORDER BY created_at DESC, id DESC LIMIT 1')->fetch();
$infoTgl = function ($d) { return $d ? date('d M Y', strtotime($d)) : ''; };
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>SMK INFOKOM — Sistem Informasi Sekolah</title>
<meta name="description" content="Website resmi SMK Infokom. Profil sekolah, akademik, berita, galeri kegiatan, dan penerimaan peserta didik baru.">
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

  <section class="hero" style="background-image:url('assets/images/GEDUNG.DEPAN.png');">
    <div class="container hero-inner">
      <span class="hero-eyebrow">Selamat Datang di</span>
      <h1>SMK INFOKOM KOTA BOGOR</h1>
      <p>Membentuk generasi unggul, berkarakter, siap kerja, dan berdaya saing global melalui pendidikan kejuruan yang berkualitas.</p>
      <div class="hero-actions">
        <a href="profil.php" class="btn btn-outline">Profil Sekolah</a>
        <a href="ppdb.php" class="btn btn-accent">PPDB Online</a>
      </div>
    </div>
    <div class="hero-dots"><span class="is-active"></span><span></span><span></span><span></span></div>
  </section>

  <section class="section">
    <div class="container">
      <div class="info-strip reveal">
        <a class="info-item" href="berita.php">
          <span class="info-icon">📢</span>
          <div>
            <h3>Pengumuman</h3>
            <p class="text-muted"><?php echo $infoPengumuman ? htmlspecialchars($infoPengumuman['judul']) : 'Belum ada pengumuman.'; ?></p>
            <?php if ($infoPengumuman): ?><small><?php echo $infoTgl($infoPengumuman['tanggal']); ?></small><?php endif; ?>
          </div>
        </a>
        <a class="info-item" href="akademik-info.php#panel-kalender">
          <span class="info-icon">📅</span>
          <div>
            <h3>Agenda</h3>
            <p class="text-muted"><?php echo $infoAgenda ? htmlspecialchars($infoAgenda['nama_kegiatan']) : 'Belum ada agenda.'; ?></p>
            <?php if ($infoAgenda): ?><small><?php echo $infoTgl($infoAgenda['tanggal_mulai']); ?></small><?php endif; ?>
          </div>
        </a>
        <a class="info-item" href="berita.php#panel-prestasi">
          <span class="info-icon">🏆</span>
          <div>
            <h3>Prestasi</h3>
            <p class="text-muted"><?php echo $infoPrestasi ? htmlspecialchars($infoPrestasi['nama_prestasi']) : 'Belum ada prestasi.'; ?></p>
            <?php if ($infoPrestasi && $infoPrestasi['tahun']): ?><small><?php echo (int)$infoPrestasi['tahun']; ?></small><?php endif; ?>
          </div>
        </a>
        <a class="info-item" href="berita.php#panel-galeri">
          <span class="info-icon">🖼️</span>
          <div>
            <h3>Galeri</h3>
            <p class="text-muted"><?php echo $infoGaleri ? htmlspecialchars($infoGaleri['judul'] ?: 'Dokumentasi kegiatan sekolah') : 'Belum ada foto galeri.'; ?></p>
            <?php if ($infoGaleri): ?><small><?php echo $infoTgl($infoGaleri['created_at']); ?></small><?php endif; ?>
          </div>
        </a>
      </div>
    </div>
  </section>

  <section class="section" style="padding-top:0;">
    <div class="container">
      <div class="split reveal">
        <div class="split-media" style="background-image:url('assets/images/infokom 2.webp');"></div>
        <div>
          <span class="eyebrow" style="color:var(--secondary);font-weight:700;font-size:14px;">Tentang Kami</span>
          <h2 style="margin-top:8px;">SMK INFOKOM KOTA BOGOR</h2>
          <p>Yayasan Telekomunikasi Nasional terletak di dua lokasi yang berbeda tepatnya di Jalan RE Abdullah Nomor 15 Gunung Batu, Bogor menempati lahan seluas 800 m2 dijadikan lokasi pertama satuan pendidikan: SMP INFOKOM Bogor. Sedangkan untuk lokasi kedua di Jalan Letjen Ibrahim Adjie Nomor 178 Sindang Barang Bogor menempati lahan seluas 2.600 m2 satuan pendidikan: SMK INFOKOM Bogor</p>
          <a href="profil.php" class="btn btn-primary">Selengkapnya</a>
          <div class="stat-row">
            <div class="stat-block"><span class="stat-icon">🎓</span><div><strong data-counter="900+">0</strong><span>Siswa Aktif</span></div></div>
            <div class="stat-block"><span class="stat-icon">👩‍🏫</span><div><strong data-counter="50+">0</strong><span>Guru &amp; Staff</span></div></div>
            <div class="stat-block"><span class="stat-icon">📘</span><div><strong data-counter="<?php echo count($jurusanList); ?>">0</strong><span>Jurusan</span></div></div>
            <div class="stat-block"><span class="stat-icon">🏅</span><div><strong data-counter="30+">0</strong><span>Prestasi</span></div></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section" style="padding-top:0;">
    <div class="container">
      <div class="section-header reveal">
        <span class="eyebrow">Pilihan Jurusan</span>
        <h2>Jurusan</h2>
        <p>Empat jurusan yang dapat dipilih peserta didik sesuai minat dan bakat masing-masing.</p>
      </div>
      <div class="program-grid reveal">
        <?php foreach ($jurusanList as $jr):
          $imgUrl = $jr['gambar'] ? 'uploads/jurusan/' . htmlspecialchars($jr['gambar']) : 'https://ui-avatars.com/api/?name=' . urlencode($jr['kode']) . '&background=062B5C&color=fff&size=256';
          list($singkatan, $rest) = array_pad(explode('—', $jr['nama'], 2), 2, '');
        ?>
        <a class="program-card" href="jurusan.php?j=<?php echo strtolower(urlencode($jr['kode'])); ?>" style="background-image:url('<?php echo $imgUrl; ?>');">
          <div class="label"><strong><?php echo htmlspecialchars($jr['kode']); ?></strong><span><?php echo htmlspecialchars(trim($rest) ?: $jr['nama']); ?></span></div>
        </a>
        <?php endforeach; ?>
        <?php if (!$jurusanList): ?>
          <p style="color:var(--muted);">Data jurusan belum ditambahkan oleh admin.</p>
        <?php endif; ?>
      </div>
      <div style="text-align:center;margin-top:32px;">
        <a href="akademik-info.php#panel-program" class="btn btn-ghost">Selengkapnya Tentang Jurusan</a>
      </div>
    </div>
  </section>

  <section class="section" id="berita" style="background:var(--white);">
    <div class="container">
      <div class="section-header section-header--split reveal">
        <div>
          <span class="eyebrow">Informasi Sekolah</span>
          <h2>Berita Terbaru</h2>
        </div>
        <a href="berita.php" class="btn btn-ghost">Lihat Semua</a>
      </div>
      <div class="news-grid reveal">
        <?php foreach ($beritaList as $b):
          $imgUrl = $b['foto'] ? 'uploads/berita/' . htmlspecialchars($b['foto']) : 'https://ui-avatars.com/api/?name=' . urlencode('Berita') . '&background=eef4ff&color=062B5C&size=400';
        ?>
        <article class="news-card">
          <div class="news-thumb"><img src="<?php echo $imgUrl; ?>" alt="<?php echo htmlspecialchars($b['judul']); ?>" loading="lazy"></div>
          <div class="news-body">
            <div class="news-meta"><span>📅 <?php echo date('d M Y', strtotime($b['tanggal'])); ?></span><span>👤 Admin</span></div>
            <h3><?php echo htmlspecialchars($b['judul']); ?></h3>
            <a href="berita-detail.php?id=<?php echo (int)$b['id']; ?>" class="btn btn-ghost btn-sm">Baca Selengkapnya</a>
          </div>
        </article>
        <?php endforeach; ?>
        <?php if (!$beritaList): ?>
          <p style="color:var(--muted);">Belum ada berita yang dipublikasikan.</p>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="section" id="galeri">
    <div class="container">
      <div class="section-header section-header--split reveal">
        <div>
          <span class="eyebrow">Dokumentasi</span>
          <h2>Galeri Kegiatan</h2>
        </div>
        <a href="berita.php#panel-galeri" class="btn btn-ghost">Lihat Semua Galeri</a>
      </div>
      <div class="gallery-grid reveal">
        <?php foreach ($galeriList as $g): ?>
        <div class="gallery-item"><img src="uploads/galeri/<?php echo htmlspecialchars($g['foto']); ?>" alt="<?php echo htmlspecialchars($g['judul'] ?: 'Galeri kegiatan sekolah'); ?>" loading="lazy"></div>
        <?php endforeach; ?>
        <?php if (!$galeriList): ?>
          <p style="color:var(--muted);">Belum ada foto di galeri.</p>
        <?php endif; ?>
      </div>
      <div style="text-align:center;margin-top:32px;">
        <a href="berita.php#panel-galeri" class="btn btn-ghost">Lihat Semua Galeri</a>
      </div>
    </div>
  </section>

  <section class="section promo-band">
    <div class="container">
      <div class="promo-card reveal">
        <div class="promo-content">
          <span class="eyebrow">Penerimaan Peserta Didik Baru</span>
          <h2>Tahun Ajaran 2026/2027</h2>
          <p>Daftarkan dirimu sekarang dan jadilah bagian dari keluarga besar SMK Infokom.</p>
          <a href="ppdb.php" class="btn btn-accent">Daftar Sekarang</a>
        </div>
        <div class="promo-media" style="background-image:url('assets/images/GEDUNG.DEPAN.png');"></div>
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
