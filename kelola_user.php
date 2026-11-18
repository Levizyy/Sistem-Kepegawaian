<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once "includes/cek_login.php";
require_once "config/koneksi.php";

// Proteksi: hanya super_admin & admin yang boleh akses halaman ini
if ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

$pesan = "";
if (isset($_GET['sukses'])) {
    $map = [
        'tambah' => 'Data user berhasil ditambahkan.',
        'ubah' => 'Data user berhasil diubah.',
        'hapus' => 'Data user berhasil dihapus.'
    ];
    $pesan = $map[$_GET['sukses']] ?? "";
}

$keyword = trim($_GET['q'] ?? '');
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$perHalaman = 10;
$offset = ($halaman - 1) * $perHalaman;

if ($keyword !== '') {
    $like = "%{$keyword}%";

    $sqlCount = "SELECT COUNT(*) AS total FROM users WHERE nama LIKE ? OR email LIKE ? OR unit_kerja LIKE ? OR id LIKE ?";
    $stmtCount = db_prepare($koneksi, $sqlCount);
    mysqli_stmt_bind_param($stmtCount, "ssss", $like, $like, $like, $like);
    mysqli_stmt_execute($stmtCount);
    $totalData = db_fetch(db_result($stmtCount))['total'];

    $query = "SELECT id, nama, unit_kerja, email, nomor_hp, role, aktif FROM users 
              WHERE nama LIKE ? OR email LIKE ? OR unit_kerja LIKE ? OR id LIKE ?
              ORDER BY nama ASC LIMIT ? OFFSET ?";
    $stmt = db_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ssssii", $like, $like, $like, $like, $perHalaman, $offset);
    mysqli_stmt_execute($stmt);
    $result = db_result($stmt);
} else {
    $totalData = db_fetch(db_query($koneksi, "SELECT COUNT(*) AS total FROM users"))['total'];

    $query = "SELECT id, nama, unit_kerja, email, nomor_hp, role, aktif FROM users ORDER BY nama ASC LIMIT ? OFFSET ?";
    $stmt = db_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ii", $perHalaman, $offset);
    mysqli_stmt_execute($stmt);
    $result = db_result($stmt);
}

$totalHalaman = max(1, ceil($totalData / $perHalaman));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Sistem Kepegawaian</title>
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
            <div class="topbar-title">Kelola User</div>
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
                <h2>Kelola Data User</h2>
                <form method="GET" action="kelola_user.php" class="search-form">
                    <div class="search-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                        <input type="text" name="q" placeholder="Cari nama, email, unit kerja..." value="<?= htmlspecialchars($keyword) ?>">
                    </div>
                    <?php if ($keyword !== ''): ?>
                        <a href="kelola_user.php" class="btn-search-clear">Reset</a>
                    <?php endif; ?>
                </form>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <a href="tambah_user.php" class="btn-primary">+ Tambah User</a>
                    <a href="export_users.php<?= $keyword !== '' ? '?q=' . urlencode($keyword) : '' ?>" class="btn-primary" style="background:var(--success);">⬇ Export ke Excel</a>
                </div>
            </div>

            <?php if ($keyword !== ''): ?>
                <p style="font-size:12.5px; color:var(--text-muted); margin-bottom:16px;">
                    Menampilkan <?= mysqli_num_rows($result) ?> hasil untuk "<?= htmlspecialchars($keyword) ?>"
                </p>
            <?php endif; ?>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Unit Kerja</th>
                            <th>Email</th>
                            <th>No. HP</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['id']) ?></td>
                                    <td><?= htmlspecialchars($row['nama']) ?></td>
                                    <td><?= htmlspecialchars($row['unit_kerja']) ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['nomor_hp']) ?></td>
                                    <td><span class="role-badge role-<?= htmlspecialchars($row['role']) ?>"><?= htmlspecialchars(str_replace('_', ' ', $row['role'])) ?></span></td>
                                    <td>
                                        <?php if ($row['aktif']): ?>
                                            <span class="badge badge-aktif">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge badge-nonaktif">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-btns">
                                            <a href="ubah_user.php?id=<?= urlencode($row['id']) ?>" class="btn-sm btn-edit">Ubah</a>
                                            <a href="hapus_user.php?id=<?= urlencode($row['id']) ?>" class="btn-sm btn-delete" onclick="return confirm('Yakin ingin menghapus data ini?');">Hapus</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="8" style="text-align:center; padding:20px;"><?= $keyword !== '' ? 'Tidak ditemukan user yang cocok.' : 'Belum ada data user.' ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalHalaman > 1): ?>
                <?php
                    require_once "includes/pagination.php";
                    $paramLain = $keyword !== '' ? ['q' => $keyword] : [];
                    tampilkanPagination($halaman, $totalHalaman, 'kelola_user.php', $paramLain);
                ?>
                <p style="text-align:center; font-size:12px; color:var(--text-muted); margin-top:10px;">
                    Halaman <?= $halaman ?> dari <?= $totalHalaman ?> — Total <?= $totalData ?> user
                </p>
            <?php endif; ?>
        </div>
    </div>
    </div>
</div>
    <script src="assets/js/theme.js"></script>
</body>
</html>
