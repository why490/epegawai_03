<?php
require_once '../config.php';
require_login();

$surat_tugas_id = $_GET['id'] ?? '';
if (empty($surat_tugas_id)) {
    die('ID surat tugas tidak ditemukan');
}

try {
    // Get surat tugas data
    $stmt = $pdo->prepare("
        SELECT st.*, p.nama as pejabat_nama, p.nip as pejabat_nip, p.jabatan as pejabat_jabatan
        FROM surat_tugas st
        LEFT JOIN pegawai p ON st.pejabat_tanda_tangan = p.id
        WHERE st.id = ?
    ");
    $stmt->execute([$surat_tugas_id]);
    $surat_tugas = $stmt->fetch();

    if (!$surat_tugas) {
        die('Data surat tugas tidak ditemukan');
    }

    // Get pegawai bertugas data
    $stmt = $pdo->prepare("
        SELECT * FROM surat_tugas_pegawai
        WHERE id_surat_tugas = ?
        ORDER BY id
    ");
    $stmt->execute([$surat_tugas_id]);
    $pegawai_bertugas = $stmt->fetchAll();

    if (empty($pegawai_bertugas)) {
        die('Tidak ada pegawai bertugas');
    }

} catch (PDOException $e) {
    die('DB Error: ' . $e->getMessage());
}

require_once '../vendor/autoload.php';

// Load template
$templatePath = '../templates/template_surat_tugas.docx';
if (!file_exists($templatePath)) {
    die('Template file tidak ditemukan: ' . $templatePath);
}

$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

// Replace placeholders for surat info
$templateProcessor->setValue('nomor_surat_tugas', $surat_tugas['nomor_surat_tugas']);
$templateProcessor->setValue('nomor_surat', $surat_tugas['nomor_surat']);
$templateProcessor->setValue('berdasarkan_surat', $surat_tugas['berdasarkan_surat'] ?? '-');
$templateProcessor->setValue('hari', $surat_tugas['hari']);
$templateProcessor->setValue('tanggal_surat', format_date($surat_tugas['tanggal_surat']));
$templateProcessor->setValue('tentang', $surat_tugas['tentang']);
$templateProcessor->setValue('lokasi_tugas', $surat_tugas['tempat_tugas']);
$templateProcessor->setValue('tanggal_surat_tugas', format_date($surat_tugas['tanggal_surat_tugas']));

// Replace placeholders for pejabat
$templateProcessor->setValue('pejabat_nama', strtoupper(remove_gelar($surat_tugas['pejabat_nama'])) ?? '-');
$templateProcessor->setValue('pejabat_nip', $surat_tugas['pejabat_nip'] ?? '-');
$templateProcessor->setValue('pejabat_jabatan', $surat_tugas['pejabat_jabatan'] ?? '-');

// Clone rows for pegawai bertugas
try {
    $templateProcessor->cloneRow('pegawai_no', count($pegawai_bertugas));

    foreach ($pegawai_bertugas as $index => $pegawai) {
        $rowIndex = $index + 1;
        $templateProcessor->setValue('pegawai_no#' . $rowIndex, $rowIndex);
        $templateProcessor->setValue('pegawai_nama#' . $rowIndex, $pegawai['nama_pegawai']);
        $templateProcessor->setValue('pegawai_nip#' . $rowIndex, $pegawai['nip']);
        $templateProcessor->setValue('pegawai_jabatan#' . $rowIndex, $pegawai['jabatan']);
        $templateProcessor->setValue('pegawai_golongan#' . $rowIndex, $pegawai['golongan_ruang']);
        $templateProcessor->setValue('pegawai_tanggal_mulai#' . $rowIndex, format_date($pegawai['tanggal_mulai_tugas']));
        $templateProcessor->setValue('pegawai_tanggal_selesai#' . $rowIndex, format_date($pegawai['tanggal_selesai_tugas']));
    }
} catch (Exception $e) {
    die('Error cloneRow: Pastikan placeholder ${pegawai_no} ada dalam tabel di template Word. Error: ' . $e->getMessage());
}

// Save file
$filename = 'Surat_Tugas_' . str_replace(' ', '_', $surat_tugas['tentang']) . '_' . date('Y-m-d_H-i-s') . '.docx';

header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: private');
header('Pragma: public');

$templateProcessor->saveAs('php://output');

log_activity($_SESSION['user_id'], 'download_word_surat_tugas', "Success for {$surat_tugas['nomor_surat_tugas']}");
exit();
