<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once "config/koneksi.php";
require_once "includes/validasi.php";

$token = isset($_GET['token']) ? (string) $_GET['token'] : (isset($_POST['token']) ? (string) $_POST['token'] : '');
$error = "";
$sukses = "";
$tokenValid = false;
$user = null;

if (!empty($token)) {
    $stmt = db_prepare($koneksi, "SELECT id, token_reset_kadaluarsa FROM users WHERE token_reset = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        $result = db_result($stmt);
        $user = db_fetch($result);

        if ($user && isset($user['token_reset_kadaluarsa']) && strtotime((string) $user['token_reset_kadaluarsa']) >= time()) {
            $tokenValid = true;
        }
    }
}

if (!$tokenValid && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $error = "Link reset password tidak valid atau sudah kadaluarsa. Silakan minta link baru.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValid) {
    $password = trim($_POST['password'] ?? '');
    $konfirmasi = trim($_POST['konfirmasi_password'] ?? '');

    if (($e = validasiPassword($password)) !== "") {
        $error = $e;
    } elseif ($password !== $konfirmasi) {
        $error = "Konfirmasi password tidak cocok.";
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $upd = db_prepare($koneksi, "UPDATE users SET password = ?, token_reset = NULL, token_reset_kadaluarsa = NULL WHERE id = ?");
        if ($upd) {
            mysqli_stmt_bind_param($upd, "ss", $passwordHash, $user['id']);
            mysqli_stmt_execute($upd);
            $sukses = "Password berhasil direset! Silakan login dengan password baru kamu.";
            $tokenValid = false; // form disembunyikan setelah berhasil
            $error = "";
        } else {
            $error = "Gagal memperbarui password. Silakan coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Sistem Kepegawaian</title>
    <script>
        (function () {
            var tema = localStorage.getItem('sik_theme') || 'light';
            document.documentElement.setAttribute('data-theme', tema);
        })();
    </script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-body">
    <div class="theme-toggle-floating">
        <button class="theme-toggle" onclick="toggleTheme()" type="button">
            <span class="theme-toggle-icon"></span><span class="theme-toggle-label">Gelap</span>
        </button>
    </div>
    <div class="login-wrapper" style="max-width:480px;">
        <div class="login-card" style="min-height:auto;">
            <div class="login-right" style="flex:1;">
                <h2>Reset Password</h2>
                <p class="subtitle">Buat password baru untuk akun kamu</p>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($sukses): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($sukses) ?></div>
                <?php endif; ?>

                <?php if ($tokenValid): ?>
                <form method="POST" action="reset_password.php" autocomplete="off">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    <div class="form-group">
                        <label>Password Baru</label>
                        <input type="password" name="password" minlength="6" required autofocus>
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password Baru</label>
                        <input type="password" name="konfirmasi_password" minlength="6" required>
                    </div>
                    <button type="submit" class="btn-login">Reset Password</button>
                </form>
                <?php endif; ?>

                <p class="footer-text"><a href="login.php" style="color:var(--primary); font-weight:600;">&larr; Kembali ke Login</a></p>
            </div>
        </div>
    </div>
    <script src="assets/js/theme.js"></script>
</body>
</html>
