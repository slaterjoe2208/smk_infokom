<?php
require_once __DIR__ . '/../includes/session-init.php';
require_once __DIR__ . '/../includes/auth-check.php';
requireRole('admin');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/upload-helper.php';
require_once __DIR__ . '/../includes/magang-jepang-helper.php';

$errors = [];
$success = '';
$act = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['action'] ?? '') : '';

if ($act === 'add' || $act === 'edit') {
    $id = (int)($_POST['id'] ?? 0);
    $platform = ($_POST['platform'] ?? '') === 'YouTube' ? 'YouTube' : 'Instagram';
    $akun = ltrim(clean_text($_POST['akun'] ?? '', 80), '@');
    $ket = clean_text($_POST['keterangan'] ?? '', 120);
    $link = trim($_POST['link'] ?? '');
    $tahun = (int)($_POST['tahun'] ?? 0);
    $urutan = (int)($_POST['urutan'] ?? 0);
    $status = ($_POST['status'] ?? 'Aktif') === 'Nonaktif' ? 'Nonaktif' : 'Aktif';

    if ($akun === '' || !preg_match('/^[A-Za-z0-9._\-]+$/', $akun)) $errors[] = 'Nama akun wajib diisi (huruf, angka, titik, garis bawah).';
    if (!mj_valid_link($link, $platform)) $errors[] = 'Link harus diawali https:// dan berasal dari ' . ($platform === 'YouTube' ? 'youtube.com / youtu.be' : 'instagram.com') . '.';
    if ($tahun < 2000 || $tahun > 2100) $errors[] = 'Tahun tidak valid.';
    if ($act === 'edit' && $id <= 0) $errors[] = 'Data tidak ditemukan.';
    if ($ket === '') $ket = 'Audio asli';

    if (!$errors) {
        try {
            $thumb = smkn1_handle_upload('thumbnail', 'magang-jepang');
            if ($act === 'add') {
                $pdo->prepare('INSERT INTO magang_jepang_post (platform, akun, keterangan, link, thumbnail, tahun, urutan, status) VALUES (?,?,?,?,?,?,?,?)')
                    ->execute([$platform, $akun, $ket, $link, $thumb, $tahun, $urutan, $status]);
                $success = 'Kartu video berhasil ditambahkan.';
            } else {
                $old = $pdo->prepare('SELECT thumbnail FROM magang_jepang_post WHERE id = ?');
                $old->execute([$id]);
                $oldThumb = $old->fetchColumn();
                $pdo->prepare('UPDATE magang_jepang_post SET platform=?, akun=?, keterangan=?, link=?, thumbnail=COALESCE(?,thumbnail), tahun=?, urutan=?, status=? WHERE id=?')
                    ->execute([$platform, $akun, $ket, $link, $thumb, $tahun, $urutan, $status, $id]);
                if ($thumb) smkn1_delete_upload($oldThumb ?: null, 'magang-jepang');
                $success = 'Kartu video berhasil diperbarui.';
            }
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
}

if ($act === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $old = $pdo->prepare('SELECT thumbnail FROM magang_jepang_post WHERE id = ?');
        $old->execute([$id]);
        $t = $old->fetchColumn();
        $pdo->prepare('DELETE FROM magang_jepang_post WHERE id = ?')->execute([$id]);
        smkn1_delete_upload($t ?: null, 'magang-jepang');
        $success = 'Kartu video berhasil dihapus.';
    }
}

$list = $pdo->query('SELECT * FROM magang_jepang_post ORDER BY urutan ASC, created_at DESC, id DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Magang Jepang — SMK Infokom</title>
<meta name="description" content="Kelola kartu video Magang Jepang SMK Infokom.">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/style.css">
</head>
<body class="dashboard-body ">
<div class="app-shell">
  <aside class="sidebar">
    <div class="sidebar-head">
      <a href="../index.php" class="brand">
        <span class="brand-mark"><img src="../assets/images/logo-infokom.png" alt="Logo SMK Infokom" loading="lazy"></span>
        <span class="brand-text"><strong>SMK INFOKOM</strong><span>BOGOR</span></span>
      </a>
    </div>
    <?php include __DIR__ . '/_sidebar-nav.php'; ?>
    <div class="sidebar-bottom">
      <a href="pengaturan.php" class="nav-link" data-tooltip="Pengaturan"><span class="nav-ic">⚙️</span><span class="label">Pengaturan</span></a>
      <a href="#" class="nav-link" data-tooltip="Pusat Bantuan"><span class="nav-ic">❓</span><span class="label">Pusat Bantuan</span></a>
      <div class="sidebar-profile">
        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user']['name']); ?>&background=062B5C&color=fff&bold=true" alt="">
        <div class="who">
          <strong><?php echo htmlspecialchars($_SESSION['user']['name']); ?></strong>
          <span>Administrator</span>
        </div>
      </div>
    </div>
  </aside>
  <div class="sidebar-scrim"></div>
  <div class="main-wrap">
    <header class="topbar">
      <div class="topbar-left">
        <button class="sidebar-toggle" id="sidebarCollapseBtn" aria-label="Ciutkan sidebar">☰</button>
        <button class="sidebar-toggle" id="sidebarMobileBtn" aria-label="Buka menu" style="display:none;">☰</button>
        <div class="topbar-title">Magang Jepang</div>
      </div>
      <div class="topbar-right">
        <div class="dropdown">
          <button class="dropdown-trigger" style="display:flex;align-items:center;gap:10px;background:none;border:none;padding:4px;" aria-label="Menu profil">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user']['name']); ?>&background=FFFFFF&color=062B5C&bold=true" alt="" style="width:36px;height:36px;border-radius:50%;">
          </button>
          <div class="dropdown-menu">
            <div class="dropdown-header"><strong><?php echo htmlspecialchars($_SESSION['user']['name']); ?></strong><span>Administrator</span></div>
            <div class="divider"></div>
            <button type="button" onclick="window.location.href='../logout.php';">🚪 Keluar</button>
          </div>
        </div>
      </div>
    </header>
    <div class="content">

<div class="page-head">
  <div>
    <h1>Magang Jepang</h1>
    <p>Tambahkan link Instagram Reels / YouTube — tampil sebagai kartu di halaman <a href="../magang-jepang.php" target="_blank" rel="noopener">Magang Jepang</a>. Saat kartu diklik, pengunjung langsung dibawa ke video aslinya.</p>
  </div>
  <button class="btn btn-primary" data-modal-open="#modalTambah">+ Tambah Video</button>
</div>

<?php if ($success): ?><div class="login-disclaimer" style="background:#e8f5e9;color:#1b5e20;margin-bottom:20px;"><span>✅</span><span><?php echo e($success); ?></span></div><?php endif; ?>
<?php foreach ($errors as $err): ?><div class="login-disclaimer" style="background:#fdecea;color:#b3261e;margin-bottom:20px;"><span>⚠️</span><span><?php echo e($err); ?></span></div><?php endforeach; ?>

<div class="table-card">
  <div class="table-scroll">
    <table class="data-table">
      <thead><tr><th>Thumbnail</th><th>Platform</th><th>Akun</th><th>Tahun</th><th>Link</th><th>Urutan</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($list as $it): ?>
        <tr>
          <td><?php if ($it['thumbnail']): ?><img class="bkk-admin-thumb" src="../uploads/magang-jepang/<?php echo e($it['thumbnail']); ?>" alt=""><?php else: ?><span class="text-muted">—</span><?php endif; ?></td>
          <td><span class="badge badge-info"><?php echo e($it['platform']); ?></span></td>
          <td><?php echo e($it['akun']); ?></td>
          <td><?php echo (int)$it['tahun']; ?></td>
          <td><a href="<?php echo e($it['link']); ?>" target="_blank" rel="noopener noreferrer">Buka ↗</a></td>
          <td><?php echo (int)$it['urutan']; ?></td>
          <td><span class="badge <?php echo $it['status'] === 'Aktif' ? 'badge-success' : ''; ?>" style="<?php echo $it['status'] === 'Aktif' ? '' : 'background:#fdecea;color:#b3261e;'; ?>"><?php echo e($it['status']); ?></span></td>
          <td style="display:flex;gap:6px;flex-wrap:wrap;">
            <button type="button" class="btn btn-ghost btn-sm" onclick='
              window.SMKN1.openModal(document.getElementById("modalEdit"));
              document.getElementById("editId").value = <?php echo (int)$it["id"]; ?>;
              document.getElementById("editPlatform").value = <?php echo json_encode($it["platform"]); ?>;
              document.getElementById("editTahun").value = <?php echo (int)$it["tahun"]; ?>;
              document.getElementById("editLink").value = <?php echo json_encode($it["link"]); ?>;
              document.getElementById("editAkun").value = <?php echo json_encode($it["akun"]); ?>;
              document.getElementById("editKet").value = <?php echo json_encode($it["keterangan"]); ?>;
              document.getElementById("editUrutan").value = <?php echo (int)$it["urutan"]; ?>;
              document.getElementById("editStatus").value = <?php echo json_encode($it["status"]); ?>;
            '>✏️ Edit</button>
            <form method="POST" style="display:inline;" onsubmit="return confirm(&quot;Yakin hapus kartu ini?&quot;);"><?php echo csrf_field(); ?>
              <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo (int)$it['id']; ?>">
              <button type="submit" class="btn btn-ghost btn-sm" style="color:#b3261e;">Hapus</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$list): ?><tr><td colspan="8" style="text-align:center;">Belum ada video. Klik &ldquo;+ Tambah Video&rdquo;.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal" id="modalTambah">
  <div class="modal-overlay" data-modal-close></div>
  <div class="modal-dialog">
    <div class="modal-head"><h3>Tambah Video</h3><button class="modal-close" data-modal-close aria-label="Tutup">✕</button></div>
    <form method="POST" enctype="multipart/form-data"><?php echo csrf_field(); ?>
      <input type="hidden" name="action" value="add">
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group"><label>Platform</label>
            <select name="platform" id="addPlatform" class="form-control"><option value="Instagram">Instagram (Reels)</option><option value="YouTube">YouTube</option></select></div>
          <div class="form-group"><label>Tahun</label><input type="number" name="tahun" id="addTahun" class="form-control" min="2000" max="2100" value="<?php echo date('Y'); ?>" required></div>
        </div>
        <div class="form-group"><label>Link Video (saat kartu diklik, pengunjung dibawa ke sini)</label>
          <input type="url" name="link" id="addLink" class="form-control" placeholder="https://www.instagram.com/reel/xxxxx/" required></div>
        <div class="form-row">
          <div class="form-group"><label>Nama Akun</label><input type="text" name="akun" id="addAkun" class="form-control" maxlength="80" value="smk.infokom" required></div>
          <div class="form-group"><label>Keterangan</label><input type="text" name="keterangan" id="addKet" class="form-control" maxlength="120" value="Audio asli"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>Urutan (kecil = paling awal)</label><input type="number" name="urutan" id="addUrutan" class="form-control" value="0"></div>
          <div class="form-group"><label>Status</label>
            <select name="status" id="addStatus" class="form-control"><option value="Aktif">Aktif</option><option value="Nonaktif">Nonaktif</option></select></div>
        </div>
        <div class="form-group"><label>Thumbnail (screenshot/cover video — JPG, PNG, WEBP, maks 3 MB)</label><input type="file" name="thumbnail" class="form-control" accept="image/jpeg,image/png,image/webp"></div>
        <p class="text-muted" style="font-size:12.5px;">Instagram tidak mengizinkan gambar diambil otomatis dari link, jadi unggah cover-nya di sini. Tanpa thumbnail, kartu memakai latar biru polos.</p>
      </div>
      <div class="modal-foot"><button type="button" class="btn btn-ghost" data-modal-close>Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
    </form>
  </div>
</div>

<div class="modal" id="modalEdit">
  <div class="modal-overlay" data-modal-close></div>
  <div class="modal-dialog">
    <div class="modal-head"><h3>Edit Video</h3><button class="modal-close" data-modal-close aria-label="Tutup">✕</button></div>
    <form method="POST" enctype="multipart/form-data"><?php echo csrf_field(); ?>
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" id="editId">
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group"><label>Platform</label>
            <select name="platform" id="editPlatform" class="form-control"><option value="Instagram">Instagram (Reels)</option><option value="YouTube">YouTube</option></select></div>
          <div class="form-group"><label>Tahun</label><input type="number" name="tahun" id="editTahun" class="form-control" min="2000" max="2100" value="<?php echo date('Y'); ?>" required></div>
        </div>
        <div class="form-group"><label>Link Video (saat kartu diklik, pengunjung dibawa ke sini)</label>
          <input type="url" name="link" id="editLink" class="form-control" placeholder="https://www.instagram.com/reel/xxxxx/" required></div>
        <div class="form-row">
          <div class="form-group"><label>Nama Akun</label><input type="text" name="akun" id="editAkun" class="form-control" maxlength="80" value="smk.infokom" required></div>
          <div class="form-group"><label>Keterangan</label><input type="text" name="keterangan" id="editKet" class="form-control" maxlength="120" value="Audio asli"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>Urutan (kecil = paling awal)</label><input type="number" name="urutan" id="editUrutan" class="form-control" value="0"></div>
          <div class="form-group"><label>Status</label>
            <select name="status" id="editStatus" class="form-control"><option value="Aktif">Aktif</option><option value="Nonaktif">Nonaktif</option></select></div>
        </div>
        <div class="form-group"><label>Ganti Thumbnail (kosongkan jika tidak diubah)</label><input type="file" name="thumbnail" class="form-control" accept="image/jpeg,image/png,image/webp"></div>
      </div>
      <div class="modal-foot"><button type="button" class="btn btn-ghost" data-modal-close>Batal</button><button type="submit" class="btn btn-primary">Simpan Perubahan</button></div>
    </form>
  </div>
</div>

    </div>
  </div>
</div>
<script src="../js/script.js"></script>
</body>
</html>
