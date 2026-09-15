<?php
/**
 * Reset Password Script
 * Gunakan untuk reset password admin jika lupa
 */
require_once 'config.php';

// Password baru yang diinginkan
$new_password = 'admin123'; // Ganti dengan password baru

// Username admin yang akan di-reset
$username = 'admin';

try {
    // Cek user dan tampilkan password hash saat ini
    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) {
        die("User '$username' tidak ditemukan!");
    }

    echo "User ditemukan:\n";
    echo "Username: " . $user['username'] . "\n";
    echo "Password Hash (saat ini): " . $user['password'] . "\n";
    echo "\nCatatan: Hash tidak bisa didecrypt ke password asli.\n\n";

    // Hash password baru
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashed_password, $user['id']]);

    echo "Password berhasil di-reset!\n";
    echo "Username: $username\n";
    echo "Password Baru: $new_password\n";
    echo "Password Hash (baru): $hashed_password\n";
    echo "\nSilakan hapus file ini setelah selesai untuk keamanan.\n";
    echo "File: " . __FILE__ . "\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
