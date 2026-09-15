<?php
require_once 'config.php';
require_login();

// Handle actions
$action = $_GET['action'] ?? '';

// Handle delete
if ($action == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Check if pegawai has related records
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cuti WHERE id_pegawai = ?");
    $stmt->execute([$id]);
    $cuti_count = $stmt->fetch()['count'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM surat_tugas WHERE id_pegawai = ?");
    $stmt->execute([$id]);
    $surat_count = $stmt->fetch()['count'];
    
    if ($cuti_count > 0 || $surat_count > 0) {
        set_flash_message('error', 'Tidak dapat menghapus pegawai yang memiliki riwayat cuti atau surat tugas!');
    } else {
        try {
            // Delete photo if exists
            $stmt = $pdo->prepare("SELECT foto FROM pegawai WHERE id = ?");
            $stmt->execute([$id]);
            $pegawai = $stmt->fetch();
            
            if ($pegawai && $pegawai['foto']) {
                delete_file($pegawai['foto'], 'assets/images');
            }
            
            $stmt = $pdo->prepare("DELETE FROM pegawai WHERE id = ?");
            $stmt->execute([$id]);
            
            log_activity($_SESSION['user_id'], 'delete_pegawai', "Deleted pegawai ID: $id");
            set_flash_message('success', 'Data pegawai berhasil dihapus!');
        } catch (PDOException $e) {
            set_flash_message('error', 'Gagal menghapus data pegawai: ' . $e->getMessage());
        }
    }
    
    header('Location: data_pegawai.php');
    exit();
}

// Handle view detail
if ($action == 'view' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt = $pdo->prepare("SELECT * FROM pegawai WHERE id = ?");
    $stmt->execute([$id]);
    $pegawai = $stmt->fetch();
    
    if (!$pegawai) {
        set_flash_message('error', 'Data pegawai tidak ditemukan!');
        header('Location: data_pegawai.php');
        exit();
    }
    
    // Show detail view
    include 'includes/pegawai_detail.php';
    exit();
}

// Handle add/edit
if ($action == 'add' || $action == 'edit') {
    $pegawai = null;
    
    if ($action == 'edit' && isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM pegawai WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $pegawai = $stmt->fetch();
       
        if (!$pegawai) {
            set_flash_message('error', 'Data pegawai tidak ditemukan!');
            header('Location: data_pegawai.php');
            exit();
        }
    }
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nip = clean_input($_POST['nip']);
        $nama = clean_input($_POST['nama']);
        $tempat_lahir = clean_input($_POST['tempat_lahir']);
        $tanggal_lahir = $_POST['tanggal_lahir'];
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $agama = clean_input($_POST['agama']);
        $status_kepegawaian = $_POST['status_kepegawaian'];
        $jabatan = clean_input($_POST['jabatan']);
        $unit_kerja = clean_input($_POST['unit_kerja']);
        $pendidikan_terakhir = clean_input($_POST['pendidikan_terakhir']);
        $jurusan = clean_input($_POST['jurusan']);
        $tahun_lulus = $_POST['tahun_lulus'];
        $no_hp = clean_input($_POST['no_hp']);
        $email = clean_input($_POST['email']);
        $alamat = clean_input($_POST['alamat']);
        $status_pegawai = $_POST['status_pegawai'];
        $golongan_ruangan = clean_input($_POST['golongan_ruangan']);
        $tanggal_masuk = $_POST['tanggal_masuk'];
        
        // Validation
        if (empty($nip) || empty($nama) || empty($jenis_kelamin) || empty($status_kepegawaian)) {
            set_flash_message('error', 'NIP, nama, jenis kelamin, dan status kepegawaian harus diisi!');
        } else {

            try {
                if ($action == 'add') {
                    // Check if NIP exists
                    $stmt = $pdo->prepare("SELECT id FROM pegawai WHERE nip = ?");
                    $stmt->execute([$nip]);
                    if ($stmt->fetch()) {
                        set_flash_message('error', 'NIP sudah digunakan!');
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO pegawai (nip, nama, tempat_lahir, tanggal_lahir, jenis_kelamin, agama, status_kepegawaian, golongan_ruangan, jabatan, unit_kerja, pendidikan_terakhir, jurusan, tahun_lulus, no_hp, email, alamat, status_pegawai, tanggal_masuk) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $nip, $nama, $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $agama,
                            $status_kepegawaian, $golongan_ruangan, $jabatan, $unit_kerja, $pendidikan_terakhir, $jurusan,
                            $tahun_lulus, $no_hp, $email, $alamat, $status_pegawai, $tanggal_masuk
                        ]);
                        
                        log_activity($_SESSION['user_id'], 'add_pegawai', "Added pegawai: $nama");
                        set_flash_message('success', 'Data pegawai berhasil ditambahkan!');
                        header('Location: data_pegawai.php');
                        exit();
                    }
                } else {
                    // Edit pegawai
                    $stmt = $pdo->prepare("
                        UPDATE pegawai SET nip = ?, nama = ?, tempat_lahir = ?, tanggal_lahir = ?, 
                                         jenis_kelamin = ?, agama = ?, status_kepegawaian = ?, golongan_ruangan = ?, jabatan = ?, 
                                         unit_kerja = ?, pendidikan_terakhir = ?, jurusan = ?, tahun_lulus = ?, 
                                         no_hp = ?, email = ?, alamat = ?, status_pegawai = ?, tanggal_masuk = ?
                        WHERE id = ?
                    ");

                            $stmt->execute([
                                $nip, $nama, $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $agama,
                                $status_kepegawaian, $golongan_ruangan, $jabatan, $unit_kerja, $pendidikan_terakhir, $jurusan,
                                $tahun_lulus, $no_hp, $email, $alamat, $status_pegawai, $tanggal_masuk, $pegawai['id']
                            ]);

                    
                    log_activity($_SESSION['user_id'], 'edit_pegawai', "Updated pegawai: $nama");
                    set_flash_message('success', 'Data pegawai berhasil diperbarui!');
                    header('Location: data_pegawai.php');
                    exit();
                }
            } catch (PDOException $e) {
                set_flash_message('error', 'Gagal menyimpan data pegawai: ' . $e->getMessage());
            }
        }
    }
    
    // Show add/edit form
    include 'includes/pegawai_form.php';
    exit();
}

// Get pegawai list
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$kepegawaian_filter = $_GET['kepegawaian'] ?? '';
$golongan_filter = $_GET['golongan'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;

$query = "SELECT * FROM pegawai WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (nip LIKE ? OR nama LIKE ? OR jabatan LIKE ? OR unit_kerja LIKE ? OR golongan_ruangan LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param, $search_param]);
}

if (!empty($status_filter)) {
    $query .= " AND status_pegawai = ?";
    $params[] = $status_filter;
}

if (!empty($kepegawaian_filter)) {
    $query .= " AND status_kepegawaian = ?";
    $params[] = $kepegawaian_filter;
}

if (!empty($golongan_filter)) {
    $query .= " AND golongan_ruangan = ?";
    $params[] = $golongan_filter;
}

$query .= " ORDER BY nama ASC";

$pegawai_list = paginate($query, $params, $page, $per_page);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pegawai - <?php echo APP_NAME; ?></title>
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
                            <i class="fas fa-users me-2"></i>Data Pegawai
                        </h1>
                        <p class="mb-0">Kelola data pegawai instansi</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="data_pegawai.php?action=add" class="btn btn-light">
                            <i class="fas fa-user-plus me-2"></i>Tambah Pegawai
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
                                   placeholder="Cari NIP, nama, jabatan, atau unit kerja..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="kepegawaian">
                                <option value="">Semua Status</option>
                                <option value="PNS" <?php echo $kepegawaian_filter == 'PNS' ? 'selected' : ''; ?>>PNS</option>
                                <option value="PPPK" <?php echo $kepegawaian_filter == 'PPPK' ? 'selected' : ''; ?>>PPPK</option>
                            </select>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="status">
                                <option value="">Semua Status</option>
                                <option value="aktif" <?php echo $status_filter == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                <option value="nonaktif" <?php echo $status_filter == 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                                <option value="pensiun" <?php echo $status_filter == 'pensiun' ? 'selected' : ''; ?>>Pensiun</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="golongan">
                                <option value="">Semua Golongan</option>
                                <option value="III/a" <?php echo $golongan_filter == 'III/a' ? 'selected' : ''; ?>>III/a</option>
                                <option value="III/b" <?php echo $golongan_filter == 'III/b' ? 'selected' : ''; ?>>III/b</option>
                                <option value="III/c" <?php echo $golongan_filter == 'III/c' ? 'selected' : ''; ?>>III/c</option>
                                <option value="III/d" <?php echo $golongan_filter == 'III/d' ? 'selected' : ''; ?>>III/d</option>
                                <option value="IV/a" <?php echo $golongan_filter == 'IV/a' ? 'selected' : ''; ?>>IV/a</option>
                                <option value="IV/b" <?php echo $golongan_filter == 'IV/b' ? 'selected' : ''; ?>>IV/b</option>
                                <option value="IV/c" <?php echo $golongan_filter == 'IV/c' ? 'selected' : ''; ?>>IV/c</option>
                                <option value="IV/d" <?php echo $golongan_filter == 'IV/d' ? 'selected' : ''; ?>>IV/d</option>
                                <option value="IV/e" <?php echo $golongan_filter == 'IV/e' ? 'selected' : ''; ?>>IV/e</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Cari
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="data_pegawai.php" class="btn btn-secondary w-100">
                                <i class="fas fa-redo me-2"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Pegawai List -->
            <div class="content-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>Daftar Pegawai</h5>
                    <span class="badge bg-primary"><?php echo $pegawai_list['total']; ?> pegawai</span>
                </div>

                <?php if (empty($pegawai_list['records'])): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak ada data pegawai</h5>
                        <p class="text-muted">Mulai dengan menambah data pegawai</p>
                        <a href="data_pegawai.php?action=add" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>Tambah Pegawai
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($pegawai_list['records'] as $pegawai): ?>
                        <div class="pegawai-card">
                            <div class="row align-items-center">
                                <div class="col-md-1">

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
                                <div class="col-md-5">
                                    <div class="pegawai-info">
                                        <h5><?php echo htmlspecialchars($pegawai['nama']); ?></h5>
                                        <div class="pegawai-meta">
                                            <i class="fas fa-id-badge me-1"></i><?php echo htmlspecialchars($pegawai['nip']); ?><br>
                                            <i class="fas fa-briefcase me-1"></i><?php echo htmlspecialchars($pegawai['jabatan']); ?><br>
                                            <i class="fas fa-building me-1"></i><?php echo htmlspecialchars($pegawai['unit_kerja']); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex flex-column gap-1">
                                        <span class="badge bg-<?php echo $pegawai['status_kepegawaian'] == 'PNS' ? 'success' : 'info'; ?>">
                                            <?php echo htmlspecialchars($pegawai['status_kepegawaian']); ?>
                                        </span>
                                        <span class="badge bg-<?php echo $pegawai['status_pegawai'] == 'aktif' ? 'primary' : 'secondary'; ?>">
                                            <?php echo ucfirst($pegawai['status_pegawai']); ?>
                                        </span>
                                        <?php if ($pegawai['pendidikan_terakhir']): ?>
                                        <span class="badge bg-light text-dark">
                                            <?php echo htmlspecialchars($pegawai['pendidikan_terakhir']); ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-3 text-end">
                                    <div class="btn-group">
                                        <a href="data_pegawai.php?action=edit&id=<?php echo $pegawai['id']; ?>" 
                                           class="btn btn-sm btn-warning btn-action">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-info btn-action" 
                                                onclick="viewDetails(<?php echo $pegawai['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger btn-action" 
                                                onclick="confirmDelete(<?php echo $pegawai['id']; ?>, '<?php echo htmlspecialchars($pegawai['nama']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Pagination -->
                    <?php if ($pegawai_list['total_pages'] > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php if ($pegawai_list['page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $pegawai_list['page'] - 1; ?>&search=<?php echo urlencode($search); ?>&kepegawaian=<?php echo urlencode($kepegawaian_filter); ?>&status=<?php echo urlencode($status_filter); ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $pegawai_list['total_pages']; $i++): ?>
                                    <li class="page-item <?php echo $i == $pegawai_list['page'] ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&kepegawaian=<?php echo urlencode($kepegawaian_filter); ?>&status=<?php echo urlencode($status_filter); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($pegawai_list['page'] < $pegawai_list['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $pegawai_list['page'] + 1; ?>&search=<?php echo urlencode($search); ?>&kepegawaian=<?php echo urlencode($kepegawaian_filter); ?>&status=<?php echo urlencode($status_filter); ?>">
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
