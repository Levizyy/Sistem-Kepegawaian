<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once "includes/cek_login.php";
require_once "config/koneksi.php";

$role = $_SESSION['role'];
$userId = $_SESSION['id'];
$nomor = (int)($_GET['nomor'] ?? 0);
$error = "";

$stmt = db_prepare($koneksi, "SELECT * FROM to_do_list WHERE nomor = ?");
mysqli_stmt_bind_param($stmt, "i", $nomor);
mysqli_stmt_execute($stmt);
$result = db_result($stmt);
$tugas = db_fetch($result);

if (!$tugas) {
    header("Location: to_do_list.php");
    exit();
}

// Proteksi: hanya pemilik tugas atau admin/super_admin yang boleh ubah
$boleh_akses = ($role === 'super_admin' || $role === 'admin' || $tugas['pemilik'] === $userId);
if (!$boleh_akses) {
    header("Location: to_do_list.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_tugas = trim($_POST['tugas']);
    $prioritas = $_POST['prioritas'];
    $status = $_POST['status'];
    $progres = (int)$_POST['progres'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = !empty($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : null;
    $target_pencapaian = trim($_POST['target_pencapaian']);
    $rincian_data = trim($_POST['rincian_data']);
    $catatan_progres = trim($_POST['catatan_progres']);
    $link_excel = trim($_POST['link_excel']);

    // Kalau ada file baru diupload, prioritaskan itu.
    // Kalau tidak ada file baru & link_excel dikosongkan, pertahankan nilai lama.
    if (isset($_FILES['file_excel']) && $_FILES['file_excel']['error'] === UPLOAD_ERR_OK) {
        $ekstensiDiizinkan = ['xlsx', 'xls', 'csv'];
        $namaAsli = $_FILES['file_excel']['name'];
        $ekstensi = strtolower(pathinfo($namaAsli, PATHINFO_EXTENSION));

        if (in_array($ekstensi, $ekstensiDiizinkan)) {
            $namaUnik = uniqid('tugas_') . '.' . $ekstensi;
            $tujuan = __DIR__ . '/uploads/tugas/' . $namaUnik;
            if (move_uploaded_file($_FILES['file_excel']['tmp_name'], $tujuan)) {
                // Hapus file lama kalau sebelumnya berupa file upload (bukan link eksternal)
                $fileLama = $tugas['link_excel'] ?? '';
                if (!empty($fileLama) && strpos($fileLama, 'uploads/tugas/') === 0) {
                    $pathLama = __DIR__ . '/' . $fileLama;
                    if (file_exists($pathLama)) {
                        unlink($pathLama);
                    }
                }
                $link_excel = 'uploads/tugas/' . $namaUnik;
            }
        } else {
            $error = "File harus berformat .xlsx, .xls, atau .csv.";
        }
    } elseif (empty($link_excel)) {
        $link_excel = $tugas['link_excel'] ?? null;
    }

    if (empty($nama_tugas) || empty($tanggal_mulai) || empty($target_pencapaian)) {
        $error = "Tugas, Tanggal Mulai, dan Target Pencapaian wajib diisi.";
    } elseif ($progres < 0 || $progres > 100) {
        $error = "Progres harus di antara 0 - 100.";
    } elseif (empty($error)) {
        $sql = "UPDATE to_do_list SET tugas=?, prioritas=?, status=?, progres=?, tanggal_mulai=?, tanggal_selesai=?, target_pencapaian=?, rincian_data=?, catatan_progres=?, link_excel=? WHERE nomor=?";
        $stmt2 = db_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt2, "sssissssssi", $nama_tugas, $prioritas, $status, $progres, $tanggal_mulai, $tanggal_selesai, $target_pencapaian, $rincian_data, $catatan_progres, $link_excel, $nomor);

        if (mysqli_stmt_execute($stmt2)) {
            $log = db_prepare($koneksi, "INSERT INTO log_aktivitas (user_id, aksi, keterangan) VALUES (?, 'edit_tugas', ?)");
            $ket = $_SESSION['nama'] . " mengubah tugas: " . $nama_tugas;
            mysqli_stmt_bind_param($log, "ss", $userId, $ket);
            mysqli_stmt_execute($log);

            header("Location: to_do_list.php?sukses=ubah");
            exit();
        } else {
            $error = "Gagal menyimpan perubahan: " . mysqli_error($koneksi);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Tugas - Sistem Kepegawaian</title>
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
            <div class="topbar-title">Ubah Tugas</div>
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
            <h2 style="margin-bottom:20px;">Ubah Tugas #<?= (int)$tugas['nomor'] ?></h2>
            <form method="POST" action="ubah_tugas.php?nomor=<?= (int)$tugas['nomor'] ?>" enctype="multipart/form-data">
                <div class="form-row">
                    <label>Nama Tugas</label>
                    <input type="text" name="tugas" value="<?= htmlspecialchars($tugas['tugas']) ?>" required>
                </div>
                <div class="form-row">
                    <label>Prioritas</label>
                    <select name="prioritas" required>
                        <option value="rendah" <?= $tugas['prioritas'] === 'rendah' ? 'selected' : '' ?>>Rendah</option>
                        <option value="sedang" <?= $tugas['prioritas'] === 'sedang' ? 'selected' : '' ?>>Sedang</option>
                        <option value="tinggi" <?= $tugas['prioritas'] === 'tinggi' ? 'selected' : '' ?>>Tinggi</option>
                    </select>
                </div>
                <div class="form-row">
                    <label>Status</label>
                    <select name="status" required>
                        <option value="belum_mulai" <?= $tugas['status'] === 'belum_mulai' ? 'selected' : '' ?>>Belum Mulai</option>
                        <option value="proses" <?= $tugas['status'] === 'proses' ? 'selected' : '' ?>>Proses</option>
                        <option value="selesai" <?= $tugas['status'] === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                        <option value="tertunda" <?= $tugas['status'] === 'tertunda' ? 'selected' : '' ?>>Tertunda</option>
                    </select>
                </div>
                <div class="form-row">
                    <label>Progres (%)</label>
                    <input type="number" name="progres" min="0" max="100" value="<?= (int)$tugas['progres'] ?>" required>
                </div>
                <div class="form-row">
                    <label>Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" value="<?= htmlspecialchars($tugas['tanggal_mulai']) ?>" required>
                </div>
                <div class="form-row">
                    <label>Tanggal Selesai (opsional)</label>
                    <input type="date" name="tanggal_selesai" value="<?= htmlspecialchars($tugas['tanggal_selesai'] ?? '') ?>">
                </div>
                <div class="form-row">
                    <label>Target Pencapaian</label>
                    <input type="text" name="target_pencapaian" value="<?= htmlspecialchars($tugas['target_pencapaian']) ?>" required>
                </div>
                <div class="form-row">
                    <label>Rincian Data</label>
                    <input type="text" name="rincian_data" value="<?= htmlspecialchars($tugas['rincian_data'] ?? '') ?>">
                </div>
                <div class="form-row">
                    <label>Catatan/Update Progres</label>
                    <input type="text" name="catatan_progres" value="<?= htmlspecialchars($tugas['catatan_progres'] ?? '') ?>">
                </div>
                <div class="form-row">
                    <label>Link File External (opsional)</label>
                    <input type="url" name="link_excel" value="<?= htmlspecialchars($tugas['link_excel'] ?? '') ?>" placeholder="https://docs.google.com/spreadsheets/...">
                    <small style="color:var(--text-muted); font-size:11.5px;">Isi salah satu: link online, atau upload file di bawah</small>
                </div>
                <div class="form-row">
                    <label>Atau Upload File Excel Baru (opsional)</label>
                    <input type="file" name="file_excel" accept=".xlsx,.xls,.csv">
                    <small style="color:var(--text-muted); font-size:11.5px;">
                        Format .xlsx, .xls, atau .csv.
                        <?php if (!empty($tugas['link_excel'])): ?>
                            File/link saat ini: <a href="<?= htmlspecialchars($tugas['link_excel']) ?>" target="_blank" style="color:var(--primary);">Lihat</a>
                        <?php endif; ?>
                    </small>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Simpan Perubahan</button>
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
