<?php
/**
 * CAPTCHA sederhana berupa soal penjumlahan.
 * Jawaban disimpan di session, dicocokkan saat form disubmit.
 */
function buatCaptcha() {
    $a = rand(1, 10);
    $b = rand(1, 10);
    $_SESSION['captcha_jawaban'] = $a + $b;
    return "$a + $b";
}

function cekCaptcha($jawabanUser) {
    $benar = isset($_SESSION['captcha_jawaban']) && (int)$jawabanUser === (int)$_SESSION['captcha_jawaban'];
    unset($_SESSION['captcha_jawaban']); // sekali pakai, biar tidak ditebak berulang
    return $benar;
}
