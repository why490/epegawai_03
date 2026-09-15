<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_GET['id_pegawai']) || empty($_GET['id_pegawai'])) {
    echo json_encode(['success' => false, 'message' => 'ID pegawai tidak ditemukan']);
    exit();
}

try {
    $id_pegawai = $_GET['id_pegawai'];
    
    // Get the latest KGB record for this pegawai
    $stmt = $pdo->prepare("SELECT * FROM kgb WHERE id_pegawai = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$id_pegawai]);
    $last_kgb = $stmt->fetch();
    
    if ($last_kgb) {
        echo json_encode([
            'success' => true,
            'data' => [
                'gaji_pokok_lama' => $last_kgb['gaji_pokok_lama'],
                'oleh_pejabat' => $last_kgb['oleh_pejabat'],
                'nomor_surat_sk' => $last_kgb['nomor_surat_sk'],
                'tanggal_surat_sk' => $last_kgb['tanggal_surat_sk'],
                'tanggal_mulai_berlaku' => $last_kgb['tanggal_mulai_berlaku'],
                'masa_kerja_golongan' => $last_kgb['masa_kerja_golongan']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Tidak ada data KGB sebelumnya']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
