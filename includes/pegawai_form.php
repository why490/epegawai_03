<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pegawai) ? 'Edit Data Pegawai' : 'Tambah Data Pegawai'; ?> - <?php echo APP_NAME; ?></title>
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
                            <i class="fas fa-user-<?php echo isset($pegawai) ? 'edit' : 'plus'; ?> me-2"></i>
                            <?php echo isset($pegawai) ? 'Edit Data Pegawai' : 'Tambah Data Pegawai'; ?>
                        </h1>
                        <p class="mb-0"><?php echo isset($pegawai) ? 'Perbarui data pegawai' : 'Tambah pegawai baru ke sistem'; ?></p>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="data_pegawai.php" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <?php if ($message = get_flash_message('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="content-card">
                <form method="POST" action="" id="pegawaiForm" enctype="multipart/form-data">
                    <!-- Data Pribadi -->
                    <div class="form-section">
                        <h5><i class="fas fa-user me-2"></i>Data Pribadi</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nip" class="form-label">
                                        <i class="fas fa-id-badge me-1"></i>NIP <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="nip" name="nip" 
                                           value="<?php echo htmlspecialchars($pegawai['nip'] ?? ''); ?>" 
                                           required <?php echo isset($pegawai) ? 'readonly' : ''; ?>>
                                    <?php if (isset($pegawai)): ?>
                                        <small class="text-muted">NIP tidak dapat diubah</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nama" class="form-label">
                                        <i class="fas fa-user me-1"></i>Nama Lengkap <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="nama" name="nama" 
                                           value="<?php echo htmlspecialchars($pegawai['nama'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="tempat_lahir" class="form-label">
                                        <i class="fas fa-map-marker-alt me-1"></i>Tempat Lahir
                                    </label>
                                    <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" 
                                           value="<?php echo htmlspecialchars($pegawai['tempat_lahir'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="tanggal_lahir" class="form-label">
                                        <i class="fas fa-calendar me-1"></i>Tanggal Lahir
                                    </label>
                                    <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" 
                                           value="<?php echo htmlspecialchars($pegawai['tanggal_lahir'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="jenis_kelamin" class="form-label">
                                        <i class="fas fa-venus-mars me-1"></i>Jenis Kelamin <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                        <option value="">Pilih Jenis Kelamin</option>
                                        <option value="L" <?php echo (isset($pegawai) && $pegawai['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                                        <option value="P" <?php echo (isset($pegawai) && $pegawai['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="agama" class="form-label">
                                        <i class="fas fa-pray me-1"></i>Agama
                                    </label>
                                    <select class="form-select" id="agama" name="agama">
                                        <option value="">Pilih Agama</option>
                                        <option value="Islam" <?php echo (isset($pegawai) && $pegawai['agama'] == 'Islam') ? 'selected' : ''; ?>>Islam</option>
                                        <option value="Kristen" <?php echo (isset($pegawai) && $pegawai['agama'] == 'Kristen') ? 'selected' : ''; ?>>Kristen</option>
                                        <option value="Katolik" <?php echo (isset($pegawai) && $pegawai['agama'] == 'Katolik') ? 'selected' : ''; ?>>Katolik</option>
                                        <option value="Hindu" <?php echo (isset($pegawai) && $pegawai['agama'] == 'Hindu') ? 'selected' : ''; ?>>Hindu</option>
                                        <option value="Buddha" <?php echo (isset($pegawai) && $pegawai['agama'] == 'Buddha') ? 'selected' : ''; ?>>Buddha</option>
                                        <option value="Konghucu" <?php echo (isset($pegawai) && $pegawai['agama'] == 'Konghucu') ? 'selected' : ''; ?>>Konghucu</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="no_hp" class="form-label">
                                        <i class="fas fa-phone me-1"></i>No. HP
                                    </label>
                                    <input type="tel" class="form-control" id="no_hp" name="no_hp" 
                                           value="<?php echo htmlspecialchars($pegawai['no_hp'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-1"></i>Email
                                    </label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($pegawai['email'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="alamat" class="form-label">
                                        <i class="fas fa-home me-1"></i>Alamat
                                    </label>
                                    <textarea class="form-control" id="alamat" name="alamat" rows="3"><?php echo htmlspecialchars($pegawai['alamat'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Kepegawaian -->
                    <div class="form-section">
                        <h5><i class="fas fa-briefcase me-2"></i>Data Kepegawaian</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="status_kepegawaian" class="form-label">
                                        <i class="fas fa-user-tag me-1"></i>Status Kepegawaian <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="status_kepegawaian" name="status_kepegawaian" required>
                                        <option value="">Pilih Status</option>
                                        <option value="PNS" <?php echo (isset($pegawai) && $pegawai['status_kepegawaian'] == 'PNS') ? 'selected' : ''; ?>>PNS</option>
                                        <option value="PPPK" <?php echo (isset($pegawai) && $pegawai['status_kepegawaian'] == 'PPPK') ? 'selected' : ''; ?>>PPPK</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="golongan_ruangan" class="form-label">
                                        <i class="fas fa-tag me-1"></i>Golongan Ruang
                                    </label>
                                    <input type="text" class="form-control" id="golongan_ruangan" name="golongan_ruangan" 
                                           maxlength="25"
                                           value="<?php echo htmlspecialchars($pegawai['golongan_ruangan'] ?? ''); ?>">
                                    <small class="text-muted">Format lengkap: Penata Muda (III/a), Penata Tk.I (IV/b), dll.</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="jabatan" class="form-label">
                                        <i class="fas fa-chalkboard-teacher me-1"></i>Jabatan
                                    </label>
                                    <input type="text" class="form-control" id="jabatan" name="jabatan" 
                                           value="<?php echo htmlspecialchars($pegawai['jabatan'] ?? ''); ?>">
                                 </div>
                             </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="unit_kerja" class="form-label">
                                        <i class="fas fa-building me-1"></i>Unit Kerja
                                    </label>
                                    <input type="text" class="form-control" id="unit_kerja" name="unit_kerja" 
                                           value="<?php echo htmlspecialchars($pegawai['unit_kerja'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="status_pegawai" class="form-label">
                                        <i class="fas fa-toggle-on me-1"></i>Status Pegawai <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="status_pegawai" name="status_pegawai" required>
                                        <option value="">Pilih Status</option>
                                        <option value="aktif" <?php echo (isset($pegawai) && $pegawai['status_pegawai'] == 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                                        <option value="nonaktif" <?php echo (isset($pegawai) && $pegawai['status_pegawai'] == 'nonaktif') ? 'selected' : ''; ?>>Nonaktif</option>
                                        <option value="pensiun" <?php echo (isset($pegawai) && $pegawai['status_pegawai'] == 'pensiun') ? 'selected' : ''; ?>>Pensiun</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="tanggal_masuk" class="form-label">
                                        <i class="fas fa-calendar-check me-1"></i>Tanggal Masuk
                                    </label>
                                    <input type="date" class="form-control" id="tanggal_masuk" name="tanggal_masuk"
                                           value="<?php echo htmlspecialchars($pegawai['tanggal_masuk'] ?? ''); ?>">
                                    <small class="text-muted">Digunakan untuk perhitungan masa kerja</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Pendidikan -->
                    <div class="form-section">
                        <h5><i class="fas fa-graduation-cap me-2"></i>Data Pendidikan</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="pendidikan_terakhir" class="form-label">
                                        <i class="fas fa-school me-1"></i>Pendidikan Terakhir
                                    </label>
                                    <select class="form-select" id="pendidikan_terakhir" name="pendidikan_terakhir">
                                        <option value="">Pilih Pendidikan</option>
                                        <option value="SD" <?php echo (isset($pegawai) && $pegawai['pendidikan_terakhir'] == 'SD') ? 'selected' : ''; ?>>SD</option>
                                        <option value="SLTP" <?php echo (isset($pegawai) && $pegawai['pendidikan_terakhir'] == 'SLTP') ? 'selected' : ''; ?>>SLTP</option>
                                        <option value="SLTA/SEDERAJAT" <?php echo (isset($pegawai) && $pegawai['pendidikan_terakhir'] == 'SLTA/SEDERAJAT') ? 'selected' : ''; ?>>SLTA/SEDERAJAT</option>
                                        <option value="D-I" <?php echo (isset($pegawai) && $pegawai['pendidikan_terakhir'] == 'D-I') ? 'selected' : ''; ?>>D-I</option>
                                        <option value="D-II" <?php echo (isset($pegawai) && $pegawai['pendidikan_terakhir'] == 'D-II') ? 'selected' : ''; ?>>D-II</option>
                                        <option value="D-III" <?php echo (isset($pegawai) && $pegawai['pendidikan_terakhir'] == 'D-III') ? 'selected' : ''; ?>>D-III</option>
                                        <option value="D-IV" <?php echo (isset($pegawai) && $pegawai['pendidikan_terakhir'] == 'D-IV') ? 'selected' : ''; ?>>D-IV</option>
                                        <option value="S-1" <?php echo (isset($pegawai) && $pegawai['pendidikan_terakhir'] == 'S-1') ? 'selected' : ''; ?>>S-1</option>
                                        <option value="S-2" <?php echo (isset($pegawai) && $pegawai['pendidikan_terakhir'] == 'S-2') ? 'selected' : ''; ?>>S-2</option>
                                        <option value="S-3" <?php echo (isset($pegawai) && $pegawai['pendidikan_terakhir'] == 'S-3') ? 'selected' : ''; ?>>S-3</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="jurusan" class="form-label">
                                        <i class="fas fa-book me-1"></i>Jurusan
                                    </label>
                                    <input type="text" class="form-control" id="jurusan" name="jurusan" 
                                           value="<?php echo htmlspecialchars($pegawai['jurusan'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="tahun_lulus" class="form-label">
                                        <i class="fas fa-calendar-alt me-1"></i>Tahun Lulus
                                    </label>
                                    <input type="number" class="form-control" id="tahun_lulus" name="tahun_lulus" 
                                           value="<?php echo htmlspecialchars($pegawai['tahun_lulus'] ?? ''); ?>" 
                                           min="1950" max="<?php echo date('Y'); ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            <?php echo isset($pegawai) ? 'Perbarui Data' : 'Simpan Data'; ?>
                        </button>
                        <a href="data_pegawai.php" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
