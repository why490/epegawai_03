<?php
require_once 'config.php';
require_login();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = clean_input($_POST['nama_lengkap']);
    $email = clean_input($_POST['email']);
    $username = clean_input($_POST['username']);
    $password = $_POST['password'] ?? '';
    
    try {
        // Check if username already exists (excluding current user)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            set_flash_message('error', 'Username sudah digunakan oleh user lain!');
        } else {
            // Update user
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ?, email = ?, username = ?, password = ? WHERE id = ?");
                $stmt->execute([$nama_lengkap, $email, $username, $hashed_password, $_SESSION['user_id']]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ?, email = ?, username = ? WHERE id = ?");
                $stmt->execute([$nama_lengkap, $email, $username, $_SESSION['user_id']]);
            }
            
            // Update session
            $_SESSION['nama_lengkap'] = $nama_lengkap;
            $_SESSION['email'] = $email;
            $_SESSION['username'] = $username;
            
            log_activity($_SESSION['user_id'], 'update_profile', 'Updated own profile');
            set_flash_message('success', 'Profile berhasil diperbarui!');
        }
    } catch (PDOException $e) {
        set_flash_message('error', 'Gagal memperbarui profile: ' . $e->getMessage());
    }
    
    header('Location: profile.php');
    exit();
}

// Get user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    set_flash_message('error', 'Gagal mengambil data profile: ' . $e->getMessage());
    header('Location: index.php');
    exit();
}

// Get pegawai data for leave quota
try {
    $stmt = $pdo->prepare("SELECT * FROM pegawai WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $pegawai = $stmt->fetch();
    if ($pegawai) {
        $total_quota = $pegawai['cuti_n'] + $pegawai['cuti_n_minus_1'] + $pegawai['cuti_n_minus_2'];
    } else {
        $total_quota = 12;
        $pegawai = ['cuti_n' => 12, 'cuti_n_minus_1' => 0, 'cuti_n_minus_2' => 0];
    }
} catch (PDOException $e) {
    $total_quota = 12;
    $pegawai = ['cuti_n' => 12, 'cuti_n_minus_1' => 0, 'cuti_n_minus_2' => 0];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo APP_NAME; ?></title>
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
                            <i class="fas fa-user me-2"></i>Profile User
                        </h1>
                        <p class="mb-0">Kelola informasi profile Anda</p>
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

            <!-- Profile Form -->
            <div class="content-card">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-section">
                                <h5>Informasi Profile</h5>
                                
                                <div class="mb-3">
                                    <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                           value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Password Baru (Opsional)</label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Kosongkan jika tidak ingin mengubah password">
                                    <small class="form-text text-muted">Minimal 6 karakter</small>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                                </button>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-section">
                                <h5>Informasi Akun</h5>
                                
                                <div class="mb-3">
                                    <label class="form-label">Role</label>
                                    <div class="form-control-plaintext">
                                        <?php echo htmlspecialchars($user['role']); ?>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Tanggal Dibuat</label>
                                    <div class="form-control-plaintext">
                                        <?php echo format_date($user['created_at']); ?>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Terakhir Diupdate</label>
                                    <div class="form-control-plaintext">
                                        <?php echo format_date($user['updated_at']); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
