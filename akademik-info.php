<?php
require_once __DIR__ . '/includes/session-init.php';
require_once __DIR__ . '/includes/session-widget.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/settings-helper.php';

$jurusanList = $pdo->query('SELECT * FROM jurusan ORDER BY nama')->fetchAll();
// Urutan fasilitas mengikuti urutan input admin (id), bukan abjad; data ganda otomatis dibuang dari tampilan.
$fasilitasList = smkn1_unique_rows($pdo->query("SELECT * FROM fasilitas WHERE foto IS NOT NULL AND foto <> '' ORDER BY id")->fetchAll(), ['nama', 'foto']);
$kurikulumList = $pdo->query('SELECT * FROM kurikulum ORDER BY urutan, id')->fetchAll();
$ekskulList = smkn1_unique_rows($pdo->query('SELECT * FROM ekskul ORDER BY nama, id')->fetchAll(), ['nama']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Akademik — SMK Infokom</title>
<meta name="description" content="Informasi akademik SMK Infokom: kurikulum, jurusan, kalender akademik, ekstrakurikuler, dan fasilitas sekolah.">
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
  <section class="page-hero">
    <div class="container">
      <h1>Akademik</h1>
      <p class="breadcrumb"><a href="index.php">Beranda</a> / Akademik</p>
    </div>
  </section>

  <?php echo smkn1_pagenav_html([
      'panel-tatatertib' => 'Tata Tertib',
      'panel-kurikulum'  => 'Kurikulum',
      'panel-program'    => 'Jurusan',
      'panel-kalender'   => 'Kalender Akademik',
      'panel-ekskul'     => 'Ekstrakurikuler',
      'panel-fasilitas'  => 'Fasilitas',
  ], 'Pilih bagian akademik'); ?>

  <section class="section">
    <div class="container stack-wrap">

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
            <img src="assets/images/GEDUNG.DEPAN.png" alt="Siswa SMK Infokom mengikuti apel pagi" loading="lazy">
          </div>
        </div>

        <div class="editorial-panel" id="panel-kurikulum">
          <h3>Kurikulum</h3>
          <?php foreach ($kurikulumList as $kr): ?>
          <h4 style="margin-top:20px;"><?php echo htmlspecialchars($kr['judul']); ?></h4>
          <?php foreach (preg_split('/\R{2,}/', trim($kr['isi'])) as $para): if (trim($para) === '') continue; ?>
          <p><?php echo nl2br(htmlspecialchars(trim($para))); ?></p>
          <?php endforeach; ?>
          <?php endforeach; ?>
          <?php if (!$kurikulumList): ?>
          <p style="color:var(--muted);">Data kurikulum belum ditambahkan oleh admin.</p>
          <?php endif; ?>
        </div>

        <div class="editorial-panel" id="panel-program">
          <h3>Jurusan</h3>
          <p>Terdapat empat jurusan yang dapat dipilih oleh peserta didik sesuai minat dan bakat masing-masing.</p>
          <div class="facility-grid">
            <?php foreach ($jurusanList as $jr):
              $imgUrl = $jr['gambar'] ? 'uploads/jurusan/' . htmlspecialchars($jr['gambar']) : 'https://ui-avatars.com/api/?name=' . urlencode($jr['kode']) . '&background=062B5C&color=fff&size=256';
            ?>
            <a class="facility-item" href="jurusan.php?j=<?php echo strtolower(urlencode($jr['kode'])); ?>">
              <div class="thumb" style="background-image:url('<?php echo $imgUrl; ?>');"></div>
              <div class="label"><?php echo htmlspecialchars($jr['nama']); ?></div>
            </a>
            <?php endforeach; ?>
            <?php if (!$jurusanList): ?>
              <p style="color:var(--muted);">Data jurusan belum ditambahkan oleh admin.</p>
            <?php endif; ?>
          </div>
        </div>

        <div class="editorial-panel" id="panel-kalender">
          <h3>Kalender Akademik</h3>
          <p>Kalender agenda sekolah tahun ajaran berjalan. Tanggal yang memiliki kegiatan diberi penanda — klik tanggalnya untuk melihat detail kegiatan. Data diperbarui langsung oleh admin setiap tahun tanpa mengubah kode.</p>

          <?php
          $kalenderEvents = smkn1_unique_rows($pdo->query("SELECT * FROM kalender_akademik WHERE status = 'Aktif' ORDER BY tanggal_mulai, id")->fetchAll(), ['nama_kegiatan', 'tanggal_mulai', 'tanggal_selesai']);
          $jenisWarna = [
              'Kegiatan Sekolah'   => ['bg' => '#e3f2fd', 'fg' => '#0d47a1', 'dot' => '#1565c0'],
              'Ujian'              => ['bg' => '#fdecea', 'fg' => '#b3261e', 'dot' => '#c62828'],
              'Libur'              => ['bg' => '#e8f5e9', 'fg' => '#1b5e20', 'dot' => '#2e7d32'],
              'Pendaftaran'        => ['bg' => '#fff3e0', 'fg' => '#8a5300', 'dot' => '#ef6c00'],
              'Kegiatan Akademik'  => ['bg' => '#f3e5f5', 'fg' => '#5e2a84', 'dot' => '#7b1fa2'],
          ];
          $eventsForJs = array_map(function ($e) {
              return [
                  'nama' => $e['nama_kegiatan'],
                  'mulai' => $e['tanggal_mulai'],
                  'selesai' => $e['tanggal_selesai'],
                  'jenis' => $e['jenis'],
                  'deskripsi' => $e['deskripsi'],
              ];
          }, $kalenderEvents);
          ?>

          <div class="kalender-legend" style="display:flex;flex-wrap:wrap;gap:14px;margin:18px 0;">
            <?php foreach ($jenisWarna as $jenis => $warna): ?>
            <span style="display:inline-flex;align-items:center;gap:6px;font-size:12.5px;color:var(--muted);"><span style="width:10px;height:10px;border-radius:50%;background:<?php echo $warna['dot']; ?>;display:inline-block;"></span><?php echo htmlspecialchars($jenis); ?></span>
            <?php endforeach; ?>
          </div>

          <div class="kalender-wrap" style="display:grid;grid-template-columns:1.3fr 1fr;gap:24px;align-items:start;">
            <div class="kalender-box" style="border:1px solid var(--border);border-radius:14px;padding:18px;">
              <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                <button type="button" id="kalPrev" class="btn btn-ghost btn-sm" aria-label="Bulan sebelumnya">←</button>
                <strong id="kalLabel" style="font-size:15.5px;"></strong>
                <button type="button" id="kalNext" class="btn btn-ghost btn-sm" aria-label="Bulan berikutnya">→</button>
              </div>
              <div id="kalGridHead" style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;text-align:center;font-size:11.5px;color:var(--muted);margin-bottom:6px;font-weight:700;"></div>
              <div id="kalGrid" style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;"></div>
            </div>
            <div class="kalender-agenda">
              <h4 style="margin-bottom:10px;font-size:14.5px;">Agenda Bulan Ini</h4>
              <div id="kalAgendaList" class="schedule-list"></div>
            </div>
          </div>

          <script>
          (function() {
            var EVENTS = <?php echo json_encode($eventsForJs); ?>;
            var WARNA = <?php echo json_encode($jenisWarna); ?>;
            var BULAN = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            var HARI = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];

            var today = new Date();
            var view = { y: today.getFullYear(), m: today.getMonth() };
            // Kalau ada agenda mendatang, buka kalender langsung di bulan agenda terdekat.
            var upcoming = EVENTS.filter(function(e){ return e.mulai >= today.toISOString().slice(0,10); }).sort(function(a,b){ return a.mulai < b.mulai ? -1 : (a.mulai > b.mulai ? 1 : 0); });
            if (upcoming.length) {
              var d0 = new Date(upcoming[0].mulai);
              view = { y: d0.getFullYear(), m: d0.getMonth() };
            }

            function pad(n) { return n < 10 ? '0' + n : '' + n; }
            function toISO(y, m, d) { return y + '-' + pad(m + 1) + '-' + pad(d); }

            function eventsOnDate(iso) {
              return EVENTS.filter(function(e) {
                var end = e.selesai || e.mulai;
                return iso >= e.mulai && iso <= end;
              });
            }
            function eventsInMonth(y, m) {
              var first = toISO(y, m, 1);
              var last = toISO(y, m, new Date(y, m + 1, 0).getDate());
              return EVENTS.filter(function(e) {
                var end = e.selesai || e.mulai;
                return e.mulai <= last && end >= first;
              }).sort(function(a, b) { return a.mulai < b.mulai ? -1 : 1; });
            }

            var head = document.getElementById('kalGridHead');
            HARI.forEach(function(h) { var s = document.createElement('span'); s.textContent = h; head.appendChild(s); });

            function render() {
              document.getElementById('kalLabel').textContent = BULAN[view.m] + ' ' + view.y;
              var grid = document.getElementById('kalGrid');
              grid.innerHTML = '';
              var firstDow = new Date(view.y, view.m, 1).getDay();
              var totalDays = new Date(view.y, view.m + 1, 0).getDate();
              var todayIso = toISO(today.getFullYear(), today.getMonth(), today.getDate());

              for (var i = 0; i < firstDow; i++) {
                var empty = document.createElement('div');
                grid.appendChild(empty);
              }
              for (var d = 1; d <= totalDays; d++) {
                var iso = toISO(view.y, view.m, d);
                var evs = eventsOnDate(iso);
                var cell = document.createElement('button');
                cell.type = 'button';
                cell.textContent = d;
                cell.style.cssText = 'position:relative;aspect-ratio:1;border:1px solid var(--border);border-radius:8px;background:var(--white,#fff);cursor:pointer;font-size:13px;padding:0;';
                if (iso === todayIso) { cell.style.borderColor = 'var(--primary)'; cell.style.fontWeight = '800'; cell.style.color = 'var(--primary)'; }
                if (evs.length) {
                  var dotWrap = document.createElement('div');
                  dotWrap.style.cssText = 'position:absolute;bottom:4px;left:50%;transform:translateX(-50%);display:flex;gap:2px;';
                  evs.slice(0, 3).forEach(function(e) {
                    var dot = document.createElement('span');
                    var warna = WARNA[e.jenis] || WARNA['Kegiatan Sekolah'];
                    dot.style.cssText = 'width:5px;height:5px;border-radius:50%;background:' + warna.dot + ';display:inline-block;';
                    dotWrap.appendChild(dot);
                  });
                  cell.appendChild(dotWrap);
                  cell.addEventListener('click', function(evObj, list) {
                    return function() { showDetail(evObj, list); };
                  }(iso, evs));
                }
                grid.appendChild(cell);
              }
              renderAgenda();
            }

            function formatTgl(iso) {
              var d = new Date(iso + 'T00:00:00');
              return d.getDate() + ' ' + BULAN[d.getMonth()] + ' ' + d.getFullYear();
            }

            function renderAgenda() {
              var list = document.getElementById('kalAgendaList');
              var evs = eventsInMonth(view.y, view.m);
              list.innerHTML = '';
              if (!evs.length) {
                list.innerHTML = '<p class="text-muted" style="font-size:13.5px;">Tidak ada agenda pada bulan ini.</p>';
                return;
              }
              evs.forEach(function(e) {
                var warna = WARNA[e.jenis] || WARNA['Kegiatan Sekolah'];
                var item = document.createElement('div');
                item.className = 'schedule-item';
                item.style.cursor = 'pointer';
                var tglLabel = e.selesai && e.selesai !== e.mulai ? (formatTgl(e.mulai) + ' – ' + formatTgl(e.selesai)) : formatTgl(e.mulai);
                item.innerHTML = '<div class="time-col"><strong>' + new Date(e.mulai + 'T00:00:00').getDate() + '</strong><span>' + BULAN[new Date(e.mulai + 'T00:00:00').getMonth()].slice(0,3) + '</span></div>' +
                  '<div class="info"><h4>' + e.nama + ' <span class="schedule-badge" style="background:' + warna.bg + ';color:' + warna.fg + ';">' + e.jenis + '</span></h4>' +
                  '<p>' + tglLabel + (e.deskripsi ? ' — ' + e.deskripsi : '') + '</p></div>';
                item.addEventListener('click', function() { showDetail(e.mulai, [e]); });
                list.appendChild(item);
              });
            }

            function showDetail(iso, evs) {
              var desc = '<p style="font-weight:600;margin-bottom:8px;">' + formatTgl(iso) + '</p>';
              evs.forEach(function(e) {
                var warna = WARNA[e.jenis] || WARNA['Kegiatan Sekolah'];
                desc += '<p style="margin-bottom:10px;"><span class="schedule-badge" style="background:' + warna.bg + ';color:' + warna.fg + ';">' + e.jenis + '</span><br><strong>' + e.nama + '</strong>' + (e.deskripsi ? '<br>' + e.deskripsi : '') + '</p>';
              });
              if (window.SMKN1 && window.SMKN1.openDetailModal) {
                window.SMKN1.openDetailModal('Agenda Kegiatan', desc, '');
              } else {
                alert(evs.map(function(e){ return e.nama; }).join('\n'));
              }
            }

            document.getElementById('kalPrev').addEventListener('click', function() {
              view.m--; if (view.m < 0) { view.m = 11; view.y--; } render();
            });
            document.getElementById('kalNext').addEventListener('click', function() {
              view.m++; if (view.m > 11) { view.m = 0; view.y++; } render();
            });

            render();
          })();
          </script>
        </div>

        <div class="editorial-panel" id="panel-ekskul">
          <h3>Ekstrakurikuler</h3>
          <p>Selain kegiatan akademik, siswa dapat mengembangkan minat dan bakat melalui berbagai kegiatan ekstrakurikuler berikut.</p>
          <div class="facility-grid">
            <?php foreach ($ekskulList as $ek):
              $imgUrl = $ek['foto'] ? 'uploads/ekskul/' . htmlspecialchars($ek['foto']) : 'https://ui-avatars.com/api/?name=' . urlencode($ek['nama']) . '&background=eef4ff&color=062B5C&size=256';
            ?>
            <button type="button" class="facility-item" data-detail-title="<?php echo htmlspecialchars($ek['nama']); ?>" data-detail-img="<?php echo $imgUrl; ?>" data-detail-desc="<?php echo htmlspecialchars($ek['deskripsi'] ?? ''); ?>">
              <div class="thumb" style="background-image:url('<?php echo $imgUrl; ?>');"></div><div class="label"><?php echo htmlspecialchars($ek['nama']); ?></div>
            </button>
            <?php endforeach; ?>
            <?php if (!$ekskulList): ?>
              <p style="color:var(--muted);">Data ekstrakurikuler belum ditambahkan oleh admin.</p>
            <?php endif; ?>
          </div>
        </div>

        <div class="editorial-panel" id="panel-fasilitas">
          <h3>Fasilitas Sekolah</h3>
          <p>Fasilitas belajar yang lengkap dan terawat menjadi salah satu prioritas kami dalam mendukung proses pembelajaran dan praktik siswa.</p>
          <div class="facility-grid">
            <?php foreach ($fasilitasList as $fa):
              $imgUrl = $fa['foto'] ? 'uploads/fasilitas/' . htmlspecialchars($fa['foto']) : 'https://ui-avatars.com/api/?name=' . urlencode($fa['nama']) . '&background=eef4ff&color=062B5C&size=256';
            ?>
            <button type="button" class="facility-item" data-detail-title="<?php echo htmlspecialchars($fa['nama']); ?>" data-detail-img="<?php echo $imgUrl; ?>" data-detail-desc="<?php echo htmlspecialchars($fa['deskripsi'] ?? ''); ?>">
              <div class="thumb" style="background-image:url('<?php echo $imgUrl; ?>');"></div>
              <div class="label"><?php echo htmlspecialchars($fa['nama']); ?></div>
            </button>
            <?php endforeach; ?>
            <?php if (!$fasilitasList): ?>
              <p style="color:var(--muted);">Data fasilitas belum ditambahkan oleh admin.</p>
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
<script>if (location.hash === '#panel-prestasi') { location.replace('berita.php#panel-prestasi'); }</script>
<script src="js/script.js"></script>
</body>
</html>
