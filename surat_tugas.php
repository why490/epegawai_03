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
        $stmt = $pdo->prepare("SELECT file_surat FROM surat_tugas WHERE id = ?");
        $stmt->execute([$id]);
        $surat = $stmt->fetch();
        
        if ($surat && $surat['file_surat']) {
            delete_file($surat['file_surat'], 'documents');
        }
        
        $stmt = $pdo->prepare("DELETE FROM surat_tugas WHERE id = ?");
        $stmt->execute([$id]);
        
        log_activity($_SESSION['user_id'], 'delete_surat_tugas', "Deleted surat tugas ID: $id");
        set_flash_message('success', 'Data surat tugas berhasil dihapus!');
    } catch (PDOException $e) {
        set_flash_message('error', 'Gagal menghapus data surat tugas: ' . $e->getMessage());
    }
    
    header('Location: surat_tugas.php');
    exit();
}

// Handle submit (change status from Draft to Diajukan)
if ($action == 'submit' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        $stmt = $pdo->prepare("UPDATE surat_tugas SET status_surat = 'Diajukan' WHERE id = ? AND status_surat = 'Draft'");
        $stmt->execute([$id]);
        
        log_activity($_SESSION['user_id'], 'submit_surat_tugas', "Submitted surat tugas ID: $id");
        set_flash_message('success', 'Surat tugas berhasil diajukan!');
    } catch (PDOException $e) {
        set_flash_message('error', 'Gagal mengajukan surat tugas: ' . $e->getMessage());
    }
    
    header('Location: surat_tugas.php');
    exit();
}

// Handle approval
if ($action == 'approve' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $status = $_GET['status'] ?? 'Disetujui';
    $catatan = clean_input($_POST['catatan'] ?? '');

    try {
        if ($status == 'Disetujui') {
            // Update status
            $stmt = $pdo->prepare("
                UPDATE surat_tugas SET status_surat = ?, approved_by = ?, tanggal_approve = ?
                WHERE id = ?
            ");
            $stmt->execute([$status, $_SESSION['nama_lengkap'], date('Y-m-d'), $id]);

            log_activity($_SESSION['user_id'], 'approve_surat_tugas', "Approved surat tugas ID: $id");
            set_flash_message('success', 'Surat tugas berhasil disetujui! Silakan download dokumen dari halaman list.');
        } else {
            // Reject
            $stmt = $pdo->prepare("
                UPDATE surat_tugas SET status_surat = ?, catatan_penolakan = ?, approved_by = ?, tanggal_approve = ?
                WHERE id = ?
            ");
            $stmt->execute([$status, $catatan, $_SESSION['nama_lengkap'], date('Y-m-d'), $id]);

            log_activity($_SESSION['user_id'], 'reject_surat_tugas', "Rejected surat tugas ID: $id");
            set_flash_message('success', 'Surat tugas berhasil ditolak!');
        }
    } catch (PDOException $e) {
        set_flash_message('error', 'Gagal memproses persetujuan: ' . $e->getMessage());
    }

    header('Location: surat_tugas.php');
    exit();
}

// Handle add/edit
if ($action == 'add' || $action == 'edit') {
    $surat_tugas = null;
    
    if ($action == 'edit' && isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM surat_tugas WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $surat_tugas = $stmt->fetch();
        
        if (!$surat_tugas) {
            set_flash_message('error', 'Data surat tugas tidak ditemukan!');
            header('Location: surat_tugas.php');
            exit();
        }
    }
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nomor_surat_tugas = clean_input($_POST['nomor_surat_tugas']);
        $berdasarkan_surat = clean_input($_POST['berdasarkan_surat']);
        $nomor_surat = clean_input($_POST['nomor_surat']);
        $hari = clean_input($_POST['hari']);
        $tanggal_surat = $_POST['tanggal_surat'];
        $tentang = clean_input($_POST['tentang']);
        $tempat_tugas = clean_input($_POST['lokasi_tugas']);
        $tanggal_surat_tugas = $_POST['tanggal_surat_tugas'];
        $pejabat_tanda_tangan = $_POST['pejabat_tanda_tangan'];
        $pegawai_bertugas = $_POST['pegawai_bertugas'] ?? [];

        if (empty($pegawai_bertugas)) {
            set_flash_message('error', 'Tambahkan minimal satu pegawai bertugas!');
        } else {
            try {
                if ($action == 'add') {
                    $stmt = $pdo->prepare("
                        INSERT INTO surat_tugas (nomor_surat_tugas, berdasarkan_surat, nomor_surat, hari, tanggal_surat, tentang,
                                            tempat_tugas, tanggal_surat_tugas, pejabat_tanda_tangan, status_surat)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Draft')
                    ");
                    $stmt->execute([
                        $nomor_surat_tugas, $berdasarkan_surat, $nomor_surat, $hari, $tanggal_surat, $tentang,
                        $tempat_tugas, $tanggal_surat_tugas, $pejabat_tanda_tangan
                    ]);

                    $surat_tugas_id = $pdo->lastInsertId();

                    // Insert pegawai bertugas
                    foreach ($pegawai_bertugas as $pegawai) {
                        $stmt = $pdo->prepare("
                            INSERT INTO surat_tugas_pegawai (id_surat_tugas, id_pegawai, nip, nama_pegawai,
                                                            jabatan, golongan_ruang, tanggal_mulai_tugas, tanggal_selesai_tugas)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $surat_tugas_id,
                            $pegawai['id_pegawai'],
                            $pegawai['nip'],
                            $pegawai['nama_pegawai'],
                            $pegawai['jabatan'],
                            $pegawai['golongan_ruang'],
                            $pegawai['tanggal_mulai_tugas'],
                            $pegawai['tanggal_selesai_tugas']
                        ]);
                    }

                    log_activity($_SESSION['user_id'], 'add_surat_tugas', "Added surat tugas: $nomor_surat_tugas");
                    set_flash_message('success', 'Surat tugas berhasil ditambahkan!');
                    header('Location: surat_tugas.php');
                    exit();
                } else {
                    // Edit surat tugas
                    $stmt = $pdo->prepare("
                        UPDATE surat_tugas SET nomor_surat_tugas = ?, berdasarkan_surat = ?, nomor_surat = ?, hari = ?, tanggal_surat = ?, tentang = ?,
                                             tempat_tugas = ?, tanggal_surat_tugas = ?, pejabat_tanda_tangan = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $nomor_surat_tugas, $berdasarkan_surat, $nomor_surat, $hari, $tanggal_surat, $tentang,
                        $tempat_tugas, $tanggal_surat_tugas, $pejabat_tanda_tangan,
                        $surat_tugas['id']
                    ]);

                    // Delete existing pegawai bertugas
                    $stmt = $pdo->prepare("DELETE FROM surat_tugas_pegawai WHERE id_surat_tugas = ?");
                    $stmt->execute([$surat_tugas['id']]);

                    // Insert new pegawai bertugas
                    foreach ($pegawai_bertugas as $pegawai) {
                        $stmt = $pdo->prepare("
                            INSERT INTO surat_tugas_pegawai (id_surat_tugas, id_pegawai, nip, nama_pegawai,
                                                            jabatan, golongan_ruang, tanggal_mulai_tugas, tanggal_selesai_tugas)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $surat_tugas['id'],
                            $pegawai['id_pegawai'],
                            $pegawai['nip'],
                            $pegawai['nama_pegawai'],
                            $pegawai['jabatan'],
                            $pegawai['golongan_ruang'],
                            $pegawai['tanggal_mulai_tugas'],
                            $pegawai['tanggal_selesai_tugas']
                        ]);
                    }

                    log_activity($_SESSION['user_id'], 'edit_surat_tugas', "Updated surat tugas: $nomor_surat_tugas");
                    set_flash_message('success', 'Surat tugas berhasil diperbarui!');
                    header('Location: surat_tugas.php');
                    exit();
                }
            } catch (PDOException $e) {
                set_flash_message('error', 'Gagal menyimpan surat tugas: ' . $e->getMessage());
            }
        }
    }
    
    // Get pegawai list for dropdown
    $stmt = $pdo->prepare("SELECT id, nip, nama, jabatan FROM pegawai WHERE status_pegawai = 'aktif' ORDER BY nama");
    $stmt->execute();
    $pegawai_list = $stmt->fetchAll();
    
    // Show add/edit form
    include 'includes/surat_tugas_form.php';
    exit();
}

// Handle view detail
if ($action == 'view' && isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $pdo->prepare("
        SELECT st.*, p.nama as pejabat_nama, p.nip as pejabat_nip, p.jabatan as pejabat_jabatan
        FROM surat_tugas st
        LEFT JOIN pegawai p ON st.pejabat_tanda_tangan = p.id
        WHERE st.id = ?
    ");
    $stmt->execute([$id]);
    $surat_tugas = $stmt->fetch();

    if (!$surat_tugas) {
        set_flash_message('error', 'Data surat tugas tidak ditemukan!');
        header('Location: surat_tugas.php');
        exit();
    }

    include 'includes/surat_tugas_detail.php';
    exit();
}

// Get surat tugas list
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;

$query = "SELECT st.*, p.jabatan FROM surat_tugas st LEFT JOIN pegawai p ON st.pejabat_tanda_tangan = p.id WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (st.nomor_surat LIKE ? OR st.tentang LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param]);
}

if (!empty($status_filter)) {
    $query .= " AND st.status_surat = ?";
    $params[] = $status_filter;
}

$query .= " ORDER BY st.created_at DESC";

$surat_tugas_list = paginate($query, $params, $page, $per_page);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Tugas - <?php echo APP_NAME; ?></title>
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
                            <i class="fas fa-file-alt me-2"></i>Surat Tugas
                        </h1>
                        <p class="mb-0">Kelola surat tugas pegawai</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="surat_tugas.php?action=add" class="btn btn-light">
                            <i class="fas fa-file-plus me-2"></i>Buat Surat Tugas
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
                                   placeholder="Cari nama, NIP, atau jenis tugas..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="status">
                                <option value="">Semua Status</option>
                                <option value="Draft" <?php echo $status_filter == 'Draft' ? 'selected' : ''; ?>>Draft</option>
                                <option value="Diajukan" <?php echo $status_filter == 'Diajukan' ? 'selected' : ''; ?>>Diajukan</option>
                                <option value="Disetujui" <?php echo $status_filter == 'Disetujui' ? 'selected' : ''; ?>>Disetujui</option>
                                <option value="Ditolak" <?php echo $status_filter == 'Ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                                <option value="Selesai" <?php echo $status_filter == 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Cari
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="surat_tugas.php" class="btn btn-secondary w-100">
                                <i class="fas fa-redo me-2"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Surat Tugas List -->
            <div class="content-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>Daftar Surat Tugas</h5>
                    <span class="badge bg-primary"><?php echo $surat_tugas_list['total']; ?> surat</span>
                </div>

                <?php if (empty($surat_tugas_list['records'])): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak ada surat tugas</h5>
                        <p class="text-muted"><i class="fas fa-envelope me-2"></i>Mulai dengan membuat surat tugas baru</p>
                        <a href="surat_tugas.php?action=add" class="btn btn-primary">
                            <i class="fas fa-file-plus me-2"></i>Buat Surat Tugas
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($surat_tugas_list['records'] as $surat): ?>
                        <div class="surat-card">
                            <div class="surat-header">
                                <div>
                                    <div class="surat-title"><?php echo htmlspecialchars($surat['nomor_surat_tugas'] ?? '-'); ?></div>
                                    <div class="surat-meta">
                                        <?php echo htmlspecialchars($surat['tentang'] ?? '-'); ?> |
                                        <?php echo format_date($surat['tanggal_surat_tugas']); ?>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge badge-status badge-<?php echo strtolower($surat['status_surat']); ?>">
                                        <?php echo htmlspecialchars($surat['status_surat']); ?>
                                    </span>
                                    <div class="surat-actions">
                                        <a href="surat_tugas.php?action=view&id=<?php echo $surat['id']; ?>" class="btn btn-sm btn-info btn-action">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($surat['status_surat'] == 'Disetujui'): ?>
                                        <a href="download/download_surat_tugas.php?id=<?php echo $surat['id']; ?>" class="btn btn-sm btn-success btn-action">
                                            <i class="fas fa-file-word"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php if ($surat['status_surat'] == 'Draft'): ?>
                                        <a href="surat_tugas.php?action=submit&id=<?php echo $surat['id']; ?>" class="btn btn-sm btn-primary btn-action" onclick="return confirm('Ajukan surat tugas ini?')">
                                            <i class="fas fa-paper-plane"></i>
                                        </a>
                                        <a href="surat_tugas.php?action=edit&id=<?php echo $surat['id']; ?>" class="btn btn-sm btn-warning btn-action">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger btn-action"
                                                onclick="confirmDeleteSurat(<?php echo $surat['id']; ?>, '<?php echo htmlspecialchars($surat['nomor_surat_tugas'] ?? 'Surat Tugas'); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php if ($surat['status_surat'] == 'Diajukan'): ?>
                                        <a href="surat_tugas.php?action=edit&id=<?php echo $surat['id']; ?>" class="btn btn-sm btn-warning btn-action">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger btn-action"
                                                onclick="confirmDeleteSurat(<?php echo $surat['id']; ?>, '<?php echo htmlspecialchars($surat['nomor_surat_tugas'] ?? 'Surat Tugas'); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php if ($surat['catatan_penolakan']): ?>
                            <div class="alert alert-warning mt-2 mb-0">
                                <small><strong>Catatan Penolakan:</strong> <?php echo htmlspecialchars($surat['catatan_penolakan']); ?></small>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <!-- Pagination -->
                    <?php if ($surat_tugas_list['total_pages'] > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php if ($surat_tugas_list['page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $surat_tugas_list['page'] - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $surat_tugas_list['total_pages']; $i++): ?>
                                    <li class="page-item <?php echo $i == $surat_tugas_list['page'] ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($surat_tugas_list['page'] < $surat_tugas_list['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $surat_tugas_list['page'] + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">
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
