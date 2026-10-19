<?php
require_once __DIR__ . '/includes/session-init.php';
require_once __DIR__ . '/includes/session-widget.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/settings-helper.php';
require_once __DIR__ . '/includes/gelombang-helper.php';

$gelombangAktif = smkn1_gelombang_aktif($pdo);
$ppdbOpen = smkn1_ppdb_is_open($pdo) && $gelombangAktif !== null;
$jurusanList = $pdo->query('SELECT * FROM jurusan ORDER BY nama')->fetchAll();
$gelombangList = smkn1_get_gelombang($pdo);
$gelombangTerisi = smkn1_gelombang_terisi($pdo);
$totalPendaftar = (int)$pdo->query("SELECT COUNT(*) FROM ppdb_pendaftaran")->fetchColumn();
$totalDiterima = (int)$pdo->query("SELECT COUNT(*) FROM ppdb_pendaftaran WHERE status = 'Diterima'")->fetchColumn();
$totalMenunggu = (int)$pdo->query("SELECT COUNT(*) FROM ppdb_pendaftaran WHERE status = 'Menunggu'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>PPDB 2026/2027 — SMK Infokom</title>
<meta name="description" content="Penerimaan Peserta Didik Baru SMK Infokom Tahun Ajaran 2026/2027. Daftar sekarang dan siapkan masa depanmu.">
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
      <a href="ppdb.php" class="active">PPDB</a>
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

  <section class="hero hero-blue" style="min-height:520px;background-image:url('assets/images/gedung-depan.jpg');">
    <div class="container hero-inner">
      <span class="hero-eyebrow">Tahun Ajaran 2026/2027</span>
      <h1>Penerimaan Peserta Didik Baru</h1>
      <p>Bergabung bersama SMK Infokom dan siapkan masa depanmu bersama pendidik profesional dan fasilitas praktik terbaik.</p>
      <div class="hero-actions">
        <?php if ($ppdbOpen): ?>
        <a href="ppdb-daftar.php" class="btn btn-accent">Daftar Sekarang</a>
        <?php endif; ?>
        <a href="#gelombang" class="btn btn-outline">Lihat Gelombang</a>
      </div>
    </div>
  </section>

  <section class="section" id="poster" style="padding-bottom:0;">
    <div class="container">
      <div class="ppdb-poster-wrap reveal">
        <div class="ppdb-poster-info" id="gelombang">
          <span class="eyebrow">Jadwal &amp; Biaya</span>
          <h2>3 Gelombang Pendaftaran</h2>
          <p>Pilih gelombang yang sedang dibuka, lalu daftar online. Klik poster untuk memperbesar dan melihat info lengkap.</p>
      <div class="gb-carousel" id="gbCarousel" aria-roledescription="carousel" aria-label="Gelombang pendaftaran PPDB">
        <div class="gb-viewport">
          <div class="gb-track">
            <?php foreach ($gelombangList as $g):
              $n = (int)$g['nomor'];
              $isi = $gelombangTerisi[(int)$g['id']] ?? 0;
              $kuota = (int)$g['kuota'];
              $sisa = max(0, $kuota - $isi);
              $pct = $kuota > 0 ? min(100, (int)round($isi / $kuota * 100)) : 0;
              $penuh = $kuota > 0 && $isi >= $kuota;
              $isAktif = $gelombangAktif && (int)$gelombangAktif['id'] === (int)$g['id'];
              $statusLabel = $isAktif ? 'Pendaftaran Dibuka' : ($g['status'] === 'Aktif' ? ($penuh ? 'Kuota Penuh' : 'Belum/Sudah Lewat Jadwal') : ($g['status'] === 'Mendatang' ? 'Segera Dibuka' : 'Ditutup'));
              $statusCls = $isAktif ? 'is-open' : ($g['status'] === 'Mendatang' ? 'is-soon' : 'is-closed');
            ?>
            <article class="gb-slide" aria-roledescription="slide" aria-label="Gelombang <?php echo $n; ?> dari 3">
              <div class="gb-card gb-card-<?php echo $n; ?>">
                <div class="gb-card-top">
                  <span class="gb-pill gb-pill-<?php echo $n; ?>">Gelombang <?php echo $n; ?></span>
                  <span class="gb-status <?php echo $statusCls; ?>"><?php echo e($statusLabel); ?></span>
                </div>
                <h3><?php echo e($g['nama']); ?></h3>
                <p class="gb-desc"><?php echo e($g['deskripsi']); ?></p>
                <dl class="gb-facts">
                  <div><dt>Jadwal</dt><dd><?php echo e(smkn1_tgl_gelombang($g['tgl_mulai'])); ?> – <?php echo e(smkn1_tgl_gelombang($g['tgl_selesai'])); ?></dd></div>
                  <div><dt>Biaya</dt><dd><?php echo e(smkn1_gelombang_biaya($g['biaya'])); ?></dd></div>
                  <div><dt>Kuota</dt><dd><?php echo $kuota; ?> siswa</dd></div>
                </dl>
                <?php $bonus = smkn1_gelombang_bonus($n); if ($bonus !== ''): ?>
                <div class="gelombang-bonus"><span class="bonus-ic" aria-hidden="true">🎁</span><div><strong>Bonus pendaftar</strong><?php echo e($bonus); ?></div></div>
                <?php endif; ?>
                <?php if ($kuota > 0): ?>
                <div class="gb-quota" aria-label="Kuota terisi <?php echo $pct; ?> persen">
                  <div class="gb-quota-bar"><span style="width:<?php echo $pct; ?>%;"></span></div>
                  <small><?php echo $penuh ? 'Kuota sudah penuh' : 'Sisa ' . $sisa . ' kursi'; ?></small>
                </div>
                <?php endif; ?>
              </div>
            </article>
            <?php endforeach; ?>
          </div>
        </div>
        <button type="button" class="gb-nav gb-prev" aria-label="Gelombang sebelumnya">&#8249;</button>
        <button type="button" class="gb-nav gb-next" aria-label="Gelombang berikutnya">&#8250;</button>
        <div class="gb-dots" role="tablist" aria-label="Pilih gelombang">
          <?php foreach ($gelombangList as $i => $g): ?>
          <button type="button" role="tab" aria-label="Gelombang <?php echo (int)$g['nomor']; ?>" class="<?php echo $i === 0 ? 'is-active' : ''; ?>"></button>
          <?php endforeach; ?>
        </div>
      </div>
          <div class="ppdb-poster-cta">
            <?php if ($ppdbOpen): ?>
            <a href="ppdb-daftar.php" class="btn btn-accent btn-lg">Daftar Sekarang</a>
            <span class="ppdb-poster-note">Gelombang <?php echo (int)$gelombangAktif['nomor']; ?> sedang dibuka</span>
            <?php else: ?>
            <span class="btn btn-ghost gb-btn-off">🔒 Pendaftaran Belum Dibuka</span>
            <span class="ppdb-poster-note">Pantau jadwal gelombang di atas</span>
            <?php endif; ?>
          </div>
        </div>
        <button type="button" class="ppdb-poster-btn" id="ppdbPosterBtn" data-full="assets/images/poster-ppdb.jpg" aria-label="Perbesar poster PPDB 2026/2027">
          <img src="assets/images/poster-ppdb.jpg" alt="Poster PPDB SMK Infokom Kota Bogor tahun ajaran 2026-2027" width="399" height="501" loading="lazy">
        </button>
      </div>
    </div>
  </section>

  <section class="section" style="padding-bottom:0;">
    <div class="container">
      <div class="ppdb-stat-row reveal">
        <div class="ppdb-stat"><strong data-counter="<?php echo $totalPendaftar; ?>">0</strong><span>Pendaftar</span></div>
        <div class="ppdb-stat"><strong data-counter="<?php echo $totalMenunggu; ?>">0</strong><span>Menunggu Verifikasi</span></div>
        <div class="ppdb-stat"><strong data-counter="<?php echo $totalDiterima; ?>">0</strong><span>Diterima</span></div>
      </div>
    </div>
  </section>

  <section class="section" id="persyaratan" style="background:var(--white);">
    <div class="container">
      <div class="section-header reveal">
        <span class="eyebrow">Tahapan</span>
        <h2>Alur Pendaftaran</h2>
        <p style="color:var(--muted);margin-top:8px;">Lima langkah mudah dari mengisi formulir sampai pengumuman hasil.</p>
      </div>
      <ol class="alur-list reveal">
        <li class="alur-item">
          <span class="alur-num">1</span>
          <div class="alur-body"><h3>Isi Formulir</h3><p>Isi biodata, data orang tua, dan unggah dokumen. Tidak perlu login.</p></div>
        </li>
        <li class="alur-item">
          <span class="alur-num">2</span>
          <div class="alur-body"><h3>Akun Otomatis</h3><p>Username &amp; password dibuat sistem, lalu konfirmasi via WhatsApp.</p></div>
        </li>
        <li class="alur-item">
          <span class="alur-num">3</span>
          <div class="alur-body"><h3>Verifikasi Berkas</h3><p>Panitia memeriksa kelengkapan dan keabsahan berkas.</p></div>
        </li>
        <li class="alur-item">
          <span class="alur-num">4</span>
          <div class="alur-body"><h3>Seleksi</h3><p>Mengikuti tes atau seleksi nilai sesuai jadwal.</p></div>
        </li>
        <li class="alur-item">
          <span class="alur-num">5</span>
          <div class="alur-body"><h3>Pengumuman</h3><p>Hasil seleksi diumumkan secara online.</p></div>
        </li>
      </ol>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="section-header reveal">
        <span class="eyebrow">Pilihan Jurusan</span>
        <h2>Jurusan</h2>
      </div>
      <div class="program-grid reveal">
        <?php foreach ($jurusanList as $jr):
          $imgUrl = $jr['gambar'] ? 'uploads/jurusan/' . rawurlencode($jr['gambar']) : 'https://ui-avatars.com/api/?name=' . urlencode($jr['kode']) . '&background=062B5C&color=fff&size=256';
          list(, $rest) = array_pad(explode('—', $jr['nama'], 2), 2, '');
        ?>
        <a class="program-card" href="jurusan.php?j=<?php echo strtolower(urlencode($jr['kode'])); ?>" style="background-image:url('<?php echo e($imgUrl); ?>');" aria-label="Lihat detail jurusan <?php echo e($jr['kode']); ?>">
          <div class="label"><strong><?php echo e($jr['kode']); ?></strong><span><?php echo e(trim($rest) ?: $jr['nama']); ?></span></div>
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

  <section class="section" id="daftar" style="background:var(--white);">
    <div class="container">
      <div class="promo-card reveal" style="background:linear-gradient(120deg, var(--primary), var(--primary-700));">
        <div class="promo-content">
          <span class="eyebrow">Formulir Pendaftaran</span>
          <h2>Siap Bergabung?</h2>
          <?php if ($ppdbOpen): ?>
          <p>Formulir pendaftaran online sudah aktif. Persiapkan dokumen berikut: fotokopi rapor, ijazah/SKL, kartu keluarga, dan pas foto terbaru.</p>
          <a href="ppdb-daftar.php" class="btn btn-accent">Daftar Sekarang</a>
          <?php else: ?>
          <p><strong>Pendaftaran PPDB saat ini sedang ditutup.</strong> Silakan pantau halaman ini secara berkala — informasi pembukaan gelombang berikutnya akan diumumkan di sini dan melalui Kalender Akademik.</p>
          <span class="btn btn-ghost" style="cursor:default;background:rgba(255,255,255,0.15);color:#fff;">🔒 PPDB Sedang Ditutup</span>
          <?php endif; ?>
        </div>
        <div class="promo-media" style="background-image:url('assets/images/gedung-depan.jpg');"></div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="section-header reveal">
        <span class="eyebrow">Pertanyaan Umum</span>
        <h2>FAQ Pendaftaran</h2>
      </div>
      <div class="reveal" style="max-width:760px;">
        <div class="accordion-item is-open">
          <button class="accordion-trigger" aria-expanded="true">
            Kapan pendaftaran PPDB dibuka?
            <span class="chevron">⌄</span>
          </button>
          <div class="accordion-panel" style="max-height:200px;">
            <div class="accordion-panel-inner">Pendaftaran dibagi menjadi 3 gelombang, masing-masing dengan jadwal, kuota, dan biaya sendiri. Lihat bagian &ldquo;3 Gelombang Pendaftaran&rdquo; di atas untuk tanggal terbaru.</div>
          </div>
        </div>
        <div class="accordion-item">
          <button class="accordion-trigger" aria-expanded="false">
            Apa saja dokumen yang perlu disiapkan?
            <span class="chevron">⌄</span>
          </button>
          <div class="accordion-panel">
            <div class="accordion-panel-inner">Dokumen yang diperlukan meliputi fotokopi rapor semester 1–5, ijazah atau surat keterangan lulus, kartu keluarga, akta kelahiran, dan pas foto terbaru berwarna.</div>
          </div>
        </div>
        <div class="accordion-item">
          <button class="accordion-trigger" aria-expanded="false">
            Apakah harus login atau membuat akun dulu untuk mendaftar?
            <span class="chevron">⌄</span>
          </button>
          <div class="accordion-panel">
            <div class="accordion-panel-inner">Tidak perlu. Cukup buka halaman pendaftaran dan isi formulir. Setelah formulir terkirim, sistem otomatis membuatkan akun (username dan password) yang bisa Anda pakai untuk login.</div>
          </div>
        </div>
        <div class="accordion-item">
          <button class="accordion-trigger" aria-expanded="false">
            Saya lupa username atau password, bagaimana?
            <span class="chevron">⌄</span>
          </button>
          <div class="accordion-panel">
            <div class="accordion-panel-inner">Hubungi panitia PPDB melalui WhatsApp atau halaman Kontak dengan menyebutkan nama dan nomor pendaftaran Anda, agar panitia dapat membantu.</div>
          </div>
        </div>
        <div class="accordion-item">
          <button class="accordion-trigger" aria-expanded="false">
            Apakah ada biaya pendaftaran?
            <span class="chevron">⌄</span>
          </button>
          <div class="accordion-panel">
            <div class="accordion-panel-inner">Biaya berbeda di tiap gelombang (ada yang gratis, ada yang berbayar). Rincian biaya tercantum pada masing-masing gelombang di bagian &ldquo;3 Gelombang Pendaftaran&rdquo;.</div>
          </div>
        </div>
        <div class="accordion-item">
          <button class="accordion-trigger" aria-expanded="false">
            Bagaimana cara mengetahui hasil seleksi?
            <span class="chevron">⌄</span>
          </button>
          <div class="accordion-panel">
            <div class="accordion-panel-inner">Hasil seleksi akan diumumkan melalui website resmi sekolah dan papan pengumuman di lokasi sekolah sesuai jadwal yang ditentukan panitia PPDB.</div>
          </div>
        </div>
      </div>
    </div>
  </section>

</main>

<div class="bkk-lightbox" id="ppdbLightbox" role="dialog" aria-modal="true" aria-label="Poster PPDB">
  <button type="button" class="bkk-lightbox-close" aria-label="Tutup">&#10005;</button>
  <img src="" alt="Poster PPDB SMK Infokom">
</div>

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
<script src="js/gelombang.js"></script>
<script>
(function () {
  var btn = document.getElementById('ppdbPosterBtn');
  var box = document.getElementById('ppdbLightbox');
  if (!btn || !box) return;
  var img = box.querySelector('img');
  function close() { box.classList.remove('is-open'); img.src = ''; }
  btn.addEventListener('click', function () { img.src = btn.dataset.full; box.classList.add('is-open'); });
  box.addEventListener('click', function (e) { if (e.target !== img) close(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
})();
</script>
</body>
</html>
