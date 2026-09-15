<?php
require_once 'config.php';
require_login();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($holiday) ? 'Edit' : 'Tambah'; ?> Hari Libur - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
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
                            <i class="fas fa-calendar-day me-2"></i><?php echo isset($holiday) ? 'Edit' : 'Tambah'; ?> Hari Libur
                        </h1>
                        <p class="mb-0"><?php echo isset($holiday) ? 'Edit data hari libur' : 'Tambah hari libur baru'; ?></p>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="holidays.php" class="btn btn-light">
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

            <!-- Holiday Form -->
            <div class="content-card">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-section">
                                <h5>Informasi Hari Libur</h5>
                                
                                <div class="mb-3">
                                    <label for="tanggal" class="form-label">Tanggal</label>
                                    <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                           value="<?php echo isset($holiday) ? htmlspecialchars($holiday['tanggal']) : ''; ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="nama_libur" class="form-label">Nama Libur</label>
                                    <input type="text" class="form-control" id="nama_libur" name="nama_libur" 
                                           value="<?php echo isset($holiday) ? htmlspecialchars($holiday['nama_libur']) : ''; ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="jenis_libur" class="form-label">Jenis Libur</label>
                                    <select class="form-select" id="jenis_libur" name="jenis_libur" required>
                                        <option value="">Pilih Jenis Libur</option>
                                        <option value="Nasional" <?php echo isset($holiday) && $holiday['jenis_libur'] == 'Nasional' ? 'selected' : ''; ?>>Nasional</option>
                                        <option value="Daerah" <?php echo isset($holiday) && $holiday['jenis_libur'] == 'Daerah' ? 'selected' : ''; ?>>Daerah</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="tahun" class="form-label">Tahun</label>
                                    <input type="number" class="form-control" id="tahun" name="tahun" 
                                           value="<?php echo isset($holiday) ? $holiday['tahun'] : date('Y'); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="keterangan" class="form-label">Keterangan</label>
                                    <textarea class="form-control" id="keterangan" name="keterangan" rows="3"><?php echo isset($holiday) ? htmlspecialchars($holiday['keterangan']) : ''; ?></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Simpan
                                </button>
                                <a href="holidays.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Batal
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
