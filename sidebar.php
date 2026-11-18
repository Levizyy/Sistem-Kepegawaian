<?php
$halaman_ini = basename($_SERVER['PHP_SELF']);
function navActive($nama, $halaman_ini) {
    return $nama === $halaman_ini ? 'active' : '';
}
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-mark">SK</div>
        <span>Sistem Informasi<br>Kepegawaian</span>
    </div>

    <nav class="sidebar-nav">
        <a href="dashboard.php" class="sidebar-link <?= navActive('dashboard.php', $halaman_ini) ?>">
            <span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg></span>
            Beranda
        </a>

        <?php if ($_SESSION['role'] === 'super_admin' || $_SESSION['role'] === 'admin'): ?>
        <a href="kelola_user.php" class="sidebar-link <?= navActive('kelola_user.php', $halaman_ini) ?> <?= in_array($halaman_ini, ['tambah_user.php','ubah_user.php']) ? 'active' : '' ?>">
            <span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
            Kelola User
        </a>
        <?php endif; ?>

        <a href="to_do_list.php" class="sidebar-link <?= navActive('to_do_list.php', $halaman_ini) ?>">
            <span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></span>
            To Do List
        </a>

        <a href="absensi.php" class="sidebar-link <?= navActive('absensi.php', $halaman_ini) ?>">
            <span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></span>
            Absensi
        </a>

        <a href="izin.php" class="sidebar-link <?= navActive('izin.php', $halaman_ini) ?>">
            <span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/></svg></span>
            Izin & Cuti
        </a>

        <a href="rekap_absensi.php" class="sidebar-link <?= navActive('rekap_absensi.php', $halaman_ini) ?>">
            <span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19h16"/><path d="M7 16V8"/><path d="M12 16V5"/><path d="M17 16v-6"/></svg></span>
            Rekap Absensi
        </a>

        <a href="notifikasi.php" class="sidebar-link <?= navActive('notifikasi.php', $halaman_ini) ?>">
            <span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg></span>
            Notifikasi
        </a>

        <a href="kinerja.php" class="sidebar-link <?= navActive('kinerja.php', $halaman_ini) ?>">
            <span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19h16"/><path d="M7 16V8"/><path d="M12 16V5"/><path d="M17 16v-6"/></svg></span>
            Kinerja
        </a>

        <a href="laporan.php" class="sidebar-link <?= navActive('laporan.php', $halaman_ini) ?>">
            <span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19h16"/><path d="M7 16V8"/><path d="M12 16V5"/><path d="M17 16v-6"/></svg></span>
            Laporan
        </a>

        <?php if ($_SESSION['role'] === 'super_admin' || $_SESSION['role'] === 'admin'): ?>
        <a href="log_aktivitas.php" class="sidebar-link <?= navActive('log_aktivitas.php', $halaman_ini) ?>">
            <span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="9"/></svg></span>
            Log Aktivitas
        </a>
        <?php endif; ?>

        <a href="profil.php" class="sidebar-link <?= navActive('profil.php', $halaman_ini) ?>">
            <span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-6 8-6s8 2 8 6"/></svg></span>
            Profil Saya
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?></div>
            <div class="sidebar-user-info">
                <div class="name"><?= htmlspecialchars($_SESSION['nama']) ?></div>
                <div class="role"><?= htmlspecialchars(str_replace('_', ' ', $_SESSION['role'])) ?></div>
            </div>
        </div>
        <button class="sidebar-logout" onclick="window.location.href='logout.php'" type="button">
            <span class="sidebar-link-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg></span>
            Keluar
        </button>
    </div>
</aside>
