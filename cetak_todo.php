<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once "includes/cek_login.php";
require_once "config/koneksi.php";

$role = $_SESSION['role'];
$userId = $_SESSION['id'];

if ($role === 'super_admin' || $role === 'admin') {
    $sql = "SELECT t.*, u.nama AS nama_pemilik FROM to_do_list t 
            JOIN users u ON t.pemilik = u.id 
            ORDER BY t.tanggal_mulai DESC";
    $result = db_query($koneksi, $sql);
} else {
    $stmt = db_prepare($koneksi, "SELECT t.*, u.nama AS nama_pemilik FROM to_do_list t 
            JOIN users u ON t.pemilik = u.id 
            WHERE t.pemilik = ? ORDER BY t.tanggal_mulai DESC");
    mysqli_stmt_bind_param($stmt, "s", $userId);
    mysqli_stmt_execute($stmt);
    $result = db_result($stmt);
}

function labelPrioritas($p) {
    $map = ['rendah' => 'Rendah', 'sedang' => 'Sedang', 'tinggi' => 'Tinggi'];
    return $map[$p] ?? $p;
}
function labelStatus($s) {
    $map = ['belum_mulai' => 'Belum Mulai', 'proses' => 'Proses', 'selesai' => 'Selesai', 'tertunda' => 'Tertunda'];
    return $map[$s] ?? $s;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan To Do List</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family: Arial, sans-serif; }
        body { padding: 30px; color:#1a1a1a; }
        .no-print { margin-bottom: 20px; }
        .no-print button {
            padding: 10px 20px; background:#4361EE; color:#fff; border:none;
            border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;
        }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .subjudul { font-size: 12px; color:#555; margin-bottom: 20px; }
        table { width:100%; border-collapse: collapse; font-size: 11px; }
        th, td { border: 1px solid #999; padding: 6px 8px; text-align: left; }
        th { background:#eee; }
        .footer-cetak { margin-top: 20px; font-size: 11px; color:#555; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">🖨️ Cetak / Simpan sebagai PDF</button>
    </div>

    <h1>Laporan To Do List</h1>
    <p class="subjudul">
        Dicetak oleh: <?= htmlspecialchars($_SESSION['nama']) ?> (<?= htmlspecialchars(str_replace('_', ' ', $role)) ?>)
        — Tanggal cetak: <?= date("d/m/Y H:i") ?>
    </p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <?php if ($role !== 'user'): ?><th>Pemilik</th><?php endif; ?>
                <th>Tugas</th>
                <th>Prioritas</th>
                <th>Status</th>
                <th>Progres</th>
                <th>Mulai</th>
                <th>Selesai</th>
                <th>Target</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <?php if ($role !== 'user'): ?><td><?= htmlspecialchars($row['nama_pemilik']) ?></td><?php endif; ?>
                    <td><?= htmlspecialchars($row['tugas']) ?></td>
                    <td><?= labelPrioritas($row['prioritas']) ?></td>
                    <td><?= labelStatus($row['status']) ?></td>
                    <td><?= (int)$row['progres'] ?>%</td>
                    <td><?= htmlspecialchars($row['tanggal_mulai']) ?></td>
                    <td><?= $row['tanggal_selesai'] ? htmlspecialchars($row['tanggal_selesai']) : '-' ?></td>
                    <td><?= htmlspecialchars($row['target_pencapaian']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <p class="footer-cetak">Sistem Informasi Kepegawaian — Dokumen ini dihasilkan otomatis dari sistem.</p>
</body>
</html>
