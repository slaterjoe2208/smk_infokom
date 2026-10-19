<?php
// Bukti pendaftaran PPDB (PDF). Diakses lewat tautan berisi token acak yang dikirim panitia lewat WhatsApp.
// Hanya pendaftar berstatus Diterima yang bisa membuka PDF ini.
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/ppdb-helper.php';

$t = $_GET['t'] ?? '';
if (!is_string($t) || !preg_match('/^[a-f0-9]{32}$/', $t)) { http_response_code(404); exit('Tautan tidak valid.'); }

$st = $pdo->prepare("SELECT p.*, d.nomor_pendaftaran, d.tempat_lahir, d.tanggal_lahir, d.jenis_kelamin, d.wali_nama, d.wali_hubungan,
                            j.nama AS jurusan_nama, g.nomor AS gelombang_nomor
                     FROM ppdb_detail d
                     JOIN ppdb_pendaftaran p ON p.id = d.pendaftaran_id
                     LEFT JOIN jurusan j ON UPPER(j.kode) = p.jurusan_pilihan
                     LEFT JOIN ppdb_gelombang g ON g.id = p.gelombang_id
                     WHERE d.bukti_token = ? AND p.status = 'Diterima' LIMIT 1");
$st->execute([$t]);
$r = $st->fetch();
if (!$r) { http_response_code(404); exit('Bukti pendaftaran tidak ditemukan.'); }

$r['nomor'] = $r['nomor_pendaftaran'];
$r['nama'] = $r['nama_lengkap'];
$r['jurusan_nama'] = $r['jurusan_pilihan'] . ' - ' . ($r['jurusan_nama'] ?: '');
$r['gelombang'] = $r['gelombang_nomor'] ? 'Gelombang ' . (int)$r['gelombang_nomor'] : '';

$pdfData = ppdb_bukti_pdf($r);
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="Bukti-Pendaftaran-' . preg_replace('/[^A-Za-z0-9\-]/', '', $r['nomor']) . '.pdf"');
header('Content-Length: ' . strlen($pdfData));
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: private, no-store');
echo $pdfData;
