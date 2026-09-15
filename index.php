<?php
require_once 'config.php';
require_login();

// Get dashboard statistics
$stats = get_dashboard_stats();

// Get recent activities
try {
    $stmt = $pdo->prepare("
        SELECT al.*, u.nama_lengkap 
        FROM activity_log al 
        JOIN users u ON al.user_id = u.id 
        ORDER BY al.created_at DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $recent_activities = $stmt->fetchAll();
} catch (PDOException $e) {
    $recent_activities = [];
}

// Get pending cuti
try {
    $stmt = $pdo->prepare("
        SELECT c.*, p.nama as nama_pegawai 
        FROM cuti c 
        JOIN pegawai p ON c.id_pegawai = p.id 
        WHERE c.status_cuti = 'Pending' 
        ORDER BY c.tanggal_pengajuan DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $pending_cuti = $stmt->fetchAll();
} catch (PDOException $e) {
    $pending_cuti = [];
}

// Get pending surat tugas
try {
    $stmt = $pdo->prepare("
        SELECT st.*, p.nama as nama_pegawai
        FROM surat_tugas st
        LEFT JOIN surat_tugas_pegawai stp ON st.id = stp.id_surat_tugas
        LEFT JOIN pegawai p ON stp.id_pegawai = p.id
        WHERE st.status_surat = 'Diajukan'
        GROUP BY st.id
        ORDER BY st.created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $pending_surat = $stmt->fetchAll();
} catch (PDOException $e) {
    $pending_surat = [];
}

// Get KGB statistics
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM kgb");
    $stmt->execute();
    $kgb_total = $stmt->fetch()['total'];
} catch (PDOException $e) {
    $kgb_total = 0;
}

// Get cuti by month for chart with jenis_cuti breakdown
try {
    $stmt = $pdo->prepare("
        SELECT MONTH(tanggal_pengajuan) as month, jenis_cuti, COUNT(*) as count
        FROM cuti
        WHERE YEAR(tanggal_pengajuan) = YEAR(CURDATE())
        GROUP BY MONTH(tanggal_pengajuan), jenis_cuti
        ORDER BY month, jenis_cuti
    ");
    $stmt->execute();
    $cuti_by_month_jenis = $stmt->fetchAll();
} catch (PDOException $e) {
    $cuti_by_month_jenis = [];
}

// Get cuti by status for pie chart
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

// Get pegawai by jabatan for chart
try {
    $stmt = $pdo->prepare("
        SELECT jabatan, COUNT(*) as count
        FROM pegawai
        WHERE status_pegawai = 'aktif' AND jabatan IS NOT NULL AND jabatan != ''
        GROUP BY jabatan
        ORDER BY count DESC
        LIMIT 10
    ");
    $stmt->execute();
    $pegawai_by_jabatan = $stmt->fetchAll();
} catch (PDOException $e) {
    $pegawai_by_jabatan = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="container">
                <div class="row">
                    <div class="col-md-8">
                        <h1 class="welcome-text">
                            <i class="fas fa-hand-wave me-2"></i>Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>!
                        </h1>
                        <p class="welcome-subtitle">
                            <?php 
                            $greeting = '';
                            $hour = date('H');
                            if ($hour < 12) $greeting = 'Selamat Pagi';
                            elseif ($hour < 15) $greeting = 'Selamat Siang';
                            elseif ($hour < 18) $greeting = 'Selamat Sore';
                            else $greeting = 'Selamat Malam';
                            echo $greeting . '! Semoga hari Anda menyenangkan.';
                            ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="text-white">
                            <small><i class="fas fa-clock me-1"></i><?php echo date('d/m/Y H:i'); ?></small><br>
                            <small><i class="fas fa-user-tag me-1"></i><?php echo ucfirst($_SESSION['user_role']); ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <?php if ($message = get_flash_message('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($message = get_flash_message('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
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
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-number"><?php echo $kgb_total; ?></div>
                        <div class="stat-label">Total KGB</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <a href="data_pegawai.php?action=add" class="quick-action">
                        <div class="quick-action-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="quick-action-title">Tambah Pegawai</div>
                        <div class="quick-action-desc">Input data pegawai baru</div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="cuti.php?action=add" class="quick-action">
                        <div class="quick-action-icon">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <div class="quick-action-title">Ajukan Cuti</div>
                        <div class="quick-action-desc">Pengajuan cuti online</div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="surat_tugas.php?action=add" class="quick-action">
                        <div class="quick-action-icon">
                            <i class="fas fa-file-signature"></i>
                        </div>
                        <div class="quick-action-title">Buat Surat Tugas</div>
                        <div class="quick-action-desc">Generate surat tugas</div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="kgb.php?action=add" class="quick-action">
                        <div class="quick-action-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="quick-action-title">Input KGB</div>
                        <div class="quick-action-desc">Kenaikan Gaji Berkala</div>
                    </a>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div id="chartsCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <div class="content-card">
                                    <h5 class="mb-4"><i class="fas fa-chart-line me-2"></i>Tren Cuti Tahun Ini</h5>
                                    <canvas id="cutiTrendChart" data-cuti-month-jenis='<?php echo json_encode($cuti_by_month_jenis); ?>' style="max-height: 400px;"></canvas>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="content-card">
                                    <h5 class="mb-4"><i class="fas fa-users me-2"></i>Pegawai per Jabatan</h5>
                                    <canvas id="pegawaiJabatanChart" data-jabatan-labels='<?php echo json_encode(array_column($pegawai_by_jabatan, 'jabatan')); ?>' data-jabatan-counts='<?php echo json_encode(array_column($pegawai_by_jabatan, 'count')); ?>' style="max-height: 400px;"></canvas>
                                </div>
                            </div>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#chartsCarousel" data-bs-slide="prev" style="background: rgba(0,0,0,0.5); width: 50px; height: 50px; border-radius: 50%; top: 50%; transform: translateY(-50%); margin-left: 25px; opacity: 0; transition: opacity 0.3s;">
                            <span class="carousel-control-prev-icon" aria-hidden="true" style="width: 20px; height: 20px;"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#chartsCarousel" data-bs-slide="next" style="background: rgba(0,0,0,0.5); width: 50px; height: 50px; border-radius: 50%; top: 50%; transform: translateY(-50%); margin-right: 25px; opacity: 0; transition: opacity 0.3s;">
                            <span class="carousel-control-next-icon" aria-hidden="true" style="width: 20px; height: 20px;"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
            </div>

            <style>
                #chartsCarousel:hover .carousel-control-prev,
                #chartsCarousel:hover .carousel-control-next {
                    opacity: 1 !important;
                }
            </style>

            <div class="row">
                <!-- Pending Cuti -->
                <div class="col-lg-4">
<div class="activity-card pending-scrollable">
                        <h5 class="mb-4">
                            <i class="fas fa-clock text-warning me-2"></i>Pengajuan Cuti Pending
                        </h5>
                        <?php if (empty($pending_cuti)): ?>
                            <p class="text-muted text-center py-3">Tidak ada pengajuan cuti pending</p>
                        <?php else: ?>
                            <?php foreach ($pending_cuti as $cuti): ?>
                                <div class="pending-item">
                                    <div class="pending-title"><?php echo htmlspecialchars($cuti['nama_pegawai']); ?></div>
                                    <div class="pending-meta">
                                        <i class="fas fa-calendar me-1"></i><?php echo htmlspecialchars($cuti['jenis_cuti']); ?><br>
                                        <i class="fas fa-clock me-1"></i><?php echo format_date($cuti['tanggal_pengajuan']); ?>
                                    </div>
                                    <a href="cuti.php?action=view&id=<?php echo $cuti['id']; ?>" class="btn btn-sm btn-warning mt-2">
                                        <i class="fas fa-eye me-1"></i>Lihat Detail
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pending Surat Tugas -->
                <div class="col-lg-4">
<div class="activity-card pending-scrollable">
                        <h5 class="mb-4">
                            <i class="fas fa-file-alt text-info me-2"></i>Surat Tugas Diajukan
                        </h5>
                        <?php if (empty($pending_surat)): ?>
                            <p class="text-muted text-center py-3">Tidak ada surat tugas diajukan</p>
                        <?php else: ?>
                            <?php foreach ($pending_surat as $surat): ?>
                                <div class="pending-item">
                                    <div class="pending-title"><?php echo htmlspecialchars($surat['nomor_surat_tugas'] ?? '-'); ?></div>
                                    <div class="pending-meta">
                                        <i class="fas fa-briefcase me-1"></i><?php echo htmlspecialchars($surat['tentang']); ?><br>
                                        <i class="fas fa-clock me-1"></i><?php echo format_date($surat['created_at']); ?>
                                    </div>
                                    <a href="surat_tugas.php?action=view&id=<?php echo $surat['id']; ?>" class="btn btn-sm btn-info mt-2">
                                        <i class="fas fa-eye me-1"></i>Lihat Detail
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="col-lg-4">
                    <div class="activity-card">
                        <h5 class="mb-4">
                            <i class="fas fa-history text-primary me-2"></i>Aktivitas Terbaru
                        </h5>
                        <?php if (empty($recent_activities)): ?>
                            <p class="text-muted text-center py-3">Belum ada aktivitas</p>
                        <?php else: ?>
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
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script src="assets/js/dashboard.js"></script>
</body>
</html>
