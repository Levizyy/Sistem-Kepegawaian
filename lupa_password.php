<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
session_start();
require_once "config/koneksi.php";
require_once "config/pengaturan.php";
require_once "includes/captcha.php";
require_once "includes/kirim_email.php";

$error = "";
$sukses = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $jawabanCaptcha = $_POST['captcha'] ?? '';

    if (empty($email)) {
        $error = "Email wajib diisi.";
    } elseif (!cekCaptcha($jawabanCaptcha)) {
        $error = "Jawaban captcha salah, coba lagi.";
    } else {
        $stmt = db_prepare($koneksi, "SELECT id, nama FROM users WHERE email = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = db_result($stmt);
            $user = db_fetch($result);

            // Pesan sukses selalu sama walau email tidak ditemukan (supaya tidak bocor data)
            $sukses = "Kalau email tersebut terdaftar, link reset password sudah dikirim. Silakan cek inbox/spam.";

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $kadaluarsa = date("Y-m-d H:i:s", strtotime("+1 hour"));

                $upd = db_prepare($koneksi, "UPDATE users SET token_reset = ?, token_reset_kadaluarsa = ? WHERE id = ?");
                if ($upd) {
                    mysqli_stmt_bind_param($upd, "sss", $token, $kadaluarsa, $user['id']);
                    mysqli_stmt_execute($upd);

                    $linkReset = URL_APLIKASI . "/reset_password.php?token=" . $token;
                    $isiEmail = "
                        <p>Halo <b>" . htmlspecialchars((string) $user['nama']) . "</b>,</p>
                        <p>Ada permintaan reset password untuk akun kamu. Klik link berikut (berlaku 1 jam):</p>
                        <p><a href='$linkReset'>$linkReset</a></p>
                        <p>Kalau kamu tidak merasa meminta ini, abaikan email ini.</p>
                    ";
                    kirimEmail($email, "Reset Password - Sistem Kepegawaian", $isiEmail);
                }
            }
        }
    }
}

$soalCaptcha = buatCaptcha();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Sistem Kepegawaian</title>
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
                <h2>Lupa Password</h2>
                <p class="subtitle">Masukkan email akun kamu</p>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($sukses): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($sukses) ?></div>
                <?php endif; ?>

                <form method="POST" action="lupa_password.php" autocomplete="off">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required autofocus>
                    </div>
                    <div class="form-group">
                        <label>Captcha: Berapa hasil dari <?= $soalCaptcha ?> ?</label>
                        <input type="number" name="captcha" required>
                    </div>
                    <button type="submit" class="btn-login">Kirim Link Reset</button>
                </form>

                <p class="footer-text"><a href="login.php" style="color:var(--primary); font-weight:600;">&larr; Kembali ke Login</a></p>
            </div>
        </div>
    </div>
    <script src="assets/js/theme.js"></script>
</body>
</html>
