====================================================
SISTEM LOGIN & KELOLA USER - SISTEM KEPEGAWAIAN
====================================================

CARA MENJALANKAN DI XAMPP:

1. Copy folder "sistem_kepegawaian" ke:
   C:\xampp\htdocs\sistem_kepegawaian

2. Buka XAMPP Control Panel, jalankan (Start) Apache dan MySQL.

3. Buka browser, akses phpMyAdmin:
   http://localhost/phpmyadmin

4. Buat database dan tabel:
   - Buka menu SQL di phpMyAdmin
   - Jalankan isi file: database/db_kepegawaian.sql
   (1 file ini berisi SEMUA 10 tabel: users, to_do_list, log_aktivitas,
   attendance, leave_requests, work_schedules, notifications,
   kpi_targets, kpi_scores, performance_reviews + data contoh)

5. Buka browser, akses aplikasi:
   http://localhost/sistem_kepegawaian/login.php

====================================================
KONFIGURASI DATABASE
====================================================
File: config/koneksi.php

Host     : localhost
User     : root
Password : (kosong, default XAMPP)
Database : db_kepegawaian

Jika MySQL Anda memakai password, isi baris $pass di config/koneksi.php.

====================================================
AKUN UNTUK LOGIN (contoh, sesuai data di database)
====================================================
Role         | Email                          | Password
-------------|--------------------------------|----------------------
Super Admin  | budi.santoso@instansi.go.id    | superadmin_budi2026
Admin        | siti.aminah@instansi.go.id     | admin_siti2026
User         | andi.wijaya@instansi.go.id     | user_andi2026

====================================================
FITUR
====================================================
- Login dengan validasi email & password ke tabel users
- Deteksi status akun aktif/nonaktif
- Dashboard menampilkan menu berbeda sesuai role
- Menu "Kelola User" HANYA muncul untuk role: super_admin & admin
  > Tambah user baru
  > Ubah data user
  > Hapus user
- Role "user" hanya melihat menu dasar (tanpa akses kelola user)
- Setiap aksi (login, logout, tambah, ubah, hapus) otomatis
  tercatat ke tabel log_aktivitas

====================================================
STRUKTUR FOLDER
====================================================
sistem_kepegawaian/
|-- config/
|   |-- koneksi.php          -> koneksi ke database MySQL
|-- includes/
|   |-- cek_login.php        -> proteksi halaman, wajib login
|-- assets/
|   |-- css/style.css        -> semua styling UI
|-- database/
|   |-- db_kepegawaian.sql   -> SEMUA tabel: users, to_do_list, log_aktivitas
|-- login.php                -> halaman login
|-- dashboard.php            -> halaman utama setelah login
|-- kelola_user.php          -> daftar user (khusus admin/super_admin)
|-- tambah_user.php          -> form tambah user
|-- ubah_user.php            -> form edit user
|-- hapus_user.php           -> proses hapus user
|-- logout.php                -> proses logout

====================================================
VALIDASI FORMAT INPUT
====================================================
- Email harus format valid (nama@domain.com)
- Nomor HP hanya boleh angka 9-15 digit, boleh diawali +
- Password minimal 6 karakter
- ID user hanya boleh huruf, angka, - dan _, minimal 3 karakter
Validasi berjalan di browser (langsung) dan di server (PHP),
jadi tetap aman meski JavaScript/HTML5 validation dilewati.

====================================================
HALAMAN 404 CUSTOM
====================================================
Kalau salah ketik URL, otomatis diarahkan ke halaman 404
yang didesain sesuai tema aplikasi (bukan tampilan
"Not Found" bawaan Apache).

CATATAN: kalau halaman 404 custom TIDAK otomatis muncul
(masih tampil "Not Found" bawaan Apache), berarti XAMPP
kamu belum mengizinkan file .htaccess. Perbaikannya:
1. Buka file: C:\xampp\apache\conf\httpd.conf
2. Cari baris "AllowOverride None" di bagian <Directory "C:/xampp/htdocs">
3. Ubah jadi "AllowOverride All"
4. Simpan, lalu restart Apache di XAMPP Control Panel

====================================================
UPLOAD FILE EXCEL - CATATAN PENTING
====================================================
Kode aplikasi TIDAK membatasi ukuran file upload.
Tapi PHP/XAMPP punya batas bawaan sendiri di file php.ini:
  - upload_max_filesize (default: 2MB)
  - post_max_size (default: 8MB)

Kalau upload file besar gagal, naikkan batas ini:
1. Buka file: C:\xampp\php\php.ini
2. Cari baris "upload_max_filesize" dan "post_max_size"
3. Ubah nilainya, misal:
   upload_max_filesize = 100M
   post_max_size = 100M
4. Simpan file, lalu restart Apache di XAMPP Control Panel

====================================================
FITUR BARU: REGISTER, VERIFIKASI EMAIL, LUPA PASSWORD
====================================================

1. WAJIB SETUP DULU sebelum email bisa terkirim:
   Buka file: config/pengaturan.php
   Ganti baris ini dengan akun Gmail kamu sendiri:
     define('SMTP_USERNAME', 'emailkamu@gmail.com');
     define('SMTP_PASSWORD', 'xxxx xxxx xxxx xxxx');

   SMTP_PASSWORD BUKAN password Gmail biasa, tapi "App Password" 16 karakter.
   Cara buat App Password:
     1. Aktifkan verifikasi 2 langkah di akun Google kamu
     2. Buka: myaccount.google.com/apppasswords
     3. Buat App Password baru, copy 16 karakternya ke config/pengaturan.php

2. UPDATE DATABASE:
   Semua sudah digabung jadi 1 file: database/db_kepegawaian.sql
   Tinggal jalankan file itu di phpMyAdmin (akan hapus tabel lama & buat ulang dari nol).
   Bagian reset password (opsional) ada di paling bawah file itu, dalam bentuk
   komentar (diawali "--"), tinggal hapus tanda "--" di baris yang mau dijalankan.

3. PENGATURAN MODE REGISTRASI (di config/pengaturan.php):
   - REGISTRASI_LANGSUNG_AKTIF: 
     true  = akun baru langsung bisa login setelah verifikasi email
     false = akun baru perlu di-approve manual dulu di menu Kelola User
             (checklist "Aktifkan akun" di halaman Ubah User)
   - WAJIB_VERIFIKASI_EMAIL:
     true  = user harus klik link di email dulu sebelum bisa login
     false = tidak perlu verifikasi email sama sekali

4. HALAMAN BARU:
   - register.php         -> Form daftar akun baru (role otomatis: user)
   - verifikasi_email.php  -> Diakses dari link di email verifikasi
   - lupa_password.php     -> Form minta link reset password
   - reset_password.php    -> Diakses dari link di email reset password
   - ubah_password.php     -> Ganti password (harus login dulu, minta password lama)

5. CAPTCHA:
   Sudah otomatis aktif di halaman Login, Register, dan Lupa Password.
   Berupa soal matematika sederhana (bukan Google reCAPTCHA), tidak perlu API key.

6. TESTING TANPA GMAIL DULU:
   Kalau belum sempat setup Gmail App Password, kamu tetap bisa coba fitur Register-nya:
   akun akan tetap kebuat di database, cuma nanti muncul pesan "email verifikasi gagal
   terkirim" karena SMTP belum dikonfigurasi. Untuk testing cepat, kamu bisa juga
   set WAJIB_VERIFIKASI_EMAIL jadi false dulu di config/pengaturan.php.
