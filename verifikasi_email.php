<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once "config/koneksi.php";
require_once "config/pengaturan.php";

$token = $_GET['token'] ?? '';
$pesan = "";
$berhasil = false;

if (empty($token)) {
    $pesan = "Link verifikasi tidak valid.";
} else {
    $stmt = db_prepare($koneksi, "SELECT id, nama, aktif FROM users WHERE token_verifikasi = ?");
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = db_result($stmt);
    $user = db_fetch($result);

    if (!$user) {
        $pesan = "Link verifikasi tidak ditemukan atau sudah pernah dipakai.";
    } else {
        $aktifBaru = REGISTRASI_LANGSUNG_AKTIF ? 1 : $user['aktif'];

        $upd = db_prepare($koneksi, "UPDATE users SET email_terverifikasi = 1, token_verifikasi = NULL, aktif = ? WHERE id = ?");
        mysqli_stmt_bind_param($upd, "is", $aktifBaru, $user['id']);
        mysqli_stmt_execute($upd);

        $berhasil = true;
        $pesan = REGISTRASI_LANGSUNG_AKTIF
            ? "Email berhasil diverifikasi! Akun kamu sudah aktif, silakan login."
            : "Email berhasil diverifikasi! Akun kamu masih menunggu persetujuan admin sebelum bisa login.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - Sistem Kepegawaian</title>
    <script>
        (function () {
            var tema = localStorage.getItem('sik_theme') || 'light';
            document.documentElement.setAttribute('data-theme', tema);
        })();
    </script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-body">
    <div class="login-wrapper" style="max-width:480px;">
        <div class="login-card" style="min-height:auto;">
            <div class="login-right" style="flex:1;">
                <h2><?= $berhasil ? '✅ Verifikasi Berhasil' : '⚠️ Verifikasi Gagal' ?></h2>
                <p class="subtitle"><?= htmlspecialchars($pesan) ?></p>
                <a href="login.php" class="btn-login" style="display:block; text-align:center; text-decoration:none;">Ke Halaman Login</a>
            </div>
        </div>
    </div>
    <script src="assets/js/theme.js"></script>
</body>
</html>
