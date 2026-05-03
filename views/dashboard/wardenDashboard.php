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
    <link rel="stylesheet" href="<?= $baseUrl ?>public/css/warden.css">
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
