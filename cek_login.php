<?php
session_start();

// Kalau belum login, tendang ke halaman login
if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}
?>
