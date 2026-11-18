<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once "includes/cek_login.php";
require_once "config/koneksi.php";

$role = $_SESSION['role'];
$nama = $_SESSION['nama'];
$userId = $_SESSION['id'];

// ================================================
// STATISTIK RINGKAS
// ================================================
if ($role === 'super_admin' || $role === 'admin') {
    $totalUser = db_fetch(db_query($koneksi, "SELECT COUNT(*) AS jml FROM users"))['jml'];
    $userAktif = db_fetch(db_query($koneksi, "SELECT COUNT(*) AS jml FROM users WHERE aktif = 1"))['jml'];
    $tugasProses = db_fetch(db_query($koneksi, "SELECT COUNT(*) AS jml FROM to_do_list WHERE status = 'proses'"))['jml'];
    $tugasSelesai = db_fetch(db_query($koneksi, "SELECT COUNT(*) AS jml FROM to_do_list WHERE status = 'selesai'"))['jml'];
} else {
    $stmt = db_prepare($koneksi, "SELECT COUNT(*) AS jml FROM to_do_list WHERE pemilik = ?");
    mysqli_stmt_bind_param($stmt, "s", $userId);
    mysqli_stmt_execute($stmt);
    $totalTugasSaya = db_fetch(db_result($stmt))['jml'];

    $stmt2 = db_prepare($koneksi, "SELECT COUNT(*) AS jml FROM to_do_list WHERE pemilik = ? AND status = 'proses'");
    mysqli_stmt_bind_param($stmt2, "s", $userId);
    mysqli_stmt_execute($stmt2);
    $tugasProsesSaya = db_fetch(db_result($stmt2))['jml'];

    $stmt3 = db_prepare($koneksi, "SELECT COUNT(*) AS jml FROM to_do_list WHERE pemilik = ? AND status = 'selesai'");
    mysqli_stmt_bind_param($stmt3, "s", $userId);
    mysqli_stmt_execute($stmt3);
    $tugasSelesaiSaya = db_fetch(db_result($stmt3))['jml'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda — Sistem Informasi Kepegawaian</title>
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
            <div class="topbar-title">Beranda</div>
            <div class="topbar-right">
                <button class="theme-toggle" onclick="toggleTheme()" type="button">
                    <span class="theme-toggle-icon"></span><span class="theme-toggle-label">Gelap</span>
                </button>
            </div>
        </div>
<div class="container">
        <div class="welcome-card">
            <h2>Selamat datang, <?= htmlspecialchars($nama) ?></h2>
            <p>Anda masuk sebagai <strong><?= htmlspecialchars(str_replace('_', ' ', $role)) ?></strong>. Berikut daftar menu yang dapat Anda akses.</p>
        </div>

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
                    <div class="stat-icon orange">◷</div>
                    <div>
                        <div class="stat-value"><?= (int)$tugasProses ?></div>
                        <div class="stat-label">Tugas Sedang Proses</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue">✓</div>
                    <div>
                        <div class="stat-value"><?= (int)$tugasSelesai ?></div>
                        <div class="stat-label">Tugas Selesai</div>
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
                    <div class="stat-icon orange">◷</div>
                    <div>
                        <div class="stat-value"><?= (int)$tugasProsesSaya ?></div>
                        <div class="stat-label">Sedang Proses</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">●</div>
                    <div>
                        <div class="stat-value"><?= (int)$tugasSelesaiSaya ?></div>
                        <div class="stat-label">Selesai</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="menu-grid">
            <?php if ($role === 'super_admin' || $role === 'admin'): ?>
                <a href="kelola_user.php" class="menu-card">
                    <span class="menu-icon">👥</span>
                    <div>
                        <h3>Kelola User</h3>
                        <p>Tambah, ubah, dan hapus data pegawai</p>
                    </div>
                </a>
            <?php endif; ?>

            <a href="to_do_list.php" class="menu-card">
                <span class="menu-icon">✓</span>
                <div>
                    <h3>To Do List</h3>
                    <p>Lihat dan kelola daftar tugas Anda</p>
                </div>
            </a>

            <a href="absensi.php" class="menu-card">
                <span class="menu-icon">🕒</span>
                <div>
                    <h3>Absensi</h3>
                    <p>Check in dan check out harian pegawai</p>
                </div>
            </a>

            <a href="izin.php" class="menu-card">
                <span class="menu-icon">📄</span>
                <div>
                    <h3>Izin & Cuti</h3>
                    <p>Ajukan dan kelola pengajuan izin atau cuti</p>
                </div>
            </a>

            <a href="rekap_absensi.php" class="menu-card">
                <span class="menu-icon">📊</span>
                <div>
                    <h3>Rekap Absensi</h3>
                    <p>Lihat ringkasan absensi per bulan</p>
                </div>
            </a>

            <a href="notifikasi.php" class="menu-card">
                <span class="menu-icon">🔔</span>
                <div>
                    <h3>Notifikasi</h3>
                    <p>Inbox pemberitahuan dan pengiriman massal</p>
                </div>
            </a>

            <a href="kinerja.php" class="menu-card">
                <span class="menu-icon">📈</span>
                <div>
                    <h3>Kinerja</h3>
                    <p>Target KPI, skor, dan evaluasi kinerja</p>
                </div>
            </a>

            <a href="laporan.php" class="menu-card">
                <span class="menu-icon">📋</span>
                <div>
                    <h3>Laporan</h3>
                    <p>Ringkasan laporan dan export data</p>
                </div>
            </a>

            <?php if ($role === 'super_admin' || $role === 'admin'): ?>
                <a href="log_aktivitas.php" class="menu-card">
                    <span class="menu-icon">◷</span>
                    <div>
                        <h3>Log Aktivitas</h3>
                        <p>Pantau riwayat aktivitas seluruh admin</p>
                    </div>
                </a>
            <?php endif; ?>

            <a href="profil.php" class="menu-card">
                <span class="menu-icon">◉</span>
                <div>
                    <h3>Profil Saya</h3>
                    <p>Kelola data pribadi Anda</p>
                </div>
            </a>
        </div>
    </div>
    </div>
</div>
    <script src="assets/js/theme.js"></script>
</body>
</html>
