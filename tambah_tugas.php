<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once "includes/cek_login.php";
require_once "config/koneksi.php";

$role = $_SESSION['role'];
$userId = $_SESSION['id'];
$error = "";

// Admin & Super Admin bisa menugaskan ke pegawai lain, User biasa hanya untuk dirinya sendiri
$daftarPegawai = [];
if ($role === 'super_admin' || $role === 'admin') {
    $q = db_query($koneksi, "SELECT id, nama FROM users ORDER BY nama ASC");
    while ($r = mysqli_fetch_assoc($q)) { $daftarPegawai[] = $r; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pemilik = ($role === 'super_admin' || $role === 'admin') ? trim($_POST['pemilik']) : $userId;
    $tugas = trim($_POST['tugas']);
    $prioritas = $_POST['prioritas'];
    $status = $_POST['status'];
    $progres = (int)$_POST['progres'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = !empty($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : null;
    $target_pencapaian = trim($_POST['target_pencapaian']);
    $rincian_data = trim($_POST['rincian_data']);
    $catatan_progres = trim($_POST['catatan_progres']);
    $link_excel = trim($_POST['link_excel']);

    // Kalau ada file yang diupload, prioritaskan file itu daripada link manual
    if (isset($_FILES['file_excel']) && $_FILES['file_excel']['error'] === UPLOAD_ERR_OK) {
        $ekstensiDiizinkan = ['xlsx', 'xls', 'csv'];
        $namaAsli = $_FILES['file_excel']['name'];
        $ekstensi = strtolower(pathinfo($namaAsli, PATHINFO_EXTENSION));

        if (in_array($ekstensi, $ekstensiDiizinkan)) {
            $namaUnik = uniqid('tugas_') . '.' . $ekstensi;
            $tujuan = __DIR__ . '/uploads/tugas/' . $namaUnik;
            if (move_uploaded_file($_FILES['file_excel']['tmp_name'], $tujuan)) {
                $link_excel = 'uploads/tugas/' . $namaUnik;
            }
        } else {
            $error = "File harus berformat .xlsx, .xls, atau .csv.";
        }
    }

    if (empty($tugas) || empty($tanggal_mulai) || empty($target_pencapaian)) {
        $error = "Tugas, Tanggal Mulai, dan Target Pencapaian wajib diisi.";
    } elseif ($progres < 0 || $progres > 100) {
        $error = "Progres harus di antara 0 - 100.";
    } elseif (empty($error)) {
        $sql = "INSERT INTO to_do_list (pemilik, tugas, prioritas, status, progres, tanggal_mulai, tanggal_selesai, target_pencapaian, rincian_data, catatan_progres, link_excel) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = db_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, "ssssissssss", $pemilik, $tugas, $prioritas, $status, $progres, $tanggal_mulai, $tanggal_selesai, $target_pencapaian, $rincian_data, $catatan_progres, $link_excel);

        if (mysqli_stmt_execute($stmt)) {
            $log = db_prepare($koneksi, "INSERT INTO log_aktivitas (user_id, aksi, keterangan) VALUES (?, 'tambah_tugas', ?)");
            $ket = $_SESSION['nama'] . " menambahkan tugas: " . $tugas;
            mysqli_stmt_bind_param($log, "ss", $userId, $ket);
            mysqli_stmt_execute($log);

            header("Location: to_do_list.php?sukses=tambah");
            exit();
        } else {
            $error = "Gagal menyimpan tugas: " . mysqli_error($koneksi);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Tugas - Sistem Kepegawaian</title>
    <script>
        (function () {
            var tema = localStorage.getItem('sik_theme') || 'light';
            document.documentElement.setAttribute('data-theme', tema);
        })();
    </script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="app-shell">
    <?php include 'includes/sidebar.php'; ?>
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
    <div class="main-area">
        <div class="topbar">
            <button class="hamburger-btn" onclick="toggleSidebar()" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg></button>
            <div class="topbar-title">Tambah Tugas</div>
            <div class="topbar-right">
                <button class="theme-toggle" onclick="toggleTheme()" type="button">
                    <span class="theme-toggle-icon"></span><span class="theme-toggle-label">Gelap</span>
                </button>
            </div>
        </div>
<div class="container">
        <a href="to_do_list.php" class="back-link">&larr; Kembali ke To Do List</a>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="form-box">
            <h2 style="margin-bottom:20px;">Tambah Tugas Baru</h2>
            <form method="POST" action="tambah_tugas.php" enctype="multipart/form-data">
                <?php if ($role === 'super_admin' || $role === 'admin'): ?>
                <div class="form-row">
                    <label>Pemilik Tugas</label>
                    <select name="pemilik" required>
                        <?php foreach ($daftarPegawai as $p): ?>
                            <option value="<?= htmlspecialchars($p['id']) ?>" <?= $p['id'] === $userId ? 'selected' : '' ?>><?= htmlspecialchars($p['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="form-row">
                    <label>Nama Tugas</label>
                    <input type="text" name="tugas" required>
                </div>
                <div class="form-row">
                    <label>Prioritas</label>
                    <select name="prioritas" required>
                        <option value="rendah">Rendah</option>
                        <option value="sedang" selected>Sedang</option>
                        <option value="tinggi">Tinggi</option>
                    </select>
                </div>
                <div class="form-row">
                    <label>Status</label>
                    <select name="status" required>
                        <option value="belum_mulai" selected>Belum Mulai</option>
                        <option value="proses">Proses</option>
                        <option value="selesai">Selesai</option>
                        <option value="tertunda">Tertunda</option>
                    </select>
                </div>
                <div class="form-row">
                    <label>Progres (%)</label>
                    <input type="number" name="progres" min="0" max="100" value="0" required>
                </div>
                <div class="form-row">
                    <label>Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" required>
                </div>
                <div class="form-row">
                    <label>Tanggal Selesai (opsional)</label>
                    <input type="date" name="tanggal_selesai">
                </div>
                <div class="form-row">
                    <label>Target Pencapaian</label>
                    <input type="text" name="target_pencapaian" required>
                </div>
                <div class="form-row">
                    <label>Rincian Data</label>
                    <input type="text" name="rincian_data">
                </div>
                <div class="form-row">
                    <label>Catatan/Update Progres</label>
                    <input type="text" name="catatan_progres">
                </div>
                <div class="form-row">
                    <label>Link File External (opsional)</label>
                    <input type="url" name="link_excel" placeholder="https://docs.google.com/spreadsheets/...">
                    <small style="color:var(--text-muted); font-size:11.5px;">Isi salah satu: link online, atau upload file di bawah</small>
                </div>
                <div class="form-row">
                    <label>Atau Upload File Excel (opsional)</label>
                    <input type="file" name="file_excel" accept=".xlsx,.xls,.csv">
                    <small style="color:var(--text-muted); font-size:11.5px;">Format .xlsx, .xls, atau .csv</small>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Simpan</button>
                    <a href="to_do_list.php" class="btn-cancel">Batal</a>
                </div>
            </form>
        </div>
    </div>
    </div>
</div>
    <script src="assets/js/theme.js"></script>
</body>
</html>
