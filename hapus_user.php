<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once "includes/cek_login.php";
require_once "config/koneksi.php";

if ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

$id = $_GET['id'] ?? '';

if (!empty($id)) {
    // Ambil nama dulu buat keterangan log, sebelum dihapus
    $stmt = db_prepare($koneksi, "SELECT nama FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "s", $id);
    mysqli_stmt_execute($stmt);
    $result = db_result($stmt);
    $user = db_fetch($result);

    if ($user) {
        // Jangan biarkan user menghapus akunnya sendiri
        if ($id === $_SESSION['id']) {
            header("Location: kelola_user.php");
            exit();
        }

        $hapus = db_prepare($koneksi, "DELETE FROM users WHERE id = ?");
        mysqli_stmt_bind_param($hapus, "s", $id);

        if (mysqli_stmt_execute($hapus)) {
            $log = db_prepare($koneksi, "INSERT INTO log_aktivitas (user_id, aksi, keterangan) VALUES (?, 'hapus_user', ?)");
            $ket = $_SESSION['nama'] . " menghapus user: " . $user['nama'];
            mysqli_stmt_bind_param($log, "ss", $_SESSION['id'], $ket);
            mysqli_stmt_execute($log);
        }
    }
}

header("Location: kelola_user.php?sukses=hapus");
exit();
?>
