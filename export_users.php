<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once "includes/cek_login.php";
require_once "config/koneksi.php";

if ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Ikutkan filter pencarian yang sedang aktif (kalau ada), sama seperti di kelola_user.php
$keyword = trim($_GET['q'] ?? '');

if ($keyword !== '') {
    $sql = "SELECT id, nama, unit_kerja, email, nomor_hp, role, aktif FROM users 
            WHERE nama LIKE ? OR email LIKE ? OR unit_kerja LIKE ? OR id LIKE ?
            ORDER BY nama ASC";
    $stmt = db_prepare($koneksi, $sql);
    $like = "%{$keyword}%";
    mysqli_stmt_bind_param($stmt, "ssss", $like, $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $result = db_result($stmt);
} else {
    $result = db_query($koneksi, "SELECT id, nama, unit_kerja, email, nomor_hp, role, aktif FROM users ORDER BY nama ASC");
}

$namaFile = "data_user_" . date("Y-m-d") . ".xls";
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$namaFile\"");
header("Pragma: no-cache");
header("Expires: 0");

echo "\xEF\xBB\xBF";
?>
<table border="1">
    <thead>
        <tr style="background:#4361EE; color:#ffffff; font-weight:bold;">
            <th>ID</th>
            <th>Nama</th>
            <th>Unit Kerja</th>
            <th>Email</th>
            <th>Nomor HP</th>
            <th>Role</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['nama']) ?></td>
                <td><?= htmlspecialchars($row['unit_kerja']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['nomor_hp']) ?></td>
                <td><?= htmlspecialchars(str_replace('_', ' ', $row['role'])) ?></td>
                <td><?= $row['aktif'] ? 'Aktif' : 'Nonaktif' ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
