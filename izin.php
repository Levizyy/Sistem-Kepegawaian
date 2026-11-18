<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once "includes/cek_login.php";
require_once "config/koneksi.php";

$role = $_SESSION['role'];
$userId = $_SESSION['id'];
$nama = $_SESSION['nama'];

$pesan = $_GET['pesan'] ?? '';
$filterStatus = $_GET['status'] ?? '';
if (!in_array($filterStatus, ['pending', 'disetujui', 'ditolak'], true)) { $filterStatus = ''; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'ajukan') {
        $jenis = $_POST['jenis'] ?? '';
        $tanggalMulai = $_POST['tanggal_mulai'] ?? '';
        $tanggalSelesai = $_POST['tanggal_selesai'] ?? '';
        $alasan = trim($_POST['alasan'] ?? '');

        if (!in_array($jenis, ['izin', 'sakit', 'cuti'], true) || $tanggalMulai === '' || $tanggalSelesai === '' || $alasan === '') {
            $pesan = 'Semua field harus diisi dengan benar.';
        } elseif (strtotime($tanggalSelesai) < strtotime($tanggalMulai)) {
            $pesan = 'Tanggal selesai tidak boleh lebih kecil dari tanggal mulai.';
        } else {
            $stmt = db_prepare($koneksi, "INSERT INTO leave_requests (user_id, jenis, tanggal_mulai, tanggal_selesai, alasan, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            mysqli_stmt_bind_param($stmt, 'sssss', $userId, $jenis, $tanggalMulai, $tanggalSelesai, $alasan);
            mysqli_stmt_execute($stmt);
            $pesan = 'Pengajuan izin/cuti berhasil dikirim.';
        }

        header("Location: izin.php?pesan=" . urlencode($pesan));
        exit();
    }

    if ($aksi === 'update_status') {
        if ($role === 'super_admin' || $role === 'admin') {
            $requestId = (int)($_POST['request_id'] ?? 0);
            $status = $_POST['status'] ?? '';
            if (in_array($status, ['pending', 'disetujui', 'ditolak'], true) && $requestId > 0) {
                $stmt = db_prepare($koneksi, "UPDATE leave_requests SET status = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'si', $status, $requestId);
                mysqli_stmt_execute($stmt);
                $pesan = 'Status pengajuan berhasil diperbarui.';
            }
        }

        header("Location: izin.php?pesan=" . urlencode($pesan));
        exit();
    }
}

$where = [];
$params = [];
$types = '';

if ($role !== 'super_admin' && $role !== 'admin') {
    $where[] = 'lr.user_id = ?';
    $params[] = $userId;
    $types .= 's';
}

if ($filterStatus !== '') {
    $where[] = 'lr.status = ?';
    $params[] = $filterStatus;
    $types .= 's';
}

$whereSql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT lr.*, u.nama AS nama_user FROM leave_requests lr JOIN users u ON lr.user_id = u.id $whereSql ORDER BY lr.created_at DESC";
$stmt = db_prepare($koneksi, $sql);
if (count($params) > 0) {
    $refs = [];
    foreach ($params as $key => $value) { $refs[$key] = &$params[$key]; }
    array_unshift($refs, $types);
    call_user_func_array([$stmt, 'bind_param'], $refs);
}
mysqli_stmt_execute($stmt);
$requestResult = db_result($stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Izin & Cuti — Sistem Informasi Kepegawaian</title>
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
                <div class="topbar-title">Izin & Cuti</div>
                <div class="topbar-right">
                    <button class="theme-toggle" onclick="toggleTheme()" type="button">
                        <span class="theme-toggle-icon"></span><span class="theme-toggle-label">Gelap</span>
                    </button>
                </div>
            </div>

            <div class="container">
                <a href="dashboard.php" class="back-link">&larr; Kembali ke Dashboard</a>

                <?php if ($pesan): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($pesan) ?></div>
                <?php endif; ?>

                <div class="card-box">
                    <div class="card-header">
                        <h2>Ajukan Izin / Cuti</h2>
                    </div>

                    <form method="POST" action="izin.php" style="display:grid; gap:12px; max-width:600px;">
                        <input type="hidden" name="aksi" value="ajukan">

                        <label>
                            <span style="display:block; margin-bottom:6px; font-weight:600;">Jenis</span>
                            <select name="jenis" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); background:var(--surface); color:var(--text);">
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                                <option value="cuti">Cuti</option>
                            </select>
                        </label>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <label>
                                <span style="display:block; margin-bottom:6px; font-weight:600;">Tanggal Mulai</span>
                                <input type="date" name="tanggal_mulai" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); background:var(--surface); color:var(--text);">
                            </label>
                            <label>
                                <span style="display:block; margin-bottom:6px; font-weight:600;">Tanggal Selesai</span>
                                <input type="date" name="tanggal_selesai" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); background:var(--surface); color:var(--text);">
                            </label>
                        </div>

                        <label>
                            <span style="display:block; margin-bottom:6px; font-weight:600;">Alasan</span>
                            <textarea name="alasan" rows="4" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); background:var(--surface); color:var(--text);"></textarea>
                        </label>

                        <button type="submit" class="btn-primary">Kirim Pengajuan</button>
                    </form>
                </div>

                <div class="card-box" style="margin-top:20px;">
                    <div class="card-header">
                        <h2>Riwayat Pengajuan</h2>
                        <form method="GET" action="izin.php" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                            <select name="status" onchange="this.form.submit()" style="padding:9px 13px; border:1.5px solid var(--border); border-radius:9px; background:var(--surface); color:var(--text); font-size:13px;">
                                <option value="">Semua Status</option>
                                <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="disetujui" <?= $filterStatus === 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
                                <option value="ditolak" <?= $filterStatus === 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                            </select>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <?php if ($role === 'super_admin' || $role === 'admin'): ?><th>Pegawai</th><?php endif; ?>
                                    <th>Jenis</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Alasan</th>
                                    <th>Status</th>
                                    <?php if ($role === 'super_admin' || $role === 'admin'): ?><th>Aksi</th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($requestResult) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($requestResult)): ?>
                                        <tr>
                                            <?php if ($role === 'super_admin' || $role === 'admin'): ?><td><?= htmlspecialchars($row['nama_user']) ?></td><?php endif; ?>
                                            <td><?= htmlspecialchars($row['jenis']) ?></td>
                                            <td><?= htmlspecialchars($row['tanggal_mulai']) ?></td>
                                            <td><?= htmlspecialchars($row['tanggal_selesai']) ?></td>
                                            <td><?= htmlspecialchars($row['alasan']) ?></td>
                                            <td><span class="badge badge-status-<?= htmlspecialchars($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                                            <?php if ($role === 'super_admin' || $role === 'admin'): ?>
                                                <td>
                                                    <form method="POST" action="izin.php" style="display:flex; gap:8px; align-items:center;">
                                                        <input type="hidden" name="aksi" value="update_status">
                                                        <input type="hidden" name="request_id" value="<?= (int)$row['id'] ?>">
                                                        <select name="status" style="padding:6px 8px; border-radius:8px; border:1px solid var(--border); background:var(--surface); color:var(--text);">
                                                            <option value="pending" <?= $row['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                            <option value="disetujui" <?= $row['status'] === 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
                                                            <option value="ditolak" <?= $row['status'] === 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                                                        </select>
                                                        <button type="submit" class="btn-sm btn-edit">Simpan</button>
                                                    </form>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" style="text-align:center; padding:20px;">Belum ada data pengajuan.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/theme.js"></script>
</body>
</html>
