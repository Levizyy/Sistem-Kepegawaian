<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once "includes/cek_login.php";
require_once "config/koneksi.php";

$role = $_SESSION['role'];
$userId = $_SESSION['id'];

$pesan = $_GET['pesan'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'tambah_target' && ($role === 'super_admin' || $role === 'admin')) {
        $targetUserId = $_POST['user_id'] ?? '';
        $periode = $_POST['periode'] ?? '';
        $targetNilai = (float)($_POST['target_nilai'] ?? 0);
        $targetDeskripsi = trim($_POST['target_deskripsi'] ?? '');

        if ($targetUserId !== '' && $periode !== '' && $targetDeskripsi !== '') {
            $stmt = db_prepare($koneksi, "INSERT INTO kpi_targets (user_id, periode, target_nilai, target_deskripsi) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'ssds', $targetUserId, $periode, $targetNilai, $targetDeskripsi);
            mysqli_stmt_execute($stmt);
            $pesan = 'Target KPI berhasil ditambahkan.';
        }
    }

    if ($aksi === 'tambah_skor' && ($role === 'super_admin' || $role === 'admin')) {
        $targetUserId = $_POST['user_id'] ?? '';
        $periode = $_POST['periode'] ?? '';
        $skor = (float)($_POST['skor'] ?? 0);
        $kategori = $_POST['kategori'] ?? 'cukup';
        if (!in_array($kategori, ['baik', 'cukup', 'kurang'], true)) { $kategori = 'cukup'; }
        $catatan = trim($_POST['catatan'] ?? '');

        if ($targetUserId !== '' && $periode !== '') {
            $stmt = db_prepare($koneksi, "INSERT INTO kpi_scores (user_id, periode, skor, kategori, catatan) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'ssdss', $targetUserId, $periode, $skor, $kategori, $catatan);
            mysqli_stmt_execute($stmt);
            $pesan = 'Skor KPI berhasil disimpan.';
        }
    }

    if ($aksi === 'tambah_review' && ($role === 'super_admin' || $role === 'admin')) {
        $targetUserId = $_POST['user_id'] ?? '';
        $periode = $_POST['periode'] ?? '';
        $hasilEvaluasi = trim($_POST['hasil_evaluasi'] ?? '');
        $rekomendasi = trim($_POST['rekomendasi'] ?? '');

        if ($targetUserId !== '' && $periode !== '' && $hasilEvaluasi !== '') {
            $stmt = db_prepare($koneksi, "INSERT INTO performance_reviews (user_id, reviewer_id, periode, hasil_evaluasi, rekomendasi) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'sssss', $targetUserId, $userId, $periode, $hasilEvaluasi, $rekomendasi);
            mysqli_stmt_execute($stmt);
            $pesan = 'Hasil evaluasi kinerja berhasil disimpan.';
        }
    }

    header("Location: kinerja.php?pesan=" . urlencode($pesan));
    exit();
}

if ($role === 'super_admin' || $role === 'admin') {
    $queryTargets = db_query($koneksi, "SELECT kt.*, u.nama FROM kpi_targets kt JOIN users u ON kt.user_id = u.id ORDER BY kt.created_at DESC LIMIT 50");
    $queryScores = db_query($koneksi, "SELECT ks.*, u.nama FROM kpi_scores ks JOIN users u ON ks.user_id = u.id ORDER BY ks.created_at DESC LIMIT 50");
    $queryReviews = db_query($koneksi, "SELECT pr.*, u.nama AS nama_user, r.nama AS reviewer_name FROM performance_reviews pr JOIN users u ON pr.user_id = u.id JOIN users r ON pr.reviewer_id = r.id ORDER BY pr.created_at DESC LIMIT 50");
    $queryUsers = db_query($koneksi, "SELECT id, nama, role FROM users WHERE aktif = 1 ORDER BY nama ASC");
} else {
    $queryTargets = db_query($koneksi, "SELECT kt.*, u.nama FROM kpi_targets kt JOIN users u ON kt.user_id = u.id WHERE kt.user_id = '$userId' ORDER BY kt.created_at DESC LIMIT 20");
    $queryScores = db_query($koneksi, "SELECT ks.*, u.nama FROM kpi_scores ks JOIN users u ON ks.user_id = u.id WHERE ks.user_id = '$userId' ORDER BY ks.created_at DESC LIMIT 20");
    $queryReviews = db_query($koneksi, "SELECT pr.*, u.nama AS nama_user, r.nama AS reviewer_name FROM performance_reviews pr JOIN users u ON pr.user_id = u.id JOIN users r ON pr.reviewer_id = r.id WHERE pr.user_id = '$userId' ORDER BY pr.created_at DESC LIMIT 20");
    $queryUsers = db_query($koneksi, "SELECT id, nama, role FROM users WHERE 1 = 0");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kinerja — Sistem Informasi Kepegawaian</title>
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
                <div class="topbar-title">Kinerja</div>
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

                <?php if ($role === 'super_admin' || $role === 'admin'): ?>
                    <div class="card-box">
                        <div class="card-header">
                            <h2>Tambah Target KPI</h2>
                        </div>
                        <form method="POST" action="kinerja.php" style="display:grid; gap:12px; max-width:700px;">
                            <input type="hidden" name="aksi" value="tambah_target">
                            <label>
                                <span style="display:block; margin-bottom:6px; font-weight:600;">Pegawai</span>
                                <select name="user_id" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); background:var(--surface); color:var(--text);">
                                    <?php while ($user = mysqli_fetch_assoc($queryUsers)): ?>
                                        <option value="<?= htmlspecialchars($user['id']) ?>"><?= htmlspecialchars($user['nama']) ?> (<?= htmlspecialchars($user['role']) ?>)</option>
                                    <?php endwhile; ?>
                                </select>
                            </label>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                <label>
                                    <span style="display:block; margin-bottom:6px; font-weight:600;">Periode</span>
                                    <input type="text" name="periode" placeholder="2026-07" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); background:var(--surface); color:var(--text);">
                                </label>
                                <label>
                                    <span style="display:block; margin-bottom:6px; font-weight:600;">Target Nilai</span>
                                    <input type="number" step="0.01" name="target_nilai" value="0" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); background:var(--surface); color:var(--text);">
                                </label>
                            </div>
                            <label>
                                <span style="display:block; margin-bottom:6px; font-weight:600;">Deskripsi Target</span>
                                <textarea name="target_deskripsi" rows="3" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); background:var(--surface); color:var(--text);"></textarea>
                            </label>
                            <button type="submit" class="btn-primary">Simpan Target</button>
                        </form>
                    </div>

                    <div class="card-box" style="margin-top:20px;">
                        <div class="card-header">
                            <h2>Input Skor KPI</h2>
                        </div>
                        <form method="POST" action="kinerja.php" style="display:grid; gap:12px; max-width:700px;">
                            <input type="hidden" name="aksi" value="tambah_skor">
                            <label>
                                <span style="display:block; margin-bottom:6px; font-weight:600;">Pegawai</span>
                                <select name="user_id" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); background:var(--surface); color:var(--text);">
                                    <?php mysqli_data_seek($queryUsers, 0); while ($user = mysqli_fetch_assoc($queryUsers)): ?>
                                        <option value="<?= htmlspecialchars($user['id']) ?>"><?= htmlspecialchars($user['nama']) ?> (<?= htmlspecialchars($user['role']) ?>)</option>
                                    <?php endwhile; ?>
                                </select>
                            </label>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                <label>
                                    <span style="display:block; margin-bottom:6px; font-weight:600;">Periode</span>
                                    <input type="text" name="periode" placeholder="2026-07" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); background:var(--surface); color:var(--text);">
                                </label>
                                <label>
                                    <span style="display:block; margin-bottom:6px; font-weight:600;">Skor</span>
                                    <input type="number" step="0.01" name="skor" value="0" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); background:var(--surface); color:var(--text);">
                                </label>
                            </div>
                            <label>
                                <span style="display:block; margin-bottom:6px; font-weight:600;">Kategori</span>
                                <select name="kategori" style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); background:var(--surface); color:var(--text);">
                                    <option value="baik">Baik</option>
                                    <option value="cukup">Cukup</option>
                                    <option value="kurang">Kurang</option>
                                </select>
                            </label>
                            <label>
                                <span style="display:block; margin-bottom:6px; font-weight:600;">Catatan</span>
                                <textarea name="catatan" rows="3" style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); background:var(--surface); color:var(--text);"></textarea>
                            </label>
                            <button type="submit" class="btn-primary">Simpan Skor</button>
                        </form>
                    </div>

                    <div class="card-box" style="margin-top:20px;">
                        <div class="card-header">
                            <h2>Tambah Evaluasi Kinerja</h2>
                        </div>
                        <form method="POST" action="kinerja.php" style="display:grid; gap:12px; max-width:700px;">
                            <input type="hidden" name="aksi" value="tambah_review">
                            <label>
                                <span style="display:block; margin-bottom:6px; font-weight:600;">Pegawai</span>
                                <select name="user_id" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); background:var(--surface); color:var(--text);">
                                    <?php mysqli_data_seek($queryUsers, 0); while ($user = mysqli_fetch_assoc($queryUsers)): ?>
                                        <option value="<?= htmlspecialchars($user['id']) ?>"><?= htmlspecialchars($user['nama']) ?> (<?= htmlspecialchars($user['role']) ?>)</option>
                                    <?php endwhile; ?>
                                </select>
                            </label>
                            <label>
                                <span style="display:block; margin-bottom:6px; font-weight:600;">Periode</span>
                                <input type="text" name="periode" placeholder="2026-07" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); background:var(--surface); color:var(--text);">
                            </label>
                            <label>
                                <span style="display:block; margin-bottom:6px; font-weight:600;">Hasil Evaluasi</span>
                                <textarea name="hasil_evaluasi" rows="4" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); background:var(--surface); color:var(--text);"></textarea>
                            </label>
                            <label>
                                <span style="display:block; margin-bottom:6px; font-weight:600;">Rekomendasi</span>
                                <textarea name="rekomendasi" rows="3" style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); background:var(--surface); color:var(--text);"></textarea>
                            </label>
                            <button type="submit" class="btn-primary">Simpan Evaluasi</button>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="card-box" style="margin-top:20px;">
                    <div class="card-header">
                        <h2>Target KPI</h2>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Pegawai</th>
                                    <th>Periode</th>
                                    <th>Target</th>
                                    <th>Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($queryTargets) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($queryTargets)): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['nama']) ?></td>
                                            <td><?= htmlspecialchars($row['periode']) ?></td>
                                            <td><?= htmlspecialchars($row['target_nilai']) ?></td>
                                            <td><?= htmlspecialchars($row['target_deskripsi']) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" style="text-align:center; padding:20px;">Belum ada target KPI.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-box" style="margin-top:20px;">
                    <div class="card-header">
                        <h2>Skor KPI</h2>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Pegawai</th>
                                    <th>Periode</th>
                                    <th>Skor</th>
                                    <th>Kategori</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($queryScores) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($queryScores)): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['nama']) ?></td>
                                            <td><?= htmlspecialchars($row['periode']) ?></td>
                                            <td><?= htmlspecialchars($row['skor']) ?></td>
                                            <td><?= htmlspecialchars($row['kategori']) ?></td>
                                            <td><?= htmlspecialchars($row['catatan'] ?? '-') ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" style="text-align:center; padding:20px;">Belum ada skor KPI.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-box" style="margin-top:20px;">
                    <div class="card-header">
                        <h2>Evaluasi Kinerja</h2>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Pegawai</th>
                                    <th>Reviewer</th>
                                    <th>Periode</th>
                                    <th>Hasil Evaluasi</th>
                                    <th>Rekomendasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($queryReviews) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($queryReviews)): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['nama_user']) ?></td>
                                            <td><?= htmlspecialchars($row['reviewer_name']) ?></td>
                                            <td><?= htmlspecialchars($row['periode']) ?></td>
                                            <td><?= htmlspecialchars($row['hasil_evaluasi']) ?></td>
                                            <td><?= htmlspecialchars($row['rekomendasi'] ?? '-') ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" style="text-align:center; padding:20px;">Belum ada evaluasi kinerja.</td></tr>
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
