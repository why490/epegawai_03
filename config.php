<?php
/**
 * Config E-Pegawai
 * Konfigurasi database dan fungsi helper
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'epegawai');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application Configuration
define('APP_NAME', 'E-Pegawai');
define('APP_VERSION', '2.0.0');
define('BASE_URL', 'http://localhost/epegawai_new');
define('UPLOAD_PATH', __DIR__ . '/documents');
define('TEMPLATE_PATH', __DIR__ . '/templates');

// Session Configuration
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_NAME', 'epegawai_session');

// Security Configuration
define('HASH_ALGO', PASSWORD_DEFAULT);
define('HASH_COST', 12);

// Start Session
session_start();

// Database Connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper Functions

/**
 * Clean input data
 */
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Generate secure password hash
 */
function hash_password($password) {
    return password_hash($password, HASH_ALGO, ['cost' => HASH_COST]);
}

/**
 * Verify password
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function is_admin() {
    return is_logged_in() && $_SESSION['user_role'] === 'admin';
}

/**
 * Require login
 */
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Remove gelar (academic/professional titles) from nama
 */
function remove_gelar($nama) {
    // Ambil bagian sebelum koma (nama saja)
    $parts = explode(',', $nama);
    $nama = trim($parts[0]);
    
    return $nama;
}

/**
 * Require admin
 */
function require_admin() {
    if (!is_admin()) {
        $_SESSION['error'] = 'Anda tidak memiliki akses ke halaman ini!';
        header('Location: index.php');
        exit();
    }
}

/**
 * Set flash message
 */
function set_flash_message($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

/**
 * Get flash message
 */
function get_flash_message($type) {
    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    return null;
}

/**
 * Format date
 */
function format_date($date, $format = 'd F Y') {
    if (empty($date)) return '-';
    $date_obj = date_create($date);
    if (!$date_obj) return '-';

    // Indonesian month names
    $month_names = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    $day = date_format($date_obj, 'j');
    $month_num = date_format($date_obj, 'n');
    $year = date_format($date_obj, 'Y');

    $month_name = $month_names[$month_num] ?? $month_num;

    return $day . ' ' . $month_name . ' ' . $year;
}

/**
 * Format currency
 */
function format_currency($amount, $currency = 'Rp') {
    return $currency . ' ' . number_format($amount, 0, ',', '.');
}

/**
 * Generate unique filename
 */
function generate_filename($original_name, $prefix = '') {
    $extension = pathinfo($original_name, PATHINFO_EXTENSION);
    $filename = $prefix . '_' . date('YmdHis') . '_' . uniqid();
    return $filename . '.' . $extension;
}

/**
 * Upload file
 */
function upload_file($file, $target_dir, $allowed_types = ['pdf', 'doc', 'docx'], $max_size = 5242880) {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new Exception('No file uploaded');
    }
    
    // Check file size
    if ($file['size'] > $max_size) {
        throw new Exception('File size too large');
    }
    
    // Check file type
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed_types)) {
        throw new Exception('File type not allowed');
    }
    
    // Create directory if not exists
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    // Generate unique filename
    $filename = generate_filename($file['name']);
    $target_path = $target_dir . '/' . $filename;
    
    // Move file
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return $filename;
    } else {
        throw new Exception('Failed to upload file');
    }
}

/**
 * Delete file
 */
function delete_file($filename, $target_dir) {
    $file_path = $target_dir . '/' . $filename;
    if (file_exists($file_path)) {
        return unlink($file_path);
    }
    return false;
}

/**
 * Get user data
 */
function get_user($user_id = null) {
    global $pdo;
    
    if ($user_id === null && is_logged_in()) {
        $user_id = $_SESSION['user_id'];
    }
    
    if ($user_id) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    }
    
    return null;
}

/**
 * Get pegawai data
 */
function get_pegawai($pegawai_id = null) {
    global $pdo;
    
    if ($pegawai_id) {
        $stmt = $pdo->prepare("SELECT * FROM pegawai WHERE id = ?");
        $stmt->execute([$pegawai_id]);
        return $stmt->fetch();
    }
    
    return null;
}

/**
 * Get dashboard statistics
 */
function get_dashboard_stats() {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM v_dashboard_stats");
    $stmt->execute();
    return $stmt->fetch();
}

/**
 * Calculate working days excluding weekends and holidays
 */
function calculate_working_days($start_date, $end_date) {
    global $pdo;

    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $interval = new DateInterval('P1D');
    $period = new DatePeriod($start, $interval, $end->modify('+1 day'));

    $working_days = 0;
    $holidays = [];

    // Get holidays from database
    try {
        $stmt = $pdo->prepare("SELECT tanggal FROM holidays WHERE YEAR(tanggal) = YEAR(?) OR YEAR(tanggal) = YEAR(?)");
        $stmt->execute([$start_date, $end_date]);
        $holiday_data = $stmt->fetchAll();
        foreach ($holiday_data as $holiday) {
            $holidays[] = $holiday['tanggal'];
        }
    } catch (PDOException $e) {
        // If holidays table doesn't exist, continue without holidays
    }

    foreach ($period as $day) {
        $day_of_week = $day->format('N'); // 1 = Monday, 7 = Sunday
        $date_string = $day->format('Y-m-d');

        // Exclude Saturday (6) and Sunday (7)
        if ($day_of_week < 6) {
            // Exclude holidays
            if (!in_array($date_string, $holidays)) {
                $working_days++;
            }
        }
    }

    return $working_days;
}

/**
 * Log activity
 */
function log_activity($user_id, $action, $description = '') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO activity_log (user_id, action, description, ip_address, user_agent, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $user_id,
            $action,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    } catch (PDOException $e) {
        // Ignore log errors
    }
}

/**
 * Pagination
 */
function paginate($query, $params = [], $page = 1, $per_page = 10) {
    global $pdo;
    
    $offset = ($page - 1) * $per_page;
    
    // Get total records
    $count_query = "SELECT COUNT(*) as total FROM ($query) as count_query";
    $stmt = $pdo->prepare($count_query);
    $stmt->execute($params);
    $total = $stmt->fetch()['total'];
    
    // Get records
    $query .= " LIMIT $per_page OFFSET $offset";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $records = $stmt->fetchAll();
    
    return [
        'records' => $records,
        'total' => $total,
        'page' => $page,
        'per_page' => $per_page,
        'total_pages' => ceil($total / $per_page)
    ];
}

/**
 * Create activity log table if not exists
 */
function create_activity_log_table() {
    global $pdo;
    
    $sql = "
        CREATE TABLE IF NOT EXISTS activity_log (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            action VARCHAR(100) NOT NULL,
            description TEXT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ";
    
    try {
        $pdo->exec($sql);
    } catch (PDOException $e) {
        // Table might already exist
    }
}

// Initialize activity log table
create_activity_log_table();

/**
 * Auto-rotate cuti quotas if needed (for admin only)
 * Checks if current year > last_rotation_year and performs rotation
 */
function auto_rotate_cuti_quotas() {
    global $pdo;
    
    if (!isset($_SESSION['user_id']) || !is_admin()) {
        return false;
    }
    
    $current_year = date('Y');
    
    try {
        // Check if any pegawai needs rotation
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM pegawai WHERE last_rotation_year < ?");
        $stmt->execute([$current_year]);
        $needs_rotation = $stmt->fetch()['count'];
        
        if ($needs_rotation > 0) {
            // Get all pegawai that need rotation
            $stmt = $pdo->prepare("SELECT * FROM pegawai WHERE last_rotation_year < ?");
            $stmt->execute([$current_year]);
            $pegawai_list = $stmt->fetchAll();
            
            $count = 0;
            foreach ($pegawai_list as $pegawai) {
                // Logic rotation:
                // N-3 → Hangus (set to 0)
                // N-2 = min(N-1, 6)
                // N-1 = min(N, 6)
                // N = 12
                
                $old_n = $pegawai['cuti_n'];
                $old_n_minus_1 = $pegawai['cuti_n_minus_1'];
                $old_n_minus_2 = $pegawai['cuti_n_minus_2'];
                
                $new_n_minus_2 = min($old_n_minus_1, 6); // N-1 → N-2 (max 6)
                $new_n_minus_1 = min($old_n, 6); // N → N-1 (max 6)
                $new_n = 12; // N baru = 12 hari
                
                $update_stmt = $pdo->prepare("UPDATE pegawai SET 
                    cuti_n_minus_2 = ?, 
                    cuti_n_minus_1 = ?, 
                    cuti_n = ?, 
                    last_rotation_year = ? 
                    WHERE id = ?");
                
                $update_stmt->execute([$new_n_minus_2, $new_n_minus_1, $new_n, $current_year, $pegawai['id']]);
                
                $count++;
            }
            
            // Log activity
            log_activity($_SESSION['user_id'], 'auto_rotate_cuti', "Auto-rotated cuti quotas for $count pegawai for year $current_year");
            
            // Set flash message for admin
            set_flash_message('success', "Rotasi kuota cuti tahun $current_year berhasil dilakukan untuk $count pegawai!");
            
            return true;
        }
    } catch (PDOException $e) {
        // Log error but don't break the app
        error_log("Auto-rotate cuti error: " . $e->getMessage());
    }
    
    return false;
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Set default charset
header('Content-Type: text/html; charset=utf-8');

// Auto logout after inactivity
if (is_logged_in()) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
        session_unset();
        session_destroy();
        header('Location: login.php?timeout=1');
        exit();
    }
    $_SESSION['last_activity'] = time();
}
?>
