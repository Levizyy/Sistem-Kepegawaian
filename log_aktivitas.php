<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once "includes/cek_login.php";
require_once "config/koneksi.php";

if ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

$sql = "SELECT l.*, u.nama AS nama_user, u.role AS role_user 
        FROM log_aktivitas l 
        JOIN users u ON l.user_id = u.id 
        ORDER BY l.waktu DESC";
$result = db_query($koneksi, $sql);

function labelAksi($a) {
    $map = [
        'login' => 'Login',
        'logout' => 'Logout',
        'tambah_user' => 'Tambah User',
        'edit_user' => 'Edit User',
        'hapus_user' => 'Hapus User',
        'tambah_tugas' => 'Tambah Tugas',
        'edit_tugas' => 'Edit Tugas',
        'hapus_tugas' => 'Hapus Tugas'
    ];
    return $map[$a] ?? $a;
}
function warnaAksi($a) {
    $map = [
        'login' => 'badge-aktif',
        'logout' => 'badge-nonaktif',
        'tambah_user' => 'badge-aktif',
        'edit_user' => 'badge-prioritas-sedang',
        'hapus_user' => 'badge-nonaktif',
        'tambah_tugas' => 'badge-aktif',
        'edit_tugas' => 'badge-prioritas-sedang',
        'hapus_tugas' => 'badge-nonaktif'
    ];
    return $map[$a] ?? '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Aktivitas - Sistem Kepegawaian</title>
    <script>
        (function () {
            var tema = localStorage.getItem('sik_theme') || 'light';
            document.documentElement.setAttribute('data-theme', tema);
        })();
    </script>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .badge-prioritas-sedang { background:#fef3c7; color:#b45309; }
    </style>
</head>
<body>
    <div class="app-shell">
    <?php include 'includes/sidebar.php'; ?>
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
    <div class="main-area">
        <div class="topbar">
            <button class="hamburger-btn" onclick="toggleSidebar()" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg></button>
            <div class="topbar-title">Log Aktivitas</div>
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
                <h2>Log Aktivitas</h2>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Nama</th>
                            <th>Role</th>
                            <th>Aksi</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['waktu']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_user']) ?></td>
                                    <td><span class="role-badge role-<?= htmlspecialchars($row['role_user']) ?>"><?= htmlspecialchars(str_replace('_', ' ', $row['role_user'])) ?></span></td>
                                    <td><span class="badge <?= warnaAksi($row['aksi']) ?>"><?= labelAksi($row['aksi']) ?></span></td>
                                    <td><?= htmlspecialchars($row['keterangan']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center; padding:20px;">Belum ada aktivitas tercatat.</td></tr>
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
