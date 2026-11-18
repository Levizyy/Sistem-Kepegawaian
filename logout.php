<?php
session_start();
require_once "config/koneksi.php";

if (isset($_SESSION['id'])) {
    $log = db_prepare($koneksi, "INSERT INTO log_aktivitas (user_id, aksi, keterangan) VALUES (?, 'logout', ?)");
    $ket = $_SESSION['nama'] . " logout dari sistem";
    mysqli_stmt_bind_param($log, "ss", $_SESSION['id'], $ket);
    mysqli_stmt_execute($log);
}

session_unset();
session_destroy();
header("Location: login.php");
exit();
?>
