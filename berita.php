<?php
require_once __DIR__ . '/includes/session-init.php';
require_once __DIR__ . '/includes/session-widget.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/settings-helper.php';
require_once __DIR__ . '/includes/video-helper.php';

$perPage = 9;
$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));

$where = '';
$params = [];
if ($q !== '') {
    $where = 'WHERE judul LIKE ? OR isi LIKE ?';
    $params = ['%' . $q . '%', '%' . $q . '%'];
}
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM berita $where");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare("SELECT * FROM berita $where ORDER BY tanggal DESC, id DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$beritaList = $stmt->fetchAll();

// Galeri kegiatan (ditampilkan di halaman yang sama, bagian "Galeri Kegiatan")
$gPerPage = 12;
$gPage = max(1, (int)($_GET['gpage'] ?? 1));
$gTotal = (int)$pdo->query('SELECT COUNT(*) FROM galeri')->fetchColumn();
$gTotalPages = max(1, (int)ceil($gTotal / $gPerPage));
$gPage = min($gPage, $gTotalPages);
$gOffset = ($gPage - 1) * $gPerPage;
$prestasiList = smkn1_unique_rows($pdo->query('SELECT * FROM prestasi ORDER BY tahun DESC, id DESC')->fetchAll(), ['nama_prestasi', 'tahun']);
$galeriList = $pdo->query("SELECT * FROM galeri ORDER BY created_at DESC, id DESC LIMIT $gPerPage OFFSET $gOffset")->fetchAll();


// Konten Sekolah: kumpulan link video YouTube (thumbnail otomatis dari YouTube).
// Dibungkus try/catch supaya halaman tetap jalan sebelum migration-20 dijalankan.
try {
    $kontenList = $pdo->query('SELECT * FROM konten_sekolah ORDER BY urutan ASC, id DESC')->fetchAll();
} catch (Throwable $e) {
    $kontenList = [];
}

// ---- Tampilan "Semua": berita + prestasi + kegiatan (galeri) digabung dalam satu aliran, terbaru di atas ----
$hasKat = smkn1_has_column($pdo, 'berita', 'kategori');
$feed = [];
foreach ($pdo->query('SELECT * FROM berita ORDER BY created_at DESC, id DESC LIMIT 30')->fetchAll() as $b) {
    $plain = trim(preg_replace('/\s+/', ' ', strip_tags((string)$b['isi'])));
    $feed[] = [
        'cat' => $hasKat ? ($b['kategori'] ?: 'Berita') : 'Berita',
        'ts' => (string)($b['created_at'] ?? $b['tanggal']), 'date' => $b['tanggal'], 'title' => $b['judul'],
        'excerpt' => mb_strlen($plain) > 150 ? mb_substr($plain, 0, 147) . '…' : $plain,
        'img' => $b['foto'] ? 'uploads/berita/' . $b['foto'] : null,
        'href' => 'berita-detail.php?id=' . (int)$b['id'], 'goto' => null, 'cta' => 'Baca selengkapnya',
    ];
}
foreach (array_slice($prestasiList, 0, 12) as $p) {
    $feed[] = [
        'cat' => 'Prestasi', 'ts' => (string)$p['created_at'], 'date' => substr((string)$p['created_at'], 0, 10), 'title' => $p['nama_prestasi'],
        'excerpt' => trim(($p['deskripsi'] ?? '') !== '' ? mb_substr(strip_tags($p['deskripsi']), 0, 150) : (($p['tingkat'] ?: 'Prestasi siswa') . ($p['tahun'] ? ' · ' . (int)$p['tahun'] : ''))),
        'img' => $p['foto'] ? 'uploads/prestasi/' . $p['foto'] : null,
        'href' => '#panel-prestasi', 'goto' => 'panel-prestasi', 'cta' => 'Lihat prestasi',
    ];
}
foreach ($pdo->query('SELECT * FROM galeri ORDER BY created_at DESC, id DESC LIMIT 8')->fetchAll() as $g) {
    $feed[] = [
        'cat' => 'Kegiatan', 'ts' => (string)$g['created_at'], 'date' => substr((string)$g['created_at'], 0, 10), 'title' => $g['judul'] ?: 'Dokumentasi kegiatan sekolah',
        'excerpt' => 'Momen kegiatan di SMK Infokom Kota Bogor.',
        'img' => 'uploads/galeri/' . $g['foto'], 'href' => '#panel-galeri', 'goto' => 'panel-galeri', 'cta' => 'Lihat galeri',
    ];
}
foreach (array_slice($kontenList, 0, 12) as $v) {
    $feed[] = [
        'cat' => 'Konten Sekolah', 'ts' => (string)$v['created_at'], 'date' => substr((string)$v['created_at'], 0, 10), 'title' => $v['judul'],
        'excerpt' => 'Video kegiatan dan konten SMK Infokom Kota Bogor.',
        'img' => vid_youtube_thumb($v['youtube_id'], 'hqdefault'), 'href' => '#panel-konten', 'goto' => 'panel-konten', 'cta' => 'Lihat konten',
    ];
}
// Data yang paling baru ditambahkan selalu di urutan pertama (tampil di kotak besar).
usort($feed, fn($a, $b) => strcmp($b['ts'], $a['ts']));
// Pastikan tiap kategori (Prestasi, Kegiatan, Konten Sekolah) minimal punya satu wakil di 9 kartu pertama "Semua",
// walau datanya lebih lama dari berita terbaru.
$firstPage = 9;
foreach (['Konten Sekolah', 'Kegiatan', 'Prestasi'] as $catName) {
    $pos = null;
    foreach ($feed as $k => $it) { if ($it['cat'] === $catName) { $pos = $k; break; } }
    if ($pos !== null && $pos >= $firstPage) {
        $moved = array_splice($feed, $pos, 1);
        array_splice($feed, $firstPage - 1 - array_search($catName, ['Prestasi', 'Kegiatan', 'Konten Sekolah']), 0, $moved);
    }
}
$feed = array_slice($feed, 0, 60);

// URL halaman yang membawa posisi halaman berita & galeri sekaligus, lalu loncat ke bagian terkait.
function info_page_url(int $page, int $gpage, string $q, string $hash): string {
    $args = [];
    if ($page > 1) $args['page'] = $page;
    if ($gpage > 1) $args['gpage'] = $gpage;
    if ($q !== '') $args['q'] = $q;
    return 'berita.php' . ($args ? '?' . http_build_query($args) : '') . '#' . $hash;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Info &amp; Berita — SMK Infokom</title>
<meta name="description" content="Berita, pengumuman, dan galeri kegiatan terbaru SMK Infokom Kota Bogor.">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css?v=<?php echo @filemtime(__DIR__ . '/css/style.css'); ?>">
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
        <a href="berita.php" class="nav-toggle active">Info &amp; Berita <span class="nav-caret" aria-hidden="true">&#9662;</span></a>
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
      <h1>Info &amp; Berita</h1>
      <p class="breadcrumb"><a href="index.php">Beranda</a> / Info &amp; Berita</p>
    </div>
  </section>

  <?php echo smkn1_pagenav_html([
      'panel-semua' => 'Semua',
      'panel-berita' => 'Berita',
      'panel-galeri' => 'Galeri Kegiatan',
      'panel-prestasi' => 'Prestasi Siswa',
      'panel-konten' => 'Konten Sekolah',
  ], 'Pilih bagian info dan berita'); ?>

  <section class="section">
    <div class="container stack-wrap">

      <div class="editorial-panel is-active feed-panel" id="panel-semua">
        <div class="feed-hero">
          <div>
            <span class="feed-eyebrow">Info &amp; Berita</span>
            <h2>Yang sedang terjadi di SMK Infokom Bogor</h2>
          </div>
          <div class="feed-chips" role="group" aria-label="Filter kategori">
            <button type="button" class="feed-chip is-active" data-cat="Semua">Semua</button>
            <button type="button" class="feed-chip" data-cat="Berita">Berita</button>
            <button type="button" class="feed-chip" data-cat="Prestasi">Prestasi</button>
            <button type="button" class="feed-chip" data-cat="Kegiatan">Kegiatan</button>
            <button type="button" class="feed-chip" data-cat="Konten Sekolah">Konten Sekolah</button>
          </div>
        </div>

        <?php if ($feed): ?>
        <div class="feed-grid" id="feedGrid">
          <?php foreach ($feed as $it):
            $img = $it['img'] ? htmlspecialchars($it['img']) : 'https://ui-avatars.com/api/?name=' . urlencode($it['cat']) . '&background=E9F1F9&color=062B5C&size=600';
          ?>
          <article class="feed-card" data-cat="<?php echo htmlspecialchars($it['cat']); ?>">
            <a class="feed-thumb" href="<?php echo htmlspecialchars($it['href']); ?>"<?php echo $it['goto'] ? ' data-goto="' . $it['goto'] . '"' : ''; ?> tabindex="-1" aria-hidden="true"><img src="<?php echo $img; ?>" alt="" loading="lazy"></a>
            <div class="feed-body">
              <div class="feed-meta"><span class="feed-badge feed-badge--<?php echo $it['cat'] === 'Konten Sekolah' ? 'konten' : strtolower($it['cat']); ?>"><?php echo htmlspecialchars($it['cat']); ?></span><time><?php echo date('d M Y', strtotime($it['date'])); ?></time><span>Admin</span></div>
              <h3><a href="<?php echo htmlspecialchars($it['href']); ?>"<?php echo $it['goto'] ? ' data-goto="' . $it['goto'] . '"' : ''; ?>><?php echo htmlspecialchars($it['title']); ?></a></h3>
              <?php if ($it['excerpt'] !== ''): ?><p><?php echo htmlspecialchars($it['excerpt']); ?></p><?php endif; ?>
              <a class="feed-more" href="<?php echo htmlspecialchars($it['href']); ?>"<?php echo $it['goto'] ? ' data-goto="' . $it['goto'] . '"' : ''; ?>><?php echo $it['cta']; ?> <span aria-hidden="true">→</span></a>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
        <p class="feed-empty" id="feedEmpty" hidden>Belum ada info pada kategori ini.</p>
        <div class="feed-more-wrap"><button type="button" class="btn btn-ghost" id="feedMoreBtn" hidden>Tampilkan lebih banyak</button></div>
        <?php else: ?>
        <div class="empty-state"><div class="icon">📰</div><h3>Belum ada info</h3><p>Berita, prestasi, dan kegiatan akan tampil di sini setelah dipublikasikan oleh admin.</p></div>
        <?php endif; ?>
      </div>

      <div class="editorial-panel" id="panel-berita">
        <h3>Berita</h3>
        <p class="stack-section-intro">Kabar terbaru, pengumuman, dan kegiatan di SMK INFOKOM Kota Bogor.</p>

      <form method="GET" action="berita.php#panel-berita" class="toolbar">
        <div class="search-field">
          <span class="icon">🔍</span>
          <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Cari judul atau isi berita...">
        </div>
        <?php if ($gPage > 1): ?><input type="hidden" name="gpage" value="<?php echo (int)$gPage; ?>"><?php endif; ?>
        <button type="submit" class="btn btn-primary btn-sm">Cari</button>
        <?php if ($q !== ''): ?><a href="berita.php#panel-berita" class="btn btn-ghost btn-sm">Reset</a><?php endif; ?>
      </form>

      <div class="news-grid news-grid--3">
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
      </div>

      <?php if (!$beritaList): ?>
      <div class="empty-state">
        <div class="icon">📰</div>
        <h3><?php echo $q !== '' ? 'Berita tidak ditemukan' : 'Belum ada berita'; ?></h3>
        <p><?php echo $q !== '' ? 'Coba gunakan kata kunci lain.' : 'Berita akan tampil di sini setelah dipublikasikan oleh admin.'; ?></p>
      </div>
      <?php endif; ?>

      <?php if ($totalPages > 1): ?>
      <nav class="pagination pagination--links" aria-label="Halaman berita">
        <?php if ($page > 1): ?><a href="<?php echo info_page_url($page - 1, $gPage, $q, 'panel-berita'); ?>">←</a><?php else: ?><span class="is-disabled">←</span><?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <a href="<?php echo info_page_url($i, $gPage, $q, 'panel-berita'); ?>" class="<?php echo $i === $page ? 'is-active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?><a href="<?php echo info_page_url($page + 1, $gPage, $q, 'panel-berita'); ?>">→</a><?php else: ?><span class="is-disabled">→</span><?php endif; ?>
      </nav>
      <?php endif; ?>
      </div>

      <div class="editorial-panel" id="panel-galeri">
        <h3>Galeri Kegiatan</h3>
        <p class="stack-section-intro">Dokumentasi kegiatan dan momen di SMK INFOKOM Kota Bogor. Klik foto untuk memperbesar.</p>

      <div class="gallery-grid gallery-grid--page">
        <?php foreach ($galeriList as $g):
          $src = 'uploads/galeri/' . htmlspecialchars($g['foto']);
          $cap = $g['judul'] ?: 'Galeri kegiatan sekolah';
        ?>
        <button type="button" class="gallery-item gallery-item--btn" data-full="<?php echo $src; ?>" data-caption="<?php echo htmlspecialchars($cap); ?>" aria-label="Perbesar foto: <?php echo htmlspecialchars($cap); ?>">
          <img src="<?php echo $src; ?>" alt="<?php echo htmlspecialchars($cap); ?>" loading="lazy">
          <?php if ($g['judul']): ?><span class="gallery-cap"><?php echo htmlspecialchars($g['judul']); ?></span><?php endif; ?>
        </button>
        <?php endforeach; ?>
      </div>

      <?php if (!$galeriList): ?>
      <div class="empty-state">
        <div class="icon">🖼️</div>
        <h3>Belum ada foto</h3>
        <p>Foto kegiatan akan tampil di sini setelah ditambahkan oleh admin.</p>
      </div>
      <?php endif; ?>

      <?php if ($gTotalPages > 1): ?>
      <nav class="pagination pagination--links" aria-label="Halaman galeri">
        <?php if ($gPage > 1): ?><a href="<?php echo info_page_url($page, $gPage - 1, $q, 'panel-galeri'); ?>">←</a><?php else: ?><span class="is-disabled">←</span><?php endif; ?>
        <?php for ($i = 1; $i <= $gTotalPages; $i++): ?>
          <a href="<?php echo info_page_url($page, $i, $q, 'panel-galeri'); ?>" class="<?php echo $i === $gPage ? 'is-active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        <?php if ($gPage < $gTotalPages): ?><a href="<?php echo info_page_url($page, $gPage + 1, $q, 'panel-galeri'); ?>">→</a><?php else: ?><span class="is-disabled">→</span><?php endif; ?>
      </nav>
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

      <div class="editorial-panel" id="panel-konten">
        <h3>Konten Sekolah</h3>
        <p class="stack-section-intro">Video kegiatan dan konten SMK INFOKOM Kota Bogor di YouTube. Klik video untuk menonton di YouTube.</p>

        <?php if ($kontenList): ?>
        <div class="vid-grid">
          <?php foreach ($kontenList as $v): ?>
          <a class="vid-card" href="<?php echo e(vid_youtube_watch_url($v['youtube_id'])); ?>" target="_blank" rel="noopener noreferrer" aria-label="Tonton: <?php echo e($v['judul']); ?>">
            <span class="vid-thumb">
              <img src="<?php echo e(vid_youtube_thumb($v['youtube_id'], 'maxresdefault')); ?>" alt="Thumbnail: <?php echo e($v['judul']); ?>" loading="lazy"
                   onerror="this.onerror=null;this.src='<?php echo e(vid_youtube_thumb($v['youtube_id'], 'hqdefault')); ?>';">
              <span class="mj-play" aria-hidden="true"><svg viewBox="0 0 24 24" width="30" height="30" fill="#fff"><path d="M8 5v14l11-7z"/></svg></span>
            </span>
            <span class="vid-body"><h4><?php echo e($v['judul']); ?></h4><span>▶ Tonton di YouTube</span></span>
          </a>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
          <div class="icon">▶️</div>
          <h3>Belum ada video</h3>
          <p>Video YouTube akan tampil di sini setelah ditambahkan oleh admin.</p>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </section>
</main>

<div class="lightbox" id="lightbox" aria-hidden="true">
  <button type="button" class="lightbox-close" aria-label="Tutup">✕</button>
  <button type="button" class="lightbox-nav lightbox-prev" aria-label="Foto sebelumnya">‹</button>
  <figure>
    <img src="" alt="" id="lightboxImg">
    <figcaption id="lightboxCap"></figcaption>
  </figure>
  <button type="button" class="lightbox-nav lightbox-next" aria-label="Foto berikutnya">›</button>
</div>


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
    var items = Array.prototype.slice.call(document.querySelectorAll(".gallery-item--btn"));
    var box = document.getElementById("lightbox");
    if (!box || !items.length) return;
    var img = document.getElementById("lightboxImg");
    var cap = document.getElementById("lightboxCap");
    var idx = 0;
    function show(i) {
      idx = (i + items.length) % items.length;
      img.src = items[idx].getAttribute("data-full");
      img.alt = items[idx].getAttribute("data-caption");
      cap.textContent = items[idx].getAttribute("data-caption");
    }
    function open(i) { show(i); box.classList.add("is-open"); box.setAttribute("aria-hidden", "false"); document.body.style.overflow = "hidden"; }
    function close() { box.classList.remove("is-open"); box.setAttribute("aria-hidden", "true"); document.body.style.overflow = ""; img.src = ""; }
    items.forEach(function (el, i) { el.addEventListener("click", function () { open(i); }); });
    box.querySelector(".lightbox-close").addEventListener("click", close);
    box.querySelector(".lightbox-prev").addEventListener("click", function () { show(idx - 1); });
    box.querySelector(".lightbox-next").addEventListener("click", function () { show(idx + 1); });
    box.addEventListener("click", function (e) { if (e.target === box) close(); });
    document.addEventListener("keydown", function (e) {
      if (!box.classList.contains("is-open")) return;
      if (e.key === "Escape") close();
      if (e.key === "ArrowLeft") show(idx - 1);
      if (e.key === "ArrowRight") show(idx + 1);
    });
  });
</script>



<script>
(function () {
  var grid = document.getElementById("feedGrid");
  if (!grid) return;
  var cards = Array.prototype.slice.call(grid.querySelectorAll(".feed-card"));
  var chips = document.querySelectorAll(".feed-chip");
  var moreBtn = document.getElementById("feedMoreBtn");
  var emptyMsg = document.getElementById("feedEmpty");
  var STEP = 9, shown = STEP, cat = "Semua";

  function render() {
    var matched = cards.filter(function (c) { return cat === "Semua" || c.getAttribute("data-cat") === cat; });
    cards.forEach(function (c) { c.hidden = true; c.classList.remove("is-featured", "is-side"); });
    matched.forEach(function (c, i) { c.hidden = i >= shown; });
    if (matched[0]) matched[0].classList.add("is-featured");
    if (matched[1] && shown > 1) matched[1].classList.add("is-side");
    emptyMsg.hidden = matched.length > 0;
    moreBtn.hidden = matched.length <= shown;
  }
  chips.forEach(function (ch) {
    ch.addEventListener("click", function () {
      chips.forEach(function (x) { x.classList.remove("is-active"); });
      ch.classList.add("is-active");
      cat = ch.getAttribute("data-cat"); shown = STEP; render();
    });
  });
  moreBtn.addEventListener("click", function () { shown += STEP; render(); });

  // Kartu prestasi / kegiatan: gulung ke bagian terkait di halaman yang sama.
  grid.addEventListener("click", function (e) {
    var a = e.target.closest("[data-goto]");
    if (!a) return;
    var btn = document.querySelector('.pagenav-item[data-target="' + a.getAttribute("data-goto") + '"]');
    if (btn) { e.preventDefault(); btn.click(); }
  });
  render();
})();
</script>
</body>
</html>
