<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once "includes/cek_login.php";
require_once "config/koneksi.php";

$role = $_SESSION['role'];
$userId = $_SESSION['id'];
$bulanIni = date('Y-m');

$totalUser = db_fetch(db_query($koneksi, "SELECT COUNT(*) AS jml FROM users"))['jml'];
$userAktif = db_fetch(db_query($koneksi, "SELECT COUNT(*) AS jml FROM users WHERE aktif = 1"))['jml'];
$totalTugas = db_fetch(db_query($koneksi, "SELECT COUNT(*) AS jml FROM to_do_list"))['jml'];
$tugasSelesai = db_fetch(db_query($koneksi, "SELECT COUNT(*) AS jml FROM to_do_list WHERE status = 'selesai'"))['jml'];
$izinPending = db_fetch(db_query($koneksi, "SELECT COUNT(*) AS jml FROM leave_requests WHERE status = 'pending'"))['jml'];
$absensiBulanIni = db_fetch(db_query($koneksi, "SELECT COUNT(*) AS jml FROM attendance WHERE DATE_FORMAT(tanggal, '%Y-%m') = '" . mysqli_real_escape_string($koneksi, $bulanIni) . "'"))['jml'];

if ($role === 'user') {
    $st1 = db_prepare($koneksi, "SELECT COUNT(*) AS jml FROM to_do_list WHERE pemilik = ?");
    mysqli_stmt_bind_param($st1, 's', $userId);
    mysqli_stmt_execute($st1);
    $totalTugasSaya = db_fetch(db_result($st1))['jml'];
    $st2 = db_prepare($koneksi, "SELECT COUNT(*) AS jml FROM to_do_list WHERE pemilik = ? AND status = 'selesai'");
    mysqli_stmt_bind_param($st2, 's', $userId);
    mysqli_stmt_execute($st2);
    $tugasSelesaiSaya = db_fetch(db_result($st2))['jml'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan — Sistem Informasi Kepegawaian</title>
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
                <div class="topbar-title">Laporan</div>
                <div class="topbar-right">
                    <button class="theme-toggle" onclick="toggleTheme()" type="button">
                        <span class="theme-toggle-icon"></span><span class="theme-toggle-label">Gelap</span>
                    </button>
                </div>
            </div>

            <div class="container">
                <a href="dashboard.php" class="back-link">&larr; Kembali ke Dashboard</a>

                <div class="stats-grid">
                    <?php if ($role === 'super_admin' || $role === 'admin'): ?>
                        <div class="stat-card">
                            <div class="stat-icon blue">👥</div>
                            <div>
                                <div class="stat-value"><?= (int)$totalUser ?></div>
                                <div class="stat-label">Total Pegawai</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon green">●</div>
                            <div>
                                <div class="stat-value"><?= (int)$userAktif ?></div>
                                <div class="stat-label">Pegawai Aktif</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon orange">✓</div>
                            <div>
                                <div class="stat-value"><?= (int)$totalTugas ?></div>
                                <div class="stat-label">Total Tugas</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon blue">✔</div>
                            <div>
                                <div class="stat-value"><?= (int)$tugasSelesai ?></div>
                                <div class="stat-label">Tugas Selesai</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon orange">🗓️</div>
                            <div>
                                <div class="stat-value"><?= (int)$absensiBulanIni ?></div>
                                <div class="stat-label">Absensi Bulan Ini</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon blue">📄</div>
                            <div>
                                <div class="stat-value"><?= (int)$izinPending ?></div>
                                <div class="stat-label">Izin Pending</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="stat-card">
                            <div class="stat-icon blue">✓</div>
                            <div>
                                <div class="stat-value"><?= (int)$totalTugasSaya ?></div>
                                <div class="stat-label">Total Tugas Saya</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon green">✔</div>
                            <div>
                                <div class="stat-value"><?= (int)$tugasSelesaiSaya ?></div>
                                <div class="stat-label">Tugas Selesai</div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card-box" style="margin-top:20px;">
                    <div class="card-header">
                        <h2>Export Laporan</h2>
                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                            <a href="export_todo.php" class="btn-primary">Export Tugas Excel</a>
                            <a href="export_users.php" class="btn-primary" style="background:var(--success);">Export User Excel</a>
                            <a href="export_laporan.php" class="btn-primary" style="background:var(--warning);">Export Laporan Ringkas</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/theme.js"></script>
</body>
</html>
