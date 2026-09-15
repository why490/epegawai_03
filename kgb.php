<?php
require_once 'config.php';
require_login();

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM kgb WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        set_flash_message('success', 'Data KGB berhasil dihapus!');
        header('Location: kgb.php');
        exit();
    } catch (PDOException $e) {
        set_flash_message('error', 'Gagal menghapus data: ' . $e->getMessage());
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    try {
        // Get form data
        $id = $_POST['id'] ?? '';
        $id_pegawai = $_POST['id_pegawai'];
        $nomor_surat = $_POST['nomor_surat'];
        $lampiran = $_POST['lampiran'] ?? '-';
        $tanggal_surat = $_POST['tanggal_surat'];
        $nip = $_POST['nip'];
        $jabatan = $_POST['jabatan'];
        $golongan_saat_ini = $_POST['golongan_saat_ini'];
        
        // Dasar SK Terakhir
        $gaji_pokok_lama = $_POST['gaji_pokok_lama'];
        $oleh_pejabat = $_POST['oleh_pejabat'];
        $nomor_surat_sk = $_POST['nomor_surat_sk'];
        $tanggal_surat_sk = $_POST['tanggal_surat_sk'];
        $tanggal_mulai_berlaku = $_POST['tanggal_mulai_berlaku'];
        $masa_kerja_golongan = $_POST['masa_kerja_golongan'];
        
        // Data Kenaikan Gaji
        $gaji_pokok_baru = $_POST['gaji_pokok_baru'];
        $mulai_tanggal = $_POST['mulai_tanggal'];
        $berdasarkan_masa_kerja = $_POST['berdasarkan_masa_kerja'];
        $kenaikan_gaji_yad = $_POST['kenaikan_gaji_yad'];
        $dalam_golongan = $_POST['dalam_golongan'];
        $pejabat_tanda_tangan = $_POST['pejabat_tanda_tangan'];
        $jabatan_pejabat = $_POST['jabatan_pejabat'];
        
        if ($id) {
            // Update existing record
            $sql = "UPDATE kgb SET 
                id_pegawai = ?, nomor_surat = ?, lampiran = ?, tanggal_surat = ?, nip = ?, jabatan = ?, golongan_saat_ini = ?,
                gaji_pokok_lama = ?, oleh_pejabat = ?, nomor_surat_sk = ?, tanggal_surat_sk = ?, tanggal_mulai_berlaku = ?, masa_kerja_golongan = ?,
                gaji_pokok_baru = ?, mulai_tanggal = ?, berdasarkan_masa_kerja = ?, kenaikan_gaji_yad = ?, dalam_golongan = ?,
                pejabat_tanda_tangan = ?, jabatan_pejabat = ?
            WHERE id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $id_pegawai, $nomor_surat, $lampiran, $tanggal_surat, $nip, $jabatan, $golongan_saat_ini,
                $gaji_pokok_lama, $oleh_pejabat, $nomor_surat_sk, $tanggal_surat_sk, $tanggal_mulai_berlaku, $masa_kerja_golongan,
                $gaji_pokok_baru, $mulai_tanggal, $berdasarkan_masa_kerja, $kenaikan_gaji_yad, $dalam_golongan,
                $pejabat_tanda_tangan, $jabatan_pejabat, $id
            ]);
            
            set_flash_message('success', 'Data KGB berhasil diperbarui!');
        } else {
            // Insert new record
            $sql = "INSERT INTO kgb (
                id_pegawai, nomor_surat, lampiran, tanggal_surat, nip, jabatan, golongan_saat_ini,
                gaji_pokok_lama, oleh_pejabat, nomor_surat_sk, tanggal_surat_sk, tanggal_mulai_berlaku, masa_kerja_golongan,
                gaji_pokok_baru, mulai_tanggal, berdasarkan_masa_kerja, kenaikan_gaji_yad, dalam_golongan,
                pejabat_tanda_tangan, jabatan_pejabat
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $id_pegawai, $nomor_surat, $lampiran, $tanggal_surat, $nip, $jabatan, $golongan_saat_ini,
                $gaji_pokok_lama, $oleh_pejabat, $nomor_surat_sk, $tanggal_surat_sk, $tanggal_mulai_berlaku, $masa_kerja_golongan,
                $gaji_pokok_baru, $mulai_tanggal, $berdasarkan_masa_kerja, $kenaikan_gaji_yad, $dalam_golongan,
                $pejabat_tanda_tangan, $jabatan_pejabat
            ]);
            
            set_flash_message('success', 'Data KGB berhasil disimpan!');
        }
        
        header('Location: kgb.php');
        exit();
    } catch (PDOException $e) {
        set_flash_message('error', 'Gagal menyimpan data: ' . $e->getMessage());
    }
}

// Handle edit action
$kgb = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM kgb WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $kgb = $stmt->fetch();
        
        if (!$kgb) {
            set_flash_message('error', 'Data KGB tidak ditemukan!');
            header('Location: kgb.php');
            exit();
        }
    } catch (PDOException $e) {
        set_flash_message('error', 'Gagal mengambil data: ' . $e->getMessage());
        header('Location: kgb.php');
        exit();
    }
}

// Fetch KGB data list
$kgb_query = "SELECT k.*, p.nama as nama_pegawai FROM kgb k LEFT JOIN pegawai p ON k.id_pegawai = p.id ORDER BY k.created_at DESC";
$kgb_result = $pdo->query($kgb_query);
$kgb_list = [];
if ($kgb_result) {
    while ($row = $kgb_result->fetch()) {
        $kgb_list[] = $row;
    }
}

// Fetch pegawai data for dropdown
$pegawai_query = "SELECT id, nama, nip, jabatan, golongan_ruangan FROM pegawai WHERE status_pegawai = 'aktif' ORDER BY nama ASC";
$pegawai_result = $pdo->query($pegawai_query);
$pegawai_list = [];
if ($pegawai_result) {
    while ($row = $pegawai_result->fetch()) {
        $pegawai_list[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kenaikan Gaji Berkala - <?php echo APP_NAME; ?></title>
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
                            <i class="fas fa-chart-line me-2"></i>Kenaikan Gaji Berkala
                        </h1>
                        <p class="mb-0">Manajemen Kenaikan Gaji Berkala Pegawai</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <?php if (isset($_GET['action']) && ($_GET['action'] === 'add' || $_GET['action'] === 'edit')): ?>
                            <a href="kgb.php" class="btn btn-light">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>
                        <?php else: ?>
                            <a href="kgb.php?action=add" class="btn btn-light">
                                <i class="fas fa-plus me-2"></i>Tambah KGB Baru
                            </a>
                        <?php endif; ?>
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
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Cari nama pegawai, NIP, atau nomor surat..." 
                                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Cari
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="kgb.php" class="btn btn-secondary w-100">
                                <i class="fas fa-redo me-2"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- KGB List -->
            <div class="content-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>Daftar Kenaikan Gaji Berkala</h5>
                    <span class="badge bg-primary"><?php echo count($kgb_list); ?> data</span>
                </div>

                <?php if ((isset($_GET['action']) && $_GET['action'] === 'add') || (isset($_GET['action']) && $_GET['action'] === 'edit')): ?>
                    <!-- Include form file -->
                    <?php include 'includes/kgb_form.php'; ?>
                    <?php exit(); ?>
                <?php else: ?>
                    <!-- KGB List Cards -->
                    <?php if (empty($kgb_list)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-chart-line fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">Belum ada data KGB</h5>
                            <p class="text-muted">Mulai dengan menambahkan data Kenaikan Gaji Berkala</p>
                            <a href="kgb.php?action=add" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Tambah KGB Baru
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($kgb_list as $kgb): ?>
                            <div class="cuti-card">
                                <div class="cuti-header">
                                    <div>
                                        <div class="cuti-title"><?php echo htmlspecialchars($kgb['nomor_surat']); ?></div>
                                        <div class="cuti-meta">
                                            <?php echo htmlspecialchars($kgb['nama_pegawai'] ?? '-'); ?> | <?php echo htmlspecialchars($kgb['nip']); ?> |
                                            <?php echo htmlspecialchars($kgb['gaji_pokok_baru']); ?> | <?php echo format_date($kgb['tanggal_surat']); ?>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="cuti-actions">
                                            <a href="includes/kgb_detail.php?id=<?php echo $kgb['id']; ?>" class="btn btn-sm btn-info btn-action" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="download/download_kgb.php?id=<?php echo $kgb['id']; ?>" class="btn btn-sm btn-success btn-action" title="Download Word">
                                                <i class="fas fa-file-word"></i>
                                            </a>
                                            <a href="kgb.php?action=edit&id=<?php echo $kgb['id']; ?>" class="btn btn-sm btn-warning btn-action" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="kgb.php?action=delete&id=<?php echo $kgb['id']; ?>" class="btn btn-sm btn-danger btn-action" title="Delete" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script src="assets/js/kgb.js"></script>
    <script src="assets/js/live-search.js"></script>
</body>
</html>
