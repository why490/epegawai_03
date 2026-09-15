<?php
require_once '../config.php';
require_login();

if (!isset($_GET['id'])) {
    header('Location: kgb.php');
    exit();
}

// Fetch KGB data
$stmt = $pdo->prepare("SELECT k.*, p.nama as nama_pegawai FROM kgb k LEFT JOIN pegawai p ON k.id_pegawai = p.id WHERE k.id = ?");
$stmt->execute([$_GET['id']]);
$kgb = $stmt->fetch();

if (!$kgb) {
    set_flash_message('error', 'Data KGB tidak ditemukan!');
    header('Location: kgb.php');
    exit();
}

require_once '../vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

$templatePath = '../templates/template_kgb.docx';

if (!file_exists($templatePath)) {
    set_flash_message('error', 'Template file tidak ditemukan!');
    header('Location: kgb.php');
    exit();
}

try {
    $templateProcessor = new TemplateProcessor($templatePath);

    // Replace placeholders with actual data
    $templateProcessor->setValue('nomor_surat', $kgb['nomor_surat']);
    $templateProcessor->setValue('lampiran', $kgb['lampiran']);
    $templateProcessor->setValue('tanggal_surat', format_date($kgb['tanggal_surat']));
    
    $templateProcessor->setValue('nama_pegawai', $kgb['nama_pegawai'] ?? '-');
    $templateProcessor->setValue('nip', $kgb['nip']);
    $templateProcessor->setValue('jabatan', $kgb['jabatan']);
    $templateProcessor->setValue('golongan_saat_ini', $kgb['golongan_saat_ini']);
    
    $templateProcessor->setValue('gaji_pokok_lama', $kgb['gaji_pokok_lama']);
    $templateProcessor->setValue('oleh_pejabat', $kgb['oleh_pejabat']);
    $templateProcessor->setValue('nomor_surat_sk', $kgb['nomor_surat_sk']);
    $templateProcessor->setValue('tanggal_surat_sk', format_date($kgb['tanggal_surat_sk']));
    $templateProcessor->setValue('tanggal_mulai_berlaku', format_date($kgb['tanggal_mulai_berlaku']));
    $templateProcessor->setValue('masa_kerja_golongan', $kgb['masa_kerja_golongan']);
    
    $templateProcessor->setValue('gaji_pokok_baru', $kgb['gaji_pokok_baru']);
    $templateProcessor->setValue('mulai_tanggal', format_date($kgb['mulai_tanggal']));
    $templateProcessor->setValue('berdasarkan_masa_kerja', $kgb['berdasarkan_masa_kerja']);
    $templateProcessor->setValue('kenaikan_gaji_yad', format_date($kgb['kenaikan_gaji_yad']));
    $templateProcessor->setValue('dalam_golongan', $kgb['dalam_golongan']);
    $templateProcessor->setValue('pejabat_tanda_tangan', strtoupper(remove_gelar($kgb['pejabat_tanda_tangan'])));
    $templateProcessor->setValue('jabatan_pejabat', $kgb['jabatan_pejabat']);

    // Generate filename
    $filename = 'KGB_' . str_replace(' ', '_', $kgb['nama_pegawai'] ?? '') . '_' . date('YmdHis') . '.docx';

    // Save and download
    $tempFile = tempnam(sys_get_temp_dir(), 'kgb_');
    $templateProcessor->saveAs($tempFile);

    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    readfile($tempFile);
    unlink($tempFile);
    exit();
} catch (Exception $e) {
    set_flash_message('error', 'Gagal generate dokumen: ' . $e->getMessage());
    header('Location: kgb.php');
    exit();
}
?>
