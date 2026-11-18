<?php
// ================================================
// KONEKSI DATABASE - XAMPP MySQL
// ================================================
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "db_kepegawaian";

// Kalau koneksi gagal, mysqli melempar exception (pesan jelas di layar)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/** @var mysqli $koneksi */
$koneksi = new mysqli($host, $user, $pass, $dbname);

// Set charset supaya karakter khusus (misal spasi, tanda petik) aman
$koneksi->set_charset("utf8mb4");

require_once __DIR__ . "/../includes/db.php";
