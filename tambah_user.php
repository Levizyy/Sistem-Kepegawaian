<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "includes/cek_login.php";
require_once "config/koneksi.php";
require_once "includes/validasi.php";

if ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['id']);
    $nama = trim($_POST['nama']);
    $unit_kerja = trim($_POST['unit_kerja']);
    $email = trim($_POST['email']);
    $nomor_hp = trim($_POST['nomor_hp']);
    $role = $_POST['role'];
    $password = trim($_POST['password']);
    $aktif = isset($_POST['aktif']) ? 1 : 0;

    if (empty($id) || empty($nama) || empty($unit_kerja) || empty($email) || empty($nomor_hp) || empty($password)) {
        $error = "Semua field wajib diisi.";
    } elseif (($e = validasiId($id)) !== "") {
        $error = $e;
    } elseif (($e = validasiEmail($email)) !== "") {
        $error = $e;
    } elseif (($e = validasiNomorHp($nomor_hp)) !== "") {
        $error = $e;
    } elseif (($e = validasiPassword($password)) !== "") {
        $error = $e;
    } else {
        // Cek apakah ID atau email sudah dipakai
        $cek = db_prepare($koneksi, "SELECT id FROM users WHERE id = ? OR email = ?");
        mysqli_stmt_bind_param($cek, "ss", $id, $email);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);

        if (mysqli_stmt_num_rows($cek) > 0) {
            $error = "ID atau Email sudah digunakan.";
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (id, nama, unit_kerja, email, nomor_hp, role, password, aktif) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = db_prepare($koneksi, $sql);
            mysqli_stmt_bind_param($stmt, "sssssssi", $id, $nama, $unit_kerja, $email, $nomor_hp, $role, $passwordHash, $aktif);

            if (mysqli_stmt_execute($stmt)) {
                // Catat log aktivitas
                $log = db_prepare($koneksi, "INSERT INTO log_aktivitas (user_id, aksi, keterangan) VALUES (?, 'tambah_user', ?)");
                $ket = $_SESSION['nama'] . " menambahkan user baru: " . $nama;
                mysqli_stmt_bind_param($log, "ss", $_SESSION['id'], $ket);
                mysqli_stmt_execute($log);

                header("Location: kelola_user.php?sukses=tambah");
                exit();
            } else {
                $error = "Gagal menyimpan data: " . mysqli_error($koneksi);
            }
        }
        mysqli_stmt_close($cek);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User - Sistem Kepegawaian</title>
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
            <div class="topbar-title">Tambah User</div>
            <div class="topbar-right">
                <button class="theme-toggle" onclick="toggleTheme()" type="button">
                    <span class="theme-toggle-icon"></span><span class="theme-toggle-label">Gelap</span>
                </button>
            </div>
        </div>
<div class="container">
        <a href="kelola_user.php" class="back-link">&larr; Kembali ke Kelola User</a>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="form-box">
            <h2 style="margin-bottom:20px; color:#1f2937;">Tambah User Baru</h2>
            <form method="POST" action="tambah_user.php">
                <div class="form-row">
                    <label>ID User</label>
                    <input type="text" name="id" placeholder="Contoh: USR021" required>
                </div>
                <div class="form-row">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" required>
                </div>
                <div class="form-row">
                    <label>Unit Kerja</label>
                    <input type="text" name="unit_kerja" placeholder="Contoh: Kepegawaian, Keuangan, Umum" autocomplete="off" required>
                </div>
                <div class="form-row">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-row">
                    <label>Nomor HP</label>
                    <input type="text" name="nomor_hp" pattern="\+?[0-9]{9,15}" title="Hanya angka, 9-15 digit, boleh diawali +" required>
                </div>
                <div class="form-row">
                    <label>Role</label>
                    <select name="role" required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                        <?php if ($_SESSION['role'] === 'super_admin'): ?>
                            <option value="super_admin">Super Admin</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-row">
                    <label>Password</label>
                    <input type="password" name="password" minlength="6" title="Minimal 6 karakter" required>
                </div>
                <div class="form-row">
                    <label><input type="checkbox" name="aktif" checked style="width:auto; display:inline-block;"> Aktifkan akun</label>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Simpan</button>
                    <a href="kelola_user.php" class="btn-cancel">Batal</a>
                </div>
            </form>
        </div>
    </div>
    </div>
</div>
    <script src="assets/js/theme.js"></script>
</body>
</html>
