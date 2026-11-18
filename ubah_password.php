<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once "includes/cek_login.php";
require_once "config/koneksi.php";
require_once "includes/validasi.php";

$userId = $_SESSION['id'];
$error = "";
$sukses = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passwordLama = trim($_POST['password_lama']);
    $passwordBaru = trim($_POST['password_baru']);
    $konfirmasi = trim($_POST['konfirmasi_password']);

    $stmt = db_prepare($koneksi, "SELECT password FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "s", $userId);
    mysqli_stmt_execute($stmt);
    $result = db_result($stmt);
    $user = db_fetch($result);

    $passwordLamaCocok = false;
    if (password_verify($passwordLama, $user['password'])) {
        $passwordLamaCocok = true;
    } elseif ($passwordLama === $user['password']) {
        $passwordLamaCocok = true;
        $passwordBaruHash = password_hash($passwordLama, PASSWORD_DEFAULT);
        $upgradeHash = db_prepare($koneksi, "UPDATE users SET password = ? WHERE id = ?");
        mysqli_stmt_bind_param($upgradeHash, "ss", $passwordBaruHash, $userId);
        mysqli_stmt_execute($upgradeHash);
    }

    if (!$passwordLamaCocok) {
        $error = "Password lama tidak sesuai.";
    } elseif (($e = validasiPassword($passwordBaru)) !== "") {
        $error = $e;
    } elseif ($passwordBaru !== $konfirmasi) {
        $error = "Konfirmasi password baru tidak cocok.";
    } elseif ($passwordBaru === $passwordLama) {
        $error = "Password baru tidak boleh sama dengan password lama.";
    } else {
        $passwordBaruHash = password_hash($passwordBaru, PASSWORD_DEFAULT);
        $upd = db_prepare($koneksi, "UPDATE users SET password = ? WHERE id = ?");
        mysqli_stmt_bind_param($upd, "ss", $passwordBaruHash, $userId);
        mysqli_stmt_execute($upd);
        $sukses = "Password berhasil diubah.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        (function () {
            var tema = localStorage.getItem('sik_theme') || 'light';
            document.documentElement.setAttribute('data-theme', tema);
        })();
    </script>
    <title>Ubah Password - Sistem Kepegawaian</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="app-shell">
    <?php include 'includes/sidebar.php'; ?>
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
    <div class="main-area">
        <div class="topbar">
            <button class="hamburger-btn" onclick="toggleSidebar()" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg></button>
            <div class="topbar-title">Ubah Password</div>
            <div class="topbar-right">
                <button class="theme-toggle" onclick="toggleTheme()" type="button">
                    <span class="theme-toggle-icon"></span><span class="theme-toggle-label">Gelap</span>
                </button>
            </div>
        </div>
<div class="container">
        <a href="profil.php" class="back-link">&larr; Kembali ke Profil</a>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($sukses): ?>
            <div class="alert alert-success"><?= htmlspecialchars($sukses) ?></div>
        <?php endif; ?>

        <div class="form-box">
            <h2 style="margin-bottom:20px;">Ubah Password</h2>
            <form method="POST" action="ubah_password.php" autocomplete="off">
                <div class="form-row">
                    <label>Password Lama</label>
                    <input type="password" name="password_lama" required>
                </div>
                <div class="form-row">
                    <label>Password Baru</label>
                    <input type="password" name="password_baru" minlength="6" required>
                </div>
                <div class="form-row">
                    <label>Konfirmasi Password Baru</label>
                    <input type="password" name="konfirmasi_password" minlength="6" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Ubah Password</button>
                    <a href="profil.php" class="btn-cancel">Batal</a>
                </div>
            </form>
        </div>
    </div>
    </div>
</div>
    <script src="assets/js/theme.js"></script>
</body>
</html>
