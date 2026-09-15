<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengajuan Cuti - <?php echo APP_NAME; ?></title>
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
                            <i class="fas fa-calendar-alt me-2"></i>Detail Pengajuan Cuti
                        </h1>
                        <p class="mb-0">Informasi lengkap pengajuan cuti</p>
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

            <div class="content-card">
                <!-- Status Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1"><?php echo htmlspecialchars($cuti['nomor_surat_cuti'] ?? '-'); ?></h4>
                        <p class="text-muted mb-0">Nama: <?php echo htmlspecialchars($cuti['nama_pegawai']); ?> | NIP: <?php echo htmlspecialchars($cuti['nip']); ?></p>
                    </div>
                    <div class="text-end">
                        <span class="status-badge status-<?php echo strtolower($cuti['status_cuti']); ?>">
                            <?php echo htmlspecialchars($cuti['status_cuti']); ?>
                        </span>
                    </div>
                </div>

                <!-- Data Pegawai -->
                <div class="detail-section">
                    <h5><i class="fas fa-user me-2"></i>Data Pegawai</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150"><strong>Nama Lengkap</strong></td>
                                    <td><?php echo htmlspecialchars($cuti['nama_pegawai']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>NIP</strong></td>
                                    <td><?php echo htmlspecialchars($cuti['nip']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Jabatan</strong></td>
                                    <td><?php echo htmlspecialchars($cuti['jabatan']); ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150"><strong>Unit Kerja</strong></td>
                                    <td><?php echo htmlspecialchars($cuti['unit_kerja']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Status Kepegawaian</strong></td>
                                    <td><?php echo htmlspecialchars($cuti['status_kepegawaian']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Template</strong></td>
                                    <td>Surat Cuti <?php echo htmlspecialchars($cuti['status_kepegawaian']); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Data Cuti -->
                <div class="detail-section">
                    <h5><i class="fas fa-calendar-alt me-2"></i>Data Cuti</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150"><strong>Jenis Cuti</strong></td>
                                    <td><?php echo htmlspecialchars($cuti['jenis_cuti']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Pengajuan</strong></td>
                                    <td><?php echo format_date($cuti['tanggal_pengajuan']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Mulai</strong></td>
                                    <td><?php echo format_date($cuti['tanggal_mulai']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Selesai</strong></td>
                                    <td><?php echo format_date($cuti['tanggal_selesai']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Lama Cuti</strong></td>
                                    <td><?php echo $cuti['lama_cuti']; ?> hari</td>
                                </tr>
                                <tr>
                                    <td><strong>Sisa Kuota Cuti Tahunan</strong></td>
                                    <td>
                                        N: <?php echo $cuti['cuti_n'] ?? 12; ?> hari, 
                                        N-1: <?php echo $cuti['cuti_n_minus_1'] ?? 0; ?> hari, 
                                        N-2: <?php echo $cuti['cuti_n_minus_2'] ?? 0; ?> hari
                                        <br>
                                        <small class="text-muted">Total: <?php echo ($cuti['cuti_n'] ?? 12) + ($cuti['cuti_n_minus_1'] ?? 0) + ($cuti['cuti_n_minus_2'] ?? 0); ?> hari (Max 24 hari)</small>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150"><strong>Alasan Cuti</strong></td>
                                    <td><?php echo htmlspecialchars($cuti['alasan_cuti']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Alamat Cuti</strong></td>
                                    <td><?php echo htmlspecialchars($cuti['alamat_cuti'] ?: '-'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>No. Telepon</strong></td>
                                    <td><?php echo htmlspecialchars($cuti['no_telepon_cuti'] ?: '-'); ?></td>
                                </tr>
                                <?php if ($cuti['approved_by']): ?>
                                <tr>
                                    <td><strong>Disetujui Oleh</strong></td>
                                    <td><?php echo htmlspecialchars($cuti['approved_by']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Approve</strong></td>
                                    <td><?php echo format_date($cuti['tanggal_approve']); ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Catatan Penolakan -->
                <?php if ($cuti['catatan_penolakan']): ?>
                <div class="detail-section">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Catatan Penolakan</h5>
                    <div class="alert alert-warning">
                        <?php echo htmlspecialchars($cuti['catatan_penolakan']); ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Dokumen -->
                <?php if ($cuti['file_surat']): ?>
                <div class="detail-section">
                    <h5><i class="fas fa-file-alt me-2"></i>Dokumen Cuti</h5>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        Surat cuti telah dibuat dan tersedia untuk diunduh.
                        <br><br>
                        <a href="../download/download.php?file=<?php echo htmlspecialchars($cuti['file_surat']); ?>" class="btn btn-success">
                            <i class="fas fa-download me-2"></i>Unduh Surat Cuti
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <?php if ($cuti['status_cuti'] == 'Pending'): ?>
                <div class="detail-section">
                    <h5><i class="fas fa-tasks me-2"></i>Tindakan</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <form method="POST" action="cuti.php?action=approve&id=<?php echo $cuti['id']; ?>&status=Disetujui">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-info-circle me-1"></i>Informasi Approve
                                    </label>
                                    <div class="alert alert-info py-2">
                                        <small>
                                            <strong>Template:</strong> Surat Cuti <?php echo htmlspecialchars($cuti['status_kepegawaian']); ?><br>
                                            <strong>Output:</strong> Dokumen akan otomatis dibuat dengan placeholder ${nama}<br>
                                            <strong>Format:</strong> File text yang dapat di-download
                                        </small>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-approve btn-action w-100">
                                    <i class="fas fa-check me-2"></i>Setujui & Generate Dokumen
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form method="POST" action="cuti.php?action=approve&id=<?php echo $cuti['id']; ?>&status=Ditolak">
                                <div class="mb-3">
                                    <label for="catatan" class="form-label">
                                        <i class="fas fa-comment me-1"></i>Catatan Penolakan
                                    </label>
                                    <textarea class="form-control" id="catatan" name="catatan" rows="3" 
                                              placeholder="Alasan penolakan..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-reject btn-action w-100">
                                    <i class="fas fa-times me-2"></i>Tolak
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Action History -->
                <div class="detail-section">
                    <h5><i class="fas fa-history me-2"></i>Riwayat</h5>
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-dot bg-primary"></div>
                            <div class="timeline-content">
                                <strong>Pengajuan Dibuat</strong><br>
                                <small class="text-muted"><?php echo format_date($cuti['tanggal_pengajuan']); ?> oleh <?php echo htmlspecialchars($cuti['nama_pegawai']); ?></small>
                            </div>
                        </div>
                        <?php if ($cuti['approved_by']): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot bg-<?php echo $cuti['status_cuti'] == 'Disetujui' ? 'success' : 'danger'; ?>"></div>
                            <div class="timeline-content">
                                <strong><?php echo $cuti['status_cuti']; ?></strong><br>
                                <small class="text-muted"><?php echo format_date($cuti['tanggal_approve']); ?> oleh <?php echo htmlspecialchars($cuti['approved_by']); ?></small>
                                <?php if ($cuti['file_surat']): ?>
                                <br><small class="text-success"><i class="fas fa-file-alt me-1"></i>Dokumen dibuat</small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
