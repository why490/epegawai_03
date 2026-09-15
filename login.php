<?php
require_once 'config.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: index.php');
    exit();
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    if (empty($username) || empty($password)) {
        set_flash_message('error', 'Username dan password harus diisi!');
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND status = 'aktif'");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && verify_password($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['last_activity'] = time();
                
                // Remember me functionality
                if ($remember) {
                    setcookie('remember_username', $username, time() + (86400 * 30), '/');
                }
                
                // Log activity
                log_activity($user['id'], 'login', 'User logged in');
                
                // Auto-rotate cuti quotas if admin and needed
                if ($user['role'] === 'admin') {
                    auto_rotate_cuti_quotas();
                }
                
                set_flash_message('success', 'Selamat datang, ' . $user['nama_lengkap'] . '!');
                header('Location: index.php');
                exit();
            } else {
                set_flash_message('error', 'Username atau password salah!');
            }
        } catch (PDOException $e) {
            set_flash_message('error', 'Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }
}

// Get remembered username
$remembered_username = $_COOKIE['remember_username'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-left">
                <div class="login-logo">
                    <i class="fas fa-id-card"></i>
                </div>
                <h2 class="login-title"><?php echo APP_NAME; ?></h2>
                <p class="login-subtitle">Sistem Manajemen Pegawai Terintegrasi</p>
                
                <div class="features">
                    <div class="feature-item">
                        <i class="fas fa-users"></i>
                        Manajemen Data Pegawai
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-calendar-alt"></i>
                        Pengajuan Cuti Online
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-file-alt"></i>
                        Surat Tugas Digital
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-shield-alt"></i>
                        Sistem Keamanan Terjamin
                    </div>
                </div>
            </div>
            
            <div class="login-right">
                <h3 class="mb-4">Masuk ke Akun Anda</h3>
                
                <?php if (isset($_GET['timeout'])): ?>
                    <div class="timeout-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Sesi Anda telah berakhir. Silakan login kembali.
                    </div>
                <?php endif; ?>
                
                <?php if ($message = get_flash_message('error')): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($message = get_flash_message('success')): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="username" name="username" 
                               placeholder="Username" value="<?php echo htmlspecialchars($remembered_username); ?>" required>
                        <label for="username">
                            <i class="fas fa-user me-2"></i>Username
                        </label>
                    </div>
                    
                    <div class="form-floating">
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Password" required>
                        <label for="password">
                            <i class="fas fa-lock me-2"></i>Password
                        </label>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember" 
                               <?php echo $remembered_username ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="remember">
                            Ingat username saya
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-login w-100 mb-3">
                        <i class="fas fa-sign-in-alt me-2"></i>Masuk
                    </button>
                </form>
                
                <div class="text-center">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Default Admin: username <strong>admin</strong>, password <strong>password</strong>
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
