<?php
require_once 'config.php';

// Log activity before destroying session
if (is_logged_in()) {
    log_activity($_SESSION['user_id'], 'logout', 'User logged out');
}

// Destroy session
session_unset();
session_destroy();

// Clear remember cookie
if (isset($_COOKIE['remember_username'])) {
    setcookie('remember_username', '', time() - 3600, '/');
}

// Redirect to login
header('Location: login.php');
exit();
?>
