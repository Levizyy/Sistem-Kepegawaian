-- ================================================================
-- DATABASE : db_kepegawaian
-- Sistem Manajemen Tugas & Kinerja Karyawan
-- Cara pakai: phpMyAdmin -> tab "Import" -> pilih file ini -> Go
--             (atau tab SQL -> paste seluruh isi file ini -> Go)
-- Isi: 10 tabel (users, to_do_list, log_aktivitas, attendance,
--      leave_requests, work_schedules, notifications, kpi_targets,
--      kpi_scores, performance_reviews) + data contoh.
-- Semua password contoh sudah di-hash (bcrypt). Password asli untuk
-- login ada di README.txt.
-- CATATAN: file ini MENGHAPUS tabel lama dengan nama yang sama.
-- ================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `db_kepegawaian`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `db_kepegawaian`;

DROP TABLE IF EXISTS `performance_reviews`;
DROP TABLE IF EXISTS `kpi_scores`;
DROP TABLE IF EXISTS `kpi_targets`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `work_schedules`;
DROP TABLE IF EXISTS `leave_requests`;
DROP TABLE IF EXISTS `attendance`;
DROP TABLE IF EXISTS `log_aktivitas`;
DROP TABLE IF EXISTS `to_do_list`;
DROP TABLE IF EXISTS `users`;

-- ----------------------------------------------------------------
-- 1. USERS
-- ----------------------------------------------------------------
CREATE TABLE `users` (
  `id` VARCHAR(50) NOT NULL,
  `nama` VARCHAR(100) NOT NULL,
  `unit_kerja` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `nomor_hp` VARCHAR(20) NOT NULL,
  `role` ENUM('super_admin','admin','user') NOT NULL DEFAULT 'user',
  `password` VARCHAR(255) NOT NULL,
  `aktif` TINYINT(1) NOT NULL DEFAULT 1,
  `email_terverifikasi` TINYINT(1) NOT NULL DEFAULT 0,
  `token_verifikasi` VARCHAR(64) DEFAULT NULL,
  `token_reset` VARCHAR(64) DEFAULT NULL,
  `token_reset_kadaluarsa` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_users_email` (`email`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_token_verifikasi` (`token_verifikasi`),
  KEY `idx_users_token_reset` (`token_reset`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------
-- 2. TO DO LIST
-- ----------------------------------------------------------------
CREATE TABLE `to_do_list` (
  `nomor` INT(11) NOT NULL AUTO_INCREMENT,
  `pemilik` VARCHAR(50) NOT NULL,
  `tugas` VARCHAR(255) NOT NULL,
  `prioritas` ENUM('rendah','sedang','tinggi') NOT NULL DEFAULT 'sedang',
  `status` ENUM('belum_mulai','proses','selesai','tertunda') NOT NULL DEFAULT 'belum_mulai',
  `progres` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
  `tanggal_mulai` DATE NOT NULL,
  `tanggal_selesai` DATE DEFAULT NULL,
  `target_pencapaian` VARCHAR(255) NOT NULL,
  `rincian_data` TEXT DEFAULT NULL,
  `catatan_progres` TEXT DEFAULT NULL,
  `link_excel` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`nomor`),
  KEY `idx_todo_pemilik` (`pemilik`),
  KEY `idx_todo_status` (`status`),
  CONSTRAINT `fk_to_do_list_pemilik` FOREIGN KEY (`pemilik`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------
-- 3. ATTENDANCE (absensi)
-- ----------------------------------------------------------------
CREATE TABLE `attendance` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` VARCHAR(50) NOT NULL,
  `tanggal` DATE NOT NULL,
  `jam_masuk` TIME DEFAULT NULL,
  `jam_pulang` TIME DEFAULT NULL,
  `status` ENUM('hadir','izin','sakit','alpha','terlambat') NOT NULL DEFAULT 'hadir',
  `keterangan` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_attendance_user_tanggal` (`user_id`,`tanggal`),
  KEY `idx_attendance_tanggal` (`tanggal`),
  CONSTRAINT `fk_attendance_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------
-- 4. LEAVE REQUESTS (izin & cuti)
-- ----------------------------------------------------------------
CREATE TABLE `leave_requests` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` VARCHAR(50) NOT NULL,
  `jenis` ENUM('izin','sakit','cuti') NOT NULL,
  `tanggal_mulai` DATE NOT NULL,
  `tanggal_selesai` DATE NOT NULL,
  `alasan` TEXT NOT NULL,
  `status` ENUM('pending','disetujui','ditolak') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_leave_user` (`user_id`),
  KEY `idx_leave_status` (`status`),
  CONSTRAINT `fk_leave_requests_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------
-- 5. WORK SCHEDULES (jadwal kerja)
-- ----------------------------------------------------------------
CREATE TABLE `work_schedules` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` VARCHAR(50) NOT NULL,
  `hari` VARCHAR(20) NOT NULL,
  `jam_masuk` TIME NOT NULL,
  `jam_pulang` TIME NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_schedule_user` (`user_id`),
  CONSTRAINT `fk_work_schedules_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------
-- 6. NOTIFICATIONS
-- ----------------------------------------------------------------
CREATE TABLE `notifications` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` VARCHAR(50) NOT NULL,
  `judul` VARCHAR(150) NOT NULL,
  `pesan` TEXT NOT NULL,
  `tipe` ENUM('info','warning','success','danger') NOT NULL DEFAULT 'info',
  `sudah_dibaca` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notif_user` (`user_id`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------
-- 7. KPI TARGETS
-- ----------------------------------------------------------------
CREATE TABLE `kpi_targets` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` VARCHAR(50) NOT NULL,
  `periode` VARCHAR(20) NOT NULL,
  `target_nilai` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `target_deskripsi` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_kpi_target_user` (`user_id`),
  CONSTRAINT `fk_kpi_targets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------
-- 8. KPI SCORES
-- ----------------------------------------------------------------
CREATE TABLE `kpi_scores` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` VARCHAR(50) NOT NULL,
  `periode` VARCHAR(20) NOT NULL,
  `skor` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `kategori` ENUM('baik','cukup','kurang') NOT NULL DEFAULT 'cukup',
  `catatan` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_kpi_score_user` (`user_id`),
  CONSTRAINT `fk_kpi_scores_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------
-- 9. PERFORMANCE REVIEWS
-- ----------------------------------------------------------------
CREATE TABLE `performance_reviews` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` VARCHAR(50) NOT NULL,
  `reviewer_id` VARCHAR(50) NOT NULL,
  `periode` VARCHAR(20) NOT NULL,
  `hasil_evaluasi` TEXT NOT NULL,
  `rekomendasi` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_review_user` (`user_id`),
  KEY `idx_review_reviewer` (`reviewer_id`),
  CONSTRAINT `fk_performance_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_performance_reviews_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------
-- 10. LOG AKTIVITAS
-- ----------------------------------------------------------------
CREATE TABLE `log_aktivitas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` VARCHAR(50) NOT NULL,
  `aksi` ENUM('login','logout','tambah_user','edit_user','hapus_user','tambah_tugas','edit_tugas','hapus_tugas') NOT NULL,
  `keterangan` VARCHAR(255) DEFAULT NULL,
  `waktu` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_log_user` (`user_id`),
  KEY `idx_log_waktu` (`waktu`),
  CONSTRAINT `fk_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ================================================================
-- DATA CONTOH
-- ================================================================

-- USERS (20 akun, password sudah di-hash, email sudah terverifikasi)
INSERT INTO `users` (`id`,`nama`,`unit_kerja`,`email`,`nomor_hp`,`role`,`password`,`aktif`,`email_terverifikasi`) VALUES
('USR001','Budi Santoso','IT','budi.santoso@instansi.go.id','081234567890','super_admin','$2y$10$6AAuz2iSEv8P8tANLiPIFuAydiKcMTIRyXH64fl2uoRX6Ut3ywezK',1,1),
('USR002','Siti Aminah','Kepegawaian','siti.aminah@instansi.go.id','081234567891','admin','$2y$10$Mrxmb1PXaZNkxdq86f4dHuJt5SIBLDCz2CRG256NbAD9CkZmRhBQu',1,1),
('USR003','Andi Wijaya','Keuangan','andi.wijaya@instansi.go.id','081234567892','user','$2y$10$0bUNe65sWA39MbRWbPSKe.T.cJLrBGQS0M6tBSI/9QWZcfZFzUjbq',1,1),
('USR004','Rina Marlina','Umum','rina.marlina@instansi.go.id','081234567893','user','$2y$10$Km4Gcn1mIGKMfF/XiO/lP.YERsNVHSMPKYzmfTabWvfq0YQ5dfx2G',1,1),
('USR005','Doni Prasetyo','IT','doni.prasetyo@instansi.go.id','081234567894','admin','$2y$10$Jcy5yIJR9xq4AZx5fIf7p.1mXb5KUD3.MWaJSY3lo7XFslM..9QhW',0,1),
('USR006','Sri Wahyuni','Kepegawaian','sri.wahyuni@instansi.go.id','081234567895','user','$2y$10$y8wdDrf4iRBsFdRPmgzmuu..R3pVPvep.YQ0eLVzJAxldko1.i7q6',1,1),
('USR007','Agus Setiawan','Keuangan','agus.setiawan@instansi.go.id','081234567896','user','$2y$10$BKKH9lSC/z/THgD8ROKpM.kY5lP5d3wFg14AhAk8RWdZtY1ft58RK',1,1),
('USR008','Dewi Lestari','Umum','dewi.lestari@instansi.go.id','081234567897','user','$2y$10$RyC.TdkIFWruPN0kex4RYe7b..iBkNj7YHVy28uTRakj8bMdNGMGK',1,1),
('USR009','Hendra Gunawan','IT','hendra.gunawan@instansi.go.id','081234567898','admin','$2y$10$beoeMckGck.aPmTY6Oty9.YfKyAE3F6tz7oXb63.3Ym9/rV9vNTpy',1,1),
('USR010','Yuni Kartika','Kepegawaian','yuni.kartika@instansi.go.id','081234567899','user','$2y$10$vjhT9rr0tf93uhh.ndyPDe932evByJCLqxr12uDf0tjz./EELE9jG',1,1),
('USR011','Fajar Nugroho','Keuangan','fajar.nugroho@instansi.go.id','081234567800','user','$2y$10$FLToNmjnsQQJ8GcvGWBGu.75RFxI5JkDjMXhiHJsMKal7qFxiPQVq',1,1),
('USR012','Wulan Sari','Umum','wulan.sari@instansi.go.id','081234567801','user','$2y$10$jq0z.4gEwQJXfFqOgZl/S.QahBNnlU5rCU8Ru/JIqf8qeKOPGJ42u',0,1),
('USR013','Rudi Hartono','IT','rudi.hartono@instansi.go.id','081234567802','user','$2y$10$dknLie3DCsNDIvaZ3d2KL.3ENj/S6fJ.hBUeQQIDeHHic93qvvOVG',1,1),
('USR014','Nur Halimah','Kepegawaian','nur.halimah@instansi.go.id','081234567803','admin','$2y$10$efLphb/ZA0b3j.MHr4hfWuep.3esiMoGqg5jP60Csqh9UVC0aeWHW',1,1),
('USR015','Bambang Irawan','Keuangan','bambang.irawan@instansi.go.id','081234567804','user','$2y$10$.k9pu8k31wS1a74dfk5kx.ZGVuTxCihMPiOGOXCeBZ2XXW95qBRZW',1,1),
('USR016','Indah Permata','Umum','indah.permata@instansi.go.id','081234567805','user','$2y$10$r00xp4XBEIU0L4D5ALluBO1PwQSZ21mv5u7uYWyaNtyXy14dApc.O',1,1),
('USR017','Eko Purnomo','IT','eko.purnomo@instansi.go.id','081234567806','user','$2y$10$9Om3pCS5KZJ2fsNDGUWRx.0tPJdAJMGGleCPAxOnSOiSfA5ROfU3C',1,1),
('USR018','Lina Marlina','Kepegawaian','lina.marlina@instansi.go.id','081234567807','user','$2y$10$7wYl7aA57JrxmcZBiOn50OWQnCSHGy5GFRtOeaTxl4pisOiARig7i',0,1),
('USR019','Wahyu Ramadhan','Keuangan','wahyu.ramadhan@instansi.go.id','081234567808','user','$2y$10$U77xVXdD726yk.ENU5UpeOFy0JucWXWJmRvjPF9XxB4jkpkd5NTFm',1,1),
('USR020','Maya Anggraini','Umum','maya.anggraini@instansi.go.id','081234567809','user','$2y$10$SxfAh4bMLxR8CrO6bAzmqekEtkRmMgz4cAzHCFvTBbtqhnOAs5gOK',1,1);

-- TO DO LIST
INSERT INTO to_do_list (pemilik, tugas, prioritas, status, progres, tanggal_mulai, tanggal_selesai, target_pencapaian, rincian_data, catatan_progres) VALUES
('USR001', 'Audit data user tidak aktif', 'tinggi', 'proses', 60, '2026-06-01', NULL, 'Semua data user tidak aktif teridentifikasi', 'Mengecek user dengan status aktif=FALSE di semua unit kerja', 'Sudah 12 dari 20 user dicek'),
('USR002', 'Update data kontak unit Kepegawaian', 'sedang', 'selesai', 100, '2026-05-20', '2026-05-25', 'Data email & nomor HP terbaru', 'Konfirmasi ulang kontak ke tiap pegawai unit Kepegawaian', 'Selesai, semua data sudah diverifikasi'),
('USR005', 'Perbaikan bug login', 'tinggi', 'tertunda', 30, '2026-06-10', NULL, 'Bug login teratasi', 'User tidak bisa login setelah reset password', 'Menunggu akses server dari tim IT'),
('USR009', 'Migrasi data ke server baru', 'tinggi', 'proses', 45, '2026-06-15', NULL, 'Semua data tersalin ke server baru tanpa error', 'Migrasi tabel users dan log_aktivitas', 'Tabel users sudah selesai, log_aktivitas masih proses'),
('USR014', 'Buat laporan bulanan kepegawaian', 'sedang', 'belum_mulai', 0, '2026-07-01', NULL, 'Laporan bulan Juni selesai dan terkirim', 'Rekap data user aktif, cuti, dan absensi bulan Juni', NULL),
('USR003', 'Rekap absensi bulanan', 'sedang', 'proses', 50, '2026-06-05', NULL, 'Rekap absensi Juni selesai', 'Mengumpulkan data absensi dari tiap unit kerja', 'Data 2 unit sudah masuk, menunggu 2 unit lagi'),
('USR004', 'Verifikasi data cuti pegawai', 'rendah', 'selesai', 100, '2026-05-15', '2026-05-18', 'Semua pengajuan cuti terverifikasi', 'Cek kesesuaian jumlah cuti dengan sisa kuota', 'Selesai tanpa kendala'),
('USR006', 'Update foto profil pegawai', 'rendah', 'belum_mulai', 0, '2026-07-05', NULL, 'Semua foto profil terupdate', 'Kumpulkan foto terbaru dari tiap pegawai', NULL),
('USR007', 'Cek anggaran unit Keuangan', 'tinggi', 'proses', 70, '2026-06-08', NULL, 'Laporan anggaran triwulan selesai', 'Rekonsiliasi data pengeluaran dan pemasukan', 'Tinggal validasi akhir dari kepala unit'),
('USR008', 'Susun jadwal rapat bulanan', 'sedang', 'selesai', 100, '2026-05-25', '2026-05-27', 'Jadwal rapat Juni tersusun', 'Koordinasi jadwal dengan semua kepala unit', 'Jadwal sudah disebar ke semua unit'),
('USR010', 'Input data pegawai baru', 'tinggi', 'proses', 80, '2026-06-12', NULL, 'Semua data pegawai baru terinput', 'Input data 5 pegawai baru unit Kepegawaian', 'Tinggal 1 pegawai lagi yang belum lengkap datanya'),
('USR011', 'Cek ulang laporan pajak', 'tinggi', 'tertunda', 20, '2026-06-20', NULL, 'Laporan pajak sesuai dan terkirim', 'Verifikasi perhitungan pajak triwulan', 'Menunggu data tambahan dari bendahara'),
('USR012', 'Bersihkan data user duplikat', 'sedang', 'belum_mulai', 0, '2026-07-03', NULL, 'Tidak ada lagi data user ganda', 'Cek email/nomor HP yang sama di beberapa akun', NULL),
('USR013', 'Setup akses printer unit IT', 'rendah', 'selesai', 100, '2026-05-10', '2026-05-11', 'Semua komputer bisa akses printer', 'Instalasi driver dan setting jaringan printer', 'Selesai, sudah dites di 5 komputer'),
('USR015', 'Rekap pengajuan lembur', 'sedang', 'proses', 40, '2026-06-18', NULL, 'Data lembur bulan Juni lengkap', 'Kumpulkan form lembur dari unit Keuangan', 'Baru terkumpul separuh dari total pegawai'),
('USR016', 'Update SOP layanan umum', 'rendah', 'proses', 55, '2026-06-14', NULL, 'SOP baru siap diterapkan', 'Revisi SOP sesuai kebijakan terbaru', 'Draft sudah dibuat, menunggu review atasan'),
('USR017', 'Cek jaringan internet kantor', 'tinggi', 'selesai', 100, '2026-05-28', '2026-05-29', 'Koneksi internet stabil di semua ruangan', 'Pengecekan kabel dan router tiap lantai', 'Selesai, kecepatan sudah normal kembali'),
('USR018', 'Susun data pegawai pensiun', 'sedang', 'belum_mulai', 0, '2026-07-10', NULL, 'Data pegawai pensiun tahun ini lengkap', 'Kumpulkan data pegawai yang akan pensiun 2026', NULL),
('USR019', 'Cek ulang data rekening gaji', 'tinggi', 'proses', 65, '2026-06-16', NULL, 'Semua rekening gaji terverifikasi aktif', 'Konfirmasi rekening ke bank terkait', 'Sudah verifikasi 13 dari 20 rekening'),
('USR020', 'Buat template surat tugas', 'rendah', 'selesai', 100, '2026-05-05', '2026-05-06', 'Template surat tugas siap dipakai', 'Desain format surat sesuai standar instansi', 'Sudah disetujui dan dipakai unit Umum');

-- LOG AKTIVITAS
INSERT INTO log_aktivitas (user_id, aksi, keterangan) VALUES
('USR001', 'login', 'Budi Santoso (Super Admin) login ke sistem'),
('USR001', 'tambah_user', 'Menambahkan user baru: Maya Anggraini'),
('USR002', 'login', 'Siti Aminah (Admin) login ke sistem'),
('USR002', 'edit_user', 'Mengubah data user: Andi Wijaya'),
('USR005', 'login', 'Doni Prasetyo (Admin) login ke sistem'),
('USR002', 'hapus_user', 'Menonaktifkan user: Doni Prasetyo'),
('USR001', 'logout', 'Budi Santoso logout dari sistem'),
('USR009', 'login', 'Hendra Gunawan (Admin) login ke sistem'),
('USR009', 'tambah_user', 'Menambahkan user baru: Wahyu Ramadhan'),
('USR014', 'login', 'Nur Halimah (Admin) login ke sistem'),
('USR014', 'edit_user', 'Mengubah data user: Wulan Sari'),
('USR014', 'hapus_user', 'Menonaktifkan user: Wulan Sari'),
('USR009', 'logout', 'Hendra Gunawan logout dari sistem'),
('USR002', 'login', 'Siti Aminah (Admin) login ke sistem'),
('USR002', 'edit_user', 'Mengubah data user: Lina Marlina'),
('USR002', 'hapus_user', 'Menonaktifkan user: Lina Marlina'),
('USR002', 'logout', 'Siti Aminah logout dari sistem'),
('USR014', 'logout', 'Nur Halimah logout dari sistem');

-- ATTENDANCE (contoh data bulan November 2026)
INSERT INTO `attendance` (`user_id`,`tanggal`,`jam_masuk`,`jam_pulang`,`status`,`keterangan`) VALUES
('USR001','2026-11-16','07:55:00','16:05:00','hadir','Check-in via web absensi'),
('USR001','2026-11-17','08:02:00','16:10:00','hadir','Check-in via web absensi'),
('USR002','2026-11-16','08:10:00','16:00:00','hadir','Check-in via web absensi'),
('USR002','2026-11-17','09:25:00','16:30:00','terlambat','Check-in via web absensi'),
('USR003','2026-11-16','08:00:00','16:00:00','hadir','Check-in via web absensi'),
('USR003','2026-11-17',NULL,NULL,'sakit','Sakit, surat dokter menyusul'),
('USR004','2026-11-16','08:45:00','16:15:00','hadir','Check-in via web absensi'),
('USR004','2026-11-17','09:40:00','16:20:00','terlambat','Check-in via web absensi'),
('USR006','2026-11-16',NULL,NULL,'izin','Keperluan keluarga'),
('USR007','2026-11-16','08:05:00','16:00:00','hadir','Check-in via web absensi'),
('USR007','2026-11-17','08:00:00','16:05:00','hadir','Check-in via web absensi'),
('USR008','2026-11-16',NULL,NULL,'alpha','Tidak ada keterangan'),
('USR009','2026-11-16','07:50:00','17:00:00','hadir','Check-in via web absensi'),
('USR010','2026-11-17','08:30:00','16:00:00','hadir','Check-in via web absensi');

-- LEAVE REQUESTS
INSERT INTO `leave_requests` (`user_id`,`jenis`,`tanggal_mulai`,`tanggal_selesai`,`alasan`,`status`) VALUES
('USR003','sakit','2026-11-17','2026-11-18','Demam dan perlu istirahat','disetujui'),
('USR006','izin','2026-11-16','2026-11-16','Keperluan keluarga','disetujui'),
('USR010','cuti','2026-12-22','2026-12-26','Cuti akhir tahun','pending'),
('USR015','cuti','2026-12-01','2026-12-03','Acara keluarga di luar kota','pending'),
('USR016','izin','2026-11-20','2026-11-20','Mengurus dokumen di kantor pajak','ditolak');

-- WORK SCHEDULES
INSERT INTO `work_schedules` (`user_id`,`hari`,`jam_masuk`,`jam_pulang`) VALUES
('USR001','Senin','08:00:00','16:00:00'),
('USR001','Selasa','08:00:00','16:00:00'),
('USR001','Rabu','08:00:00','16:00:00'),
('USR001','Kamis','08:00:00','16:00:00'),
('USR001','Jumat','08:00:00','16:30:00'),
('USR003','Senin','08:00:00','16:00:00'),
('USR003','Selasa','08:00:00','16:00:00'),
('USR003','Rabu','08:00:00','16:00:00'),
('USR003','Kamis','08:00:00','16:00:00'),
('USR003','Jumat','08:00:00','16:30:00');

-- NOTIFICATIONS
INSERT INTO `notifications` (`user_id`,`judul`,`pesan`,`tipe`,`sudah_dibaca`) VALUES
('USR001','Selamat datang','Selamat datang di Sistem Manajemen Tugas dan Kinerja Karyawan.','info',0),
('USR002','Pengajuan cuti baru','Ada 2 pengajuan izin/cuti yang menunggu persetujuan.','warning',0),
('USR003','Izin disetujui','Pengajuan sakit kamu tanggal 17-18 November 2026 telah disetujui.','success',1),
('USR003','Tugas mendekati tenggat','Tugas "Rekap absensi bulanan" masih berstatus proses.','warning',0),
('USR016','Izin ditolak','Pengajuan izin tanggal 20 November 2026 ditolak oleh admin.','danger',0);

-- KPI TARGETS
INSERT INTO `kpi_targets` (`user_id`,`periode`,`target_nilai`,`target_deskripsi`) VALUES
('USR003','2026-11',90.00,'Rekap absensi dan laporan keuangan unit selesai tepat waktu'),
('USR007','2026-11',85.00,'Laporan anggaran triwulan tervalidasi tanpa revisi'),
('USR010','2026-11',80.00,'Seluruh data pegawai baru terinput lengkap'),
('USR013','2026-11',85.00,'Gangguan jaringan dan perangkat IT teratasi maksimal 1 hari');

-- KPI SCORES
INSERT INTO `kpi_scores` (`user_id`,`periode`,`skor`,`kategori`,`catatan`) VALUES
('USR003','2026-10',88.50,'baik','Pekerjaan rapi dan tepat waktu'),
('USR007','2026-10',76.00,'cukup','Perlu meningkatkan ketelitian laporan'),
('USR010','2026-10',92.00,'baik','Input data cepat dan akurat'),
('USR016','2026-10',58.00,'kurang','Beberapa tugas terlambat diselesaikan');

-- PERFORMANCE REVIEWS
INSERT INTO `performance_reviews` (`user_id`,`reviewer_id`,`periode`,`hasil_evaluasi`,`rekomendasi`) VALUES
('USR003','USR002','2026-10','Kinerja stabil dan konsisten sepanjang bulan Oktober.','Pertahankan dan ambil tanggung jawab tambahan.'),
('USR016','USR002','2026-10','Beberapa tugas melewati tenggat tanpa pemberitahuan.','Ikuti pendampingan manajemen waktu dan laporkan kendala lebih awal.');

SET FOREIGN_KEY_CHECKS = 1;
