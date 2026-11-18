<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "config/koneksi.php";
require_once "config/pengaturan.php";
require_once "includes/captcha.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $jawabanCaptcha = $_POST['captcha'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Email dan password wajib diisi.";
    } elseif (!cekCaptcha($jawabanCaptcha)) {
        $error = "Jawaban captcha salah, coba lagi.";
    } else {
        $sql = "SELECT id, nama, email, role, password, aktif, email_terverifikasi FROM users WHERE email = ? LIMIT 1";
        $stmt = db_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = db_result($stmt);

        if ($row = db_fetch($result)) {
            $passwordCocok = false;

            if (password_verify($password, $row['password'])) {
                $passwordCocok = true;
            } elseif ($password === $row['password']) {
                $passwordCocok = true;
                $passwordBaruHash = password_hash($password, PASSWORD_DEFAULT);
                $updateHash = db_prepare($koneksi, "UPDATE users SET password = ? WHERE id = ?");
                mysqli_stmt_bind_param($updateHash, "ss", $passwordBaruHash, $row['id']);
                mysqli_stmt_execute($updateHash);
            }

            if ($passwordCocok) {
                if (WAJIB_VERIFIKASI_EMAIL && $row['email_terverifikasi'] == 0) {
                    $error = "Email kamu belum diverifikasi. Cek inbox/spam email untuk link verifikasi.";
                } elseif ($row['aktif'] == 1) {
                    $_SESSION['id'] = $row['id'];
                    $_SESSION['nama'] = $row['nama'];
                    $_SESSION['email'] = $row['email'];
                    $_SESSION['role'] = $row['role'];

                    // Catat log aktivitas login
                    $log = "INSERT INTO log_aktivitas (user_id, aksi, keterangan) VALUES (?, 'login', ?)";
                    $stmtLog = db_prepare($koneksi, $log);
                    $ket = $row['nama'] . " (" . $row['role'] . ") login ke sistem";
                    mysqli_stmt_bind_param($stmtLog, "ss", $row['id'], $ket);
                    mysqli_stmt_execute($stmtLog);

                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Akun Anda belum aktif / menunggu persetujuan admin.";
                }
            } else {
                $error = "Password salah.";
            }
        } else {
            $error = "Email tidak ditemukan.";
        }
        mysqli_stmt_close($stmt);
    }
}

$soalCaptcha = buatCaptcha();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Sistem Informasi Kepegawaian</title>
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
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-left">
                <div class="brand-mark">SK</div>
                <h1>Sistem Informasi<br>Kepegawaian</h1>
                <p>Kelola data pegawai, tugas, dan aktivitas admin dalam satu platform yang simpel dan cepat.</p>
                <ul class="feature-list">
                    <li>Manajemen data pegawai</li>
                    <li>Pantauan tugas & progres</li>
                    <li>Log aktivitas real-time</li>
                </ul>
            </div>
            <div class="login-right">
                <h2>Selamat Datang Kembali</h2>
                <p class="subtitle">Masuk menggunakan akun yang terdaftar</p>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="login.php" autocomplete="off">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="nama@instansi.go.id" required autofocus>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                            <span class="toggle-password" onclick="togglePassword()">Lihat</span>
                        </div>
                        <p style="text-align:right; margin-top:6px;"><a href="lupa_password.php" style="font-size:12px; color:var(--text-muted);">Lupa password?</a></p>
                    </div>
                    <div class="form-group">
                        <label>Captcha: Berapa hasil dari <?= $soalCaptcha ?> ?</label>
                        <input type="number" name="captcha" required>
                    </div>
                    <button type="submit" class="btn-login">Masuk</button>
                </form>

                <p class="footer-text">Belum punya akun? <a href="register.php" style="color:var(--primary); font-weight:600;">Daftar di sini</a></p>
                <p class="footer-text">&copy; <?= date("Y") ?> Sistem Informasi Kepegawaian</p>
            </div>
        </div>
    </div>

    <script src="assets/js/theme.js"></script>
    <script>
        function togglePassword() {
            const pass = document.getElementById('password');
            pass.type = pass.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>
