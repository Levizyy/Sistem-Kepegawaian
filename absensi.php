<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
date_default_timezone_set('Asia/Jakarta');

require_once "includes/cek_login.php";
require_once "config/koneksi.php";

$role = $_SESSION['role'];
$userId = $_SESSION['id'];
$nama = $_SESSION['nama'];
$today = date('Y-m-d');
$currentTime = date('H:i:s');

$pesan = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'check_in') {
        $cek = db_prepare($koneksi, "SELECT id, jam_masuk, jam_pulang FROM attendance WHERE user_id = ? AND tanggal = ? LIMIT 1");
        mysqli_stmt_bind_param($cek, 'ss', $userId, $today);
        mysqli_stmt_execute($cek);
        $record = db_fetch(db_result($cek));

        if ($record) {
            $pesan = 'Anda sudah check-in hari ini.';
        } else {
            $status = (strtotime($currentTime) > strtotime('09:00:00')) ? 'terlambat' : 'hadir';
            $stmt = db_prepare($koneksi, "INSERT INTO attendance (user_id, tanggal, jam_masuk, status, keterangan) VALUES (?, ?, ?, ?, ?)");
            $keterangan = 'Check-in via web absensi';
            mysqli_stmt_bind_param($stmt, 'sssss', $userId, $today, $currentTime, $status, $keterangan);
            mysqli_stmt_execute($stmt);
            $pesan = 'Check-in berhasil dicatat.';
        }
    }

    if ($aksi === 'check_out') {
        $stmt = db_prepare($koneksi, "UPDATE attendance SET jam_pulang = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ? AND tanggal = ? AND jam_pulang IS NULL");
        mysqli_stmt_bind_param($stmt, 'sss', $currentTime, $userId, $today);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) > 0) {
            $pesan = 'Check-out berhasil dicatat.';
        } else {
            $pesan = 'Tidak ada data check-in hari ini yang bisa diakhiri.';
        }
    }

    header("Location: absensi.php?pesan=" . urlencode($pesan));
    exit();
}

$pesan = $_GET['pesan'] ?? '';

$stmtToday = db_prepare($koneksi, "SELECT * FROM attendance WHERE user_id = ? AND tanggal = ? LIMIT 1");
mysqli_stmt_bind_param($stmtToday, 'ss', $userId, $today);
mysqli_stmt_execute($stmtToday);
$todayData = db_fetch(db_result($stmtToday));

if ($role === 'super_admin' || $role === 'admin') {
    $sql = "SELECT a.*, u.nama AS nama_user FROM attendance a JOIN users u ON a.user_id = u.id ORDER BY a.tanggal DESC, a.id DESC LIMIT 20";
    $stmtList = db_prepare($koneksi, $sql);
} else {
    $sql = "SELECT a.*, u.nama AS nama_user FROM attendance a JOIN users u ON a.user_id = u.id WHERE a.user_id = ? ORDER BY a.tanggal DESC, a.id DESC LIMIT 20";
    $stmtList = db_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmtList, 's', $userId);
}

mysqli_stmt_execute($stmtList);
$attendanceResult = db_result($stmtList);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi — Sistem Informasi Kepegawaian</title>
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
                <div class="topbar-title">Absensi</div>
                <div class="topbar-right">
                    <button class="theme-toggle" onclick="toggleTheme()" type="button">
                        <span class="theme-toggle-icon"></span><span class="theme-toggle-label">Gelap</span>
                    </button>
                </div>
            </div>

            <div class="container">
                <a href="dashboard.php" class="back-link">&larr; Kembali ke Dashboard</a>

                <?php if ($pesan): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($pesan) ?></div>
                <?php endif; ?>

                <div class="card-box">
                    <div class="card-header">
                        <h2>Absensi Hari Ini</h2>
                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                            <form method="POST" action="absensi.php">
                                <input type="hidden" name="aksi" value="check_in">
                                <button type="submit" class="btn-primary">Check In</button>
                            </form>
                            <form method="POST" action="absensi.php">
                                <input type="hidden" name="aksi" value="check_out">
                                <button type="submit" class="btn-primary" style="background:var(--warning);">Check Out</button>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Status</th>
                                <th>Keterangan</th>
                            </tr>
                            <tr>
                                <td><?= htmlspecialchars($today) ?></td>
                                <td><?= htmlspecialchars($todayData['jam_masuk'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($todayData['jam_pulang'] ?? '-') ?></td>
                                <td>
                                    <?php if (!empty($todayData['status'])): ?>
                                        <span class="badge badge-status-<?= htmlspecialchars($todayData['status']) ?>"><?= htmlspecialchars($todayData['status']) ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-status-belum_mulai">Belum Check In</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($todayData['keterangan'] ?? '-') ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card-box" style="margin-top:20px;">
                    <div class="card-header">
                        <h2>Rekap Absensi Terbaru</h2>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <?php if ($role === 'super_admin' || $role === 'admin'): ?><th>Pegawai</th><?php endif; ?>
                                    <th>Tanggal</th>
                                    <th>Jam Masuk</th>
                                    <th>Jam Pulang</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($attendanceResult) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($attendanceResult)): ?>
                                        <tr>
                                            <?php if ($role === 'super_admin' || $role === 'admin'): ?><td><?= htmlspecialchars($row['nama_user']) ?></td><?php endif; ?>
                                            <td><?= htmlspecialchars($row['tanggal']) ?></td>
                                            <td><?= htmlspecialchars($row['jam_masuk'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($row['jam_pulang'] ?? '-') ?></td>
                                            <td><span class="badge badge-status-<?= htmlspecialchars($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" style="text-align:center; padding:20px;">Belum ada data absensi.</td></tr>
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
