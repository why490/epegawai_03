<?php
require_once 'config.php';
require_login();

// Check if user is admin
if (!is_admin()) {
    set_flash_message('error', 'Anda tidak memiliki akses ke halaman ini!');
    header('Location: index.php');
    exit();
}

// Handle backup action
if (isset($_POST['backup']) && $_POST['backup'] === 'true') {
    try {
        // Set headers for download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="epegawai_backup_' . date('Y-m-d_H-i-s') . '.sql"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        $backup_file = '';
        
        // Get all tables
        $tables = [];
        $stmt = $pdo->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        // Add header
        $backup_file .= "-- Database Backup: " . DB_NAME . "\n";
        $backup_file .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $backup_file .= "-- Server: " . DB_HOST . "\n\n";

        // Loop through tables
        foreach ($tables as $table) {
            $backup_file .= "-- Table: $table\n";
            
            // Get table structure
            $stmt = $pdo->query("SHOW CREATE TABLE $table");
            $row = $stmt->fetch(PDO::FETCH_NUM);
            $backup_file .= $row[1] . ";\n\n";
            
            // Get table data
            $stmt = $pdo->query("SELECT * FROM $table");
            $column_count = $stmt->columnCount();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $values = array_map(function($val) {
                    if ($val === null) {
                        return 'NULL';
                    } elseif ($val === '') {
                        return "''";
                    } else {
                        return "'" . addslashes($val) . "'";
                    }
                }, array_values($row));
                
                $backup_file .= "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
            }
            
            $backup_file .= "\n";
        }

        echo $backup_file;
        
        log_activity($_SESSION['user_id'], 'backup_database', 'Database backup successful');
        exit();
    } catch (Exception $e) {
        set_flash_message('error', 'Gagal backup database: ' . $e->getMessage());
        header('Location: backup.php');
        exit();
    }
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup Database - <?php echo APP_NAME; ?></title>
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
                            <i class="fas fa-database me-2"></i>Backup Database
                        </h1>
                        <p class="mb-0">Backup database aplikasi e-pegawai</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="index.php" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
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

            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h4>Informasi Backup</h4>
                            <p class="text-muted">
                                Backup database akan menghasilkan file SQL yang berisi seluruh data dari database e-pegawai.
                                File ini dapat digunakan untuk restore database jika terjadi kerusakan data.
                            </p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-info-circle me-2 text-primary"></i>Nama Database: <strong><?php echo DB_NAME; ?></strong></li>
                                <li><i class="fas fa-server me-2 text-primary"></i>Host: <strong><?php echo DB_HOST; ?></strong></li>
                                <li><i class="fas fa-calendar me-2 text-primary"></i>Backup Terakhir: <strong><?php echo date('d/m/Y H:i:s'); ?></strong></li>
                            </ul>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="p-4 bg-light rounded">
                                <i class="fas fa-cloud-download-alt fa-4x text-primary mb-3"></i>
                                <h5>Download Backup</h5>
                                <p class="text-muted small">Klik tombol di bawah untuk download backup database</p>
                                <form method="POST" action="">
                                    <input type="hidden" name="backup" value="true">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-download me-2"></i>Backup Database
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="alert alert-info">
                        <i class="fas fa-shield-alt me-2"></i>
                        <strong>Catatan:</strong>
                        <ul class="mb-0 mt-2">
                            <li>File backup akan diunduh dalam format .sql</li>
                            <li>Simpan file backup di tempat yang aman</li>
                            <li>Lakukan backup secara berkala untuk keamanan data</li>
                            <li>Hanya administrator yang dapat melakukan backup</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
