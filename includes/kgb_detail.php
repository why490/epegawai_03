<?php
require_once '../config.php';
require_login();

if (!isset($_GET['id'])) {
    header('Location: ../kgb.php');
    exit();
}

// Fetch KGB data
$stmt = $pdo->prepare("SELECT k.*, p.nama as nama_pegawai FROM kgb k LEFT JOIN pegawai p ON k.id_pegawai = p.id WHERE k.id = ?");
$stmt->execute([$_GET['id']]);
$kgb = $stmt->fetch();

if (!$kgb) {
    set_flash_message('error', 'Data KGB tidak ditemukan!');
    header('Location: ../kgb.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kenaikan Gaji Berkala - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h1 class="mb-0">
                            <i class="fas fa-chart-line me-2"></i>Detail Kenaikan Gaji Berkala
                        </h1>
                        <p class="mb-0">Detail Data Kenaikan Gaji Berkala Pegawai</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="../kgb.php" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="content-card">
                <?php
                $success_msg = get_flash_message('success');
                $error_msg = get_flash_message('error');
                if ($success_msg): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success_msg; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if ($error_msg): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error_msg; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row mb-4">
                    <div class="col-12">
                        <a href="../download/download_kgb.php?id=<?php echo $kgb['id']; ?>" class="btn btn-success">
                            <i class="fas fa-file-word me-2"></i>Download Word
                        </a>
                        <a href="../kgb.php?action=edit&id=<?php echo $kgb['id']; ?>" class="btn btn-warning ms-2">
                            <i class="fas fa-edit me-2"></i>Edit
                        </a>
                        <a href="../kgb.php?action=delete&id=<?php echo $kgb['id']; ?>" class="btn btn-danger ms-2" onclick="return confirm('Yakin ingin menghapus data ini?')">
                            <i class="fas fa-trash me-2"></i>Hapus
                        </a>
                    </div>
                </div>

                <!-- Header Fields -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="section-title">
                            <i class="fas fa-file-alt me-2"></i>Header Surat
                        </h5>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Nomor Surat:</label>
                        <p><?php echo htmlspecialchars($kgb['nomor_surat']); ?></p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Lampiran:</label>
                        <p><?php echo htmlspecialchars($kgb['lampiran']); ?></p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Tanggal Surat:</label>
                        <p><?php echo format_date($kgb['tanggal_surat']); ?></p>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Data Pegawai -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="section-title">
                            <i class="fas fa-user me-2"></i>Data Pegawai
                        </h5>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nama Pegawai:</label>
                        <p><?php echo htmlspecialchars($kgb['nama_pegawai'] ?? '-'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">NIP:</label>
                        <p><?php echo htmlspecialchars($kgb['nip']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Jabatan:</label>
                        <p><?php echo htmlspecialchars($kgb['jabatan']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Golongan Saat Ini:</label>
                        <p><?php echo htmlspecialchars($kgb['golongan_saat_ini']); ?></p>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Dasar SK Terakhir -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="section-title">
                            <i class="fas fa-file-alt me-2"></i>Dasar SK Terakhir Tentang Gaji/Pangkat
                        </h5>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Gaji Pokok Lama:</label>
                        <p><?php echo htmlspecialchars($kgb['gaji_pokok_lama']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Oleh Pejabat:</label>
                        <p><?php echo htmlspecialchars($kgb['oleh_pejabat']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nomor Surat SK:</label>
                        <p><?php echo htmlspecialchars($kgb['nomor_surat_sk']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tanggal Surat SK:</label>
                        <p><?php echo format_date($kgb['tanggal_surat_sk']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tanggal Mulai Berlaku:</label>
                        <p><?php echo format_date($kgb['tanggal_mulai_berlaku']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Masa Kerja Golongan:</label>
                        <p><?php echo htmlspecialchars($kgb['masa_kerja_golongan']); ?></p>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Data Kenaikan Gaji -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="section-title">
                            <i class="fas fa-chart-line me-2"></i>Data Kenaikan Gaji
                        </h5>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Gaji Pokok Baru:</label>
                        <p><?php echo htmlspecialchars($kgb['gaji_pokok_baru']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Mulai Tanggal:</label>
                        <p><?php echo format_date($kgb['mulai_tanggal']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Berdasarkan Masa Kerja:</label>
                        <p><?php echo htmlspecialchars($kgb['berdasarkan_masa_kerja']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Kenaikan Gaji Yang Akan Datang:</label>
                        <p><?php echo format_date($kgb['kenaikan_gaji_yad']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Dalam Golongan:</label>
                        <p><?php echo htmlspecialchars($kgb['dalam_golongan']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Pejabat yang Menandatangani:</label>
                        <p><?php echo htmlspecialchars($kgb['pejabat_tanda_tangan']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Jabatan Pejabat:</label>
                        <p><?php echo htmlspecialchars($kgb['jabatan_pejabat']); ?></p>
                    </div>
                </div>

                <hr class="my-4">

                <div class="row">
                    <div class="col-12">
                        <label class="form-label fw-bold">Dibuat pada:</label>
                        <p><?php echo format_date($kgb['created_at'], 'd F Y H:i'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>
