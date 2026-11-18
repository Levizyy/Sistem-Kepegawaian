<?php
require_once "includes/cek_login.php";
require_once "config/koneksi.php";

$role = $_SESSION['role'];
$userId = $_SESSION['id'];

// Sama seperti to_do_list.php: user cuma export tugasnya sendiri, admin/super_admin export semua
if ($role === 'super_admin' || $role === 'admin') {
    $sql = "SELECT t.*, u.nama AS nama_pemilik FROM to_do_list t 
            JOIN users u ON t.pemilik = u.id 
            ORDER BY t.tanggal_mulai DESC";
    $stmt = db_prepare($koneksi, $sql);
} else {
    $sql = "SELECT t.*, u.nama AS nama_pemilik FROM to_do_list t 
            JOIN users u ON t.pemilik = u.id 
            WHERE t.pemilik = ? 
            ORDER BY t.tanggal_mulai DESC";
    $stmt = db_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "s", $userId);
}
mysqli_stmt_execute($stmt);
$result = db_result($stmt);

function labelPrioritas($p) {
    $map = ['rendah' => 'Rendah', 'sedang' => 'Sedang', 'tinggi' => 'Tinggi'];
    return $map[$p] ?? $p;
}
function labelStatus($s) {
    $map = ['belum_mulai' => 'Belum Mulai', 'proses' => 'Proses', 'selesai' => 'Selesai', 'tertunda' => 'Tertunda'];
    return $map[$s] ?? $s;
}

// Header supaya browser mengunduh sebagai file Excel
$namaFile = "to_do_list_" . date("Y-m-d") . ".xls";
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$namaFile\"");
header("Pragma: no-cache");
header("Expires: 0");

echo "\xEF\xBB\xBF"; // BOM supaya karakter khusus tampil benar di Excel
?>
<table border="1">
    <thead>
        <tr style="background:#4361EE; color:#ffffff; font-weight:bold;">
            <th>No</th>
            <?php if ($role !== 'user'): ?><th>Pemilik</th><?php endif; ?>
            <th>Tugas</th>
            <th>Prioritas</th>
            <th>Status</th>
            <th>Progres (%)</th>
            <th>Tanggal Mulai</th>
            <th>Tanggal Selesai</th>
            <th>Target Pencapaian</th>
            <th>Rincian Data</th>
            <th>Catatan/Update Progres</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= htmlspecialchars($row['nomor']) ?></td>
                <?php if ($role !== 'user'): ?><td><?= htmlspecialchars($row['nama_pemilik']) ?></td><?php endif; ?>
                <td><?= htmlspecialchars($row['tugas']) ?></td>
                <td><?= labelPrioritas($row['prioritas']) ?></td>
                <td><?= labelStatus($row['status']) ?></td>
                <td><?= (int)$row['progres'] ?></td>
                <td><?= htmlspecialchars($row['tanggal_mulai']) ?></td>
                <td><?= $row['tanggal_selesai'] ? htmlspecialchars($row['tanggal_selesai']) : '-' ?></td>
                <td><?= htmlspecialchars($row['target_pencapaian']) ?></td>
                <td><?= htmlspecialchars($row['rincian_data'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['catatan_progres'] ?? '-') ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
