<?php
require_once 'config.php';
require_login();

// Check if user is admin
if (!is_admin()) {
    set_flash_message('error', 'Anda tidak memiliki akses ke halaman ini!');
    header('Location: index.php');
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);

// Handle approval action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    $action = $_POST['action']; // approve or reject
    $catatan = clean_input($_POST['catatan'] ?? '');
    
    try {
        // Get cuti data
        $stmt = $pdo->prepare("SELECT * FROM cuti WHERE id = ?");
        $stmt->execute([$id]);
        $cuti = $stmt->fetch();
        
        if (!$cuti) {
            set_flash_message('error', 'Data cuti tidak ditemukan!');
            header('Location: approval_cuti.php');
            exit();
        }
        
        if ($cuti['status_cuti'] !== 'Pending') {
            set_flash_message('error', 'Pengajuan cuti sudah diproses!');
            header('Location: approval_cuti.php');
            exit();
        }
        
        if ($action === 'approve') {
            // Calculate working days
            $start = new DateTime($cuti['tanggal_mulai']);
            $end = new DateTime($cuti['tanggal_selesai']);
            $workingDays = 0;
            
            // Get holidays
            $stmt = $pdo->query("SELECT tanggal FROM holidays WHERE YEAR(tanggal) = YEAR(CURDATE())");
            $holidays = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $holiday_dates = array_map(function($date) {
                return date('Y-m-d', strtotime($date));
            }, $holidays);
            
            while ($start <= $end) {
                $dayOfWeek = $start->format('N'); // 1 = Monday, 7 = Sunday
                $dateStr = $start->format('Y-m-d');
                
                if ($dayOfWeek < 6 && !in_array($dateStr, $holiday_dates)) {
                    $workingDays++;
                }
                
                $start->modify('+1 day');
            }
            
            // Get pegawai quota
            $stmt = $pdo->prepare("SELECT cuti_n, cuti_n_minus_1, cuti_n_minus_2 FROM pegawai WHERE id = ?");
            $stmt->execute([$cuti['id_pegawai']]);
            $pegawai = $stmt->fetch();
            
            if (!$pegawai) {
                set_flash_message('error', 'Data pegawai tidak ditemukan!');
                header('Location: approval_cuti.php');
                exit();
            }
            
            // Auto-deduct: N-2 dulu (yang paling lama), baru N-1, baru N
            // Prioritas: gunakan cuti yang akan hangus duluan
            $remaining_days = $workingDays;
            $new_n = $pegawai['cuti_n'];
            $new_n_minus_1 = $pegawai['cuti_n_minus_1'];
            $new_n_minus_2 = $pegawai['cuti_n_minus_2'];
            
            // Track deducted amounts for cuti table
            $deducted_n = 0;
            $deducted_n_minus_1 = 0;
            $deducted_n_minus_2 = 0;
            
            // Deduct from N-2 first (oldest - will expire first)
            if ($remaining_days > 0 && $new_n_minus_2 > 0) {
                $deduct = min($remaining_days, $new_n_minus_2);
                $new_n_minus_2 -= $deduct;
                $deducted_n_minus_2 = $deduct;
                $remaining_days -= $deduct;
            }
            
            // Deduct from N-1 (next to expire)
            if ($remaining_days > 0 && $new_n_minus_1 > 0) {
                $deduct = min($remaining_days, $new_n_minus_1);
                $new_n_minus_1 -= $deduct;
                $deducted_n_minus_1 = $deduct;
                $remaining_days -= $deduct;
            }
            
            // Deduct from N last (current year)
            if ($remaining_days > 0 && $new_n > 0) {
                $deduct = min($remaining_days, $new_n);
                $new_n -= $deduct;
                $deducted_n = $deduct;
                $remaining_days -= $deduct;
            }
            
            // Update pegawai quota (remaining balance)
            $stmt = $pdo->prepare("UPDATE pegawai SET cuti_n = ?, cuti_n_minus_1 = ?, cuti_n_minus_2 = ? WHERE id = ?");
            $stmt->execute([$new_n, $new_n_minus_1, $new_n_minus_2, $cuti['id_pegawai']]);
            
            // Update cuti status with deducted amounts (what was used)
            $stmt = $pdo->prepare("UPDATE cuti SET status_cuti = 'Disetujui', approved_by = ?, tanggal_approve = ?, catatan_penolakan = ?, cuti_n = ?, cuti_n_minus_1 = ?, cuti_n_minus_2 = ? WHERE id = ?");
            $stmt->execute([$_SESSION['nama_lengkap'], date('Y-m-d'), $catatan, $deducted_n, $deducted_n_minus_1, $deducted_n_minus_2, $id]);
            
            log_activity($_SESSION['user_id'], 'approve_cuti', "Approved cuti ID: $id for {$cuti['nama_pegawai']}");
            set_flash_message('success', 'Pengajuan cuti berhasil disetujui! Kuota cuti telah dikurangi.');
            
        } else if ($action === 'reject') {
            // Update cuti status only (no quota deduction)
            $stmt = $pdo->prepare("UPDATE cuti SET status_cuti = 'Ditolak', approved_by = ?, tanggal_approve = ?, catatan_penolakan = ?, cuti_n = 0, cuti_n_minus_1 = 0, cuti_n_minus_2 = 0 WHERE id = ?");
            $stmt->execute([$_SESSION['nama_lengkap'], date('Y-m-d'), $catatan, $id]);
            
            log_activity($_SESSION['user_id'], 'reject_cuti', "Rejected cuti ID: $id for {$cuti['nama_pegawai']}");
            set_flash_message('success', 'Pengajuan cuti berhasil ditolak!');
        }
        
        header('Location: approval_cuti.php');
        exit();
        
    } catch (PDOException $e) {
        set_flash_message('error', 'Gagal memproses pengajuan cuti: ' . $e->getMessage());
        header('Location: approval_cuti.php');
        exit();
    }
}

// Get pending cuti requests
try {
    $stmt = $pdo->prepare("
        SELECT c.*, p.nama as nama_pegawai, p.nip as nip_pegawai
        FROM cuti c
        JOIN pegawai p ON c.id_pegawai = p.id
        WHERE c.status_cuti = 'Pending'
        ORDER BY c.tanggal_pengajuan DESC
    ");
    $stmt->execute();
    $pending_cuti = $stmt->fetchAll();
} catch (PDOException $e) {
    $pending_cuti = [];
}

// Get processed cuti (approved/rejected)
try {
    $stmt = $pdo->prepare("
        SELECT c.*, p.nama as nama_pegawai, p.nip as nip_pegawai
        FROM cuti c
        JOIN pegawai p ON c.id_pegawai = p.id
        WHERE c.status_cuti != 'Pending'
        ORDER BY c.tanggal_approve DESC
        LIMIT 20
    ");
    $stmt->execute();
    $processed_cuti = $stmt->fetchAll();
} catch (PDOException $e) {
    $processed_cuti = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Cuti - <?php echo APP_NAME; ?></title>
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
                            <i class="fas fa-check-circle me-2"></i>Approval Cuti
                        </h1>
                        <p class="mb-0">Setujui atau tolak pengajuan cuti</p>
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

            <!-- Pending Requests -->
            <div class="content-card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-clock me-2"></i>Pengajuan Cuti Pending (<?php echo count($pending_cuti); ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($pending_cuti)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="text-muted">Tidak ada pengajuan cuti pending</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Nomor Surat</th>
                                        <th>Nama Pegawai</th>
                                        <th>NIP</th>
                                        <th>Jenis Cuti</th>
                                        <th>Tanggal</th>
                                        <th>Lama</th>
                                        <th>Alasan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pending_cuti as $cuti): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($cuti['nomor_surat_cuti']); ?></td>
                                        <td><?php echo htmlspecialchars($cuti['nama_pegawai']); ?></td>
                                        <td><?php echo htmlspecialchars($cuti['nip_pegawai']); ?></td>
                                        <td><?php echo htmlspecialchars($cuti['jenis_cuti']); ?></td>
                                        <td>
                                            <?php echo format_date($cuti['tanggal_mulai']); ?> - <?php echo format_date($cuti['tanggal_selesai']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($cuti['lama_cuti']); ?> hari</td>
                                        <td><?php echo htmlspecialchars(substr($cuti['alasan_cuti'], 0, 50)); ?>...</td>
                                        <td>
                                            <button class="btn btn-sm btn-success" onclick="showApproveModal(<?php echo $cuti['id']; ?>, '<?php echo htmlspecialchars($cuti['nama_pegawai']); ?>')">
                                                <i class="fas fa-check"></i> Setujui
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="showRejectModal(<?php echo $cuti['id']; ?>, '<?php echo htmlspecialchars($cuti['nama_pegawai']); ?>')">
                                                <i class="fas fa-times"></i> Tolak
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Processed Requests -->
            <div class="content-card">
                <div class="card-header">
                    <h5><i class="fas fa-history me-2"></i>Riwayat Approval (20 Terakhir)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($processed_cuti)): ?>
                        <div class="text-center py-4">
                            <p class="text-muted">Belum ada riwayat approval</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Nama Pegawai</th>
                                        <th>Jenis Cuti</th>
                                        <th>Status</th>
                                        <th>Tanggal Approve</th>
                                        <th>Approved By</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($processed_cuti as $cuti): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($cuti['nama_pegawai']); ?></td>
                                        <td><?php echo htmlspecialchars($cuti['jenis_cuti']); ?></td>
                                        <td>
                                            <?php if ($cuti['status_cuti'] == 'Disetujui'): ?>
                                                <span class="badge bg-success">Disetujui</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Ditolak</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo format_date($cuti['tanggal_approve']); ?></td>
                                        <td><?php echo htmlspecialchars($cuti['approved_by']); ?></td>
                                        <td><?php echo htmlspecialchars($cuti['catatan_penolakan'] ?? '-'); ?></td>
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

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Setujui Cuti</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menyetujui pengajuan cuti untuk <strong id="approveName"></strong>?</p>
                    <p class="text-muted">Kuota cuti pegawai akan dikurangi secara otomatis.</p>
                    <form method="POST" action="" id="approveForm">
                        <input type="hidden" name="action" value="approve">
                        <input type="hidden" name="id" id="approveId">
                        <div class="mb-3">
                            <label for="approveCatatan" class="form-label">Catatan (Opsional)</label>
                            <textarea class="form-control" id="approveCatatan" name="catatan" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" onclick="document.getElementById('approveForm').submit()">
                        <i class="fas fa-check me-2"></i>Setujui
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i>Tolak Cuti</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menolak pengajuan cuti untuk <strong id="rejectName"></strong>?</p>
                    <form method="POST" action="" id="rejectForm">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="id" id="rejectId">
                        <div class="mb-3">
                            <label for="rejectCatatan" class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejectCatatan" name="catatan" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" onclick="document.getElementById('rejectForm').submit()">
                        <i class="fas fa-times me-2"></i>Tolak
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function showApproveModal(id, name) {
        document.getElementById('approveId').value = id;
        document.getElementById('approveName').textContent = name;
        new bootstrap.Modal(document.getElementById('approveModal')).show();
    }
    
    function showRejectModal(id, name) {
        document.getElementById('rejectId').value = id;
        document.getElementById('rejectName').textContent = name;
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    }
    </script>
</body>
</html>
