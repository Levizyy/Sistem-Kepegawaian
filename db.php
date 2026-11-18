<?php
/**
 * Fungsi bantu database dengan tipe kembalian yang pasti.
 * Gagal -> melempar exception (tidak pernah mengembalikan false),
 * sehingga kode di halaman tidak perlu cek false dan editor tidak menandai merah.
 */

function db_prepare(mysqli $koneksi, string $sql): mysqli_stmt
{
    $stmt = mysqli_prepare($koneksi, $sql);
    if ($stmt === false) {
        throw new RuntimeException("Query gagal disiapkan: " . mysqli_error($koneksi));
    }
    return $stmt;
}

function db_query(mysqli $koneksi, string $sql): mysqli_result
{
    $hasil = mysqli_query($koneksi, $sql);
    if (!($hasil instanceof mysqli_result)) {
        throw new RuntimeException("Query gagal dijalankan: " . mysqli_error($koneksi));
    }
    return $hasil;
}

function db_result(mysqli_stmt $stmt): mysqli_result
{
    $hasil = mysqli_stmt_get_result($stmt);
    if ($hasil === false) {
        throw new RuntimeException("Gagal mengambil hasil query: " . mysqli_stmt_error($stmt));
    }
    return $hasil;
}

/** Ambil 1 baris sebagai array. Kalau tidak ada baris -> array kosong (bernilai false di if). */
function db_fetch(mysqli_result $hasil): array
{
    $baris = mysqli_fetch_assoc($hasil);
    return is_array($baris) ? $baris : [];
}
