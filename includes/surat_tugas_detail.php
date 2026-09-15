<?php
require_once 'config.php';
require_login();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Surat Tugas - <?php echo APP_NAME; ?></title>
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
                            <i class="fas fa-file-alt me-2"></i>Detail Surat Tugas
                        </h1>
                        <p class="mb-0">Informasi lengkap surat tugas</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="surat_tugas.php" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                        <?php if ($surat_tugas['status_surat'] == 'Draft' || $surat_tugas['status_surat'] == 'Diajukan'): ?>
                        <a href="surat_tugas.php?action=edit&id=<?php echo $surat_tugas['id']; ?>" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Edit
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <!-- Informasi Surat -->
            <div class="content-card mb-4">
                <h5>Informasi Surat</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="fw-bold">Nomor Surat Tugas:</label>
                            <div><?php echo htmlspecialchars($surat_tugas['nomor_surat_tugas'] ?? '-'); ?></div>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Nomor Surat:</label>
                            <div><?php echo htmlspecialchars($surat_tugas['nomor_surat'] ?? '-'); ?></div>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Berdasarkan Surat:</label>
                            <div><?php echo htmlspecialchars($surat_tugas['berdasarkan_surat'] ?? '-'); ?></div>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Hari:</label>
                            <div><?php echo htmlspecialchars(html_entity_decode($surat_tugas['hari'] ?? '-')); ?></div>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Tanggal Surat:</label>
                            <div><?php echo format_date($surat_tugas['tanggal_surat']); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="fw-bold">Tentang:</label>
                            <div><?php echo htmlspecialchars($surat_tugas['tentang'] ?? '-'); ?></div>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Tanggal Surat Tugas:</label>
                            <div><?php echo format_date($surat_tugas['tanggal_surat_tugas']); ?></div>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Lokasi Tugas:</label>
                            <div><?php echo htmlspecialchars($surat_tugas['tempat_tugas']); ?></div>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Status:</label>
                            <div>
                                <span class="badge badge-status badge-<?php echo strtolower($surat_tugas['status_surat']); ?>">
                                    <?php echo htmlspecialchars($surat_tugas['status_surat']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pegawai Bertugas -->
            <div class="content-card mb-4">
                <h5>Pegawai Bertugas</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Pegawai</th>
                                <th>NIP</th>
                                <th>Jabatan</th>
                                <th>Golongan Ruang</th>
                                <th>Tanggal Melaksanakan Tugas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->prepare("
                                SELECT * FROM surat_tugas_pegawai
                                WHERE id_surat_tugas = ?
                                ORDER BY id
                            ");
                            $stmt->execute([$surat_tugas['id']]);
                            $pegawai_bertugas = $stmt->fetchAll();
                            $no = 1;
                            foreach ($pegawai_bertugas as $pegawai):
                            ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($pegawai['nama_pegawai']); ?></td>
                                    <td><?php echo htmlspecialchars($pegawai['nip']); ?></td>
                                    <td><?php echo htmlspecialchars($pegawai['jabatan']); ?></td>
                                    <td><?php echo htmlspecialchars($pegawai['golongan_ruang']); ?></td>
                                    <td><?php echo format_date($pegawai['tanggal_mulai_tugas']); ?> - <?php echo format_date($pegawai['tanggal_selesai_tugas']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pejabat Penanda Tangan -->
            <div class="content-card">
                <h5>Pejabat Penanda Tangan</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="fw-bold">Nama Pejabat:</label>
                            <div><?php echo htmlspecialchars($surat_tugas['pejabat_nama'] ?? '-'); ?></div>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">NIP:</label>
                            <div><?php echo htmlspecialchars($surat_tugas['pejabat_nip'] ?? '-'); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="fw-bold">Jabatan:</label>
                            <div><?php echo htmlspecialchars($surat_tugas['pejabat_jabatan'] ?? '-'); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($surat_tugas['catatan_penolakan']): ?>
            <div class="alert alert-warning mt-4">
                <strong>Catatan Penolakan:</strong> <?php echo htmlspecialchars($surat_tugas['catatan_penolakan']); ?>
            </div>
            <?php endif; ?>

            <!-- Approval Section -->
            <?php if (is_admin() && $surat_tugas['status_surat'] == 'Diajukan'): ?>
            <div class="content-card mt-4">
                <h5><i class="fas fa-tasks me-2"></i>Persetujuan</h5>
                <div class="row">
                    <div class="col-md-6">
                        <form method="POST" action="surat_tugas.php?action=approve&id=<?php echo $surat_tugas['id']; ?>&status=Disetujui">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-info-circle me-1"></i>Informasi Approve
                                </label>
                                <div class="alert alert-info py-2">
                                    <small>
                                        <strong>Template:</strong> template_surat_tugas.docx<br>
                                        <strong>Output:</strong> Dokumen Word akan otomatis dibuat dengan placeholder<br>
                                        <strong>Format:</strong> File .docx yang dapat di-download
                                    </small>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-check me-2"></i>Setujui & Generate Dokumen
                            </button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <form method="POST" action="surat_tugas.php?action=approve&id=<?php echo $surat_tugas['id']; ?>&status=Ditolak">
                            <div class="mb-3">
                                <label for="catatan" class="form-label">
                                    <i class="fas fa-comment me-1"></i>Catatan Penolakan
                                </label>
                                <textarea class="form-control" id="catatan" name="catatan" rows="3"
                                          placeholder="Alasan penolakan..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-times me-2"></i>Tolak
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
