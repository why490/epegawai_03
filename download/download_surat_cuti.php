<?php
require_once '../config.php';
require_login();

$cuti_id = $_GET['id'] ?? '';
if (empty($cuti_id)) {
    die('ID cuti tidak ditemukan');
}

try {

    $stmt = $pdo->prepare("
        SELECT c.*, p.nip, p.nama, p.jabatan, p.unit_kerja, p.status_kepegawaian, p.golongan_ruangan, p.alamat, p.no_hp, p.tanggal_masuk,
               c.sisa_cuti_n as sisa_n, c.sisa_cuti_n_minus_1 as sisa_n1, c.sisa_cuti_n_minus_2 as sisa_n2
        FROM cuti c JOIN pegawai p ON c.id_pegawai = p.id WHERE c.id = ?
    ");

    $stmt->execute([$cuti_id]);
    $cuti_data = $stmt->fetch();
    if (!$cuti_data) {
        die('Data cuti tidak ditemukan');
    }
} catch (PDOException $e) {
    die('DB Error: ' . $e->getMessage());
}

require_once '../vendor/autoload.php';

$template_file = ($cuti_data['status_kepegawaian'] == 'PNS') ? 'template_surat_cuti_pns.docx' : 'template_surat_cuti_pppk.docx';
$template_path = TEMPLATE_PATH . '/' . $template_file;

if (!file_exists($template_path)) {
    die('Template missing: ' . $template_path);
}

$template = new \PhpOffice\PhpWord\TemplateProcessor($template_path);

// Calculate masa kerja from tanggal_masuk
$masa_kerja = '-';
if (!empty($cuti_data['tanggal_masuk'])) {
    $tanggal_masuk = new DateTime($cuti_data['tanggal_masuk']);
    $sekarang = new DateTime();
    $selisih = $tanggal_masuk->diff($sekarang);
    $masa_kerja = $selisih->y . ' Tahun ' . $selisih->m . ' Bulan';
}

// PHPWord standard: ${key} format


$replacements = [
    '${nomor_surat_cuti}' => $cuti_data['nomor_surat_cuti'] ?? '-',
    '${nama_pegawai}' => $cuti_data['nama'],
    '${nip}' => $cuti_data['nip'],
    '${jabatan}' => $cuti_data['jabatan'],
    '${unit_kerja}' => $cuti_data['unit_kerja'],
    '${status_kepegawaian}' => $cuti_data['status_kepegawaian'],
    '${golongan_ruangan}' => $cuti_data['golongan_ruangan'] ?? '',
    '${alamat}' => $cuti_data['alamat'] ?? '',
    '${no_hp}' => $cuti_data['no_hp'] ?? '',
    '${jenis_cuti}' => $cuti_data['jenis_cuti'],
    '${tanggal_mulai}' => format_date($cuti_data['tanggal_mulai'], 'd F Y'),
    '${tanggal_selesai}' => format_date($cuti_data['tanggal_selesai'], 'd F Y'),
    '${lama_cuti}' => $cuti_data['lama_cuti'],
    '${alasan_cuti}' => $cuti_data['alasan_cuti'],
    '${alamat_cuti}' => $cuti_data['alamat_cuti'] ?? '',
    '${no_telepon_cuti}' => $cuti_data['no_telepon_cuti'] ?? '',
    '${tanggal_pengajuan}' => format_date($cuti_data['tanggal_pengajuan'], 'd F Y'),
    '${nomor_surat}' => sprintf('SKP/%03d/CUTI/%d', $cuti_id, date('Y')),
    '${tanggal_sekarang}' => format_date(date('Y-m-d'), 'd F Y'),
    '${tanggal_saat_ini}' => date('d/m/Y H:i'),
    '${approved_by}' => $_SESSION['nama_lengkap'] ?? 'Admin',
    '${tempat}' => 'Jakarta',
    '${atasan_nama}' => $cuti_data['atasan_nama'] ?? 'Atasan Langsung',
    '${atasan_nip}' => $cuti_data['atasan_nip'] ?? '',
    '${pejabat_nama}' => $cuti_data['pejabat_nama'] ?? 'Pejabat Berwenang',
    '${pejabat_nip}' => $cuti_data['pejabat_nip'] ?? '',
    '${cuti_n_minus_2}' => $cuti_data['cuti_n_minus_2'] ?? 0,
    '${cuti_n_minus_1}' => $cuti_data['cuti_n_minus_1'] ?? 0,
    '${cuti_n}' => $cuti_data['cuti_n'] ?? 0,
    '${sisa_cuti_n}' => $cuti_data['sisa_n'] ?? 12,
    '${sisa_cuti_n1}' => $cuti_data['sisa_n1'] ?? 0,
    '${sisa_cuti_n2}' => $cuti_data['sisa_n2'] ?? 0,
    '${total_sisa_cuti}' => ($cuti_data['sisa_n'] ?? 12) + ($cuti_data['sisa_n1'] ?? 0) + ($cuti_data['sisa_n2'] ?? 0),
    '${dipakai_n}' => $cuti_data['cuti_n'] ?? 0,
    '${dipakai_n1}' => $cuti_data['cuti_n_minus_1'] ?? 0,
    '${dipakai_n2}' => $cuti_data['cuti_n_minus_2'] ?? 0,
    '${total_dipakai}' => ($cuti_data['cuti_n'] ?? 0) + ($cuti_data['cuti_n_minus_1'] ?? 0) + ($cuti_data['cuti_n_minus_2'] ?? 0),
    '${keterangan_n}' => ($cuti_data['sisa_n'] ?? 12) - ($cuti_data['cuti_n'] ?? 0),
    '${keterangan_n1}' => ($cuti_data['sisa_n1'] ?? 0) - ($cuti_data['cuti_n_minus_1'] ?? 0),
    '${keterangan_n2}' => ($cuti_data['sisa_n2'] ?? 0) - ($cuti_data['cuti_n_minus_2'] ?? 0),
    // Checkbox tanpa kotak - singkat
    '${chk_ct}' => ($cuti_data['jenis_cuti'] == 'Cuti Tahunan') ? '✓' : '',
    '${chk_cs}' => ($cuti_data['jenis_cuti'] == 'Cuti Sakit') ? '✓' : '',
    '${chk_cm}' => ($cuti_data['jenis_cuti'] == 'Cuti Melahirkan') ? '✓' : '',
    '${chk_cb}' => ($cuti_data['jenis_cuti'] == 'Cuti Besar') ? '✓' : '',
    '${chk_cap}' => ($cuti_data['jenis_cuti'] == 'Cuti Alasan Penting') ? '✓' : '',
    '${masa_kerja}' => $masa_kerja
];



// Apply replacements
foreach ($replacements as $key => $value) {
    $template->setValue($key, $value);
}

$filename = 'Surat_Cuti_' . str_replace(' ', '_', $cuti_data['nama']) . '_' . date('Y-m-d_H-i-s') . '.docx';

header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: private');
header('Pragma: public');

$template->saveAs('php://output');

log_activity($_SESSION['user_id'], 'download_word_cuti', "Success for {$cuti_data['nama']}");
exit();
?>

