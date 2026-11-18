<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once "includes/cek_login.php";
require_once "config/koneksi.php";

$userId = $_SESSION['id'];
$role = $_SESSION['role'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'tandai_baca' && !empty($_POST['notif_id'])) {
        $notifId = (int)$_POST['notif_id'];
        $stmt = db_prepare($koneksi, "UPDATE notifications SET sudah_dibaca = 1 WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, 'is', $notifId, $userId);
        mysqli_stmt_execute($stmt);
    }

    if ($aksi === 'buat_notifikasi') {
        if ($role === 'super_admin' || $role === 'admin') {
            $judul = trim($_POST['judul'] ?? '');
            $pesan = trim($_POST['pesan'] ?? '');
            $tipe = $_POST['tipe'] ?? 'info';
            if (!in_array($tipe, ['info', 'warning', 'success', 'danger'], true)) { $tipe = 'info'; }

            if ($judul !== '' && $pesan !== '') {
                $stmt = db_prepare($koneksi, "SELECT id FROM users WHERE aktif = 1");
                mysqli_stmt_execute($stmt);
                $result = db_result($stmt);
                while ($row = mysqli_fetch_assoc($result)) {
                    $insert = db_prepare($koneksi, "INSERT INTO notifications (user_id, judul, pesan, tipe) VALUES (?, ?, ?, ?)");
                    mysqli_stmt_bind_param($insert, 'ssss', $row['id'], $judul, $pesan, $tipe);
                    mysqli_stmt_execute($insert);
                }
            }
        }
    }

    header('Location: notifikasi.php');
    exit();
}

$sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = db_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, 's', $userId);
mysqli_stmt_execute($stmt);
$result = db_result($stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi — Sistem Informasi Kepegawaian</title>
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
                <div class="topbar-title">Notifikasi</div>
                <div class="topbar-right">
                    <button class="theme-toggle" onclick="toggleTheme()" type="button">
                        <span class="theme-toggle-icon"></span><span class="theme-toggle-label">Gelap</span>
                    </button>
                </div>
            </div>

            <div class="container">
                <a href="dashboard.php" class="back-link">&larr; Kembali ke Dashboard</a>

                <?php if ($role === 'super_admin' || $role === 'admin'): ?>
                    <div class="card-box">
                        <div class="card-header">
                            <h2>Buat Notifikasi Massal</h2>
                        </div>
                        <form method="POST" action="notifikasi.php" style="display:grid; gap:12px; max-width:700px;">
                            <input type="hidden" name="aksi" value="buat_notifikasi">
                            <label>
                                <span style="display:block; margin-bottom:6px; font-weight:600;">Judul</span>
                                <input type="text" name="judul" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); background:var(--surface); color:var(--text);">
                            </label>
                            <label>
                                <span style="display:block; margin-bottom:6px; font-weight:600;">Pesan</span>
                                <textarea name="pesan" rows="4" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); background:var(--surface); color:var(--text);"></textarea>
                            </label>
                            <label>
                                <span style="display:block; margin-bottom:6px; font-weight:600;">Tipe</span>
                                <select name="tipe" style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); background:var(--surface); color:var(--text);">
                                    <option value="info">Info</option>
                                    <option value="warning">Warning</option>
                                    <option value="success">Success</option>
                                    <option value="danger">Danger</option>
                                </select>
                            </label>
                            <button type="submit" class="btn-primary">Kirim ke Semua User</button>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="card-box" style="margin-top:20px;">
                    <div class="card-header">
                        <h2>Daftar Notifikasi Saya</h2>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Judul</th>
                                    <th>Pesan</th>
                                    <th>Tipe</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($result) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['judul']) ?></td>
                                            <td><?= htmlspecialchars($row['pesan']) ?></td>
                                            <td><?= htmlspecialchars($row['tipe']) ?></td>
                                            <td><?= $row['sudah_dibaca'] ? 'Sudah Dibaca' : 'Belum Dibaca' ?></td>
                                            <td>
                                                <?php if (!$row['sudah_dibaca']): ?>
                                                    <form method="POST" action="notifikasi.php">
                                                        <input type="hidden" name="aksi" value="tandai_baca">
                                                        <input type="hidden" name="notif_id" value="<?= (int)$row['id'] ?>">
                                                        <button type="submit" class="btn-sm btn-edit">Tandai Dibaca</button>
                                                    </form>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" style="text-align:center; padding:20px;">Belum ada notifikasi.</td></tr>
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
