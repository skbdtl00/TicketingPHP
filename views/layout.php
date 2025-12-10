<?php
$config = require __DIR__ . '/../config/config.php';
$flash = flash();
$theme = $user['theme'] ?? 'light';
$base = trim($config['base_url'] ?? '', '/');
$basePath = $base === '' ? '' : '/' . $base;
$safeBasePath = sanitize($basePath);
?>
<!DOCTYPE html>
<html lang="th" data-theme="<?= sanitize($theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($config['app_name']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anuphan:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2196F3;
            --primary-light: #E3F2FD;
            --primary-dark: #1976D2;
            --neutral-gray: #546E7A;
            --bg: #F7FBFF;
            --card: #ffffff;
            --text: #102027;
        }
        [data-theme="dark"] {
            --bg: #0f172a;
            --card: #111827;
            --text: #e5e7eb;
            --neutral-gray: #9ca3af;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Anuphan', sans-serif;
            background: var(--bg);
            color: var(--text);
        }
        a { color: var(--primary); text-decoration: none; }
        .container { max-width: 1200px; margin: 0 auto; padding: 24px; }
        header {
            background: var(--card);
            border-bottom: 1px solid var(--primary-light);
            box-shadow: 0 2px 8px rgba(33, 150, 243, 0.08);
        }
        nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 24px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            color: var(--primary-dark);
            letter-spacing: 0.3px;
        }
        .brand img { height: 42px; border-radius: 8px; }
        .nav-links { display: flex; gap: 16px; align-items: center; }
        .nav-links a { font-weight: 500; color: var(--neutral-gray); }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 16px;
            border-radius: 10px;
            border: none;
            background: var(--primary);
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 6px 16px rgba(33,150,243,0.25);
            transition: transform 0.1s ease, box-shadow 0.2s ease;
        }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 10px 18px rgba(33,150,243,0.3); }
        .btn-secondary { background: var(--primary-dark); }
        .card {
            background: var(--card);
            border: 1px solid var(--primary-light);
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 8px 18px rgba(33, 150, 243, 0.08);
        }
        .grid { display: grid; gap: 16px; }
        .grid-2 { grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); }
        form { display: grid; gap: 12px; }
        label { font-weight: 600; color: var(--neutral-gray); }
        input, select, textarea {
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid var(--primary-light);
            background: var(--bg);
            color: var(--text);
            font-family: 'Anuphan', sans-serif;
        }
        textarea { min-height: 120px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 10px; border-bottom: 1px solid var(--primary-light); text-align: left; }
        th { background: var(--primary-light); color: var(--neutral-gray); }
        .status { padding: 6px 10px; border-radius: 999px; font-size: 13px; font-weight: 700; }
        .status-open { background: #E3F2FD; color: #0D47A1; }
        .status-in_progress { background: #FFF3CD; color: #8B6B00; }
        .status-closed { background: #E8F5E9; color: #1B5E20; }
        .breadcrumb { display: flex; gap: 6px; color: var(--neutral-gray); font-size: 14px; }
        .badge { background: var(--primary-light); color: var(--primary-dark); padding: 6px 10px; border-radius: 999px; font-weight: 700; }
        .flash { padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; }
        .flash.success { background: #E8F5E9; color: #1B5E20; border: 1px solid #C8E6C9; }
        .flash.error { background: #FFEBEE; color: #C62828; border: 1px solid #FFCDD2; }
        .reply { border: 1px dashed var(--primary-light); padding: 12px; border-radius: 10px; background: rgba(33,150,243,0.03); }
        .mt-2 { margin-top: 12px; }
        .mt-3 { margin-top: 18px; }
        .text-right { text-align: right; }
        .text-muted { color: var(--neutral-gray); }
        .flex { display: flex; gap: 12px; align-items: center; }
        .justify-between { justify-content: space-between; }
        .table-scroll { overflow-x: auto; }
        @media (max-width: 640px) {
            nav { flex-direction: column; align-items: flex-start; gap: 12px; }
            .nav-links { flex-wrap: wrap; }
        }
    </style>
</head>
<body>
    <header>
        <nav class="container">
            <div class="brand">
                <img src="<?= $safeBasePath ?>/public/logo.png" alt="logo">
                <div>Ticketing <span class="badge">Tozei</span></div>
            </div>
            <div class="nav-links">
                <a href="<?= $safeBasePath ?: '/' ?>">หน้าหลัก</a>
                <?php if ($user): ?>
                    <a href="<?= $safeBasePath ?>/tickets">ตั๋วของฉัน</a>
                    <a href="<?= $safeBasePath ?>/tickets/new">เปิดตั๋ว</a>
                    <?php if ($user['role'] === 'admin'): ?>
                        <a href="<?= $safeBasePath ?>/admin">แดชบอร์ดแอดมิน</a>
                    <?php endif; ?>
                    <a href="<?= $safeBasePath ?>/profile">โปรไฟล์</a>
                    <a href="<?= $safeBasePath ?>/logout">ออกจากระบบ</a>
                <?php else: ?>
                    <a href="<?= $safeBasePath ?>/login">เข้าสู่ระบบ</a>
                    <a href="<?= $safeBasePath ?>/register">สมัครสมาชิก</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="container">
        <?php if ($flash): ?>
            <div class="flash <?= sanitize($flash['type']) ?>"><?= sanitize($flash['message']) ?></div>
        <?php endif; ?>
        <?php include __DIR__ . '/' . $currentView . '.php'; ?>
    </main>
</body>
</html>
