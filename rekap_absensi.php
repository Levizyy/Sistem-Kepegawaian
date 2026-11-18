<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once "includes/cek_login.php";
require_once "config/koneksi.php";

$role = $_SESSION['role'];
$userId = $_SESSION['id'];
$bulan = $_GET['bulan'] ?? date('Y-m');

$bulanLabel = date('F Y', strtotime($bulan . '-01'));

if ($role === 'super_admin' || $role === 'admin') {
    $sql = "SELECT u.nama, 
                   SUM(CASE WHEN a.status = 'hadir' THEN 1 ELSE 0 END) AS hadir,
                   SUM(CASE WHEN a.status = 'terlambat' THEN 1 ELSE 0 END) AS terlambat,
                   SUM(CASE WHEN a.status = 'izin' THEN 1 ELSE 0 END) AS izin,
                   SUM(CASE WHEN a.status = 'sakit' THEN 1 ELSE 0 END) AS sakit,
                   SUM(CASE WHEN a.status = 'alpha' THEN 1 ELSE 0 END) AS alpha,
                   COUNT(a.id) AS total_absen
            FROM users u
            LEFT JOIN attendance a ON a.user_id = u.id AND DATE_FORMAT(a.tanggal, '%Y-%m') = ?
            GROUP BY u.id, u.nama
            ORDER BY u.nama ASC";
    $stmt = db_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, 's', $bulan);
    mysqli_stmt_execute($stmt);
    $result = db_result($stmt);
} else {
    $sql = "SELECT u.nama,
                   SUM(CASE WHEN a.status = 'hadir' THEN 1 ELSE 0 END) AS hadir,
                   SUM(CASE WHEN a.status = 'terlambat' THEN 1 ELSE 0 END) AS terlambat,
                   SUM(CASE WHEN a.status = 'izin' THEN 1 ELSE 0 END) AS izin,
                   SUM(CASE WHEN a.status = 'sakit' THEN 1 ELSE 0 END) AS sakit,
                   SUM(CASE WHEN a.status = 'alpha' THEN 1 ELSE 0 END) AS alpha,
                   COUNT(a.id) AS total_absen
            FROM users u
            LEFT JOIN attendance a ON a.user_id = u.id AND DATE_FORMAT(a.tanggal, '%Y-%m') = ?
            WHERE u.id = ?
            GROUP BY u.id, u.nama";
    $stmt = db_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, 'ss', $bulan, $userId);
    mysqli_stmt_execute($stmt);
    $result = db_result($stmt);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Absensi Bulanan — Sistem Informasi Kepegawaian</title>
    <script>
        (function () {
            var tema = localStorage.getItem('sik_theme') || 'light';
            document.documentElement.setAttribute('data-theme', tema);
        })();
    </script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="app-shell">
        <?php include 'includes/sidebar.php'; ?>
        <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
        <div class="main-area">
            <div class="topbar">
                <button class="hamburger-btn" onclick="toggleSidebar()" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg></button>
                <div class="topbar-title">Rekap Absensi Bulanan</div>
                <div class="topbar-right">
                    <button class="theme-toggle" onclick="toggleTheme()" type="button">
                        <span class="theme-toggle-icon"></span><span class="theme-toggle-label">Gelap</span>
                    </button>
                </div>
            </div>

            <div class="container">
                <a href="dashboard.php" class="back-link">&larr; Kembali ke Dashboard</a>

                <div class="card-box">
                    <div class="card-header">
                        <h2>Rekap Bulan: <?= htmlspecialchars($bulanLabel) ?></h2>
                        <form method="GET" action="rekap_absensi.php" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                            <input type="month" name="bulan" value="<?= htmlspecialchars($bulan) ?>" style="padding:9px 13px; border:1.5px solid var(--border); border-radius:9px; background:var(--surface); color:var(--text); font-size:13px;">
                            <button type="submit" class="btn-primary">Tampilkan</button>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Pegawai</th>
                                    <th>Hadir</th>
                                    <th>Terlambat</th>
                                    <th>Izin</th>
                                    <th>Sakit</th>
                                    <th>Alpha</th>
                                    <th>Total Absen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($result) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['nama']) ?></td>
                                            <td><?= (int)$row['hadir'] ?></td>
                                            <td><?= (int)$row['terlambat'] ?></td>
                                            <td><?= (int)$row['izin'] ?></td>
                                            <td><?= (int)$row['sakit'] ?></td>
                                            <td><?= (int)$row['alpha'] ?></td>
                                            <td><?= (int)$row['total_absen'] ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" style="text-align:center; padding:20px;">Belum ada data absensi untuk bulan ini.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/theme.js"></script>
</body>
</html>
