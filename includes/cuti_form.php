<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($cuti) ? 'Edit Pengajuan Cuti' : 'Ajukan Cuti'; ?> - <?php echo APP_NAME; ?></title>
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
                            <i class="fas fa-calendar-<?php echo isset($cuti) ? 'edit' : 'plus'; ?> me-2"></i>
                            <?php echo isset($cuti) ? 'Edit Pengajuan Cuti' : 'Ajukan Cuti'; ?>
                        </h1>
                        <p class="mb-0"><?php echo isset($cuti) ? 'Perbarui data pengajuan cuti' : 'Buat pengajuan cuti baru'; ?></p>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="cuti.php" class="btn btn-light">
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
                <form method="POST" action="" id="cutiForm">
                    <!-- Informasi Surat -->
                    <div class="form-section">
                        <h5><i class="fas fa-envelope me-2"></i>Informasi Surat</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nomor_surat_cuti" class="form-label">
                                        <i class="fas fa-file-alt me-1"></i>Nomor Surat Cuti <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="nomor_surat_cuti" name="nomor_surat_cuti"
                                           value="<?php echo htmlspecialchars($cuti['nomor_surat_cuti'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nomor_surat_cuti" class="form-label">
                                        <i class="fas fa-file-alt me-1"></i>Tanggal Pengajuan Cuti <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control" id="tanggal_pengajuan" name="tanggal_pengajuan"
                                           value="<?php echo htmlspecialchars($cuti['tanggal_pengajuan'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Pegawai -->
                    <div class="form-section">
                        <h5><i class="fas fa-user me-2"></i>Data Pegawai</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="id_pegawai" class="form-label">
                                        <i class="fas fa-user me-1"></i>Pegawai <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="id_pegawai" name="id_pegawai" required>
                                        <option value="">Pilih Pegawai</option>
                                        <?php foreach ($pegawai_list as $pegawai): ?>
                                            <option value="<?php echo $pegawai['id']; ?>" 
                                                    <?php echo (isset($cuti) && $cuti['id_pegawai'] == $pegawai['id']) ? 'selected' : ''; ?>


data-nip="<?php echo htmlspecialchars($pegawai['nip']); ?>"
data-nama="<?php echo htmlspecialchars($pegawai['nama']); ?>"
data-alamat="<?php echo htmlspecialchars($pegawai['alamat'] ?? ''); ?>"
data-no-hp="<?php echo htmlspecialchars($pegawai['no_hp'] ?? ''); ?>"
data-status="<?php echo htmlspecialchars($pegawai['status_kepegawaian']); ?>"
data-cuti-n="<?php echo $pegawai['cuti_n'] ?? 12; ?>"
data-cuti-n-minus-1="<?php echo $pegawai['cuti_n_minus_1'] ?? 0; ?>"
data-cuti-n-minus-2="<?php echo $pegawai['cuti_n_minus_2'] ?? 0; ?>">


                                                <?php echo htmlspecialchars($pegawai['nama']); ?> - 
                                                <?php echo htmlspecialchars($pegawai['nip']); ?> 
                                                (<?php echo htmlspecialchars($pegawai['status_kepegawaian']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-info-circle me-1"></i>Info Pegawai
                                    </label>
                                    <div class="alert alert-info py-2">
                                        <small id="pegawaiInfo">
                                            <strong>Tip:</strong> Pilih pegawai untuk melihat informasi lengkap
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Cuti -->
                    <div class="form-section">
                        <h5><i class="fas fa-calendar-alt me-2"></i>Data Cuti</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jenis_cuti" class="form-label">
                                        <i class="fas fa-list me-1"></i>Jenis Cuti <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="jenis_cuti" name="jenis_cuti" required>
                                        <option value="">Pilih Jenis Cuti</option>
                                        <option value="Cuti Tahunan" <?php echo (isset($cuti) && $cuti['jenis_cuti'] == 'Cuti Tahunan') ? 'selected' : ''; ?>>Cuti Tahunan</option>
                                        <option value="Cuti Sakit" <?php echo (isset($cuti) && $cuti['jenis_cuti'] == 'Cuti Sakit') ? 'selected' : ''; ?>>Cuti Sakit</option>
                                        <option value="Cuti Melahirkan" <?php echo (isset($cuti) && $cuti['jenis_cuti'] == 'Cuti Melahirkan') ? 'selected' : ''; ?>>Cuti Melahirkan</option>
                                        <option value="Cuti Besar" <?php echo (isset($cuti) && $cuti['jenis_cuti'] == 'Cuti Besar') ? 'selected' : ''; ?>>Cuti Besar</option>
                                        <option value="Cuti Alasan Penting" <?php echo (isset($cuti) && $cuti['jenis_cuti'] == 'Cuti Alasan Penting') ? 'selected' : ''; ?>>Cuti Alasan Penting</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-calendar-check me-1"></i>Periode Cuti <span class="text-danger">*</span>
                                    </label>
                                    <div class="date-range">
                                        <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" 
                                               value="<?php echo htmlspecialchars($cuti['tanggal_mulai'] ?? ''); ?>" required>
                                        <span>sampai</span>
                                        <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" 
                                               value="<?php echo htmlspecialchars($cuti['tanggal_selesai'] ?? ''); ?>" required>
                                    </div>
                                    <small class="text-muted">Lama cuti: <span id="lama_cuti">0</span> | <span id="quota-total">0</span> tersedia</small>
                                </div>
                            </div>
                        </div>

                        <!-- Field Jatah Cuti Tahunan (N-2, N-1, N) -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle me-2"></i>Sisa Kuota Cuti Anda:</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Tahun Ini (N):</strong> <span id="quota-n"><?php echo isset($pegawai['cuti_n']) ? $pegawai['cuti_n'] : 12; ?></span> hari
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Tahun Lalu (N-1):</strong> <span id="quota-n-minus-1"><?php echo isset($pegawai['cuti_n_minus_1']) ? $pegawai['cuti_n_minus_1'] : 0; ?></span> hari
                                        </div>
                                        <div class="col-md-4">
                                            <strong>2 Tahun Lalu (N-2):</strong> <span id="quota-n-minus-2"><?php echo isset($pegawai['cuti_n_minus_2']) ? $pegawai['cuti_n_minus_2'] : 0; ?></span> hari
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <strong>Total Sisa Cuti:</strong> <span id="total-quota"><?php echo (isset($pegawai['cuti_n']) ? $pegawai['cuti_n'] : 12) + (isset($pegawai['cuti_n_minus_1']) ? $pegawai['cuti_n_minus_1'] : 0) + (isset($pegawai['cuti_n_minus_2']) ? $pegawai['cuti_n_minus_2'] : 0); ?></span> hari (Max 24 hari)
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row d-none" id="quota-fields">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="cuti_n_minus_2" class="form-label">Jatah Cuti 2 Tahun Lalu (N-2) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="cuti_n_minus_2" name="cuti_n_minus_2" min="0" value="<?php echo htmlspecialchars($cuti['cuti_n_minus_2'] ?? 6); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="cuti_n_minus_1" class="form-label">Jatah Cuti Tahun Lalu (N-1) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="cuti_n_minus_1" name="cuti_n_minus_1" min="0" value="<?php echo htmlspecialchars($cuti['cuti_n_minus_1'] ?? 6); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="cuti_n" class="form-label">Jatah Cuti Tahun Ini (N) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="cuti_n" name="cuti_n" min="0" value="<?php echo htmlspecialchars($cuti['cuti_n'] ?? 12); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="alasan_cuti" class="form-label">
                                        <i class="fas fa-comment me-1"></i>Alasan Cuti <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" id="alasan_cuti" name="alasan_cuti" rows="3" required><?php echo htmlspecialchars($cuti['alasan_cuti'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Informasi Tambahan -->
                    <div class="form-section">
                        <h5><i class="fas fa-info-circle me-2"></i>Informasi Tambahan</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="alamat_cuti" class="form-label">
                                        <i class="fas fa-map-marker-alt me-1"></i>Alamat Selama Cuti
                                    </label>
                                    <textarea class="form-control" id="alamat_cuti" name="alamat_cuti" rows="2"><?php echo htmlspecialchars($cuti['alamat_cuti'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="no_telepon_cuti" class="form-label">
                                        <i class="fas fa-phone me-1"></i>No. Telepon Selama Cuti
                                    </label>
                                    <input type="tel" class="form-control" id="no_telepon_cuti" name="no_telepon_cuti" 
                                           value="<?php echo htmlspecialchars($cuti['no_telepon_cuti'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Persetujuan Cuti -->
                    <div class="form-section">
                        <h5><i class="fas fa-user-check me-2"></i>Persetujuan Cuti <span class="text-danger">*</span></h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="atasan_id" class="form-label">
                                        <i class="fas fa-user-tie me-1"></i>Atasan Langsung
                                    </label>
                                    <select class="form-select" id="atasan_id" name="atasan_id" required>
                                        <option value="">Pilih Atasan Langsung</option>
                                        <?php foreach ($pegawai_list as $pegawai): ?>
                                            <option value="<?php echo $pegawai['id']; ?>" 
                                                    data-nip="<?php echo htmlspecialchars($pegawai['nip']); ?>"
                                                    data-nama="<?php echo htmlspecialchars($pegawai['nama']); ?>"
                                                    <?php echo (isset($cuti) && $cuti['atasan_id'] == $pegawai['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($pegawai['nama']); ?> (<?php echo htmlspecialchars($pegawai['nip']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" id="atasan_nama" name="atasan_nama">
                                    <input type="hidden" id="atasan_nip" name="atasan_nip">
                                    <small id="atasanPreview" class="form-text text-muted">Pilih atasan untuk auto-fill</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="pejabat_id" class="form-label">
                                        <i class="fas fa-user-crown me-1"></i>Pejabat Berwenang
                                    </label>
                                    <select class="form-select" id="pejabat_id" name="pejabat_id" required>
                                        <option value="">Pilih Pejabat Berwenang</option>
                                        <?php foreach ($pegawai_list as $pegawai): ?>
                                            <option value="<?php echo $pegawai['id']; ?>" 
                                                    data-nip="<?php echo htmlspecialchars($pegawai['nip']); ?>"
                                                    data-nama="<?php echo htmlspecialchars($pegawai['nama']); ?>"
                                                    <?php echo (isset($cuti) && $cuti['pejabat_id'] == $pegawai['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($pegawai['nama']); ?> (<?php echo htmlspecialchars($pegawai['nip']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" id="pejabat_nama" name="pejabat_nama">
                                    <input type="hidden" id="pejabat_nip" name="pejabat_nip">
                                    <small id="pejabatPreview" class="form-text text-muted">Pilih pejabat untuk auto-fill</small>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Informasi Penting -->
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Informasi Penting:</h6>
                        <ul class="mb-0">
                            <li>Pastikan tanggal cuti tidak bentrok dengan jadwal kerja penting</li>
                            <li>Cuti tahunan maksimal 12 hari per tahun</li>
                            <li>Cuti melahirkan maksimal 3 bulan</li>
                            <li>Surat cuti akan otomatis dibuat setelah disetujui</li>
                            <li>Dokumen yang di-generate menggunakan template sesuai status kepegawaian</li>
                        </ul>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            <?php echo isset($cuti) ? 'Perbarui Pengajuan' : 'Ajukan Cuti'; ?>
                        </button>
                        <a href="cuti.php" class="btn btn-secondary">
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
