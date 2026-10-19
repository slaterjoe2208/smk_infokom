<?php
require_once __DIR__ . '/includes/session-init.php';
require_once __DIR__ . '/includes/session-widget.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/settings-helper.php';
require_once __DIR__ . '/includes/magang-jepang-helper.php';
require_once __DIR__ . '/includes/video-helper.php';

$bkk = $pdo->query('SELECT * FROM bkk_pengaturan WHERE id = 1')->fetch() ?: [];
$bkk += ['judul' => 'BKK — Lowongan Kerja Alumni', 'deskripsi' => '', 'cara_melamar' => '', 'email' => '', 'kontak_nama' => '', 'kontak_wa' => ''];

// Lowongan aktif (dengan atau tanpa poster); yang lewat batas pendaftaran disembunyikan.
$lowonganList = $pdo->query("SELECT * FROM lowongan_kerja
    WHERE status = 'Aktif'
      AND (batas_pendaftaran IS NULL OR batas_pendaftaran >= CURDATE())
    ORDER BY COALESCE(tanggal_dibuka, DATE(created_at)) DESC, id DESC")->fetchAll();

$waDigits = preg_replace('/\D/', '', (string)$bkk['kontak_wa']);
if ($waDigits !== '' && $waDigits[0] === '0') $waDigits = '62' . substr($waDigits, 1);

// Program Industri Magang Jepang
$mjPosts = $pdo->query("SELECT * FROM magang_jepang_post WHERE status = 'Aktif' ORDER BY urutan ASC, created_at DESC, id DESC")->fetchAll();
$mjBaru = date('Y-m-d H:i:s', strtotime('-90 days'));

// Kerja Sama Sekolah
$kerjasamaList = smkn1_unique_rows($pdo->query("SELECT * FROM kerjasama_sekolah WHERE status = 'Aktif' ORDER BY nama_perusahaan, id")->fetchAll(), ['nama_perusahaan']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>BKK — Bursa Kerja Khusus — SMK Infokom</title>
<meta name="description" content="Bursa Kerja Khusus SMK INFOKOM Kota Bogor: lowongan pekerjaan, program industri magang Jepang, dan kerja sama sekolah dengan dunia usaha/industri.">
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
        <a href="bkk.php" class="nav-toggle active">BKK <span class="nav-caret" aria-hidden="true">&#9662;</span></a>
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

  <section class="bkk-hero">
    <div class="container">
      <h1>BKK — Bursa Kerja Khusus</h1>
      <p class="breadcrumb"><a href="index.php">Beranda</a> / <b>BKK</b></p>
      <?php if ($bkk['deskripsi'] !== ''): ?><p class="lead"><?php echo e($bkk['deskripsi']); ?></p><?php endif; ?>
    </div>
  </section>

  <?php echo smkn1_pagenav_html([
      'panel-lowongan'      => 'Lowongan Pekerjaan',
      'panel-magang-jepang' => 'Program Industri Magang Jepang',
      'panel-kerjasama'     => 'Kerja Sama Sekolah',
  ], 'Pilih bagian BKK'); ?>

  <section class="section">
    <div class="container stack-wrap">

      <div class="editorial-panel" id="panel-lowongan">
        <h3>Lowongan Pekerjaan</h3>
      <div class="bkk-apply reveal">
        <div>
          <h2>📣 Cara Melamar</h2>
          <p><?php echo e($bkk['cara_melamar']); ?></p>
        </div>
        <ul>
          <?php if ($bkk['email'] !== ''): ?><li>✉️ <a href="mailto:<?php echo e($bkk['email']); ?>"><?php echo e($bkk['email']); ?></a></li><?php endif; ?>
          <?php if ($bkk['kontak_wa'] !== ''): ?><li>💬 <a href="https://wa.me/<?php echo e($waDigits); ?>" target="_blank" rel="noopener noreferrer"><?php echo $bkk['kontak_nama'] !== '' ? e($bkk['kontak_nama']) . ' : ' : ''; ?><?php echo e($bkk['kontak_wa']); ?></a></li><?php endif; ?>
        </ul>
      </div>

      <h2 class="bkk-latest-title reveal">📢 Lowongan Terbaru</h2>
      <div class="bkk-grid reveal">
        <?php foreach ($lowonganList as $lk):
          $alt = trim(($lk['posisi'] !== '' ? $lk['posisi'] . ' — ' : '') . $lk['nama_perusahaan']);
          if (!empty($lk['poster'])):
            $img = 'uploads/lowongan/' . rawurlencode($lk['poster']);
        ?>
        <button type="button" class="bkk-poster" data-full="<?php echo e($img); ?>" data-alt="<?php echo e($alt); ?>" aria-label="Lihat poster: <?php echo e($alt); ?>">
          <img src="<?php echo e($img); ?>" alt="Poster lowongan <?php echo e($alt); ?>" loading="lazy">
          <?php if ($lk['batas_pendaftaran']): ?>
          <span class="bkk-poster-cap"><span><?php echo e($lk['nama_perusahaan']); ?></span><strong>s.d. <?php echo e(date('d/m/Y', strtotime($lk['batas_pendaftaran']))); ?></strong></span>
          <?php endif; ?>
        </button>
        <?php else: ?>
        <div class="bkk-poster bkk-poster-text">
          <h4><?php echo e($lk['posisi'] !== '' ? $lk['posisi'] : $lk['nama_perusahaan']); ?></h4>
          <?php if ($lk['posisi'] !== '' && $lk['nama_perusahaan'] !== ''): ?><p class="bkk-text-co"><?php echo e($lk['nama_perusahaan']); ?></p><?php endif; ?>
          <?php if (!empty($lk['lokasi'])): ?><p>📍 <?php echo e($lk['lokasi']); ?></p><?php endif; ?>
          <?php if (!empty($lk['deskripsi'])): ?><p><?php echo nl2br(e($lk['deskripsi'])); ?></p><?php endif; ?>
          <?php if (!empty($lk['persyaratan'])): ?><p class="text-muted"><?php echo nl2br(e($lk['persyaratan'])); ?></p><?php endif; ?>
          <?php if ($lk['batas_pendaftaran']): ?><p><strong>s.d. <?php echo e(date('d/m/Y', strtotime($lk['batas_pendaftaran']))); ?></strong></p><?php endif; ?>
          <?php if (!empty($lk['link_pendaftaran'])): ?><a class="btn btn-primary btn-sm" href="<?php echo e($lk['link_pendaftaran']); ?>" target="_blank" rel="noopener noreferrer">Daftar</a><?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>
        <?php if (!$lowonganList): ?>
        <p class="bkk-empty">Belum ada lowongan yang dibuka saat ini. Silakan cek kembali nanti.</p>
        <?php endif; ?>
      </div>
      </div>

      <div class="editorial-panel" id="panel-magang-jepang">
      <h3>Program Industri Magang Jepang</h3>
      <div class="section-header reveal" style="text-align:left;">
        <h4 style="margin-bottom:8px;font-size:17px;">Cerita dari program magang Jepang</h4>
        <p class="text-muted" style="max-width:640px;">Kabar terbaru kerja sama internasional dan perjalanan siswa SMK INFOKOM Bogor yang mengikuti program magang di Jepang. Klik foto untuk menonton video.</p>
      </div>

      <div class="mj-grid reveal">
        <?php foreach ($mjPosts as $p):
          $ytId = $p['platform'] === 'YouTube' ? vid_youtube_id($p['link']) : null;
          $igCode = $p['platform'] === 'Instagram' ? vid_instagram_code($p['link']) : null;
          if (!$ytId && !$igCode) continue; // link rusak -> jangan tampilkan kartu kosong
          $isBaru = $igCode && $p['created_at'] >= $mjBaru;
        ?>
        <article class="mj-card <?php echo $ytId ? 'mj-card--yt' : 'mj-card--ig'; ?>">
          <div class="mj-top">
            <span class="mj-badge <?php echo $igCode ? 'mj-ig' : 'mj-yt'; ?>"><?php echo $igCode ? ($isBaru ? 'Terbaru' : 'Reels') : 'YouTube'; ?></span>
            <span class="mj-year">📅 <?php echo (int)$p['tahun']; ?></span>
          </div>
          <?php if ($ytId): ?>
          <a class="mj-thumb mj-thumb--wide" href="<?php echo e(vid_youtube_watch_url($ytId)); ?>" target="_blank" rel="noopener noreferrer" aria-label="Tonton video di YouTube">
            <img src="<?php echo e(vid_youtube_thumb($ytId, 'maxresdefault')); ?>" alt="Thumbnail video YouTube" loading="lazy"
                 onerror="this.onerror=null;this.src='<?php echo e(vid_youtube_thumb($ytId, 'hqdefault')); ?>';">
            <span class="mj-play" aria-hidden="true"><svg viewBox="0 0 24 24" width="30" height="30" fill="#fff"><path d="M8 5v14l11-7z"/></svg></span>
          </a>
          <div class="mj-foot"><span></span><a href="<?php echo e(vid_youtube_watch_url($ytId)); ?>" target="_blank" rel="noopener noreferrer">Tonton di YouTube ↗</a></div>
          <?php else: $hasIg = true; ?>
          <div class="mj-embed">
            <blockquote class="instagram-media" data-instgrm-permalink="https://www.instagram.com/reel/<?php echo e($igCode); ?>/" data-instgrm-version="14" style="width:100%;min-width:0;max-width:540px;margin:0 auto;background:#fff;">
              <a href="<?php echo e($p['link']); ?>" target="_blank" rel="noopener noreferrer">Lihat reel di Instagram</a>
            </blockquote>
          </div>
          <div class="mj-foot"><span></span><a href="<?php echo e($p['link']); ?>" target="_blank" rel="noopener noreferrer">Buka di Instagram ↗</a></div>
          <?php endif; ?>
        </article>
        <?php endforeach; ?>
        <?php if (!empty($hasIg)): ?><script async src="https://www.instagram.com/embed.js"></script><?php endif; ?>
        <?php if (!$mjPosts): ?><p class="bkk-empty">Belum ada video yang ditambahkan. Silakan cek kembali nanti.</p><?php endif; ?>
      </div>
      </div>

      <div class="editorial-panel" id="panel-kerjasama">
          <h3>Kerja Sama Sekolah</h3>
          <p>Perusahaan dan instansi yang telah menjalin kerja sama dengan SMK Infokom dalam program PKL, rekrutmen, dan pengembangan kompetensi siswa.</p>
          <div class="program-grid" style="grid-template-columns:repeat(3,1fr);margin-top:20px;">
            <?php foreach ($kerjasamaList as $ks):
              $logo = $ks['logo'] ? 'uploads/kerjasama/' . htmlspecialchars($ks['logo']) : 'https://ui-avatars.com/api/?name=' . urlencode($ks['nama_perusahaan']) . '&background=eef4ff&color=062B5C';
            ?>
            <div class="card card-pad" style="text-align:center;">
              <img src="<?php echo $logo; ?>" alt="Logo <?php echo htmlspecialchars($ks['nama_perusahaan']); ?>" style="width:64px;height:64px;border-radius:12px;object-fit:cover;margin:0 auto 12px;border:1px solid var(--border);">
              <h3 style="margin-bottom:6px;font-size:15.5px;"><?php echo htmlspecialchars($ks['nama_perusahaan']); ?></h3>
              <?php if ($ks['deskripsi']): ?><p class="text-muted" style="font-size:13px;margin-bottom:12px;"><?php echo nl2br(htmlspecialchars(mb_strimwidth($ks['deskripsi'], 0, 140, '...'))); ?></p><?php endif; ?>
              <?php if ($ks['link_website']): ?>
                <a href="<?php echo htmlspecialchars($ks['link_website']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-ghost btn-sm">Kunjungi Website ↗</a>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php if (!$kerjasamaList): ?>
              <p style="color:var(--muted);grid-column:1/-1;">Belum ada data kerja sama sekolah yang ditambahkan admin.</p>
            <?php endif; ?>
          </div>
      </div>

    </div>
  </section>

</main>

<div class="bkk-lightbox" id="bkkLightbox" role="dialog" aria-modal="true" aria-label="Poster lowongan">
  <button type="button" class="bkk-lightbox-close" aria-label="Tutup">&#10005;</button>
  <img src="" alt="">
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
<script>
(function () {
  var box = document.getElementById('bkkLightbox');
  var img = box.querySelector('img');
  function close() { box.classList.remove('is-open'); img.src = ''; }
  document.querySelectorAll('.bkk-poster').forEach(function (b) {
    b.addEventListener('click', function () { img.src = b.dataset.full; img.alt = b.dataset.alt; box.classList.add('is-open'); });
  });
  box.addEventListener('click', function (e) { if (e.target !== img) close(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
})();
</script>
</body>
</html>
