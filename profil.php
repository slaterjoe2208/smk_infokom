<?php
require_once __DIR__ . '/includes/session-init.php';
require_once __DIR__ . '/includes/session-widget.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/settings-helper.php';

$profil = smkn1_get_profil_sekolah($pdo);
// Struktur organisasi diambil otomatis dari data guru:
// Kepala Sekolah (level 1), Wakil Kepala Sekolah (level 2), Kepala Program (level 3).
$strukturLevel = ['Kepala Sekolah' => 1, 'Wakil Kepala Sekolah' => 2, 'Kepala Program' => 3];
$strukturRows = $pdo->query("SELECT * FROM guru WHERE jabatan IN ('Kepala Sekolah','Wakil Kepala Sekolah','Kepala Program') ORDER BY urutan, nama")->fetchAll();
$strukturByLevel = [1 => [], 2 => [], 3 => []];
foreach ($strukturRows as $row) {
    $level = $strukturLevel[$row['jabatan']];
    $row['jabatan'] = $row['jabatan_detail'] ?: $row['jabatan'];
    $strukturByLevel[$level][] = $row;
}
// Direktori guru & tenaga kependidikan (bagian "Guru & Tenaga Kependidikan").
$guruList = smkn1_unique_rows($pdo->query('SELECT * FROM guru ORDER BY FIELD(jabatan,"Kepala Sekolah","Wakil Kepala Sekolah","Kepala Program","Kepala Unit","Wali Kelas","Guru","Staff"), urutan, nama, id')->fetchAll(), ['nama']);

// Pilihan slideshow filter (urutan sesuai tampilan). "semua" selalu aktif lebih dulu.
$guruSlides = [
    'semua'                => 'Semua',
    'kepala-sekolah'       => 'Kepala Sekolah',
    'wakil-kepala-sekolah' => 'Wakil Kepala Sekolah',
    'kepala-program'       => 'Kepala Program',
    'kepala-unit'          => 'Kepala Tata Usaha, Perpustakaan, Bendahara',
    'pembina-osis'         => 'Pembina OSIS',
    'guru-bk'              => 'Guru Pembimbingan dan Konseling',
    'guru'                 => 'Guru',
    'staf-kurikulum'       => 'Staff Kurikulum',
    'staf-teller'          => 'Staff Teller',
    'staf-tata-usaha'      => 'Staff Tata Usaha',
];
// Satu orang bisa masuk beberapa kategori (mis. Pembina OSIS juga tampil di "Guru").
function smkn1_guru_categories(array $g): array {
    $detail = mb_strtolower(trim(($g['jabatan_detail'] ?? '') . ' ' . ($g['jabatan_tambahan'] ?? '')));
    $cats = [];
    switch ($g['jabatan']) {
        case 'Kepala Sekolah':        $cats[] = 'kepala-sekolah'; break;
        case 'Wakil Kepala Sekolah':  $cats[] = 'wakil-kepala-sekolah'; break;
        case 'Kepala Program':        $cats[] = 'kepala-program'; break;
        case 'Kepala Unit':           $cats[] = 'kepala-unit'; break;
        case 'Wali Kelas':
        case 'Guru':
            $cats[] = 'guru';
            if (strpos($detail, 'osis') !== false) $cats[] = 'pembina-osis';
            if (strpos($detail, 'bimbingan') !== false || strpos($detail, 'konseling') !== false) $cats[] = 'guru-bk';
            break;
        case 'Staff':
            if (strpos($detail, 'kurikulum') !== false)  $cats[] = 'staf-kurikulum';
            if (strpos($detail, 'teller') !== false)     $cats[] = 'staf-teller';
            if (strpos($detail, 'tata usaha') !== false) $cats[] = 'staf-tata-usaha';
            break;
    }
    return $cats;
}
function smkn1_initials(string $name): string {
    $words = preg_split('/\s+/', trim($name));
    $initials = '';
    foreach (array_slice($words, 0, 2) as $w) {
        $initials .= mb_strtoupper(mb_substr($w, 0, 1));
    }
    return $initials ?: '?';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Profil Sekolah — SMK Infokom</title>
<meta name="description" content="Mengenal lebih dekat SMK Infokom: sejarah, visi misi, sambutan kepala sekolah, struktur organisasi, serta guru dan tenaga kependidikan.">
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
        <a href="profil.php" class="nav-toggle active">Profil <span class="nav-caret" aria-hidden="true">&#9662;</span></a>
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
  <section class="page-hero">
    <div class="container">
      <h1>Profil Sekolah</h1>
      <p class="breadcrumb"><a href="index.php">Beranda</a> / Profil</p>
    </div>
  </section>

  <div class="pagenav" aria-label="Pilih bagian profil">
    <div class="container">
      <div class="slidenav">
        <button type="button" class="slidenav-btn slidenav-prev" aria-label="Geser ke kiri">&#8249;</button>
        <div class="slidenav-track">
          <button type="button" class="pagenav-item is-active" data-target="panel-sejarah">Sejarah Sekolah</button>
          <button type="button" class="pagenav-item" data-target="panel-visi">Visi &amp; Misi</button>
          <button type="button" class="pagenav-item" data-target="panel-sambutan">Sambutan Kepala Sekolah</button>
          <button type="button" class="pagenav-item" data-target="panel-struktur">Struktur Organisasi</button>
          <button type="button" class="pagenav-item" data-target="panel-guru">Guru &amp; Tenaga Kependidikan</button>
        </div>
        <button type="button" class="slidenav-btn slidenav-next" aria-label="Geser ke kanan">&#8250;</button>
      </div>
    </div>
  </div>

  <section class="section">
    <div class="container stack-wrap">

        <div class="editorial-panel is-active" id="panel-sejarah">
          <h3>Sejarah Sekolah</h3>
          <?php foreach (preg_split('/\n\s*\n/', trim($profil['sejarah'] ?? '')) as $par): if (trim($par) === '') continue; ?>
          <p><?php echo nl2br(htmlspecialchars(trim($par))); ?></p>
          <?php endforeach; ?>
          <div class="editorial-media">
            <img src="assets/images/infokom 2.webp" alt="Gedung utama SMK Infokom" loading="lazy">
          </div>
        </div>

        <div class="editorial-panel" id="panel-visi">
          <h3>Visi</h3>
          <p><?php echo nl2br(htmlspecialchars($profil['visi'] ?? '')); ?></p>
          <h3 style="margin-top:32px;">Misi</h3>
          <?php
            $misiLines = array_values(array_filter(array_map('trim', explode("\n", $profil['misi'] ?? '')), fn($l) => $l !== ''));
          ?>
          <ol style="padding-left:20px;display:flex;flex-direction:column;gap:6px;">
            <?php foreach ($misiLines as $ml): ?>
            <li><?php echo htmlspecialchars($ml); ?></li>
            <?php endforeach; ?>
          </ol>
        </div>

        <div class="editorial-panel" id="panel-sambutan">
          <div class="editorial-flex is-sambutan">
            <div>
              <h3>Sambutan Kepala Sekolah</h3>
              <?php foreach (preg_split('/\n\s*\n/', trim($profil['sambutan_kepsek'] ?? '')) as $par): if (trim($par) === '') continue; ?>
              <p><?php echo nl2br(htmlspecialchars(trim($par))); ?></p>
              <?php endforeach; ?>
              <p style="margin-top:24px;font-weight:700;color:var(--text);"><?php echo htmlspecialchars($profil['nama_kepsek'] ?? ''); ?><br><span style="font-weight:500;color:var(--muted);font-size:13.5px;">Kepala Sekolah SMK INFOKOM Kota Bogor</span></p>
            </div>
            <div class="editorial-media is-portrait" style="margin-top:0;">
              <img src="<?php echo $profil['foto_kepsek'] ? 'uploads/profil/' . htmlspecialchars($profil['foto_kepsek']) : 'assets/images/Bu Kepala Sekolah-Photoroom.png'; ?>" alt="Foto Kepala Sekolah">
            </div>
          </div>
        </div>

        <div class="editorial-panel" id="panel-struktur">
          <h3>Struktur Organisasi</h3>
          <p>Struktur organisasi SMK Infokom disusun untuk memastikan setiap fungsi manajemen sekolah berjalan efektif dan terkoordinasi dengan baik.</p>
          <div class="org-chart">
            <?php if ($strukturByLevel[1]): ?>
            <div class="org-row">
              <?php foreach ($strukturByLevel[1] as $node):
                $fotoUrl = $node['foto'] ? 'uploads/guru/' . htmlspecialchars($node['foto']) : null;
              ?>
              <div class="org-node is-primary">
                <?php if ($fotoUrl): ?>
                  <div class="org-avatar" style="background-image:url('<?php echo $fotoUrl; ?>');background-size:cover;background-position:center top;color:transparent;"></div>
                <?php else: ?>
                  <div class="org-avatar"><?php echo htmlspecialchars(smkn1_initials($node['nama'])); ?></div>
                <?php endif; ?>
                <strong><?php echo htmlspecialchars($node['nama']); ?></strong><span><?php echo htmlspecialchars($node['jabatan']); ?></span>
              </div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($strukturByLevel[2]): ?>
            <div class="org-row">
              <?php foreach ($strukturByLevel[2] as $node):
                $fotoUrl = $node['foto'] ? 'uploads/guru/' . htmlspecialchars($node['foto']) : null;
              ?>
              <div class="org-node">
                <?php if ($fotoUrl): ?>
                  <div class="org-avatar" style="background-image:url('<?php echo $fotoUrl; ?>');background-size:cover;background-position:center top;color:transparent;"></div>
                <?php else: ?>
                  <div class="org-avatar"><?php echo htmlspecialchars(smkn1_initials($node['nama'])); ?></div>
                <?php endif; ?>
                <strong><?php echo htmlspecialchars($node['nama']); ?></strong><span><?php echo htmlspecialchars($node['jabatan']); ?></span>
              </div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($strukturByLevel[3]): ?>
            <div class="org-row">
              <?php foreach ($strukturByLevel[3] as $node):
                $fotoUrl = $node['foto'] ? 'uploads/guru/' . htmlspecialchars($node['foto']) : null;
              ?>
              <div class="org-node">
                <?php if ($fotoUrl): ?>
                  <div class="org-avatar" style="background-image:url('<?php echo $fotoUrl; ?>');background-size:cover;background-position:center top;color:transparent;"></div>
                <?php else: ?>
                  <div class="org-avatar"><?php echo htmlspecialchars(smkn1_initials($node['nama'])); ?></div>
                <?php endif; ?>
                <strong><?php echo htmlspecialchars($node['jabatan']); ?></strong><span><?php echo htmlspecialchars($node['nama']); ?></span>
              </div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!$strukturRows): ?>
              <p style="text-align:center;color:var(--muted);">Data struktur organisasi belum ditambahkan oleh admin.</p>
            <?php endif; ?>
          </div>
        </div>

        <div class="editorial-panel" id="panel-guru">
          <h3>Guru &amp; Tenaga Kependidikan</h3>
          <p>Kenali tenaga pendidik dan kependidikan yang mendukung proses pembelajaran di SMK INFOKOM Kota Bogor. Geser pilihan di bawah ini untuk melihat kelompok tertentu.</p>

          <div class="toolbar toolbar--slide">
            <div class="search-field">
              <span class="icon">🔍</span>
              <input type="text" id="guruSearch" placeholder="Cari nama atau jabatan...">
            </div>
            <div class="slidenav" id="guruSlides">
              <button type="button" class="slidenav-btn slidenav-prev" aria-label="Geser ke kiri">&#8249;</button>
              <div class="slidenav-track">
                <?php foreach ($guruSlides as $key => $label): ?>
                <button type="button" class="chip<?php echo $key === 'semua' ? ' is-active' : ''; ?>" data-filter="<?php echo $key; ?>"><?php echo htmlspecialchars($label); ?></button>
                <?php endforeach; ?>
              </div>
              <button type="button" class="slidenav-btn slidenav-next" aria-label="Geser ke kanan">&#8250;</button>
            </div>
          </div>

          <div class="profile-grid" id="guruGrid">
            <?php foreach ($guruList as $g):
              $fotoUrl = $g['foto']
                ? 'uploads/guru/' . htmlspecialchars($g['foto'])
                : 'https://ui-avatars.com/api/?name=' . urlencode($g['nama']) . '&background=062B5C&color=fff&size=256';
              $peran = $g['jabatan_detail'] ?: $g['jabatan'];
              if (!empty($g['jabatan_tambahan'])) $peran .= ' · ' . $g['jabatan_tambahan'];
              if (!empty($g['mapel'])) $peran .= ' — ' . $g['mapel'];
              $searchText = $g['nama'] . ' ' . $peran . ' ' . $g['jabatan'];
            ?>
            <div class="profile-card" data-name="<?php echo htmlspecialchars($searchText); ?>" data-category="<?php echo htmlspecialchars(implode(' ', smkn1_guru_categories($g))); ?>">
              <div class="profile-photo is-cutout" style="background-image:url('<?php echo $fotoUrl; ?>');">
                <span class="status-dot">Aktif</span>
              </div>
              <div class="profile-info">
                <h3><?php echo htmlspecialchars($g['nama']); ?></h3>
                <div class="role"><?php echo htmlspecialchars($peran); ?></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>

          <div class="empty-state <?php echo $guruList ? 'hidden' : ''; ?>" id="guruEmpty">
            <div class="icon">🔎</div>
            <h3>Data tidak ditemukan</h3>
            <p><?php echo $guruList ? 'Belum ada guru yang sesuai dengan pencarian atau filter yang dipilih.' : 'Data guru belum ditambahkan oleh admin.'; ?></p>
            <button type="button" class="btn btn-ghost" id="guruReset">Tampilkan Semua</button>
          </div>
        </div>

    </div>
  </section>

  <section class="section" style="background:var(--white);padding-top:0;">
    <div class="container">
      <div class="stat-row reveal" style="margin-top:0;">
        <div class="stat-block"><span class="stat-icon">🎓</span><div><strong data-counter="1250" data-suffix="+">0</strong><span>Siswa</span></div></div>
        <div class="stat-block"><span class="stat-icon">👩‍🏫</span><div><strong data-counter="75" data-suffix="+">0</strong><span>Guru &amp; Staff</span></div></div>
        <div class="stat-block"><span class="stat-icon">📘</span><div><strong data-counter="4">0</strong><span>Jurusan</span></div></div>
        <div class="stat-block"><span class="stat-icon">🏅</span><div><strong data-counter="120" data-suffix="+">0</strong><span>Prestasi</span></div></div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="promo-card reveal" style="background:linear-gradient(120deg, var(--primary), var(--primary-700));grid-template-columns:1fr;">
        <div class="promo-content" style="text-align:center;padding:var(--sp-10) var(--sp-6);">
          <span class="eyebrow" style="display:block;">Portal Sekolah</span>
          <h2 style="margin:10px auto 8px;">Masuk ke Portal Sekolah</h2>
          <p style="margin:0 auto var(--sp-6);">Admin, guru, siswa, dan orang tua dapat masuk ke sistem informasi sekolah melalui portal login untuk melihat data akademik, kehadiran, dan pembayaran.</p>
          <a href="login.php" id="portalCta" class="btn btn-accent">Login ke Portal</a>
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
  document.addEventListener("DOMContentLoaded", function () {
    window.SMKN1.setupDirectoryFilter({
      searchSelector: "#guruSearch",
      chipSelector: "#guruSlides .chip",
      itemSelector: "#guruGrid .profile-card",
      emptySelector: "#guruEmpty",
      gridSelector: "#guruGrid",
      resetSelector: "#guruReset"
    });
  });
</script>
</body>
</html>
