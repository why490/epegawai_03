<?php
require_once 'config.php';
require_login();

// Handle actions
$action = $_GET['action'] ?? '';

// Handle delete
if ($action == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        // Delete file if exists
        $stmt = $pdo->prepare("SELECT file_surat FROM cuti WHERE id = ?");
        $stmt->execute([$id]);
        $cuti = $stmt->fetch();
        
        if ($cuti && $cuti['file_surat']) {
            delete_file($cuti['file_surat'], 'documents');
        }
        
        $stmt = $pdo->prepare("DELETE FROM cuti WHERE id = ?");
        $stmt->execute([$id]);
        
        log_activity($_SESSION['user_id'], 'delete_cuti', "Deleted cuti ID: $id");
        set_flash_message('success', 'Data cuti berhasil dihapus!');
    } catch (PDOException $e) {
        set_flash_message('error', 'Gagal menghapus data cuti: ' . $e->getMessage());
    }
    
    header('Location: cuti.php');
    exit();
}

// Handle approval
if ($action == 'approve' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $status = $_GET['status'] ?? 'Disetujui';
    $catatan = clean_input($_GET['catatan'] ?? '');
    
    try {
        if ($status == 'Disetujui') {
            // Get cuti data first
            $stmt = $pdo->prepare("SELECT * FROM cuti WHERE id = ?");
            $stmt->execute([$id]);
            $cuti = $stmt->fetch();
            
            if ($cuti && $cuti['jenis_cuti'] == 'Cuti Tahunan') {
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
                    $dayOfWeek = $start->format('N');
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
                
                if ($pegawai) {
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
                    
                    // Update cuti table with deducted amounts (what was used)
                    $stmt = $pdo->prepare("UPDATE cuti SET cuti_n = ?, cuti_n_minus_1 = ?, cuti_n_minus_2 = ? WHERE id = ?");
                    $stmt->execute([$deducted_n, $deducted_n_minus_1, $deducted_n_minus_2, $id]);
                }
            }
            
            // Update status and generate document
            $stmt = $pdo->prepare("
                UPDATE cuti SET status_cuti = ?, approved_by = ?, tanggal_approve = ? 
                WHERE id = ?
            ");
            $stmt->execute([$status, $_SESSION['nama_lengkap'], date('Y-m-d'), $id]);
            
            // Get cuti data for document generation
            $stmt = $pdo->prepare("
                SELECT c.*, p.status_kepegawaian, p.jabatan, p.unit_kerja 
                FROM cuti c 
                JOIN pegawai p ON c.id_pegawai = p.id 
                WHERE c.id = ?
            ");
            $stmt->execute([$id]);
            $cuti_data = $stmt->fetch();
            
            if ($cuti_data) {
                // Generate document
                $template_file = $cuti_data['status_kepegawaian'] == 'PPPK' ? 'template_cuti_pppk.txt' : 'template_cuti_pns.txt';
                $template_path = TEMPLATE_PATH . '/' . $template_file;
                
                if (file_exists($template_path)) {
                    $template_content = file_get_contents($template_path);
                    
                    // Replace placeholders
                    $replacements = [
                        '${nama}' => $cuti_data['nama_pegawai'],
                        '${nip}' => $cuti_data['nip'],
                        '${jabatan}' => $cuti_data['jabatan'],
                        '${unit_kerja}' => $cuti_data['unit_kerja'],
                        '${jenis_cuti}' => $cuti_data['jenis_cuti'],
                        '${tanggal_mulai}' => format_date($cuti_data['tanggal_mulai']),
                        '${tanggal_selesai}' => format_date($cuti_data['tanggal_selesai']),
                        '${lama_cuti}' => $cuti_data['lama_cuti'],
                        '${alasan_cuti}' => $cuti_data['alasan_cuti'],
                        '${alamat_cuti}' => $cuti_data['alamat_cuti'],
                        '${no_telepon_cuti}' => $cuti_data['no_telepon_cuti'],
                        '${tanggal_pengajuan}' => format_date($cuti_data['tanggal_pengajuan']),
                        '${tanggal_approve}' => format_date(date('Y-m-d')),
                        '${approved_by}' => $_SESSION['nama_lengkap']
                    ];
                    
                    $document_content = str_replace(array_keys($replacements), array_values($replacements), $template_content);
                    
                    // Save document
                    $filename = 'surat_cuti_' . $cuti_data['nip'] . '_' . date('Y-m-d_H-i-s') . '.txt';
                    $file_path = UPLOAD_PATH . '/' . $filename;
                    
                    if (file_put_contents($file_path, $document_content)) {
                        // Update database with file path
                        $stmt = $pdo->prepare("UPDATE cuti SET file_surat = ? WHERE id = ?");
                        $stmt->execute([$filename, $id]);
                        
                        log_activity($_SESSION['user_id'], 'generate_surat_cuti', "Generated surat cuti for: {$cuti_data['nama_pegawai']}");
                    }
                }
            }
        } else {
            // Reject - set cuti quota columns to 0 since no quota was used
            $stmt = $pdo->prepare("
                UPDATE cuti SET status_cuti = ?, catatan_penolakan = ?, approved_by = ?, tanggal_approve = ?, 
                    cuti_n = 0, cuti_n_minus_1 = 0, cuti_n_minus_2 = 0 
                WHERE id = ?
            ");
            $stmt->execute([$status, $catatan, $_SESSION['nama_lengkap'], date('Y-m-d'), $id]);
        }
        
        log_activity($_SESSION['user_id'], 'approve_cuti', "Approved cuti ID: $id with status: $status");
        set_flash_message('success', 'Pengajuan cuti berhasil ' . strtolower($status) . '!');
    } catch (PDOException $e) {
        set_flash_message('error', 'Gagal memproses pengajuan cuti: ' . $e->getMessage());
    }
    
    header('Location: cuti.php');
    exit();
}

// Handle add/edit
if ($action == 'add' || $action == 'edit') {
    $cuti = null;
    $pegawai = null;
    
    // Get current logged in pegawai data
    $stmt = $pdo->prepare("SELECT * FROM pegawai WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $pegawai = $stmt->fetch();
    
    if ($action == 'edit' && isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM cuti WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $cuti = $stmt->fetch();
        
        if (!$cuti) {
            set_flash_message('error', 'Data cuti tidak ditemukan!');
            header('Location: cuti.php');
            exit();
        }
    }
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nomor_surat_cuti = clean_input($_POST['nomor_surat_cuti']);
        $tanggal_pengajuan = !empty($_POST['tanggal_pengajuan']) ? clean_input($_POST['tanggal_pengajuan']) : date('Y-m-d');
        $id_pegawai = $_POST['id_pegawai'];
        $jenis_cuti = $_POST['jenis_cuti'];
        $tanggal_mulai = $_POST['tanggal_mulai'];
        $tanggal_selesai = $_POST['tanggal_selesai'];
        $alasan_cuti = clean_input($_POST['alasan_cuti']);

        $alamat_cuti = clean_input($_POST['alamat_cuti']);
        $no_telepon_cuti = clean_input($_POST['no_telepon_cuti']);
        $cuti_n_minus_2 = intval($_POST['cuti_n_minus_2'] ?? 0);
        $cuti_n_minus_1 = intval($_POST['cuti_n_minus_1'] ?? 0);
        $cuti_n = intval($_POST['cuti_n'] ?? 0);
        $atasan_id = $_POST['atasan_id'] ?? null;
        $pejabat_id = $_POST['pejabat_id'] ?? null;
        
        // Get pegawai data (main pegawai)
        $stmt = $pdo->prepare("SELECT * FROM pegawai WHERE id = ?");
        $stmt->execute([$id_pegawai]);
        $pegawai = $stmt->fetch();

        $snapshot_cuti_n_minus_2 = intval($pegawai['cuti_n_minus_2'] ?? 0);
        $snapshot_cuti_n_minus_1 = intval($pegawai['cuti_n_minus_1'] ?? 0);
        $snapshot_cuti_n = intval($pegawai['cuti_n'] ?? 0);
        
        // Get atasan data
        $atasan_nama = $atasan_nip = null;
        if ($atasan_id) {
            $stmt = $pdo->prepare("SELECT nama, nip FROM pegawai WHERE id = ? AND status_pegawai = 'aktif'");
            $stmt->execute([$atasan_id]);
            $atasan = $stmt->fetch();
            if ($atasan) {
                $atasan_nama = $atasan['nama'];
                $atasan_nip = $atasan['nip'];
            }
        }
        
        // Get pejabat data
        $pejabat_nama = $pejabat_nip = null;
        if ($pejabat_id) {
            $stmt = $pdo->prepare("SELECT nama, nip FROM pegawai WHERE id = ? AND status_pegawai = 'aktif'");
            $stmt->execute([$pejabat_id]);
            $pejabat = $stmt->fetch();
            if ($pejabat) {
                $pejabat_nama = $pejabat['nama'];
                $pejabat_nip = $pejabat['nip'];
            }
        }

        
        if (!$pegawai) {
            set_flash_message('error', 'Pegawai tidak ditemukan!');
        } else {
            // Calculate leave duration based on jenis_cuti
            if ($jenis_cuti == 'Cuti Melahirkan') {
                // Cuti Melahirkan counts all days including weekends and holidays
                $start = new DateTime($tanggal_mulai);
                $end = new DateTime($tanggal_selesai);
                $diff = $start->diff($end);
                $lama_cuti = $diff->days + 1;
            } else {
                // Other leave types count only working days
                $lama_cuti = calculate_working_days($tanggal_mulai, $tanggal_selesai);
            }

            try {
                if ($action == 'add') {

                    $stmt = $pdo->prepare("
                        INSERT INTO cuti (nomor_surat_cuti, id_pegawai, nip, nama_pegawai, jenis_cuti, tanggal_pengajuan,
                                         tanggal_mulai, tanggal_selesai, lama_cuti, alasan_cuti, alamat_cuti, no_telepon_cuti,
                                         cuti_n_minus_2, cuti_n_minus_1, cuti_n, sisa_cuti_n_minus_2, sisa_cuti_n_minus_1, sisa_cuti_n,
                                         atasan_id, atasan_nama, atasan_nip, pejabat_id, pejabat_nama, pejabat_nip, status_cuti, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                    ");
                    $stmt->execute([
                        $nomor_surat_cuti, $id_pegawai, $pegawai['nip'], $pegawai['nama'], $jenis_cuti, $tanggal_pengajuan,
                        $tanggal_mulai, $tanggal_selesai, $lama_cuti, $alasan_cuti, $alamat_cuti, $no_telepon_cuti,
                        $cuti_n_minus_2, $cuti_n_minus_1, $cuti_n,
                        $snapshot_cuti_n_minus_2, $snapshot_cuti_n_minus_1, $snapshot_cuti_n,
                        $atasan_id, $atasan_nama, $atasan_nip,
                        $pejabat_id, $pejabat_nama, $pejabat_nip, 'Pending'
                    ]);

                    
                    log_activity($_SESSION['user_id'], 'add_cuti', "Added cuti for: {$pegawai['nama']}");
                    set_flash_message('success', 'Pengajuan cuti berhasil ditambahkan!');
                    header('Location: cuti.php');
                    exit();
                } else {
                    // Edit cuti

                    $stmt = $pdo->prepare("
                        UPDATE cuti SET nomor_surat_cuti = ?, id_pegawai = ?, nip = ?, nama_pegawai = ?, jenis_cuti = ?,
                                         tanggal_pengajuan = ?, tanggal_mulai = ?, tanggal_selesai = ?, lama_cuti = ?, alasan_cuti = ?,
                                         alamat_cuti = ?, no_telepon_cuti = ?, cuti_n_minus_2 = ?, cuti_n_minus_1 = ?, cuti_n = ?,
                                         sisa_cuti_n_minus_2 = ?, sisa_cuti_n_minus_1 = ?, sisa_cuti_n = ?,
                                         atasan_id = ?, atasan_nama = ?, atasan_nip = ?, pejabat_id = ?, pejabat_nama = ?, pejabat_nip = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $nomor_surat_cuti, $id_pegawai, $pegawai['nip'], $pegawai['nama'], $jenis_cuti,
                        $tanggal_pengajuan, $tanggal_mulai, $tanggal_selesai, $lama_cuti, $alasan_cuti, $alamat_cuti, $no_telepon_cuti,
                        $cuti_n_minus_2, $cuti_n_minus_1, $cuti_n,
                        $snapshot_cuti_n_minus_2, $snapshot_cuti_n_minus_1, $snapshot_cuti_n,
                        $atasan_id, $atasan_nama, $atasan_nip, $pejabat_id, $pejabat_nama, $pejabat_nip,
                        $cuti['id']
                    ]);

                    
                    log_activity($_SESSION['user_id'], 'edit_cuti', "Updated cuti for: {$pegawai['nama']}");
                    set_flash_message('success', 'Pengajuan cuti berhasil diperbarui!');
                    header('Location: cuti.php');
                    exit();
                }
            } catch (PDOException $e) {
                set_flash_message('error', 'Gagal menyimpan pengajuan cuti: ' . $e->getMessage());
            }
        }
    }
    

    // Get pegawai list for dropdowns

    $stmt = $pdo->prepare("SELECT id, nip, nama, status_kepegawaian, alamat, no_hp, cuti_n, cuti_n_minus_1, cuti_n_minus_2 FROM pegawai WHERE status_pegawai = 'aktif' ORDER BY nama");
    $stmt->execute();
    $pegawai_list = $stmt->fetchAll();

    
    // For edit mode, pre-load atasan/pejabat if exist
    if (isset($cuti)) {
        if ($cuti['atasan_id']) {
            $stmt = $pdo->prepare("SELECT nama, nip FROM pegawai WHERE id = ?");
            $stmt->execute([$cuti['atasan_id']]);
            $atasan_selected = $stmt->fetch();
        }
        if ($cuti['pejabat_id']) {
            $stmt = $pdo->prepare("SELECT nama, nip FROM pegawai WHERE id = ?");
            $stmt->execute([$cuti['pejabat_id']]);
            $pejabat_selected = $stmt->fetch();
        }
    }

    
    // Show add/edit form
    include 'includes/cuti_form.php';
    exit();
}

// Handle view detail
if ($action == 'view' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt = $pdo->prepare("
        SELECT c.*, p.status_kepegawaian, p.jabatan, p.unit_kerja, p.cuti_n, p.cuti_n_minus_1, p.cuti_n_minus_2
        FROM cuti c 
        JOIN pegawai p ON c.id_pegawai = p.id 
        WHERE c.id = ?
    ");
    $stmt->execute([$id]);
    $cuti = $stmt->fetch();
    
    if (!$cuti) {
        set_flash_message('error', 'Data cuti tidak ditemukan!');
        header('Location: cuti.php');
        exit();
    }
    
    // Show detail view
    include 'includes/cuti_detail.php';
    exit();
}

// Get cuti list
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$jenis_filter = $_GET['jenis'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;

$query = "SELECT c.*, p.status_kepegawaian FROM cuti c JOIN pegawai p ON c.id_pegawai = p.id WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (c.nama_pegawai LIKE ? OR c.nip LIKE ? OR c.jenis_cuti LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
}

if (!empty($status_filter)) {
    $query .= " AND c.status_cuti = ?";
    $params[] = $status_filter;
}

if (!empty($jenis_filter)) {
    $query .= " AND c.jenis_cuti = ?";
    $params[] = $jenis_filter;
}

$query .= " ORDER BY c.tanggal_pengajuan DESC";

$cuti_list = paginate($query, $params, $page, $per_page);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Cuti - <?php echo APP_NAME; ?></title>
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
                            <i class="fas fa-calendar-alt me-2"></i>Manajemen Cuti
                        </h1>
                        <p class="mb-0">Kelola pengajuan cuti pegawai</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="cuti.php?action=add" class="btn btn-light">
                            <i class="fas fa-calendar-plus me-2"></i>Ajukan Cuti
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

            <!-- Search Box -->
            <div class="search-box">
                <form method="GET" action="">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Cari nama, NIP, atau jenis cuti..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="status">
                                <option value="">Semua Status</option>
                                <option value="Pending" <?php echo $status_filter == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Disetujui" <?php echo $status_filter == 'Disetujui' ? 'selected' : ''; ?>>Disetujui</option>
                                <option value="Ditolak" <?php echo $status_filter == 'Ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="jenis">
                                <option value="">Semua Jenis</option>
                                <option value="Cuti Tahunan" <?php echo $jenis_filter == 'Cuti Tahunan' ? 'selected' : ''; ?>>Cuti Tahunan</option>
                                <option value="Cuti Sakit" <?php echo $jenis_filter == 'Cuti Sakit' ? 'selected' : ''; ?>>Cuti Sakit</option>
                                <option value="Cuti Melahirkan" <?php echo $jenis_filter == 'Cuti Melahirkan' ? 'selected' : ''; ?>>Cuti Melahirkan</option>
                                <option value="Cuti Besar" <?php echo $jenis_filter == 'Cuti Besar' ? 'selected' : ''; ?>>Cuti Besar</option>
                                <option value="Cuti Alasan Penting" <?php echo $jenis_filter == 'Cuti Alasan Penting' ? 'selected' : ''; ?>>Cuti Alasan Penting</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Cari
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="cuti.php" class="btn btn-secondary w-100">
                                <i class="fas fa-redo me-2"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Cuti List -->
            <div class="content-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>Daftar Pengajuan Cuti</h5>
                    <span class="badge bg-primary"><?php echo $cuti_list['total']; ?> pengajuan</span>
                </div>

                <?php if (empty($cuti_list['records'])): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak ada pengajuan cuti</h5>
                        <p class="text-muted">Mulai dengan mengajukan cuti pegawai</p>
                        <a href="cuti.php?action=add" class="btn btn-primary">
                            <i class="fas fa-calendar-plus me-2"></i>Ajukan Cuti
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($cuti_list['records'] as $cuti): ?>
                        <div class="cuti-card">
                            <div class="cuti-header">
                                <div>
                                    <div class="cuti-title"><?php echo htmlspecialchars($cuti['nomor_surat_cuti'] ?? '-'); ?></div>
                                    <div class="cuti-meta">
                                        <?php echo htmlspecialchars($cuti['nama_pegawai']); ?> | <?php echo htmlspecialchars($cuti['jenis_cuti']); ?> |
                                        Pengajuan: <?php echo format_date($cuti['tanggal_pengajuan']); ?> |
                                        <?php echo format_date($cuti['tanggal_mulai']); ?> - <?php echo format_date($cuti['tanggal_selesai']); ?> |
                                        <?php echo $cuti['lama_cuti']; ?> hari
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge badge-status badge-<?php echo strtolower($cuti['status_cuti']); ?>">
                                        <?php echo htmlspecialchars($cuti['status_cuti']); ?>
                                    </span>
                                    <div class="cuti-actions">
                                        <a href="cuti.php?action=view&id=<?php echo $cuti['id']; ?>" class="btn btn-sm btn-info btn-action">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($cuti['status_cuti'] == 'Disetujui'): ?>
                                        <a href="download/download_surat_cuti.php?id=<?php echo $cuti['id']; ?>" class="btn btn-sm btn-success btn-action" title="Download Surat Cuti (Word)">
                                            <i class="fas fa-file-word"></i>
                                        </a>
                                        <?php endif; ?>
                                        <!-- .txt download REMOVED per user request - Word only now -->
                                        <!-- <a href="download.php?file=... class="btn-warning">.txt</a> -->
                                        <?php if ($cuti['status_cuti'] == 'Pending'): ?>
                                        <a href="cuti.php?action=edit&id=<?php echo $cuti['id']; ?>" class="btn btn-sm btn-warning btn-action">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger btn-action" 
                                                onclick="confirmDelete(<?php echo $cuti['id']; ?>, '<?php echo htmlspecialchars($cuti['nama_pegawai']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php if ($cuti['catatan_penolakan']): ?>
                            <div class="alert alert-warning mt-2 mb-0">
                                <small><strong>Catatan Penolakan:</strong> <?php echo htmlspecialchars($cuti['catatan_penolakan']); ?></small>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <!-- Pagination -->
                    <?php if ($cuti_list['total_pages'] > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php if ($cuti_list['page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $cuti_list['page'] - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&jenis=<?php echo urlencode($jenis_filter); ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $cuti_list['total_pages']; $i++): ?>
                                    <li class="page-item <?php echo $i == $cuti_list['page'] ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&jenis=<?php echo urlencode($jenis_filter); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($cuti_list['page'] < $cuti_list['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $cuti_list['page'] + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&jenis=<?php echo urlencode($jenis_filter); ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script src="assets/js/live-search.js"></script>
</body>
</html>
