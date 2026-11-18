<?php
// ================================================
// PENGATURAN SISTEM
// ================================================

// --- MODE REGISTRASI ---
// true  = akun baru langsung aktif & bisa login setelah verifikasi email
// false = akun baru perlu di-approve dulu oleh admin di menu Kelola User
define('REGISTRASI_LANGSUNG_AKTIF', false);

// true  = wajib klik link verifikasi di email dulu sebelum bisa login
// false = tidak perlu verifikasi email
define('WAJIB_VERIFIKASI_EMAIL', true);

// --- PENGATURAN SMTP (untuk kirim email) ---
// Gunakan akun Gmail + App Password (BUKAN password Gmail biasa).
// Cara buat App Password: myaccount.google.com/apppasswords
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'emailkamu@gmail.com');   // <-- GANTI dengan email Gmail kamu
define('SMTP_PASSWORD', 'xxxx xxxx xxxx xxxx');    // <-- GANTI dengan App Password (16 karakter)
define('SMTP_FROM_NAME', 'Sistem Informasi Kepegawaian');

// --- URL DASAR APLIKASI ---
// Dipakai untuk membuat link di email (verifikasi & reset password)
define('URL_APLIKASI', 'http://localhost/sistem_kepegawaian');
