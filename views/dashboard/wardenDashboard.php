<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$baseUrl = defined('BASE_URL') ? BASE_URL : '/HostelManagementSystem/';

if (
    empty($_SESSION['logged_in']) ||
    ($_SESSION['user_role'] ?? '') !== 'warden'
) {
    header("Location: {$baseUrl}index.php?action=login");
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Student.php';
require_once __DIR__ . '/../../models/Complaint.php';
require_once __DIR__ . '/../../models/Notice.php';

$pdo = DB::connect();

$studentModel = new Student($pdo);
$complaintModel = new Complaint($pdo);
$noticeModel = new Notice($pdo);

$totalStudents = count($studentModel->getAll());
$totalRooms = (int) $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$pendingComplaints = (int) $pdo->query("SELECT COUNT(*) FROM complaints WHERE LOWER(status) = 'pending'")->fetchColumn();
$notices = $noticeModel->all();

$wardenName = trim((string) ($_SESSION['user_name'] ?? 'WARDEN'));
if ($wardenName === '') {
    $wardenName = 'WARDEN';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warden Dashboard - Pentatonic Hostel</title>
    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Inter', sans-serif; background: #f7f7f7; color: #111827; }
        .wd-page-wrap { min-height: 100vh; padding: 22px 16px; }
        .wd-body-row { display: flex; gap: 18px; min-height: calc(100vh - 44px); }
        .wd-sidebar { width: 235px; background: #082d59; color: #fff; padding: 18px 18px 22px; display: flex; flex-direction: column; }
        .wd-sidebar-logo { display: flex; align-items: center; gap: 10px; margin-bottom: 28px; font-size: 15px; font-weight: 400; }
        .wd-sidebar-logo img { width: 44px; height: 44px; object-fit: contain; }
        .wd-profile-name { font-size: 14px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px; }
        .wd-profile-role { font-size: 12px; font-weight: 400; color: rgba(255, 255, 255, 0.9); margin-bottom: 26px; }
        .wd-sidebar-nav { display: flex; flex-direction: column; gap: 16px; }
        .wd-nav-btn, .wd-signout-btn {
            width: 100%; border: 0; border-radius: 999px; background: #fff; color: #161616;
            display: flex; align-items: center; gap: 10px; padding: 11px 18px; font-size: 13px;
            cursor: pointer; text-decoration: none;
        }
        .wd-nav-btn.active { background: #000; color: #fff; }
        .wd-sidebar-spacer { flex: 1; }
        .wd-main { flex: 1; background: #fff; padding: 30px 34px; }
        .wd-title-bar {
            width: 100%; max-width: 600px; margin: 0 auto 38px;
            background: #082d59; border-radius: 14px; color: #fff;
            text-align: center; padding: 11px 20px; font-size: 20px; font-weight: 500;
        }
        .wd-grid {
            width: 100%; max-width: 760px; margin: 0 auto 40px;
            display: grid; grid-template-columns: repeat(2, minmax(240px, 1fr)); gap: 42px 54px;
        }
        .wd-card {
            background: #e8f0ff; border-radius: 22px; min-height: 176px;
            padding: 36px 28px; display: flex; flex-direction: column;
            justify-content: center; align-items: center; position: relative;
        }
        .wd-card-title { font-size: 27px; font-weight: 400; color: #181818; }
        .wd-card-badge {
            position: absolute; right: 22px; bottom: 20px; min-width: 72px;
            padding: 8px 16px; border-radius: 999px; background: #ffc425;
            text-align: center; font-size: 14px; font-weight: 500; color: #222;
        }
        .wd-notices { max-width: 760px; margin: 0 auto; }
        .wd-notices h3 { font-size: 18px; margin-bottom: 16px; }
        .wd-notice-card {
            background: #e8f0ff; border-radius: 18px; padding: 18px 20px; margin-bottom: 14px;
        }
        .wd-notice-card h4 { margin: 0 0 6px; font-size: 15px; }
        .wd-notice-card p { margin: 0; font-size: 13px; color: #444; }
        .wd-notice-meta { font-size: 11px; color: #888; margin-top: 8px; }
        @media (max-width: 900px) {
            .wd-body-row { flex-direction: column; }
            .wd-sidebar { width: 100%; }
            .wd-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="wd-page-wrap">
    <div class="wd-body-row">
        <aside class="wd-sidebar">
            <div class="wd-sidebar-logo">
                <img src="<?= $baseUrl ?>public/images/logo.png" alt="Logo">
                <span>Pentatonic hostel</span>
            </div>
            <div class="wd-profile-name"><?= htmlspecialchars($wardenName) ?></div>
            <div class="wd-profile-role">WARDEN</div>
            <nav class="wd-sidebar-nav">
                <a class="wd-nav-btn active" href="<?= $baseUrl ?>index.php?action=warden_dashboard">
                    <i class="ph-fill ph-squares-four"></i><span>Dashboard</span>
                </a>
            </nav>
            <div class="wd-sidebar-spacer"></div>
            <a class="wd-signout-btn" href="<?= $baseUrl ?>index.php?action=logout">
                <i class="ph ph-sign-out"></i><span>Sign out</span>
            </a>
        </aside>

        <main class="wd-main">
            <div class="wd-title-bar">WARDEN DASHBOARD</div>

            <section class="wd-grid">
                <div class="wd-card">
                    <div class="wd-card-title">Students</div>
                    <div class="wd-card-badge"><?= $totalStudents ?></div>
                </div>
                <div class="wd-card">
                    <div class="wd-card-title">Rooms</div>
                    <div class="wd-card-badge"><?= $totalRooms ?></div>
                </div>
                <div class="wd-card">
                    <div class="wd-card-title">Complaints</div>
                    <div class="wd-card-badge"><?= $pendingComplaints ?></div>
                </div>
            </section>

            <section class="wd-notices">
                <h3>Recent Notices</h3>
                <?php if (empty($notices)): ?>
                    <p style="font-size:13px; color:#888;">No notices available.</p>
                <?php else: ?>
                    <?php foreach (array_slice($notices, 0, 5) as $n): ?>
                        <div class="wd-notice-card">
                            <h4><?= htmlspecialchars($n['title']) ?></h4>
                            <p><?= htmlspecialchars($n['description']) ?></p>
                            <div class="wd-notice-meta"><?= htmlspecialchars($n['date']) ?> | <?= htmlspecialchars($n['author'] ?? 'HOSTEL MANAGEMENT') ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </main>
    </div>
</div>
</body>
</html>