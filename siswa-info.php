<?php
require_once __DIR__ . '/includes/session-init.php';
require_once __DIR__ . '/includes/session-widget.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/settings-helper.php';

$organisasiList = $pdo->query('SELECT * FROM organisasi_siswa ORDER BY nama')->fetchAll();
$prestasiList = $pdo->query('SELECT * FROM prestasi ORDER BY tahun DESC, id DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Siswa — SMK Infokom</title>
<meta name="description" content="Kehidupan siswa SMK Infokom: tata tertib, organisasi siswa, dan prestasi peserta didik.">
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
        <a href="profil.php" class="nav-toggle">Tentang Kami <span class="nav-caret" aria-hidden="true">&#9662;</span></a>
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
            <a href="akademik-info.php#panel-kurikulum">Kurikulum</a>
            <a href="akademik-info.php#panel-program">Jurusan</a>
            <a href="akademik-info.php#panel-kalender">Kalender Akademik</a>
            <a href="akademik-info.php#panel-ekskul">Ekstrakurikuler</a>
            <a href="siswa-info.php">Info Siswa</a>
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
      <button class="mobile-dropdown-toggle" aria-expanded="false">Tentang Kami <span class="caret" aria-hidden="true">&#9662;</span></button>
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
        <a href="akademik-info.php#panel-kurikulum">Kurikulum</a>
        <a href="akademik-info.php#panel-program">Jurusan</a>
        <a href="akademik-info.php#panel-kalender">Kalender Akademik</a>
        <a href="akademik-info.php#panel-ekskul">Ekstrakurikuler</a>
        <a href="siswa-info.php">Info Siswa</a>
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
  <section class="page-hero">
    <div class="container">
      <h1>Siswa</h1>
      <p class="breadcrumb"><a href="index.php">Beranda</a> / Siswa</p>
    </div>
  </section>

  <section class="section">
    <div class="container editorial-layout">
      <nav class="editorial-nav" aria-label="Navigasi siswa">
        <button class="is-active" data-target="panel-tatatertib">Tata Tertib</button>
        <button data-target="panel-osis">Organisasi Siswa</button>
        <button data-target="panel-prestasi">Prestasi Siswa</button>
      </nav>

      <div>
        <div class="editorial-panel is-active" id="panel-tatatertib">
          <h3>Tata Tertib Siswa</h3>
          <p>Setiap peserta didik SMK Infokom wajib menjunjung tinggi disiplin dan tata krama dalam kehidupan sehari-hari di lingkungan sekolah.</p>
          <p>Siswa wajib hadir tepat waktu pukul 06.45 WIB, mengenakan seragam sesuai ketentuan pada setiap harinya, serta menjaga kebersihan dan ketertiban lingkungan sekolah.</p>

          <h4 style="margin:24px 0 12px;font-size:15px;">Ketentuan Seragam Harian</h4>
          <div class="uniform-table">
            <div class="uniform-row uniform-head"><span>Hari</span><span>Seragam</span></div>
            <div class="uniform-row"><span>Senin</span><span>Putih – Abu-abu lengkap dengan atribut, dasi, dan topi (khusus upacara)</span></div>
            <div class="uniform-row"><span>Selasa</span><span>Putih – Seragam jurusan dan celana hitam</span></div>
            <div class="uniform-row"><span>Rabu</span><span>Seragam Pramuka</span></div>
            <div class="uniform-row"><span>Kamis</span><span>seragam putih hitam serta menggunakan almamater dan atribut dasi</span></div>
            <div class="uniform-row"><span>Jumat</span><span>Seragam muslim dan abu abu</span></div>
          </div>
          <p style="margin-top:16px;">Untuk olahraga (sesuai jadwal mata pelajaran PJOK) siswa mengenakan seragam olahraga sekolah. Sepatu yang digunakan adalah sepatu hitam polos, kecuali hari Jumat memakai sepatu bebas rapi.</p>

          <p>Pelanggaran terhadap tata tertib akan ditindaklanjuti melalui sistem poin kedisiplinan yang dikelola oleh Wakil Kepala Sekolah bidang Kesiswaan bekerja sama dengan wali kelas dan guru bimbingan konseling.</p>
          <div class="editorial-media">
            <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=900&q=80" alt="Siswa SMK Infokom mengikuti apel pagi" loading="lazy">
          </div>
        </div>

        <div class="editorial-panel" id="panel-osis">
          <h3>Organisasi Siswa Intra Sekolah (OSIS)</h3>
          <p>OSIS merupakan wadah bagi siswa untuk belajar berorganisasi, mengembangkan kepemimpinan, dan menyalurkan aspirasi seluruh peserta didik.</p>
          <p>Pengurus OSIS dipilih melalui pemilihan ketua OSIS setiap awal tahun ajaran dan bertanggung jawab menjalankan program kerja di bidang keagamaan, kedisiplinan, seni budaya, olahraga, serta lingkungan hidup.</p>
          <?php if ($organisasiList): ?>
          <div class="facility-grid" style="margin-top:24px;">
            <?php foreach ($organisasiList as $o):
              $imgUrl = $o['foto'] ? 'uploads/organisasi/' . htmlspecialchars($o['foto']) : 'https://ui-avatars.com/api/?name=' . urlencode($o['nama']) . '&background=eef4ff&color=062B5C';
            ?>
            <button type="button" class="facility-item" data-detail-title="<?php echo htmlspecialchars($o['nama']); ?>" data-detail-img="<?php echo $imgUrl; ?>" data-detail-desc="<?php echo htmlspecialchars($o['deskripsi'] ?? ''); ?>">
              <div class="thumb" style="background-image:url('<?php echo $imgUrl; ?>');"></div>
              <div class="label"><?php echo htmlspecialchars($o['nama']); ?></div>
            </button>
            <?php endforeach; ?>
          </div>
          <?php else: ?>
          <p style="color:var(--muted);margin-top:16px;">Data organisasi siswa belum ditambahkan oleh admin.</p>
          <?php endif; ?>
        </div>

        <div class="editorial-panel" id="panel-prestasi">
          <h3>Prestasi Siswa</h3>
          <p>Peserta didik SMK Infokom aktif meraih prestasi di berbagai ajang perlombaan tingkat kota, provinsi, hingga nasional.</p>
          <?php if ($prestasiList): ?>
          <div class="news-grid" style="margin-top:20px;">
            <?php foreach ($prestasiList as $p):
              $imgUrl = $p['foto'] ? 'uploads/prestasi/' . htmlspecialchars($p['foto']) : 'https://ui-avatars.com/api/?name=' . urlencode('Prestasi') . '&background=fff7e0&color=b8860b&size=400';
            ?>
            <article class="news-card">
              <div class="news-thumb"><img src="<?php echo $imgUrl; ?>" alt="<?php echo htmlspecialchars($p['nama_prestasi']); ?>" loading="lazy"></div>
              <div class="news-body">
                <div class="news-meta"><span>🏆 <?php echo htmlspecialchars($p['tingkat'] ?: '-'); ?></span><?php if ($p['tahun']): ?><span><?php echo (int)$p['tahun']; ?></span><?php endif; ?></div>
                <h3><?php echo htmlspecialchars($p['nama_prestasi']); ?></h3>
              </div>
            </article>
            <?php endforeach; ?>
          </div>
          <?php else: ?>
          <p style="color:var(--muted);margin-top:16px;">Data prestasi belum ditambahkan oleh admin.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>
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
        <li><a href="siswa-info.php#panel-tatatertib">Peraturan Sekolah</a></li>
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
