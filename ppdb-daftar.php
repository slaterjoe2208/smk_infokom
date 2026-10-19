<?php
require_once __DIR__ . '/includes/session-init.php';
require_once __DIR__ . '/includes/session-widget.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/settings-helper.php';
require_once __DIR__ . '/includes/ppdb-helper.php';
require_once __DIR__ . '/includes/gelombang-helper.php';

// Pendaftaran TIDAK memerlukan login. Akun calon siswa dibuat otomatis
// setelah formulir berhasil dikirim.
// PPDB ON/OFF tetap dicek di backend supaya tidak bisa dikirim saat OFF.
// Formulir terbuka hanya jika tombol PPDB ON dan ada gelombang Aktif (jadwal cocok, kuota belum penuh).
$gelombangAktif = smkn1_gelombang_aktif($pdo);
$ppdbOpen = smkn1_ppdb_is_open($pdo) && $gelombangAktif !== null;
$errors = [];
$old = [];

// Pilihan jurusan diambil dari database (hanya kode yang didukung kolom pendaftaran).
$jurusanOptions = [];
foreach ($pdo->query('SELECT kode, nama FROM jurusan ORDER BY nama') as $r) {
    $k = strtoupper($r['kode']);
    if (in_array($k, PPDB_JURUSAN_KODE, true)) $jurusanOptions[$k] = $r['nama'];
}
if (!$jurusanOptions) {
    $jurusanOptions = ['RPL' => 'Rekayasa Perangkat Lunak', 'TKJ' => 'Teknik Komputer & Jaringan', 'DKV' => 'Desain Komunikasi Visual', 'PSPT' => 'Produksi dan Siaran Program Televisi'];
}
// Dari halaman Program: ppdb-daftar.php?jurusan=RPL -> jurusan langsung terpilih.
$preselect = strtoupper(trim($_GET['jurusan'] ?? ''));
if (isset($jurusanOptions[$preselect])) $old['jurusan'] = $preselect;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;
    $g = fn(string $k, int $max = 255): string => clean_text($_POST[$k] ?? '', $max);

    if (!$ppdbOpen) {
        $errors[] = 'Pendaftaran PPDB sedang ditutup. Formulir tidak dapat dikirim.';
    } elseif (!csrf_valid()) {
        $errors[] = 'Sesi formulir kedaluwarsa. Silakan muat ulang halaman lalu isi kembali.';
    } elseif (!empty($_POST['website'])) {
        $errors[] = 'Pengiriman ditolak.'; // honeypot: kolom ini tersembunyi, hanya diisi bot
    } elseif (time() - (int)($_SESSION['ppdb_last_submit'] ?? 0) < 15) {
        $errors[] = 'Mohon tunggu beberapa detik sebelum mengirim ulang.';
    } else {
        // ---------- Data calon siswa ----------
        $nama = $g('nama', 150);
        $panggilan = $g('panggilan', 60);
        $nisn = preg_replace('/\D/', '', $g('nisn', 20));
        $nik = preg_replace('/\D/', '', $g('nik', 20));
        $tempatLahir = $g('tempat_lahir', 100);
        $tglLahir = $g('tanggal_lahir', 10);
        $jk = $g('jenis_kelamin', 20);
        $agama = $g('agama', 30);
        $alamat = $g('alamat', 300);
        $rtRw = $g('rt_rw', 15);
        $kelurahan = $g('kelurahan', 100);
        $kecamatan = $g('kecamatan', 100);
        $kota = $g('kota', 100);
        $provinsi = $g('provinsi', 100);
        $wa = normalize_wa($g('no_wa', 20));
        $email = mb_strtolower($g('email', 150));

        if (mb_strlen($nama) < 3 || !preg_match("/^[\p{L}\s.'\-]+$/u", $nama)) $errors[] = 'Nama lengkap harus diisi (minimal 3 huruf, tanpa angka).';
        if (!preg_match('/^\d{10}$/', $nisn)) $errors[] = 'NISN harus 10 digit angka.';
        if (!preg_match('/^\d{16}$/', $nik)) $errors[] = 'NIK harus 16 digit angka.';
        if ($tempatLahir === '') $errors[] = 'Tempat lahir wajib diisi.';
        $dt = DateTime::createFromFormat('Y-m-d', $tglLahir);
        if (!$dt || $dt->format('Y-m-d') !== $tglLahir) {
            $errors[] = 'Tanggal lahir tidak valid.';
        } else {
            $umur = (int)$dt->diff(new DateTime('today'))->y;
            if ($dt > new DateTime('today') || $umur < 11 || $umur > 22) $errors[] = 'Tanggal lahir tidak wajar untuk calon siswa SMK. Periksa kembali.';
        }
        if (!in_array($jk, ['Laki-laki', 'Perempuan'], true)) $errors[] = 'Pilih jenis kelamin.';
        if (!in_array($agama, PPDB_AGAMA, true)) $errors[] = 'Pilih agama.';
        if ($alamat === '' || $kelurahan === '' || $kecamatan === '' || $kota === '' || $provinsi === '') $errors[] = 'Alamat lengkap (alamat, kelurahan, kecamatan, kota/kabupaten, provinsi) wajib diisi.';
        if ($rtRw !== '' && !preg_match('/^\d{1,3}\s*\/\s*\d{1,3}$/', $rtRw)) $errors[] = 'Format RT/RW tidak sesuai. Contoh: 003/008.';
        if (!$wa) $errors[] = 'Nomor WhatsApp calon siswa tidak valid. Contoh: 081234567890.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Alamat email tidak valid.';

        // ---------- Akademik ----------
        $asal = $g('asal_sekolah', 150);
        $npsn = preg_replace('/\D/', '', $g('npsn', 15));
        $tahunLulus = (int)$g('tahun_lulus', 4);
        $nilaiRaw = str_replace(',', '.', $g('nilai_rata', 8));
        $jurusan = strtoupper($g('jurusan', 10));
        if ($asal === '') $errors[] = 'Asal sekolah wajib diisi.';
        if ($npsn !== '' && !preg_match('/^\d{8}$/', $npsn)) $errors[] = 'NPSN harus 8 digit angka (kosongkan jika tidak tahu).';
        if ($tahunLulus < 2000 || $tahunLulus > (int)date('Y') + 1) $errors[] = 'Tahun lulus tidak valid.';
        if ($nilaiRaw !== '' && (!is_numeric($nilaiRaw) || (float)$nilaiRaw < 0 || (float)$nilaiRaw > 100)) $errors[] = 'Nilai rata-rata harus berupa angka 0 - 100.';
        if (!isset($jurusanOptions[$jurusan])) $errors[] = 'Pilih program/jurusan yang diminati.';

        // ---------- Orang tua / wali (satu orang saja) ----------
        $wali = [
            'nama' => $g('wali_nama', 150),
            'hubungan' => $g('wali_hubungan', 50),
            'nik' => preg_replace('/\D/', '', $g('wali_nik', 20)),
            'pendidikan' => $g('wali_pendidikan', 50),
            'pekerjaan' => $g('wali_pekerjaan', 100),
            'wa' => $g('wali_wa', 20),
            'penghasilan' => $g('wali_penghasilan', 50),
        ];
        if ($wali['nama'] === '') $errors[] = 'Nama orang tua/wali wajib diisi.';
        if (!in_array($wali['hubungan'], PPDB_HUBUNGAN_WALI, true)) $errors[] = 'Pilih hubungan wali dengan calon siswa.';
        if ($wali['nik'] !== '' && !preg_match('/^\d{16}$/', $wali['nik'])) $errors[] = 'NIK wali harus 16 digit angka.';
        $waliWa = normalize_wa($wali['wa']);
        if (!$waliWa) $errors[] = 'Nomor WhatsApp orang tua/wali tidak valid.';
        $wali['wa'] = $waliWa;
        if ($wali['pendidikan'] !== '' && !in_array($wali['pendidikan'], PPDB_PENDIDIKAN, true)) $wali['pendidikan'] = '';
        if ($wali['penghasilan'] !== '' && !in_array($wali['penghasilan'], PPDB_PENGHASILAN, true)) $wali['penghasilan'] = '';
        if (!isset($_POST['setuju'])) $errors[] = 'Anda harus menyetujui pernyataan bahwa data yang diisi benar.';

        // ---------- Keunikan data ----------
        if (!$errors) {
            $q = $pdo->prepare('SELECT 1 FROM ppdb_pendaftaran WHERE nisn = ? LIMIT 1');
            $q->execute([$nisn]);
            if ($q->fetchColumn()) $errors[] = 'NISN ini sudah pernah terdaftar. Hubungi panitia PPDB bila merasa belum mendaftar.';
            $q = $pdo->prepare('SELECT 1 FROM users WHERE email = ? UNION SELECT 1 FROM ppdb_detail WHERE email = ? LIMIT 1');
            $q->execute([$email, $email]);
            if ($q->fetchColumn()) $errors[] = 'Email ini sudah dipakai pendaftar atau akun lain. Gunakan alamat email yang berbeda.';
        }

        // ---------- Dokumen + simpan ----------
        if (!$errors) {
            $docs = [];
            foreach (ppdb_doc_config() as $key => $cfg) {
                $docs[$key] = ppdb_save_document($key, $cfg, $errors);
            }
            if ($errors) ppdb_delete_documents($docs);
        }

        if (!$errors) {
            try {
                $pdo->beginTransaction();

                // Akun login TIDAK dibuat sekarang: username & password baru dibuat dan
                // dikirim lewat WhatsApp setelah admin menekan status "Diterima".
                // 1. Data pendaftaran utama (user_id kosong sampai pendaftar diterima)
                $alamatLengkap = $alamat . ($rtRw !== '' ? ', RT/RW ' . $rtRw : '') . ', ' . $kelurahan . ', ' . $kecamatan . ', ' . $kota . ', ' . $provinsi;
                $pdo->prepare('INSERT INTO ppdb_pendaftaran (user_id, nama_lengkap, nisn, asal_sekolah, jurusan_pilihan, no_hp, alamat, gelombang_id) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?)')
                    ->execute([$nama, $nisn, $asal, $jurusan, $wa, $alamatLengkap, (int)$gelombangAktif['id']]);
                $pid = (int)$pdo->lastInsertId();
                $nomor = 'PPDB-' . date('Y') . '-' . str_pad((string)$pid, 4, '0', STR_PAD_LEFT);

                // 2. Detail formulir lengkap
                $detail = [
                    'pendaftaran_id' => $pid, 'nomor_pendaftaran' => $nomor,
                    'nik' => $nik, 'nama_panggilan' => $panggilan ?: null, 'tempat_lahir' => $tempatLahir,
                    'tanggal_lahir' => $tglLahir, 'jenis_kelamin' => $jk, 'agama' => $agama, 'rt_rw' => $rtRw ?: null,
                    'kelurahan' => $kelurahan, 'kecamatan' => $kecamatan, 'kota' => $kota, 'provinsi' => $provinsi,
                    'email' => $email, 'npsn' => $npsn ?: null, 'tahun_lulus' => $tahunLulus,
                    'nilai_rata' => $nilaiRaw !== '' ? (float)$nilaiRaw : null,
                    'wali_nama' => $wali['nama'], 'wali_hubungan' => $wali['hubungan'], 'wali_nik' => $wali['nik'] ?: null,
                    'wali_pendidikan' => $wali['pendidikan'] ?: null, 'wali_pekerjaan' => $wali['pekerjaan'] ?: null,
                    'wali_wa' => $wali['wa'], 'wali_penghasilan' => $wali['penghasilan'] ?: null,
                ] + $docs;
                $cols = implode(', ', array_keys($detail));
                $marks = implode(', ', array_fill(0, count($detail), '?'));
                $pdo->prepare("INSERT INTO ppdb_detail ($cols) VALUES ($marks)")->execute(array_values($detail));

                $pdo->commit();

                // Data sukses disimpan sementara di sesi (BUKAN auto-login) untuk halaman sukses.
                $_SESSION['ppdb_last_submit'] = time();
                $_SESSION['ppdb_sukses'] = [
                    'nama' => $nama, 'nomor' => $nomor,
                    'jurusan' => $jurusanOptions[$jurusan], 'gelombang' => $gelombangAktif['nama'], 'status' => 'Menunggu', 'expires' => time() + 1800,
                ];
                header('Location: ppdb-sukses.php');
                exit;
            } catch (Throwable $ex) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                ppdb_delete_documents($docs);
                error_log('[PPDB] gagal menyimpan pendaftaran: ' . $ex->getMessage()); // detail teknis hanya ke log server
                $errors[] = 'Maaf, data belum berhasil disimpan karena kendala sistem. Data Anda tidak hilang di layar; silakan coba kirim lagi beberapa saat lagi.';
            }
        }
    }
}

// ---------- Helper tampilan form ----------
function pf_val(array $old, string $k): string { return e($old[$k] ?? ''); }
function pf_input(array $old, string $name, string $label, array $o = []): void
{
    $req = !empty($o['required']);
    $attrs = '';
    foreach (['placeholder', 'maxlength', 'inputmode', 'pattern', 'autocomplete', 'min', 'max', 'step', 'title'] as $a) {
        if (isset($o[$a])) $attrs .= ' ' . $a . '="' . e($o[$a]) . '"';
    }
    echo '<div class="form-group' . (!empty($o['full']) ? ' is-full' : '') . '">';
    echo '<label for="f_' . $name . '">' . e($label) . ($req ? ' <span class="req" aria-hidden="true">*</span>' : '') . '</label>';
    echo '<input class="form-control" id="f_' . $name . '" name="' . $name . '" type="' . e($o['type'] ?? 'text') . '" value="' . pf_val($old, $name) . '"' . $attrs . ($req ? ' required' : '') . ' data-label="' . e($label) . '">';
    if (!empty($o['hint'])) echo '<small class="field-hint">' . e($o['hint']) . '</small>';
    echo '</div>';
}
function pf_select(array $old, string $name, string $label, array $options, array $o = []): void
{
    $req = !empty($o['required']);
    echo '<div class="form-group' . (!empty($o['full']) ? ' is-full' : '') . '">';
    echo '<label for="f_' . $name . '">' . e($label) . ($req ? ' <span class="req" aria-hidden="true">*</span>' : '') . '</label>';
    echo '<select class="form-control" id="f_' . $name . '" name="' . $name . '"' . ($req ? ' required' : '') . ' data-label="' . e($label) . '"><option value="">— Pilih —</option>';
    foreach ($options as $v) {
        echo '<option value="' . e($v) . '"' . (($old[$name] ?? '') === $v ? ' selected' : '') . '>' . e($v) . '</option>';
    }
    echo '</select></div>';
}
function pf_file(string $key, array $cfg): void
{
    $accept = in_array('pdf', $cfg['types'], true) ? '.jpg,.jpeg,.png,.pdf' : '.jpg,.jpeg,.png';
    $fmt = in_array('pdf', $cfg['types'], true) ? 'JPG, PNG, atau PDF' : 'JPG atau PNG';
    echo '<div class="form-group is-full"><label for="f_' . $key . '">' . e($cfg['label']) . (!empty($cfg['required']) ? ' <span class="req">*</span>' : '') . '</label>';
    echo '<label class="file-drop" for="f_' . $key . '"><span class="file-drop-ic" aria-hidden="true">⬆</span><span class="file-drop-text"><strong>Pilih file</strong><small>' . $fmt . ' · maks. 2 MB</small></span><span class="file-drop-name" data-file-name>Belum ada file</span></label>';
    echo '<input class="file-input" type="file" id="f_' . $key . '" name="' . $key . '" accept="' . $accept . '" data-label="' . e($cfg['label']) . '"' . (!empty($cfg['required']) ? ' required' : '') . '></div>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Daftar PPDB — SMK Infokom</title>
<meta name="description" content="Formulir pendaftaran online Penerimaan Peserta Didik Baru SMK Infokom. Tanpa login, akun dibuat otomatis.">
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
    <?php echo smkn1_navbar_session_html('mobile'); ?>
  </div>
</div>

<main id="main">
  <section class="page-hero">
    <div class="container">
      <h1>Formulir Pendaftaran PPDB</h1>
      <p class="breadcrumb"><a href="index.php">Beranda</a> / <a href="ppdb.php">PPDB</a> / Daftar</p>
    </div>
  </section>

  <section class="section reg-section">
    <div class="container reg-container">

      <?php if (!$ppdbOpen): ?>
      <div class="card card-pad reveal reg-closed">
        <div class="reg-closed-ic" aria-hidden="true">🔒</div>
        <h2>PPDB Sedang Ditutup</h2>
        <p class="text-muted">Mohon maaf, saat ini tidak ada gelombang pendaftaran yang sedang dibuka (atau kuota gelombang berjalan sudah penuh), sehingga formulir tidak dapat diisi atau dikirim. Lihat jadwal 3 gelombang di halaman PPDB.</p>
        <a href="ppdb.php#gelombang" class="btn btn-primary">Lihat Jadwal Gelombang</a>
      </div>

      <?php else: ?>

      <div class="reg-intro reveal">
        <span class="eyebrow"><?php echo e($gelombangAktif['nama']); ?></span>
        <h2>Isi formulir, akun Anda dibuat otomatis</h2>
        <p class="text-muted"><strong>Periode:</strong> <?php echo e(smkn1_tgl_gelombang($gelombangAktif['tgl_mulai'])); ?> – <?php echo e(smkn1_tgl_gelombang($gelombangAktif['tgl_selesai'])); ?> &middot; <strong>Biaya:</strong> <?php echo e(smkn1_gelombang_biaya($gelombangAktif['biaya'])); ?>. Tanpa perlu login. Setelah formulir terkirim, sistem membuatkan <strong>username dan password</strong> untuk login, lalu Anda diarahkan ke WhatsApp panitia untuk konfirmasi.</p>
      </div>

      <ol class="reg-steps" id="regSteps" aria-label="Tahapan pengisian">
        <li class="is-active" data-step-ind="1"><span class="reg-step-num">1</span><span class="reg-step-label">Data Siswa</span></li>
        <li data-step-ind="2"><span class="reg-step-num">2</span><span class="reg-step-label">Orang Tua/Wali</span></li>
        <li data-step-ind="3"><span class="reg-step-num">3</span><span class="reg-step-label">Akademik</span></li>
        <li data-step-ind="4"><span class="reg-step-num">4</span><span class="reg-step-label">Dokumen</span></li>
      </ol>

      <?php if ($errors): ?>
      <div class="reg-alert" role="alert" id="regAlert">
        <strong>Formulir belum bisa dikirim. Mohon periksa:</strong>
        <ul><?php foreach ($errors as $er): ?><li><?php echo e($er); ?></li><?php endforeach; ?></ul>
        <small>Catatan: karena alasan keamanan browser, file dokumen perlu dipilih ulang.</small>
      </div>
      <?php endif; ?>

      <form id="regForm" class="reg-form" method="POST" action="ppdb-daftar.php" enctype="multipart/form-data" autocomplete="on">
        <?php echo csrf_field(); ?>
        <div class="hp-field" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

        <!-- ============ LANGKAH 1: DATA CALON SISWA ============ -->
        <fieldset class="reg-step is-active" data-step="1">
          <legend>Data Calon Siswa</legend>
          <div class="reg-grid">
            <?php pf_input($old, 'nama', 'Nama lengkap', ['required' => 1, 'full' => 1, 'maxlength' => 150, 'autocomplete' => 'name', 'hint' => 'Sesuai ijazah / akta kelahiran.']);
            pf_input($old, 'panggilan', 'Nama panggilan', ['maxlength' => 60]);
            pf_input($old, 'nisn', 'NISN', ['required' => 1, 'inputmode' => 'numeric', 'maxlength' => 10, 'pattern' => '\d{10}', 'title' => '10 digit angka', 'hint' => '10 digit angka.']);
            pf_input($old, 'nik', 'NIK', ['required' => 1, 'inputmode' => 'numeric', 'maxlength' => 16, 'pattern' => '\d{16}', 'title' => '16 digit angka', 'hint' => '16 digit, sesuai Kartu Keluarga.']);
            pf_input($old, 'tempat_lahir', 'Tempat lahir', ['required' => 1, 'maxlength' => 100]);
            pf_input($old, 'tanggal_lahir', 'Tanggal lahir', ['required' => 1, 'type' => 'date', 'max' => date('Y-m-d')]);
            pf_select($old, 'jenis_kelamin', 'Jenis kelamin', ['Laki-laki', 'Perempuan'], ['required' => 1]);
            pf_select($old, 'agama', 'Agama', PPDB_AGAMA, ['required' => 1]);
            pf_input($old, 'alamat', 'Alamat (jalan/kampung)', ['required' => 1, 'full' => 1, 'maxlength' => 300, 'autocomplete' => 'street-address']);
            pf_input($old, 'rt_rw', 'RT/RW', ['maxlength' => 15, 'placeholder' => '003/008']);
            pf_input($old, 'kelurahan', 'Kelurahan/Desa', ['required' => 1, 'maxlength' => 100]);
            pf_input($old, 'kecamatan', 'Kecamatan', ['required' => 1, 'maxlength' => 100]);
            pf_input($old, 'kota', 'Kota/Kabupaten', ['required' => 1, 'maxlength' => 100, 'autocomplete' => 'address-level2']);
            pf_input($old, 'provinsi', 'Provinsi', ['required' => 1, 'maxlength' => 100, 'autocomplete' => 'address-level1']);
            pf_input($old, 'no_wa', 'Nomor WhatsApp', ['required' => 1, 'type' => 'tel', 'inputmode' => 'tel', 'maxlength' => 20, 'placeholder' => '0812xxxxxxxx', 'autocomplete' => 'tel']);
            pf_input($old, 'email', 'Email', ['required' => 1, 'type' => 'email', 'maxlength' => 150, 'autocomplete' => 'email', 'hint' => 'Satu email untuk satu pendaftar.']); ?>
          </div>
        </fieldset>

        <!-- ============ LANGKAH 2: ORANG TUA / WALI ============ -->
        <fieldset class="reg-step" data-step="2">
          <legend>Data Orang Tua / Wali</legend>
          <p class="reg-note">Isi satu orang saja sebagai orang tua / wali. Nomor WhatsApp wali dipakai panitia untuk menghubungi Anda.</p>
          <div class="reg-grid">
            <?php pf_input($old, 'wali_nama', 'Nama orang tua / wali', ['required' => 1, 'maxlength' => 150, 'full' => 1]);
            pf_select($old, 'wali_hubungan', 'Hubungan dengan calon siswa', PPDB_HUBUNGAN_WALI, ['required' => 1]);
            pf_input($old, 'wali_nik', 'NIK', ['inputmode' => 'numeric', 'maxlength' => 16, 'pattern' => '\\d{16}', 'title' => '16 digit angka', 'hint' => 'Opsional.']);
            pf_select($old, 'wali_pendidikan', 'Pendidikan terakhir', PPDB_PENDIDIKAN);
            pf_input($old, 'wali_pekerjaan', 'Pekerjaan', ['maxlength' => 100]);
            pf_input($old, 'wali_wa', 'Nomor WhatsApp', ['required' => 1, 'type' => 'tel', 'inputmode' => 'tel', 'maxlength' => 20, 'placeholder' => '081234567890']);
            pf_select($old, 'wali_penghasilan', 'Penghasilan per bulan', PPDB_PENGHASILAN); ?>
          </div>
        </fieldset>

        <!-- ============ LANGKAH 3: AKADEMIK ============ -->
        <fieldset class="reg-step" data-step="3">
          <legend>Data Akademik &amp; Pilihan Program</legend>
          <div class="reg-grid">
            <?php pf_input($old, 'asal_sekolah', 'Asal sekolah', ['required' => 1, 'full' => 1, 'maxlength' => 150, 'placeholder' => 'Contoh: SMP Negeri 1 ...']);
            pf_input($old, 'npsn', 'NPSN sekolah asal', ['inputmode' => 'numeric', 'maxlength' => 8, 'pattern' => '\d{8}', 'title' => '8 digit angka', 'hint' => 'Opsional, 8 digit.']);
            pf_input($old, 'tahun_lulus', 'Tahun lulus', ['required' => 1, 'inputmode' => 'numeric', 'maxlength' => 4, 'pattern' => '\d{4}', 'title' => '4 digit tahun', 'placeholder' => (string)date('Y')]);
            pf_input($old, 'nilai_rata', 'Nilai rata-rata', ['inputmode' => 'decimal', 'maxlength' => 6, 'hint' => 'Opsional. Angka 0 - 100.', 'full' => 1]); ?>
          </div>
          <h3 class="reg-sub">Program / jurusan yang diminati <span class="req">*</span></h3>
          <div class="major-grid">
            <?php foreach ($jurusanOptions as $kode => $namaJ): ?>
            <label class="major-card">
              <input type="radio" name="jurusan" value="<?php echo e($kode); ?>" data-label="Program/jurusan" data-display="<?php echo e($namaJ); ?>" <?php echo (($old['jurusan'] ?? '') === $kode) ? 'checked' : ''; ?> required>
              <span class="major-card-body"><strong><?php echo e($kode); ?></strong><small><?php echo e($namaJ); ?></small></span>
            </label>
            <?php endforeach; ?>
          </div>
        </fieldset>

        <!-- ============ LANGKAH 4: DOKUMEN ============ -->
        <fieldset class="reg-step" data-step="4">
          <legend>Unggah Dokumen</legend>
          <p class="reg-note">Format JPG/PNG atau PDF (khusus KK, akta, ijazah), maksimal 2 MB per file. Dokumen dapat dilengkapi kemudian bila belum tersedia.</p>
          <div class="reg-grid">
            <?php foreach (ppdb_doc_config() as $key => $cfg) pf_file($key, $cfg); ?>
          </div>
          <label class="reg-check">
            <input type="checkbox" name="setuju" value="1" required data-label="Pernyataan kebenaran data">
            <span>Saya menyatakan bahwa seluruh data yang saya isi adalah benar dan dapat dipertanggungjawabkan.</span>
          </label>
        </fieldset>

        <div class="reg-actions">
          <button type="button" class="btn btn-ghost" id="regPrev" hidden>← Kembali</button>
          <button type="button" class="btn btn-primary" id="regNext">Lanjut →</button>
          <button type="button" class="btn btn-accent" id="regReview" hidden>Periksa Data</button>
        </div>
        <noscript><button type="submit" class="btn btn-accent">Kirim Pendaftaran</button></noscript>

        <!-- Konfirmasi data sebelum kirim -->
        <div class="reg-modal" id="regConfirm" role="dialog" aria-modal="true" aria-labelledby="regConfirmTitle" hidden>
          <div class="reg-modal-backdrop" data-reg-close></div>
          <div class="reg-modal-card">
            <h3 id="regConfirmTitle">Konfirmasi Data Pendaftaran</h3>
            <p class="text-muted">Periksa kembali data Anda. Setelah dikirim, sistem membuat akun dan Anda diarahkan ke WhatsApp.</p>
            <div class="reg-summary" id="regSummary"></div>
            <div class="reg-modal-actions">
              <button type="button" class="btn btn-ghost" data-reg-close>Perbaiki Data</button>
              <button type="submit" class="btn btn-accent" id="regSubmit"><span class="btn-label">Kirim Pendaftaran</span></button>
            </div>
          </div>
        </div>
      </form>
      <?php endif; ?>

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

<script src="js/script.js"></script>
<script src="js/pendaftaran.js"></script>
</body>
</html>
