<?php
// Get current page for active link highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-id-card me-2"></i><?php echo APP_NAME; ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="index.php">
                        <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo in_array($current_page, ['data_pegawai.php', 'kgb.php', 'kgb_detail.php']) ? 'active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-users me-1"></i>Data Pegawai
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="data_pegawai.php">
                            <i class="fas fa-user me-2"></i>Data Pegawai
                        </a></li>
                        <li><a class="dropdown-item" href="kgb.php">
                            <i class="fas fa-chart-line me-2"></i>KGB
                        </a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo in_array($current_page, ['cuti.php', 'calendar.php', 'holidays.php', 'calendar_holidays.php']) ? 'active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-calendar-alt me-1"></i>Cuti
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="cuti.php">
                            <i class="fas fa-calendar-plus me-2"></i>Pengajuan Cuti
                        </a></li>
                        <li><a class="dropdown-item" href="calendar.php">
                            <i class="fas fa-calendar me-2"></i>Kalender Cuti
                        </a></li>
                        <li><a class="dropdown-item" href="calendar_holidays.php">
                            <i class="fas fa-calendar-day me-2"></i>Kalender Hari Libur
                        </a></li>
                        <li><a class="dropdown-item" href="holidays.php">
                            <i class="fas fa-plus-circle me-2"></i>Tambah Hari Libur
                        </a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'surat_tugas.php' ? 'active' : ''; ?>" href="surat_tugas.php">
                        <i class="fas fa-file-alt me-1"></i>Surat Tugas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>" href="reports.php">
                        <i class="fas fa-chart-bar me-1"></i>Laporan
                    </a>
                </li>
                <?php if (is_admin()): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo in_array($current_page, ['manage_users.php', 'backup.php', 'approval_cuti.php']) ? 'active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cog me-1"></i>Pengaturan
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="manage_users.php">
                            <i class="fas fa-user-cog me-2"></i>Manajemen User
                        </a></li>
                        <li><a class="dropdown-item" href="backup.php">
                            <i class="fas fa-database me-2"></i>Backup Database
                        </a></li>
                        <li><a class="dropdown-item" href="approval_cuti.php">
                            <i class="fas fa-check-circle me-2"></i>Approval Cuti
                        </a></li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="profile.php">
                            <i class="fas fa-user me-1"></i>Profile
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
</div>
