<?php
require_once 'config.php';
require_admin();

// Handle actions
$action = $_GET['action'] ?? '';

// Handle delete
if ($action == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Prevent deleting self
    if ($id == $_SESSION['user_id']) {
        set_flash_message('error', 'Tidak dapat menghapus akun sendiri!');
        header('Location: manage_users.php');
        exit();
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        
        log_activity($_SESSION['user_id'], 'delete_user', "Deleted user ID: $id");
        set_flash_message('success', 'User berhasil dihapus!');
    } catch (PDOException $e) {
        set_flash_message('error', 'Gagal menghapus user: ' . $e->getMessage());
    }
    
    header('Location: manage_users.php');
    exit();
}

// Handle add/edit
if ($action == 'add' || $action == 'edit') {
    $user = null;
    
    if ($action == 'edit' && isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $user = $stmt->fetch();
        
        if (!$user) {
            set_flash_message('error', 'User tidak ditemukan!');
            header('Location: manage_users.php');
            exit();
        }
    }
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = clean_input($_POST['username']);
        $nama_lengkap = clean_input($_POST['nama_lengkap']);
        $email = clean_input($_POST['email']);
        $role = $_POST['role'];
        $status = $_POST['status'];
        $password = $_POST['password'];
        
        // Validation
        if (empty($username) || empty($nama_lengkap) || empty($role)) {
            set_flash_message('error', 'Username, nama lengkap, dan role harus diisi!');
        } else {
            try {
                if ($action == 'add') {
                    // Check if username exists
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                    $stmt->execute([$username]);
                    if ($stmt->fetch()) {
                        set_flash_message('error', 'Username sudah digunakan!');
                    } else {
                        // Password required for new user
                        if (empty($password)) {
                            set_flash_message('error', 'Password harus diisi untuk user baru!');
                        } else {
                            $hashed_password = hash_password($password);
                            $stmt = $pdo->prepare("
                                INSERT INTO users (username, password, nama_lengkap, email, role, status) 
                                VALUES (?, ?, ?, ?, ?, ?)
                            ");
                            $stmt->execute([$username, $hashed_password, $nama_lengkap, $email, $role, $status]);
                            
                            log_activity($_SESSION['user_id'], 'add_user', "Added user: $username");
                            set_flash_message('success', 'User berhasil ditambahkan!');
                            header('Location: manage_users.php');
                            exit();
                        }
                    }
                } else {
                    // Edit user
                    $update_fields = "username = ?, nama_lengkap = ?, email = ?, role = ?, status = ?";
                    $params = [$username, $nama_lengkap, $email, $role, $status];
                    
                    // Update password if provided
                    if (!empty($password)) {
                        $update_fields .= ", password = ?";
                        $hashed_password = hash_password($password);
                        $params[] = $hashed_password;
                    }
                    
                    $update_fields .= " WHERE id = ?";
                    $params[] = $user['id'];
                    
                    $stmt = $pdo->prepare("UPDATE users SET $update_fields");
                    $stmt->execute($params);
                    
                    log_activity($_SESSION['user_id'], 'edit_user', "Updated user: $username");
                    set_flash_message('success', 'User berhasil diperbarui!');
                    header('Location: manage_users.php');
                    exit();
                }
            } catch (PDOException $e) {
                set_flash_message('error', 'Gagal menyimpan user: ' . $e->getMessage());
            }
        }
    }
    
    // Show add/edit form
    include 'includes/user_form.php';
    exit();
}

// Get users list
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;

$query = "SELECT * FROM users WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (username LIKE ? OR nama_lengkap LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
}

if (!empty($role_filter)) {
    $query .= " AND role = ?";
    $params[] = $role_filter;
}

if (!empty($status_filter)) {
    $query .= " AND status = ?";
    $params[] = $status_filter;
}

$query .= " ORDER BY created_at DESC";

$users = paginate($query, $params, $page, $per_page);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h1 class="mb-0">
                            <i class="fas fa-user-cog me-2"></i>Manajemen User
                        </h1>
                        <p class="mb-0">Kelola akun pengguna sistem</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="manage_users.php?action=add" class="btn btn-light">
                            <i class="fas fa-user-plus me-2"></i>Tambah User
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
                                   placeholder="Cari username, nama, atau email..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="role">
                                <option value="">Semua Role</option>
                                <option value="admin" <?php echo $role_filter == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="user" <?php echo $role_filter == 'user' ? 'selected' : ''; ?>>User</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="status">
                                <option value="">Semua Status</option>
                                <option value="aktif" <?php echo $status_filter == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                <option value="nonaktif" <?php echo $status_filter == 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Cari
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="manage_users.php" class="btn btn-secondary w-100">
                                <i class="fas fa-redo me-2"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Users List -->
            <div class="content-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>Daftar User</h5>
                    <span class="badge bg-primary"><?php echo $users['total']; ?> user</span>
                </div>

                <?php if (empty($users['records'])): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak ada user ditemukan</h5>
                        <p class="text-muted">Mulai dengan menambah user baru</p>
                        <a href="manage_users.php?action=add" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>Tambah User
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($users['records'] as $user): ?>
                        <div class="user-card">
                            <div class="row align-items-center">
                                <div class="col-md-1">
                                    <div class="user-avatar">
                                        <?php echo strtoupper(substr($user['nama_lengkap'], 0, 1)); ?>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="user-info">
                                        <h5><?php echo htmlspecialchars($user['nama_lengkap']); ?></h5>
                                        <div class="user-meta">
                                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($user['username']); ?><br>
                                            <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($user['email'] ?: '-'); ?><br>
                                            <i class="fas fa-calendar me-1"></i><?php echo format_date($user['created_at']); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex flex-column gap-1">
                                        <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'primary'; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                        <span class="badge bg-<?php echo $user['status'] == 'aktif' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-3 text-end">
                                    <div class="btn-group">
                                        <a href="manage_users.php?action=edit&id=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-warning btn-action">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <button type="button" class="btn btn-sm btn-danger btn-action" 
                                                    onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['nama_lengkap']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Pagination -->
                    <?php if ($users['total_pages'] > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php if ($users['page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $users['page'] - 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>&status=<?php echo urlencode($status_filter); ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $users['total_pages']; $i++): ?>
                                    <li class="page-item <?php echo $i == $users['page'] ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>&status=<?php echo urlencode($status_filter); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($users['page'] < $users['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $users['page'] + 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>&status=<?php echo urlencode($status_filter); ?>">
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
</body>
</html>
