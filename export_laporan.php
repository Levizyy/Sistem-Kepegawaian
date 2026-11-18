<?php
require_once "includes/cek_login.php";
require_once "config/koneksi.php";

if ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

$namaFile = "laporan_ringkas_" . date("Y-m-d") . ".xls";
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$namaFile\"");
header("Pragma: no-cache");
header("Expires: 0");

echo "\xEF\xBB\xBF";

$totalUser = db_fetch(db_query($koneksi, "SELECT COUNT(*) AS jml FROM users"))['jml'];
$userAktif = db_fetch(db_query($koneksi, "SELECT COUNT(*) AS jml FROM users WHERE aktif = 1"))['jml'];
$totalTugas = db_fetch(db_query($koneksi, "SELECT COUNT(*) AS jml FROM to_do_list"))['jml'];
$tugasSelesai = db_fetch(db_query($koneksi, "SELECT COUNT(*) AS jml FROM to_do_list WHERE status = 'selesai'"))['jml'];
$izinPending = db_fetch(db_query($koneksi, "SELECT COUNT(*) AS jml FROM leave_requests WHERE status = 'pending'"))['jml'];
$absensiBulanIni = db_fetch(db_query($koneksi, "SELECT COUNT(*) AS jml FROM attendance WHERE DATE_FORMAT(tanggal, '%Y-%m') = '" . date('Y-m') . "'"))['jml'];
?>
<table border="1">
    <tr><th>Judul</th><th>Nilai</th></tr>
    <tr><td>Total Pegawai</td><td><?= (int)$totalUser ?></td></tr>
    <tr><td>Pegawai Aktif</td><td><?= (int)$userAktif ?></td></tr>
    <tr><td>Total Tugas</td><td><?= (int)$totalTugas ?></td></tr>
    <tr><td>Tugas Selesai</td><td><?= (int)$tugasSelesai ?></td></tr>
    <tr><td>Absensi Bulan Ini</td><td><?= (int)$absensiBulanIni ?></td></tr>
    <tr><td>Izin Pending</td><td><?= (int)$izinPending ?></td></tr>
</table>
