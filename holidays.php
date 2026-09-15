<?php
require_once 'config.php';
require_login();

// Handle API request to fetch holidays (must be first)
if (isset($_GET['api']) && $_GET['api'] === 'holidays') {
    header('Content-Type: application/json');
    $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

    try {
        $stmt = $pdo->prepare("SELECT tanggal FROM holidays WHERE tahun = ? ORDER BY tanggal");
        $stmt->execute([$year]);
        $holidays = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode($holidays);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit();
}

// Handle actions
$action = $_GET['action'] ?? '';

// Handle delete
if ($action == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM holidays WHERE id = ?");
        $stmt->execute([$id]);

        log_activity($_SESSION['user_id'], 'delete_holiday', "Deleted holiday ID: $id");
        set_flash_message('success', 'Hari libur berhasil dihapus!');
    } catch (PDOException $e) {
        set_flash_message('error', 'Gagal menghapus hari libur: ' . $e->getMessage());
    }

    // Preserve query parameters
    $search = $_GET['search'] ?? '';
    $jenis = $_GET['jenis'] ?? '';
    $page = $_GET['page'] ?? '1';

    $redirect_url = 'holidays.php';
    $params = [];
    if ($search) $params[] = 'search=' . urlencode($search);
    if ($jenis) $params[] = 'jenis=' . urlencode($jenis);
    if ($page != '1') $params[] = 'page=' . $page;

    if (!empty($params)) {
        $redirect_url .= '?' . implode('&', $params);
    }

    header('Location: ' . $redirect_url);
    exit();
}

// Handle add/edit
if ($action == 'add' || $action == 'edit') {
    // Handle form submission (AJAX)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        $tanggal = $_POST['tanggal'];
        $nama_libur = clean_input($_POST['nama_libur']);
        $jenis_libur = $_POST['jenis_libur'];
        $keterangan = clean_input($_POST['keterangan']);
        $tahun = intval($_POST['tahun']);

        try {
            if ($action == 'add') {
                // Check if date already exists
                $check_stmt = $pdo->prepare("SELECT id FROM holidays WHERE tanggal = ?");
                $check_stmt->execute([$tanggal]);
                if ($check_stmt->fetch()) {
                    echo json_encode(['success' => false, 'message' => 'Tanggal ' . format_date($tanggal) . ' sudah ada dalam database!']);
                    exit();
                }

                $stmt = $pdo->prepare("
                    INSERT INTO holidays (tanggal, nama_libur, jenis_libur, keterangan, tahun)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$tanggal, $nama_libur, $jenis_libur, $keterangan, $tahun]);
                
                log_activity($_SESSION['user_id'], 'add_holiday', "Added holiday: $nama_libur");
                echo json_encode(['success' => true, 'message' => 'Hari libur berhasil ditambahkan!']);
            } else {
                $id = $_GET['id'];
                // Check if new date already exists (excluding current record)
                $check_stmt = $pdo->prepare("SELECT id FROM holidays WHERE tanggal = ? AND id != ?");
                $check_stmt->execute([$tanggal, $id]);
                if ($check_stmt->fetch()) {
                    echo json_encode(['success' => false, 'message' => 'Tanggal ' . format_date($tanggal) . ' sudah ada dalam database!']);
                    exit();
                }

                $stmt = $pdo->prepare("
                    UPDATE holidays SET tanggal = ?, nama_libur = ?, jenis_libur = ?, keterangan = ?, tahun = ?
                    WHERE id = ?
                ");
                $stmt->execute([$tanggal, $nama_libur, $jenis_libur, $keterangan, $tahun, $id]);
                
                log_activity($_SESSION['user_id'], 'edit_holiday', "Updated holiday: $nama_libur");
                echo json_encode(['success' => true, 'message' => 'Hari libur berhasil diperbarui!']);
            }
            exit();
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan hari libur: ' . $e->getMessage()]);
            exit();
        }
    }
    
    // For GET requests, just return error (we use modal now)
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Gunakan modal untuk menambah/edit hari libur']);
    exit();
}

// Get holidays list
$search = $_GET['search'] ?? '';
$jenis_filter = $_GET['jenis'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;

$query = "SELECT * FROM holidays WHERE 1=1";
$params = [];

// Parse search for date patterns (e.g., "31 desember", "31 des 2024", "31-12-2024", "desember")
$date_filter = null;
$month_filter = null;
if (!empty($search)) {
    // Try to match Indonesian date patterns
    $date_patterns = [
        '/(\d{1,2})\s+(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)/i',
        '/(\d{1,2})\s+(jan|feb|mar|apr|mei|jun|jul|agu|sep|okt|nov|des)/i',
        '/(\d{1,2})[\-\/](\d{1,2})[\-\/](\d{4})/', // DD-MM-YYYY or DD/MM/YYYY
        '/(\d{4})[\-\/](\d{1,2})[\-\/](\d{1,2})/', // YYYY-MM-DD or YYYY/MM/DD
        '/^(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)$/i', // Month only
        '/^(jan|feb|mar|apr|mei|jun|jul|agu|sep|okt|nov|des)$/i', // Month abbreviation only
    ];
    
    // Convert Indonesian month names to numbers
    $month_map = [
        'januari' => 1, 'jan' => 1,
        'februari' => 2, 'feb' => 2,
        'maret' => 3, 'mar' => 3,
        'april' => 4, 'apr' => 4,
        'mei' => 5,
        'juni' => 6, 'jun' => 6,
        'juli' => 7, 'jul' => 7,
        'agustus' => 8, 'agu' => 8,
        'september' => 9, 'sep' => 9,
        'oktober' => 10, 'okt' => 10,
        'november' => 11, 'nov' => 11,
        'desember' => 12, 'des' => 12
    ];
    
    foreach ($date_patterns as $pattern) {
        if (preg_match($pattern, $search, $matches)) {
            if (preg_match('/(\d{1,2})\s+(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)/i', $search, $date_matches)) {
                $day = $date_matches[1];
                $month = $month_map[strtolower($date_matches[2])];
                $year = date('Y'); // Default to current year
                $date_filter = sprintf('%04d-%02d-%02d', $year, $month, $day);
            } elseif (preg_match('/(\d{1,2})[\-\/](\d{1,2})[\-\/](\d{4})/', $search, $date_matches)) {
                $date_filter = sprintf('%04d-%02d-%02d', $date_matches[3], $date_matches[2], $date_matches[1]);
            } elseif (preg_match('/(\d{4})[\-\/](\d{1,2})[\-\/](\d{1,2})/', $search, $date_matches)) {
                $date_filter = sprintf('%04d-%02d-%02d', $date_matches[1], $date_matches[2], $date_matches[3]);
            } elseif (preg_match('/^(januari|februari|maret|april|mei|juni|juli|agustus|september|oktober|november|desember)$/i', $search, $month_matches)) {
                $month_filter = $month_map[strtolower($month_matches[1])];
            } elseif (preg_match('/^(jan|feb|mar|apr|mei|jun|jul|agu|sep|okt|nov|des)$/i', $search, $month_matches)) {
                $month_filter = $month_map[strtolower($month_matches[1])];
            }
            break;
        }
    }
}

if (!empty($date_filter)) {
    $query .= " AND tanggal = ?";
    $params[] = $date_filter;
} elseif (!empty($month_filter)) {
    $query .= " AND MONTH(tanggal) = ?";
    $params[] = $month_filter;
} elseif (!empty($search)) {
    $query .= " AND (nama_libur LIKE ? OR keterangan LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param]);
}

if (!empty($jenis_filter)) {
    $query .= " AND jenis_libur = ?";
    $params[] = $jenis_filter;
}

$query .= " ORDER BY tanggal DESC";

$holidays = paginate($query, $params, $page, $per_page);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Hari Libur - <?php echo APP_NAME; ?></title>
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
                            <i class="fas fa-calendar-day me-2"></i>Manajemen Hari Libur
                        </h1>
                        <p class="mb-0">Kelola hari libur nasional dan daerah</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#holidayModal" onclick="resetHolidayForm()">
                            <i class="fas fa-plus me-2"></i>Tambah Hari Libur
                        </button>
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
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Cari nama libur atau tanggal (cth: 31 desember)..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="jenis">
                                <option value="">Semua Jenis</option>
                                <option value="Nasional" <?php echo $jenis_filter == 'Nasional' ? 'selected' : ''; ?>>Nasional</option>
                                <option value="Daerah" <?php echo $jenis_filter == 'Daerah' ? 'selected' : ''; ?>>Daerah</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Cari
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="holidays.php" class="btn btn-secondary w-100">
                                <i class="fas fa-redo me-2"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Holidays List -->
            <div class="content-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>Daftar Hari Libur</h5>
                    <span class="badge bg-primary"><?php echo $holidays['total']; ?> hari libur</span>
                </div>

                <?php if (empty($holidays['records'])): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak ada hari libur</h5>
                        <p class="text-muted">Mulai dengan menambahkan hari libur</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#holidayModal" onclick="resetHolidayForm()">
                            <i class="fas fa-plus me-2"></i>Tambah Hari Libur
                        </button>
                    </div>
                <?php else: ?>
                    <?php foreach ($holidays['records'] as $holiday): ?>
                        <div class="holiday-card">
                            <div class="holiday-header">
                                <div>
                                    <div class="holiday-title"><?php echo htmlspecialchars($holiday['nama_libur']); ?></div>
                                    <div class="holiday-meta">
                                        <?php echo format_date($holiday['tanggal']); ?> | 
                                        <?php echo htmlspecialchars($holiday['jenis_libur']); ?> | 
                                        Tahun <?php echo $holiday['tahun']; ?>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge badge-status badge-<?php echo strtolower($holiday['jenis_libur']); ?>">
                                        <?php echo htmlspecialchars($holiday['jenis_libur']); ?>
                                    </span>
                                    <div class="holiday-actions">
                                        <button type="button" class="btn btn-sm btn-warning btn-action" onclick="editHoliday(<?php echo $holiday['id']; ?>, '<?php echo htmlspecialchars($holiday['tanggal'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($holiday['nama_libur'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($holiday['jenis_libur'], ENT_QUOTES); ?>', <?php echo $holiday['tahun']; ?>, '<?php echo htmlspecialchars($holiday['keterangan'], ENT_QUOTES); ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger btn-action"
                                                onclick="confirmDeleteHoliday(<?php echo $holiday['id']; ?>, '<?php echo htmlspecialchars($holiday['nama_libur']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php if ($holiday['keterangan']): ?>
                            <div class="alert alert-info mt-2 mb-0">
                                <small><strong>Keterangan:</strong> <?php echo htmlspecialchars($holiday['keterangan']); ?></small>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <!-- Pagination -->
                    <?php if ($holidays['total_pages'] > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php if ($holidays['page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $holidays['page'] - 1; ?>&search=<?php echo urlencode($search); ?>&jenis=<?php echo urlencode($jenis_filter); ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $holidays['total_pages']; $i++): ?>
                                    <li class="page-item <?php echo $i == $holidays['page'] ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&jenis=<?php echo urlencode($jenis_filter); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($holidays['page'] < $holidays['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $holidays['page'] + 1; ?>&search=<?php echo urlencode($search); ?>&jenis=<?php echo urlencode($jenis_filter); ?>">
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

    <!-- Holiday Modal -->
    <div class="modal fade" id="holidayModal" tabindex="-1" aria-labelledby="holidayModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15); border-radius: 16px;">
                <div class="modal-header" style="border: none; padding: 24px 24px 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px 16px 0 0;">
                    <h5 class="modal-title" id="holidayModalLabel" style="color: white; font-weight: 600; margin: 0;">
                        <i class="fas fa-calendar-plus me-2"></i>Tambah Hari Libur
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
                </div>
                <div class="modal-body" style="padding: 20px;">
                    <form id="holidayForm">
                        <input type="hidden" id="holiday_id" name="id">
                        <div class="mb-3">
                            <label for="modal_tanggal" class="form-label" style="font-weight: 500; font-size: 0.9rem; color: #495057;">
                                <i class="fas fa-calendar me-2 text-primary"></i>Tanggal
                            </label>
                            <input type="date" class="form-control" id="modal_tanggal" name="tanggal" required
                                   style="border-radius: 8px; border: 2px solid #e9ecef; padding: 6px 12px; font-size: 0.9rem;">
                        </div>
                        <div class="mb-3">
                            <label for="modal_nama_libur" class="form-label" style="font-weight: 500; font-size: 0.9rem; color: #495057;">
                                <i class="fas fa-tag me-2 text-primary"></i>Nama Libur
                            </label>
                            <input type="text" class="form-control" id="modal_nama_libur" name="nama_libur" required placeholder="Contoh: Hari Kemerdekaan"
                                   style="border-radius: 8px; border: 2px solid #e9ecef; padding: 6px 12px; font-size: 0.9rem;">
                        </div>
                        <div class="mb-3">
                            <label for="modal_jenis_libur" class="form-label" style="font-weight: 500; font-size: 0.9rem; color: #495057;">
                                <i class="fas fa-layer-group me-2 text-primary"></i>Jenis Libur
                            </label>
                            <select class="form-select" id="modal_jenis_libur" name="jenis_libur" required
                                    style="border-radius: 8px; border: 2px solid #e9ecef; padding: 6px 12px; font-size: 0.9rem;">
                                <option value="">Pilih Jenis Libur</option>
                                <option value="Nasional">Nasional</option>
                                <option value="Daerah">Daerah</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="modal_tahun" class="form-label" style="font-weight: 500; font-size: 0.9rem; color: #495057;">
                                <i class="fas fa-clock me-2 text-primary"></i>Tahun
                            </label>
                            <input type="number" class="form-control" id="modal_tahun" name="tahun" required
                                   style="border-radius: 8px; border: 2px solid #e9ecef; padding: 6px 12px; font-size: 0.9rem;">
                        </div>
                        <div class="mb-3">
                            <label for="modal_keterangan" class="form-label" style="font-weight: 500; font-size: 0.9rem; color: #495057;">
                                <i class="fas fa-info-circle me-2 text-primary"></i>Keterangan
                            </label>
                            <textarea class="form-control" id="modal_keterangan" name="keterangan" rows="2" placeholder="Opsional..."
                                      style="border-radius: 8px; border: 2px solid #e9ecef; padding: 6px 12px; font-size: 0.9rem; resize: none;"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="border: none; padding: 0 20px 20px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            style="border-radius: 8px; padding: 6px 16px; font-weight: 500; font-size: 0.9rem;">
                        <i class="fas fa-times me-2"></i>Batal
                    </button>
                    <button type="button" class="btn btn-primary" onclick="saveHoliday()"
                            style="border-radius: 8px; padding: 6px 16px; font-weight: 500; font-size: 0.9rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                        <i class="fas fa-save me-2"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
    #holidayModal .form-control:focus, #holidayModal .form-select:focus {
        border-color: #667eea !important;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15) !important;
    }
    #holidayModal .form-control, #holidayModal .form-select {
        transition: all 0.3s ease;
    }
    #holidayModal .form-control:hover, #holidayModal .form-select:hover {
        border-color: #667eea;
    }
    </style>

    <script>
    function confirmDeleteHoliday(id, name) {
        if (confirm('Apakah Anda yakin ingin menghapus hari libur "' + name + '"?')) {
            const urlParams = new URLSearchParams(window.location.search);
            const search = urlParams.get('search') || '';
            const jenis = urlParams.get('jenis') || '';
            const page = urlParams.get('page') || '1';

            let deleteUrl = 'holidays.php?action=delete&id=' + id;
            const params = [];
            if (search) params.push('search=' + encodeURIComponent(search));
            if (jenis) params.push('jenis=' + encodeURIComponent(jenis));
            if (page !== '1') params.push('page=' + page);

            if (params.length > 0) {
                deleteUrl += '&' + params.join('&');
            }

            window.location.href = deleteUrl;
        }
    }

    function resetHolidayForm() {
        document.getElementById('holidayForm').reset();
        document.getElementById('holiday_id').value = '';
        document.getElementById('modal_tahun').value = new Date().getFullYear();
        document.getElementById('holidayModalLabel').textContent = 'Tambah Hari Libur';
    }

    function editHoliday(id, tanggal, nama_libur, jenis_libur, tahun, keterangan) {
        document.getElementById('holiday_id').value = id;
        document.getElementById('modal_tanggal').value = tanggal;
        document.getElementById('modal_nama_libur').value = nama_libur;
        document.getElementById('modal_jenis_libur').value = jenis_libur;
        document.getElementById('modal_tahun').value = tahun;
        document.getElementById('modal_keterangan').value = keterangan;
        document.getElementById('holidayModalLabel').textContent = 'Edit Hari Libur';
        new bootstrap.Modal(document.getElementById('holidayModal')).show();
    }

    function saveHoliday() {
        const form = document.getElementById('holidayForm');
        const id = document.getElementById('holiday_id').value;
        const formData = new FormData(form);
        const url = id ? 'holidays.php?action=edit&id=' + id : 'holidays.php?action=add';

        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('holidayModal')).hide();
                window.location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            alert('Gagal menyimpan data: ' + error);
        });
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script src="assets/js/live-search.js"></script>
</body>
</html>
