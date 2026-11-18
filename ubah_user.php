<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once "includes/cek_login.php";
require_once "config/koneksi.php";
require_once "includes/validasi.php";

if ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

$id = $_GET['id'] ?? '';
$error = "";

// Ambil data user yang mau diedit
$stmt = db_prepare($koneksi, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "s", $id);
mysqli_stmt_execute($stmt);
$result = db_result($stmt);
$user = db_fetch($result);

if (!$user) {
    header("Location: kelola_user.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $unit_kerja = trim($_POST['unit_kerja']);
    $email = trim($_POST['email']);
    $nomor_hp = trim($_POST['nomor_hp']);
    $role = $_POST['role'];
    $aktif = isset($_POST['aktif']) ? 1 : 0;
    $password_baru = trim($_POST['password']);

    if (empty($nama) || empty($unit_kerja) || empty($email) || empty($nomor_hp)) {
        $error = "Semua field wajib diisi.";
    } elseif (($e = validasiEmail($email)) !== "") {
        $error = $e;
    } elseif (($e = validasiNomorHp($nomor_hp)) !== "") {
        $error = $e;
    } elseif (!empty($password_baru) && ($e = validasiPassword($password_baru)) !== "") {
        $error = $e;
    } else {
        if (!empty($password_baru)) {
            $passwordHash = password_hash($password_baru, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET nama=?, unit_kerja=?, email=?, nomor_hp=?, role=?, aktif=?, password=? WHERE id=?";
            $stmt2 = db_prepare($koneksi, $sql);
            mysqli_stmt_bind_param($stmt2, "sssssiss", $nama, $unit_kerja, $email, $nomor_hp, $role, $aktif, $passwordHash, $id);
        } else {
            // Update tanpa mengubah password
            $sql = "UPDATE users SET nama=?, unit_kerja=?, email=?, nomor_hp=?, role=?, aktif=? WHERE id=?";
            $stmt2 = db_prepare($koneksi, $sql);
            mysqli_stmt_bind_param($stmt2, "sssssis", $nama, $unit_kerja, $email, $nomor_hp, $role, $aktif, $id);
        }

        if (mysqli_stmt_execute($stmt2)) {
            $log = db_prepare($koneksi, "INSERT INTO log_aktivitas (user_id, aksi, keterangan) VALUES (?, 'edit_user', ?)");
            $ket = $_SESSION['nama'] . " mengubah data user: " . $nama;
            mysqli_stmt_bind_param($log, "ss", $_SESSION['id'], $ket);
            mysqli_stmt_execute($log);

            header("Location: kelola_user.php?sukses=ubah");
            exit();
        } else {
            $error = "Gagal menyimpan perubahan: " . mysqli_error($koneksi);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah User - Sistem Kepegawaian</title>
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
            <div class="topbar-title">Ubah User</div>
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
            <h2 style="margin-bottom:20px; color:#1f2937;">Ubah Data User</h2>
            <form method="POST" action="ubah_user.php?id=<?= urlencode($user['id']) ?>">
                <div class="form-row">
                    <label>ID User</label>
                    <input type="text" value="<?= htmlspecialchars($user['id']) ?>" disabled>
                </div>
                <div class="form-row">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
                </div>
                <div class="form-row">
                    <label>Unit Kerja</label>
                    <input type="text" name="unit_kerja" value="<?= htmlspecialchars($user['unit_kerja']) ?>" autocomplete="off" required>
                </div>
                <div class="form-row">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="form-row">
                    <label>Nomor HP</label>
                    <input type="text" name="nomor_hp" value="<?= htmlspecialchars($user['nomor_hp']) ?>" pattern="\+?[0-9]{9,15}" title="Hanya angka, 9-15 digit, boleh diawali +" required>
                </div>
                <div class="form-row">
                    <label>Role</label>
                    <select name="role" required>
                        <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <?php if ($_SESSION['role'] === 'super_admin'): ?>
                            <option value="super_admin" <?= $user['role'] === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-row">
                    <label>Password Baru (kosongkan jika tidak ingin mengubah)</label>
                    <input type="password" name="password" placeholder="••••••••" minlength="6" title="Minimal 6 karakter">
                </div>
                <div class="form-row">
                    <label>
                        <input type="checkbox" name="aktif" <?= $user['aktif'] ? 'checked' : '' ?> style="width:auto; display:inline-block;"> Aktifkan akun
                    </label>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Simpan Perubahan</button>
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
