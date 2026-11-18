<?php
if (!function_exists('buatLinkHalaman')) {
    function buatLinkHalaman($urlDasar, $paramLain, $nomor) {
        $paramLain['halaman'] = $nomor;
        return htmlspecialchars($urlDasar . '?' . http_build_query($paramLain));
    }
}

if (!function_exists('tombolHalaman')) {
    function tombolHalaman($urlDasar, $paramLain, $nomor, $aktif, $label = null) {
        $label = $label ?? $nomor;
        $bg = $aktif ? 'var(--primary)' : 'var(--surface-alt)';
        $warna = $aktif ? '#fff' : 'var(--text)';
        return '<a href="' . buatLinkHalaman($urlDasar, $paramLain, $nomor) . '"
                   style="padding:7px 13px; border-radius:8px; font-size:13px; font-weight:600; text-decoration:none;
                          background:' . $bg . '; color:' . $warna . '; border:1px solid var(--border); min-width:36px; text-align:center;">'
                . $label . '</a>';
    }
}

/**
 * Menampilkan navigasi pagination yang scalable untuk data dalam jumlah besar.
 * Tidak menampilkan semua nomor halaman sekaligus -- hanya jendela di sekitar
 * halaman aktif + halaman pertama/terakhir, dengan "..." di celah yang dilompati.
 *
 * @param int    $halamanSekarang Halaman yang sedang aktif
 * @param int    $totalHalaman    Total jumlah halaman
 * @param string $urlDasar        Nama file tujuan, misal 'to_do_list.php'
 * @param array  $paramLain       Parameter GET lain yang perlu dipertahankan (filter, pencarian, dll)
 * @param int    $jendela         Berapa banyak nomor di kiri-kanan halaman aktif yang ditampilkan
 */
function tampilkanPagination($halamanSekarang, $totalHalaman, $urlDasar, $paramLain = [], $jendela = 2) {
    if ($totalHalaman <= 1) return;



    echo '<div style="display:flex; justify-content:center; align-items:center; gap:6px; margin-top:22px; flex-wrap:wrap;">';

    // Tombol "Sebelumnya"
    if ($halamanSekarang > 1) {
        echo tombolHalaman($urlDasar, $paramLain, $halamanSekarang - 1, false, '&larr; Sebelumnya');
    }

    // Halaman pertama + "..."
    if ($halamanSekarang - $jendela > 1) {
        echo tombolHalaman($urlDasar, $paramLain, 1, false);
        if ($halamanSekarang - $jendela > 2) {
            echo '<span style="padding:0 4px; color:var(--text-muted);">...</span>';
        }
    }

    // Jendela nomor halaman di sekitar halaman aktif
    $awal = max(1, $halamanSekarang - $jendela);
    $akhir = min($totalHalaman, $halamanSekarang + $jendela);
    for ($i = $awal; $i <= $akhir; $i++) {
        echo tombolHalaman($urlDasar, $paramLain, $i, $i === $halamanSekarang);
    }

    // "..." + halaman terakhir
    if ($halamanSekarang + $jendela < $totalHalaman) {
        if ($halamanSekarang + $jendela < $totalHalaman - 1) {
            echo '<span style="padding:0 4px; color:var(--text-muted);">...</span>';
        }
        echo tombolHalaman($urlDasar, $paramLain, $totalHalaman, false);
    }

    // Tombol "Berikutnya"
    if ($halamanSekarang < $totalHalaman) {
        echo tombolHalaman($urlDasar, $paramLain, $halamanSekarang + 1, false, 'Berikutnya &rarr;');
    }

    echo '</div>';
}
