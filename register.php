<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
session_start();
require_once "config/koneksi.php";
require_once "config/pengaturan.php";
require_once "includes/validasi.php";
require_once "includes/captcha.php";
require_once "includes/kirim_email.php";

$error = "";
$sukses = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $unit_kerja = trim($_POST['unit_kerja']);
    $email = trim($_POST['email']);
    $nomor_hp = trim($_POST['nomor_hp']);
    $password = trim($_POST['password']);
    $konfirmasi = trim($_POST['konfirmasi_password']);
    $jawabanCaptcha = $_POST['captcha'] ?? '';

    if (empty($nama) || empty($unit_kerja) || empty($email) || empty($nomor_hp) || empty($password)) {
        $error = "Semua field wajib diisi.";
    } elseif (!cekCaptcha($jawabanCaptcha)) {
        $error = "Jawaban captcha salah, coba lagi.";
    } elseif (($e = validasiEmail($email)) !== "") {
        $error = $e;
    } elseif (($e = validasiNomorHp($nomor_hp)) !== "") {
        $error = $e;
    } elseif (($e = validasiPassword($password)) !== "") {
        $error = $e;
    } elseif ($password !== $konfirmasi) {
        $error = "Konfirmasi password tidak cocok.";
    } else {
        // Cek email sudah dipakai atau belum
        $cek = db_prepare($koneksi, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($cek, "s", $email);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);

        if (mysqli_stmt_num_rows($cek) > 0) {
            $error = "Email sudah terdaftar. Silakan login atau gunakan email lain.";
        } else {
            // Generate ID unik otomatis
            do {
                $idBaru = 'USR' . rand(1000, 9999);
                $cekId = db_prepare($koneksi, "SELECT id FROM users WHERE id = ?");
                mysqli_stmt_bind_param($cekId, "s", $idBaru);
                mysqli_stmt_execute($cekId);
                mysqli_stmt_store_result($cekId);
                $sudahDipakai = mysqli_stmt_num_rows($cekId) > 0;
            } while ($sudahDipakai);

            $tokenVerifikasi = WAJIB_VERIFIKASI_EMAIL ? bin2hex(random_bytes(32)) : null;
            $emailTerverifikasi = WAJIB_VERIFIKASI_EMAIL ? 0 : 1;
            $aktif = (!WAJIB_VERIFIKASI_EMAIL && REGISTRASI_LANGSUNG_AKTIF) ? 1 : 0;
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (id, nama, unit_kerja, email, nomor_hp, role, password, aktif, email_terverifikasi, token_verifikasi) 
                    VALUES (?, ?, ?, ?, ?, 'user', ?, ?, ?, ?)";
            $stmt = db_prepare($koneksi, $sql);
            mysqli_stmt_bind_param($stmt, "ssssssiiis", $idBaru, $nama, $unit_kerja, $email, $nomor_hp, $passwordHash, $aktif, $emailTerverifikasi, $tokenVerifikasi);

            if (mysqli_stmt_execute($stmt)) {
                if (WAJIB_VERIFIKASI_EMAIL) {
                    $linkVerifikasi = URL_APLIKASI . "/verifikasi_email.php?token=" . $tokenVerifikasi;
                    $isiEmail = "
                        <p>Halo <b>" . htmlspecialchars($nama) . "</b>,</p>
                        <p>Terima kasih sudah mendaftar di Sistem Informasi Kepegawaian. Klik link berikut untuk verifikasi email kamu:</p>
                        <p><a href='$linkVerifikasi'>$linkVerifikasi</a></p>
                        <p>Kalau kamu tidak merasa mendaftar, abaikan email ini.</p>
                    ";
                    $hasilKirim = kirimEmail($email, "Verifikasi Email - Sistem Kepegawaian", $isiEmail);

                    if ($hasilKirim === true) {
                        $sukses = "Registrasi berhasil! Silakan cek email kamu ($email) untuk link verifikasi" . (!REGISTRASI_LANGSUNG_AKTIF ? ", lalu tunggu persetujuan admin sebelum bisa login." : ".");
                    } else {
                        $sukses = "Registrasi berhasil, tapi email verifikasi gagal terkirim. Hubungi admin. (Detail: $hasilKirim)";
                    }
                } else {
                    $sukses = REGISTRASI_LANGSUNG_AKTIF
                        ? "Registrasi berhasil! Akun kamu sudah aktif, silakan login."
                        : "Registrasi berhasil! Akun kamu menunggu persetujuan admin sebelum bisa login.";
                }
            } else {
                $error = "Gagal mendaftar: " . mysqli_error($koneksi);
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
    <title>Daftar Akun - Sistem Informasi Kepegawaian</title>
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
                <h1>Buat Akun Baru</h1>
                <p>Daftar sebagai pegawai untuk mengelola tugas dan data pribadi kamu di sistem ini.</p>
                <ul class="feature-list">
                    <li>Kelola tugas harian sendiri</li>
                    <li>Pantau progres pekerjaan</li>
                    <li>Akses profil kapan saja</li>
                </ul>
            </div>
            <div class="login-right">
                <h2>Daftar Akun</h2>
                <p class="subtitle">Akun akan dibuat dengan role User</p>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($sukses): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($sukses) ?></div>
                <?php endif; ?>

                <?php if (!$sukses): ?>
                <form method="POST" action="register.php" autocomplete="off">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" required>
                    </div>
                    <div class="form-group">
                        <label>Unit Kerja</label>
                        <input type="text" name="unit_kerja" autocomplete="off" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Nomor HP</label>
                        <input type="text" name="nomor_hp" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" minlength="6" required>
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password</label>
                        <input type="password" name="konfirmasi_password" minlength="6" required>
                    </div>
                    <div class="form-group">
                        <label>Captcha: Berapa hasil dari <?= $soalCaptcha ?> ?</label>
                        <input type="number" name="captcha" required>
                    </div>
                    <button type="submit" class="btn-login">Daftar</button>
                </form>
                <?php endif; ?>

                <p class="footer-text">Sudah punya akun? <a href="login.php" style="color:var(--primary); font-weight:600;">Login di sini</a></p>
            </div>
        </div>
    </div>
    <script src="assets/js/theme.js"></script>
</body>
</html>
