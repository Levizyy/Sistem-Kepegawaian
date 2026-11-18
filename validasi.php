<?php
/**
 * Kumpulan fungsi validasi format input.
 * Mengembalikan string pesan error, atau "" (kosong) kalau valid.
 */

function validasiEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Format email tidak valid.";
    }
    return "";
}

function validasiNomorHp($nomor_hp) {
    // Hanya boleh angka, dan boleh diawali tanda +
    if (!preg_match('/^\+?[0-9]{9,15}$/', $nomor_hp)) {
        return "Nomor HP harus berupa angka (9-15 digit), boleh diawali tanda +.";
    }
    return "";
}

function validasiPassword($password, $minPanjang = 6) {
    if (strlen($password) < $minPanjang) {
        return "Password minimal {$minPanjang} karakter.";
    }
    return "";
}

function validasiId($id) {
    // Hanya huruf, angka, dan underscore/dash
    if (!preg_match('/^[A-Za-z0-9_-]{3,50}$/', $id)) {
        return "ID hanya boleh huruf, angka, tanda - atau _, minimal 3 karakter.";
    }
    return "";
}
?>
