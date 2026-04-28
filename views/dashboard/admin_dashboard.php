<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$baseUrl = defined('BASE_URL') ? BASE_URL : '/HostelManagementSystem-main/';

if (
    empty($_SESSION['logged_in']) ||
    ($_SESSION['user_role'] ?? '') !== 'admin'
) {
    header("Location: {$baseUrl}index.php?action=login");
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Student.php';
require_once __DIR__ . '/../../models/User.php';

$pdo = DB::connect();

$studentModel = new Student($pdo);
$userModel = new User($pdo);

$totalStudents = count($studentModel->getAll());
$totalRooms = (int) $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$totalUsers = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$pendingComplaints = (int) $pdo->query("SELECT COUNT(*) FROM complaints WHERE LOWER(status) = 'pending'")->fetchColumn();

$adminName = trim((string) ($_SESSION['user_name'] ?? 'ADMIN'));
if ($adminName === '') {
    $adminName = 'ADMIN';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Pentatonic Hostel</title>
    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Inter', sans-serif; background: #f7f7f7; color: #111827; }
        .ad-page-wrap { min-height: 100vh; padding: 22px 16px; }
        .ad-body-row { display: flex; gap: 18px; min-height: calc(100vh - 44px); }
        .ad-sidebar { width: 235px; background: #082d59; color: #fff; padding: 18px 18px 22px; display: flex; flex-direction: column; }
        .ad-sidebar-logo { display: flex; align-items: center; gap: 10px; margin-bottom: 28px; font-size: 15px; }
        .ad-sidebar-logo img { width: 44px; height: 44px; object-fit: contain; }
        .ad-profile-name { font-size: 14px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px; }
        .ad-profile-role { font-size: 12px; color: rgba(255,255,255,0.9); margin-bottom: 26px; }
        .ad-sidebar-nav { display: flex; flex-direction: column; gap: 16px; }
        .ad-nav-btn, .ad-signout-btn {
            width: 100%; border: 0; border-radius: 999px; background: #fff; color: #161616;
            display: flex; align-items: center; gap: 10px; padding: 11px 18px; font-size: 13px;
            cursor: pointer; text-decoration: none;
        }
        .ad-nav-btn.active { background: #000; color: #fff; }
        .ad-sidebar-spacer { flex: 1; }
        .ad-main { flex: 1; background: #fff; padding: 30px 34px; }
        .ad-title-bar {
            width: 100%; max-width: 600px; margin: 0 auto 38px;
            background: #082d59; border-radius: 14px; color: #fff;
            text-align: center; padding: 11px 20px; font-size: 20px; font-weight: 500;
        }
        .ad-grid {
            width: 100%; max-width: 760px; margin: 0 auto;
            display: grid; grid-template-columns: repeat(2, minmax(240px, 1fr)); gap: 42px 54px;
        }
        .ad-card {
            background: #e8f0ff; border-radius: 22px; min-height: 176px;
            padding: 36px 28px; display: flex; flex-direction: column;
            justify-content: center; align-items: center; position: relative;
        }
        .ad-card-title { font-size: 27px; font-weight: 400; color: #181818; }
        .ad-card-badge {
            position: absolute; right: 22px; bottom: 20px; min-width: 72px;
            padding: 8px 16px; border-radius: 999px; background: #ffc425;
            text-align: center; font-size: 14px; font-weight: 500; color: #222;
        }
        @media (max-width: 900px) {
            .ad-body-row { flex-direction: column; }
            .ad-sidebar { width: 100%; }
            .ad-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="ad-page-wrap">
    <div class="ad-body-row">
        <aside class="ad-sidebar">
            <div class="ad-sidebar-logo">
                <img src="<?= $baseUrl ?>public/images/logo.png" alt="Logo">
                <span>Pentatonic hostel</span>
            </div>
            <div class="ad-profile-name"><?= htmlspecialchars($adminName) ?></div>
            <div class="ad-profile-role">ADMIN</div>
            <nav class="ad-sidebar-nav">
                <a class="ad-nav-btn active" href="<?= $baseUrl ?>index.php?action=admin_dashboard">
                    <i class="ph-fill ph-squares-four"></i><span>Dashboard</span>
                </a>
            </nav>
            <div class="ad-sidebar-spacer"></div>
            <a class="ad-signout-btn" href="<?= $baseUrl ?>index.php?action=logout">
                <i class="ph ph-sign-out"></i><span>Sign out</span>
            </a>
        </aside>

        <main class="ad-main">
            <div class="ad-title-bar">ADMIN DASHBOARD</div>
            <section class="ad-grid">
                <div class="ad-card">
                    <div class="ad-card-title">Students</div>
                    <div class="ad-card-badge"><?= $totalStudents ?></div>
                </div>
                <div class="ad-card">
                    <div class="ad-card-title">Rooms</div>
                    <div class="ad-card-badge"><?= $totalRooms ?></div>
                </div>
                <div class="ad-card">
                    <div class="ad-card-title">Staff / Users</div>
                    <div class="ad-card-badge"><?= $totalUsers ?></div>
                </div>
                <div class="ad-card">
                    <div class="ad-card-title">Complaints</div>
                    <div class="ad-card-badge"><?= $pendingComplaints ?></div>
                </div>
            </section>
        </main>
    </div>
</div>
</body>
</html>
