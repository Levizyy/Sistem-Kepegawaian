<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once "includes/cek_login.php";
require_once "config/koneksi.php";

$role = $_SESSION['role'];
$userId = $_SESSION['id'];

// ================================================
// FILTER & PAGINATION
// ================================================
$filterStatus = $_GET['status'] ?? '';
$filterPrioritas = $_GET['prioritas'] ?? '';
$halaman = max(1, (int)($_GET['halaman'] ?? 1));

// Jumlah data per halaman: 10/25/50/100, atau 'semua' untuk tanpa batas
$perHalamanInput = $_GET['per_halaman'] ?? '10';
$opsiPerHalaman = ['10', '25', '50', '100', 'semua'];
if (!in_array($perHalamanInput, $opsiPerHalaman)) {
    $perHalamanInput = '10';
}
$tampilkanSemua = ($perHalamanInput === 'semua');
$perHalaman = $tampilkanSemua ? 1000000 : (int)$perHalamanInput; // 1000000 = anggap tanpa batas
$offset = $tampilkanSemua ? 0 : ($halaman - 1) * $perHalaman;

$kondisi = [];
$params = [];
$types = "";

if ($role !== 'super_admin' && $role !== 'admin') {
    $kondisi[] = "t.pemilik = ?";
    $params[] = $userId;
    $types .= "s";
}
if ($filterStatus !== '') {
    $kondisi[] = "t.status = ?";
    $params[] = $filterStatus;
    $types .= "s";
}
if ($filterPrioritas !== '') {
    $kondisi[] = "t.prioritas = ?";
    $params[] = $filterPrioritas;
    $types .= "s";
}

$whereSql = count($kondisi) > 0 ? "WHERE " . implode(" AND ", $kondisi) : "";

// Hitung total data (untuk pagination)
$sqlCount = "SELECT COUNT(*) AS total FROM to_do_list t JOIN users u ON t.pemilik = u.id $whereSql";
$stmtCount = db_prepare($koneksi, $sqlCount);
if (count($params) > 0) {
    $refsCount = [];
    foreach ($params as $k => $v) { $refsCount[$k] = &$params[$k]; }
    array_unshift($refsCount, $types);
    call_user_func_array([$stmtCount, 'bind_param'], $refsCount);
}
mysqli_stmt_execute($stmtCount);
$totalData = db_fetch(db_result($stmtCount))['total'];
$totalHalaman = $tampilkanSemua ? 1 : max(1, ceil($totalData / $perHalaman));

// Ambil data sesuai halaman
$sql = "SELECT t.*, u.nama AS nama_pemilik FROM to_do_list t 
        JOIN users u ON t.pemilik = u.id 
        $whereSql
        ORDER BY t.tanggal_mulai DESC
        LIMIT ? OFFSET ?";
$stmt = db_prepare($koneksi, $sql);
$paramsData = array_merge($params, [$perHalaman, $offset]);
$typesData = $types . "ii";
$refsData = [];
foreach ($paramsData as $k => $v) { $refsData[$k] = &$paramsData[$k]; }
array_unshift($refsData, $typesData);
call_user_func_array([$stmt, 'bind_param'], $refsData);
mysqli_stmt_execute($stmt);
$result = db_result($stmt);

function labelPrioritas($p) {
    $map = ['rendah' => 'Rendah', 'sedang' => 'Sedang', 'tinggi' => 'Tinggi'];
    return $map[$p] ?? $p;
}
function labelStatus($s) {
    $map = ['belum_mulai' => 'Belum Mulai', 'proses' => 'Proses', 'selesai' => 'Selesai', 'tertunda' => 'Tertunda'];
    return $map[$s] ?? $s;
}

$pesan = "";
if (isset($_GET['sukses'])) {
    $map = ['tambah' => 'Tugas berhasil ditambahkan.', 'ubah' => 'Tugas berhasil diubah.', 'hapus' => 'Tugas berhasil dihapus.'];
    $pesan = $map[$_GET['sukses']] ?? "";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To Do List - Sistem Kepegawaian</title>
    <script>
        (function () {
            var tema = localStorage.getItem('sik_theme') || 'light';
            document.documentElement.setAttribute('data-theme', tema);
        })();
    </script>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .progress-bar-outer { background:#e5e7eb; border-radius:20px; height:8px; width:100px; overflow:hidden; }
        .progress-bar-inner { background:linear-gradient(90deg,#4f46e5,#7c3aed); height:100%; }
        .badge-prioritas-tinggi { background:#fee2e2; color:#dc2626; }
        .badge-prioritas-sedang { background:#fef3c7; color:#b45309; }
        .badge-prioritas-rendah { background:#dbeafe; color:#2563eb; }
        .badge-status-selesai { background:#dcfce7; color:#16a34a; }
        .badge-status-proses { background:#dbeafe; color:#2563eb; }
        .badge-status-belum_mulai { background:#f3f4f6; color:#6b7280; }
        .badge-status-tertunda { background:#fee2e2; color:#dc2626; }
    </style>
</head>
<body>
    <div class="app-shell">
    <?php include 'includes/sidebar.php'; ?>
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
    <div class="main-area">
        <div class="topbar">
            <button class="hamburger-btn" onclick="toggleSidebar()" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg></button>
            <div class="topbar-title">To Do List</div>
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
                <h2>To Do List <?= ($role === 'user') ? '- Tugas Saya' : '- Semua Pegawai' ?></h2>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <a href="tambah_tugas.php" class="btn-primary">+ Tambah Tugas</a>
                    <a href="export_todo.php" class="btn-primary" style="background:var(--success);">⬇ Export ke Excel</a>
                    <a href="cetak_todo.php" class="btn-primary" style="background:var(--text-muted);" target="_blank">🖨️ Cetak Laporan</a>
                </div>
            </div>

            <form method="GET" action="to_do_list.php" style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px;">
                <select name="status" onchange="this.form.submit()" style="padding:9px 13px; border:1.5px solid var(--border); border-radius:9px; background:var(--surface); color:var(--text); font-size:13px;">
                    <option value="">Semua Status</option>
                    <option value="belum_mulai" <?= $filterStatus === 'belum_mulai' ? 'selected' : '' ?>>Belum Mulai</option>
                    <option value="proses" <?= $filterStatus === 'proses' ? 'selected' : '' ?>>Proses</option>
                    <option value="selesai" <?= $filterStatus === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                    <option value="tertunda" <?= $filterStatus === 'tertunda' ? 'selected' : '' ?>>Tertunda</option>
                </select>
                <select name="prioritas" onchange="this.form.submit()" style="padding:9px 13px; border:1.5px solid var(--border); border-radius:9px; background:var(--surface); color:var(--text); font-size:13px;">
                    <option value="">Semua Prioritas</option>
                    <option value="rendah" <?= $filterPrioritas === 'rendah' ? 'selected' : '' ?>>Rendah</option>
                    <option value="sedang" <?= $filterPrioritas === 'sedang' ? 'selected' : '' ?>>Sedang</option>
                    <option value="tinggi" <?= $filterPrioritas === 'tinggi' ? 'selected' : '' ?>>Tinggi</option>
                </select>
                <select name="per_halaman" onchange="this.form.submit()" style="padding:9px 13px; border:1.5px solid var(--border); border-radius:9px; background:var(--surface); color:var(--text); font-size:13px;">
                    <option value="10" <?= $perHalamanInput === '10' ? 'selected' : '' ?>>10 / halaman</option>
                    <option value="25" <?= $perHalamanInput === '25' ? 'selected' : '' ?>>25 / halaman</option>
                    <option value="50" <?= $perHalamanInput === '50' ? 'selected' : '' ?>>50 / halaman</option>
                    <option value="100" <?= $perHalamanInput === '100' ? 'selected' : '' ?>>100 / halaman</option>
                    <option value="semua" <?= $perHalamanInput === 'semua' ? 'selected' : '' ?>>Tampilkan Semua</option>
                </select>
                <?php if ($filterStatus !== '' || $filterPrioritas !== ''): ?>
                    <a href="to_do_list.php" class="btn-search-clear" style="align-self:center;">Reset Filter</a>
                <?php endif; ?>
            </form>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <?php if ($role !== 'user'): ?><th>Pemilik</th><?php endif; ?>
                            <th>Tugas</th>
                            <th>Prioritas</th>
                            <th>Status</th>
                            <th>Progres</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Target</th>
                            <th>Catatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <?php
                                    $boleh_kelola = ($role === 'super_admin' || $role === 'admin' || $row['pemilik'] === $userId);
                                    $terlambat = !empty($row['tanggal_selesai']) && $row['tanggal_selesai'] < date('Y-m-d') && $row['status'] !== 'selesai';
                                ?>
                                <tr<?= $terlambat ? ' style="background:var(--danger-soft);"' : '' ?>>
                                    <td><?= htmlspecialchars($row['nomor']) ?></td>
                                    <?php if ($role !== 'user'): ?><td><?= htmlspecialchars($row['nama_pemilik']) ?></td><?php endif; ?>
                                    <td><?= htmlspecialchars($row['tugas']) ?></td>
                                    <td><span class="badge badge-prioritas-<?= htmlspecialchars($row['prioritas']) ?>"><?= labelPrioritas($row['prioritas']) ?></span></td>
                                    <td>
                                        <span class="badge badge-status-<?= htmlspecialchars($row['status']) ?>"><?= labelStatus($row['status']) ?></span>
                                        <?php if ($terlambat): ?>
                                            <span class="badge badge-status-tertunda" style="margin-left:4px;">Terlambat</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="progress-bar-outer">
                                            <div class="progress-bar-inner" style="width:<?= (int)$row['progres'] ?>%;"></div>
                                        </div>
                                        <small><?= (int)$row['progres'] ?>%</small>
                                    </td>
                                    <td><?= htmlspecialchars($row['tanggal_mulai']) ?></td>
                                    <td><?= $row['tanggal_selesai'] ? htmlspecialchars($row['tanggal_selesai']) : '-' ?></td>
                                    <td><?= htmlspecialchars($row['target_pencapaian']) ?></td>
                                    <td>
                                        <?= $row['catatan_progres'] ? htmlspecialchars($row['catatan_progres']) : '-' ?>
                                        <?php if (!empty($row['link_excel'])): ?>
                                            <br><a href="<?= htmlspecialchars($row['link_excel']) ?>" target="_blank" style="color:var(--success); font-size:11.5px; font-weight:600;">📎 Lihat Excel</a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($boleh_kelola): ?>
                                            <div class="action-btns">
                                                <a href="ubah_tugas.php?nomor=<?= (int)$row['nomor'] ?>" class="btn-sm btn-edit">Ubah</a>
                                                <a href="hapus_tugas.php?nomor=<?= (int)$row['nomor'] ?>" class="btn-sm btn-delete" onclick="return confirm('Yakin ingin menghapus tugas ini?');">Hapus</a>
                                            </div>
                                        <?php else: ?>
                                            <span style="color:var(--text-muted); font-size:12px;">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="11" style="text-align:center; padding:20px;">Belum ada tugas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalHalaman > 1): ?>
                <?php
                    require_once "includes/pagination.php";
                    $paramLain = [];
                    if ($filterStatus !== '') $paramLain['status'] = $filterStatus;
                    if ($filterPrioritas !== '') $paramLain['prioritas'] = $filterPrioritas;
                    tampilkanPagination($halaman, $totalHalaman, 'to_do_list.php', $paramLain);
                ?>
                <p style="text-align:center; font-size:12px; color:var(--text-muted); margin-top:10px;">
                    Halaman <?= $halaman ?> dari <?= $totalHalaman ?> — Total <?= $totalData ?> tugas
                </p>
            <?php endif; ?>
        </div>
    </div>
    </div>
</div>
    <script src="assets/js/theme.js"></script>
</body>
</html>
