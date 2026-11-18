<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Tidak Ditemukan - Sistem Kepegawaian</title>
    <script>
        (function () {
            var tema = localStorage.getItem('sik_theme') || 'light';
            document.documentElement.setAttribute('data-theme', tema);
        })();
    </script>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .notfound-body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg);
            padding: 24px;
        }
        .notfound-box {
            text-align: center;
            max-width: 420px;
        }
        .notfound-code {
            font-size: 88px;
            font-weight: 800;
            color: var(--primary);
            line-height: 1;
            margin-bottom: 8px;
        }
        .notfound-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 10px;
        }
        .notfound-desc {
            font-size: 13.5px;
            color: var(--text-muted);
            margin-bottom: 26px;
            line-height: 1.6;
        }
    </style>
</head>
<body class="notfound-body">
    <div class="theme-toggle-floating">
        <button class="theme-toggle" onclick="toggleTheme()" type="button">
            <span class="theme-toggle-icon"></span><span class="theme-toggle-label">Gelap</span>
        </button>
    </div>
    <div class="notfound-box">
        <div class="notfound-code">404</div>
        <div class="notfound-title">Halaman Tidak Ditemukan</div>
        <p class="notfound-desc">Halaman yang kamu cari tidak ada, sudah dipindahkan, atau URL-nya salah ketik.</p>
        <a href="dashboard.php" class="btn-primary">Kembali ke Beranda</a>
    </div>
    <script src="assets/js/theme.js"></script>
</body>
</html>
