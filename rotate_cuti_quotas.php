<?php
require_once 'config.php';
require_login();

// Check if user is admin
if (!is_admin()) {
    die("Access denied. Admin only.");
}

$current_year = date('Y');

echo "<h2>Rotasi Kuota Cuti Tahunan</h2>";
echo "<p>Tahun berjalan: $current_year</p>";

try {
    // Get all pegawai that need rotation
    $stmt = $pdo->prepare("SELECT * FROM pegawai WHERE last_rotation_year < ?");
    $stmt->execute([$current_year]);
    $pegawai_list = $stmt->fetchAll();
    
    if (empty($pegawai_list)) {
        echo "<p style='color: orange;'>Semua pegawai sudah di-rotasi untuk tahun $current_year</p>";
    } else {
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
            
            echo "<p style='color: green;'>✓ {$pegawai['nama']}: ";
            echo "N($old_n)→N-1($new_n_minus_1), N-1($old_n_minus_1)→N-2($new_n_minus_2), N baru=$new_n</p>";
            
            $count++;
        }
        
        echo "<h3 style='color: green;'>Berhasil merotasi $count pegawai</h3>";
    }
    
    // Show current status
    echo "<hr><h3>Status Kuota Cuti Saat Ini:</h3>";
    $stmt = $pdo->query("SELECT nama, cuti_n, cuti_n_minus_1, cuti_n_minus_2, last_rotation_year FROM pegawai ORDER BY nama");
    $pegawai_status = $stmt->fetchAll();
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>Nama</th><th>N (Tahun Ini)</th><th>N-1 (Tahun Lalu)</th><th>N-2 (2 Tahun Lalu)</th><th>Total</th><th>Last Rotation</th></tr>";
    
    foreach ($pegawai_status as $p) {
        $total = $p['cuti_n'] + $p['cuti_n_minus_1'] + $p['cuti_n_minus_2'];
        $rotation_status = $p['last_rotation_year'] == $current_year ? '<span style="color: green;">✓</span>' : '<span style="color: red;">✗</span>';
        echo "<tr>";
        echo "<td>{$p['nama']}</td>";
        echo "<td>{$p['cuti_n']}</td>";
        echo "<td>{$p['cuti_n_minus_1']}</td>";
        echo "<td>{$p['cuti_n_minus_2']}</td>";
        echo "<td><strong>$total</strong></td>";
        echo "<td>$rotation_status {$p['last_rotation_year']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr><p><a href='index.php'>Kembali ke Dashboard</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
