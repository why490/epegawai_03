<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pegawai - <?php echo APP_NAME; ?></title>
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
                            <i class="fas fa-user me-2"></i>Detail Pegawai
                        </h1>
                        <p class="mb-0">Informasi lengkap data pegawai</p>
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
            <div class="content-card">
                <!-- Header Info -->
                <div class="row align-items-center mb-4">
                    <div class="col-md-3 text-center">

                        <?php if (!empty($pegawai['foto']) && file_exists('assets/images/' . $pegawai['foto'])): ?>
                            <img src="assets/images/<?php echo htmlspecialchars($pegawai['foto']); ?>" 
                                 alt="<?php echo htmlspecialchars($pegawai['nama']); ?>" 
                                 class="pegawai-photo">
                        <?php else: ?>
                            <div class="pegawai-avatar">
                                <?php echo strtoupper(substr($pegawai['nama'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>

                    </div>
                    <div class="col-md-6">
                        <h3><?php echo htmlspecialchars($pegawai['nama']); ?></h3>
                        <p class="text-muted mb-2">NIP: <?php echo htmlspecialchars($pegawai['nip']); ?></p>
                        <div class="d-flex gap-2 mb-3">
                            <span class="badge badge-status badge-<?php echo strtolower($pegawai['status_pegawai']); ?>">
                                <?php echo ucfirst($pegawai['status_pegawai']); ?>
                            </span>
                            <span class="badge badge-status badge-<?php echo strtolower($pegawai['status_kepegawaian']); ?>">
                                <?php echo htmlspecialchars($pegawai['status_kepegawaian']); ?>
                            </span>
                        </div>
                        <div class="btn-group">
                            <a href="data_pegawai.php?action=edit&id=<?php echo $pegawai['id']; ?>" class="btn btn-warning">
                                <i class="fas fa-edit me-2"></i>Edit Data
                            </a>
                            <a href="cuti.php?action=add&pegawai=<?php echo $pegawai['id']; ?>" class="btn btn-info">
                                <i class="fas fa-calendar-plus me-2"></i>Ajukan Cuti
                            </a>
                            <a href="surat_tugas.php?action=add&pegawai=<?php echo $pegawai['id']; ?>" class="btn btn-success">
                                <i class="fas fa-file-plus me-2"></i>Buat Surat Tugas
                            </a>
                        </div>
                    </div>
                    <div class="col-md-3 text-end">
                        <div class="text-muted">
                            <small>Dibuat: <?php echo format_date($pegawai['created_at']); ?></small><br>
                            <small>Update: <?php echo format_date($pegawai['updated_at']); ?></small>
                        </div>
                    </div>
                </div>

                <!-- Data Pribadi -->
                <div class="detail-section">
                    <h5><i class="fas fa-user me-2"></i>Data Pribadi</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150"><strong>Nama Lengkap</strong></td>
                                    <td><?php echo htmlspecialchars($pegawai['nama']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>NIP</strong></td>
                                    <td><?php echo htmlspecialchars($pegawai['nip']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Tempat Lahir</strong></td>
                                    <td><?php echo htmlspecialchars($pegawai['tempat_lahir'] ?: '-'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Lahir</strong></td>
                                    <td><?php echo $pegawai['tanggal_lahir'] ? format_date($pegawai['tanggal_lahir']) : '-'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Jenis Kelamin</strong></td>
                                    <td><?php echo $pegawai['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150"><strong>Agama</strong></td>
                                    <td><?php echo htmlspecialchars($pegawai['agama'] ?: '-'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>No. HP</strong></td>
                                    <td><?php echo htmlspecialchars($pegawai['no_hp'] ?: '-'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Email</strong></td>
                                    <td><?php echo htmlspecialchars($pegawai['email'] ?: '-'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Alamat</strong></td>
                                    <td><?php echo htmlspecialchars($pegawai['alamat'] ?: '-'); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Data Kepegawaian -->
                <div class="detail-section">
                    <h5><i class="fas fa-briefcase me-2"></i>Data Kepegawaian</h5>
                    <?php
                    // Calculate masa kerja
                    $masa_kerja = '-';
                    if (!empty($pegawai['tanggal_masuk'])) {
                        $tanggal_masuk = new DateTime($pegawai['tanggal_masuk']);
                        $sekarang = new DateTime();
                        $selisih = $tanggal_masuk->diff($sekarang);
                        $masa_kerja = $selisih->y . ' tahun ' . $selisih->m . ' bulan';
                    }
                    ?>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150"><strong>Status Kepegawaian</strong></td>
                                    <td>
                                        <span class="badge badge-status badge-<?php echo strtolower($pegawai['status_kepegawaian']); ?>">
                                            <?php echo htmlspecialchars($pegawai['status_kepegawaian']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Golongan Ruang</strong></td>
                                    <td><?php echo htmlspecialchars($pegawai['golongan_ruangan'] ?: '-'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Jabatan</strong></td>
                                    <td><?php echo htmlspecialchars($pegawai['jabatan'] ?: '-'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Unit Kerja</strong></td>
                                    <td><?php echo htmlspecialchars($pegawai['unit_kerja'] ?: '-'); ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150"><strong>Status Pegawai</strong></td>
                                    <td>
                                        <span class="badge badge-status badge-<?php echo strtolower($pegawai['status_pegawai']); ?>">
                                            <?php echo ucfirst($pegawai['status_pegawai']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Masa Kerja</strong></td>
                                    <td><?php echo $masa_kerja; ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Data Pendidikan -->
                <div class="detail-section">
                    <h5><i class="fas fa-graduation-cap me-2"></i>Data Pendidikan</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150"><strong>Pendidikan Terakhir</strong></td>
                                    <td><?php echo htmlspecialchars($pegawai['pendidikan_terakhir'] ?: '-'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Jurusan</strong></td>
                                    <td><?php echo htmlspecialchars($pegawai['jurusan'] ?: '-'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Tahun Lulus</strong></td>
                                    <td><?php echo htmlspecialchars($pegawai['tahun_lulus'] ?: '-'); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Riwayat Cuti -->
                <div class="detail-section">
                    <h5><i class="fas fa-calendar-alt me-2"></i>Riwayat Cuti</h5>
                    
                    <!-- Sisa Kuota Cuti -->
                    <div class="alert alert-info mb-3">
                        <h6><i class="fas fa-info-circle me-2"></i>Sisa Kuota Cuti Tahunan:</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Tahun Ini (N):</strong> <?php echo $pegawai['cuti_n'] ?? 12; ?> hari
                            </div>
                            <div class="col-md-4">
                                <strong>Tahun Lalu (N-1):</strong> <?php echo $pegawai['cuti_n_minus_1'] ?? 0; ?> hari
                            </div>
                            <div class="col-md-4">
                                <strong>2 Tahun Lalu (N-2):</strong> <?php echo $pegawai['cuti_n_minus_2'] ?? 0; ?> hari
                            </div>
                        </div>
                        <div class="mt-2">
                            <strong>Total Sisa Cuti:</strong> <?php echo ($pegawai['cuti_n'] ?? 12) + ($pegawai['cuti_n_minus_1'] ?? 0) + ($pegawai['cuti_n_minus_2'] ?? 0); ?> hari (Max 24 hari)
                        </div>
                    </div>
                    
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM cuti WHERE id_pegawai = ? ORDER BY tanggal_pengajuan DESC LIMIT 5");
                    $stmt->execute([$pegawai['id']]);
                    $cuti_history = $stmt->fetchAll();
                    
                    if (empty($cuti_history)):
                    ?>
                    <p class="text-muted">Belum ada riwayat cuti</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Jenis Cuti</th>
                                    <th>Periode</th>
                                    <th>Lama</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cuti_history as $cuti): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cuti['jenis_cuti']); ?></td>
                                    <td><?php echo format_date($cuti['tanggal_mulai']); ?> - <?php echo format_date($cuti['tanggal_selesai']); ?></td>
                                    <td><?php echo $cuti['lama_cuti']; ?> hari</td>
                                    <td>
                                        <span class="badge bg-<?php echo $cuti['status_cuti'] == 'Disetujui' ? 'success' : ($cuti['status_cuti'] == 'Ditolak' ? 'danger' : 'warning'); ?>">
                                            <?php echo htmlspecialchars($cuti['status_cuti']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="cuti.php?action=view&id=<?php echo $cuti['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($cuti['file_surat']): ?>
                                        <a href="download/download.php?file=<?php echo htmlspecialchars($cuti['file_surat']); ?>" class="btn btn-sm btn-success">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Riwayat Surat Tugas -->
                <div class="detail-section">
                    <h5><i class="fas fa-file-alt me-2"></i>Riwayat Surat Tugas</h5>
                    <?php
                    $stmt = $pdo->prepare("
                        SELECT st.*
                        FROM surat_tugas st
                        INNER JOIN surat_tugas_pegawai stp ON st.id = stp.id_surat_tugas
                        WHERE stp.id_pegawai = ?
                        ORDER BY st.created_at DESC
                        LIMIT 5
                    ");
                    $stmt->execute([$pegawai['id']]);
                    $surat_history = $stmt->fetchAll();
                    
                    if (empty($surat_history)):
                    ?>
                    <p class="text-muted">Belum ada riwayat surat tugas</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Tentang</th>
                                    <th>Tanggal Surat</th>
                                    <th>Hari</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($surat_history as $surat): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($surat['tentang'] ?? '-'); ?></td>
                                    <td><?php echo format_date($surat['tanggal_surat_tugas']); ?></td>
                                    <td><?php echo htmlspecialchars($surat['hari'] ?? '-'); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $surat['status_surat'] == 'Disetujui' ? 'success' : ($surat['status_surat'] == 'Ditolak' ? 'danger' : 'info'); ?>">
                                            <?php echo htmlspecialchars($surat['status_surat']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="surat_tugas.php?action=view&id=<?php echo $surat['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($surat['file_surat']): ?>
                                        <a href="download/download.php?file=<?php echo htmlspecialchars($surat['file_surat']); ?>" class="btn btn-sm btn-success">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
