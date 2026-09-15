<?php
require_once 'config.php';
require_login();

// Get overall statistics
$stats = get_dashboard_stats();

// Get cuti statistics by status
try {
    $stmt = $pdo->prepare("
        SELECT status_cuti, COUNT(*) as count 
        FROM cuti 
        GROUP BY status_cuti
    ");
    $stmt->execute();
    $cuti_by_status = $stmt->fetchAll();
} catch (PDOException $e) {
    $cuti_by_status = [];
}

// Get cuti statistics by type
try {
    $stmt = $pdo->prepare("
        SELECT jenis_cuti, COUNT(*) as count 
        FROM cuti 
        GROUP BY jenis_cuti
        ORDER BY count DESC
    ");
    $stmt->execute();
    $cuti_by_type = $stmt->fetchAll();
} catch (PDOException $e) {
    $cuti_by_type = [];
}

// Get surat tugas statistics by status
try {
    $stmt = $pdo->prepare("
        SELECT status_surat, COUNT(*) as count 
        FROM surat_tugas 
        GROUP BY status_surat
    ");
    $stmt->execute();
    $surat_by_status = $stmt->fetchAll();
} catch (PDOException $e) {
    $surat_by_status = [];
}

// Get pegawai statistics by status kepegawaian
try {
    $stmt = $pdo->prepare("
        SELECT status_kepegawaian, COUNT(*) as count 
        FROM pegawai 
        WHERE status_pegawai = 'aktif'
        GROUP BY status_kepegawaian
    ");
    $stmt->execute();
    $pegawai_by_status = $stmt->fetchAll();
} catch (PDOException $e) {
    $pegawai_by_status = [];
}

// Get pegawai statistics by unit kerja
try {
    $stmt = $pdo->prepare("
        SELECT unit_kerja, COUNT(*) as count 
        FROM pegawai 
        WHERE status_pegawai = 'aktif' AND unit_kerja IS NOT NULL
        GROUP BY unit_kerja
        ORDER BY count DESC
        LIMIT 10
    ");
    $stmt->execute();
    $pegawai_by_unit = $stmt->fetchAll();
} catch (PDOException $e) {
    $pegawai_by_unit = [];
}

// Get monthly cuti statistics (last 6 months)
try {
    $stmt = $pdo->prepare("
        SELECT DATE_FORMAT(tanggal_pengajuan, '%Y-%m') as month, COUNT(*) as count 
        FROM cuti 
        WHERE tanggal_pengajuan >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(tanggal_pengajuan, '%Y-%m')
        ORDER BY month DESC
    ");
    $stmt->execute();
    $cuti_monthly = $stmt->fetchAll();
} catch (PDOException $e) {
    $cuti_monthly = [];
}

// Get recent activities
try {
    $stmt = $pdo->prepare("
        SELECT al.*, u.nama_lengkap 
        FROM activity_log al 
        JOIN users u ON al.user_id = u.id 
        ORDER BY al.created_at DESC 
        LIMIT 20
    ");
    $stmt->execute();
    $recent_activities = $stmt->fetchAll();
} catch (PDOException $e) {
    $recent_activities = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h1 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Laporan Statistik
                        </h1>
                        <p class="mb-0">Ringkasan data dan aktivitas sistem</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <!-- Overall Statistics -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card primary">
                        <div class="stat-icon primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['total_pegawai_aktif']; ?></div>
                        <div class="stat-label">Pegawai Aktif</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card warning">
                        <div class="stat-icon warning">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['total_cuti_pending']; ?></div>
                        <div class="stat-label">Cuti Pending</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card success">
                        <div class="stat-icon success">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['total_cuti_disetujui']; ?></div>
                        <div class="stat-label">Cuti Disetujui</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card info">
                        <div class="stat-icon info">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['total_surat_diajukan']; ?></div>
                        <div class="stat-label">Surat Tugas Diajukan</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Cuti Statistics -->
                <div class="col-lg-6">
                    <div class="content-card mb-4">
                        <h5><i class="fas fa-calendar-alt me-2"></i>Statistik Cuti</h5>
                        
                        <h6 class="mt-3">Berdasarkan Status</h6>
                        <?php if (empty($cuti_by_status)): ?>
                            <p class="text-muted">Tidak ada data cuti</p>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($cuti_by_status as $item): ?>
                                    <div class="col-md-6">
                                        <div class="report-item">
                                            <div class="report-label"><?php echo htmlspecialchars($item['status_cuti']); ?></div>
                                            <div class="report-value"><?php echo $item['count']; ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <h6 class="mt-4">Berdasarkan Jenis</h6>
                        <?php if (empty($cuti_by_type)): ?>
                            <p class="text-muted">Tidak ada data cuti</p>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($cuti_by_type as $item): ?>
                                    <div class="col-md-6">
                                        <div class="report-item">
                                            <div class="report-label"><?php echo htmlspecialchars($item['jenis_cuti']); ?></div>
                                            <div class="report-value"><?php echo $item['count']; ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <h6 class="mt-4">Bulan Terakhir (6 Bulan)</h6>
                        <?php if (empty($cuti_monthly)): ?>
                            <p class="text-muted">Tidak ada data cuti</p>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($cuti_monthly as $item): ?>
                                    <div class="col-md-6">
                                        <div class="report-item">
                                            <div class="report-label"><?php echo htmlspecialchars($item['month']); ?></div>
                                            <div class="report-value"><?php echo $item['count']; ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Surat Tugas Statistics -->
                <div class="col-lg-6">
                    <div class="content-card mb-4">
                        <h5><i class="fas fa-file-alt me-2"></i>Statistik Surat Tugas</h5>
                        
                        <h6 class="mt-3">Berdasarkan Status</h6>
                        <?php if (empty($surat_by_status)): ?>
                            <p class="text-muted">Tidak ada data surat tugas</p>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($surat_by_status as $item): ?>
                                    <div class="col-md-6">
                                        <div class="report-item">
                                            <div class="report-label"><?php echo htmlspecialchars($item['status_surat']); ?></div>
                                            <div class="report-value"><?php echo $item['count']; ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Pegawai Statistics -->
                    <div class="content-card">
                        <h5><i class="fas fa-users me-2"></i>Statistik Pegawai</h5>
                        
                        <h6 class="mt-3">Berdasarkan Status Kepegawaian</h6>
                        <?php if (empty($pegawai_by_status)): ?>
                            <p class="text-muted">Tidak ada data pegawai</p>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($pegawai_by_status as $item): ?>
                                    <div class="col-md-6">
                                        <div class="report-item">
                                            <div class="report-label"><?php echo htmlspecialchars($item['status_kepegawaian']); ?></div>
                                            <div class="report-value"><?php echo $item['count']; ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <h6 class="mt-4">Berdasarkan Unit Kerja (Top 10)</h6>
                        <?php if (empty($pegawai_by_unit)): ?>
                            <p class="text-muted">Tidak ada data unit kerja</p>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($pegawai_by_unit as $item): ?>
                                    <div class="col-md-6">
                                        <div class="report-item">
                                            <div class="report-label"><?php echo htmlspecialchars($item['unit_kerja']); ?></div>
                                            <div class="report-value"><?php echo $item['count']; ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="row mt-4">
                <div class="col-lg-12">
                    <div class="content-card">
                        <h5><i class="fas fa-history me-2"></i>Aktivitas Terbaru</h5>
                        <?php if (empty($recent_activities)): ?>
                            <p class="text-muted">Belum ada aktivitas</p>
                        <?php else: ?>
                            <div class="activity-list">
                                <?php foreach ($recent_activities as $activity): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="activity-content">
                                            <div class="activity-title"><?php echo htmlspecialchars($activity['nama_lengkap']); ?></div>
                                            <div class="activity-meta">
                                                <?php echo htmlspecialchars($activity['action']); ?><br>
                                                <small><?php echo date('d/m/Y H:i', strtotime($activity['created_at'])); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
