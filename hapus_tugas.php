<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once "includes/cek_login.php";
require_once "config/koneksi.php";

$role = $_SESSION['role'];
$userId = $_SESSION['id'];
$nomor = (int)($_GET['nomor'] ?? 0);

$stmt = db_prepare($koneksi, "SELECT * FROM to_do_list WHERE nomor = ?");
mysqli_stmt_bind_param($stmt, "i", $nomor);
mysqli_stmt_execute($stmt);
$result = db_result($stmt);
$tugas = db_fetch($result);

if ($tugas) {
    $boleh_akses = ($role === 'super_admin' || $role === 'admin' || $tugas['pemilik'] === $userId);
    if ($boleh_akses) {
        $hapus = db_prepare($koneksi, "DELETE FROM to_do_list WHERE nomor = ?");
        mysqli_stmt_bind_param($hapus, "i", $nomor);

        if (mysqli_stmt_execute($hapus)) {
            // Hapus file excel terkait kalau itu file upload lokal (bukan link eksternal)
            $fileLama = $tugas['link_excel'] ?? '';
            if (!empty($fileLama) && strpos($fileLama, 'uploads/tugas/') === 0) {
                $pathLama = __DIR__ . '/' . $fileLama;
                if (file_exists($pathLama)) {
                    unlink($pathLama);
                }
            }

            $log = db_prepare($koneksi, "INSERT INTO log_aktivitas (user_id, aksi, keterangan) VALUES (?, 'hapus_tugas', ?)");
            $ket = $_SESSION['nama'] . " menghapus tugas: " . $tugas['tugas'];
            mysqli_stmt_bind_param($log, "ss", $userId, $ket);
            mysqli_stmt_execute($log);
        }
    }
}

header("Location: to_do_list.php?sukses=hapus");
exit();
?>
