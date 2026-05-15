<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$baseUrl = defined('BASE_URL') ? BASE_URL : '/HostelManagementSystem/';

// Warden pages are protected so students/owners cannot open them directly.
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
require_once __DIR__ . '/../../controllers/WardenController.php';

$pdo = DB::connect();

$studentModel = new Student($pdo);
$complaintModel = new Complaint($pdo);
$noticeModel = new Notice($pdo);

$action = 'warden_dashboard';

// This map builds the sidebar links and dashboard page title.
$pageMap = [
    'warden_dashboard' => ['label' => 'Dashboard', 'icon' => 'ph-squares-four', 'title' => 'DASHBOARD'],
    'warden_students' => ['label' => 'Students', 'icon' => 'ph-student', 'title' => 'STUDENTS'],
    'warden_food' => ['label' => 'Food', 'icon' => 'ph-bowl-food', 'title' => 'FOOD'],
    'warden_laundry' => ['label' => 'Laundry', 'icon' => 'ph-washing-machine', 'title' => 'WEEKLY LAUNDRY'],
    'warden_cleaning' => ['label' => 'Bathroom cleaning', 'icon' => 'ph-broom', 'title' => 'BATHROOM CLEANING'],
    'warden_timing' => ['label' => 'Timing', 'icon' => 'ph-clock', 'title' => 'TIME OUT RECORDS'],
    'warden_notices' => ['label' => 'Notice', 'icon' => 'ph-warning', 'title' => 'NOTICES'],
];

// Dashboard counters and summaries are prepared before rendering HTML.
$totalStudents = count($studentModel->getAll());
$pendingComplaints = (int) $pdo->query("SELECT COUNT(*) FROM complaints WHERE LOWER(status) = 'pending'")->fetchColumn();
$recentNotices = array_slice($noticeModel->all(), 0, 5);
$recentComplaints = array_slice($complaintModel->all(), 0, 5);

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
    <link rel="stylesheet" href="<?= $baseUrl ?>public/css/warden.css?v=18">
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
                <?php foreach ($pageMap as $route => $item): ?>
                    <a class="wd-nav-btn <?= $action === $route ? 'active' : '' ?>" href="<?= $baseUrl ?>index.php?action=<?= $route ?>">
                        <i class="ph <?= htmlspecialchars($item['icon']) ?>"></i><span><?= htmlspecialchars($item['label']) ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="wd-sidebar-spacer"></div>
            <a class="wd-signout-btn" href="<?= $baseUrl ?>index.php?action=logout">
                <i class="ph ph-sign-out"></i><span>Sign out</span>
            </a>
        </aside>

        <main class="wd-main wd-dashboard-main">
            <div class="wd-title-bar"><?= htmlspecialchars($pageMap[$action]['title']) ?></div>

            <section class="wd-grid wd-dashboard-grid">
                <a class="wd-card" href="<?= $baseUrl ?>index.php?action=warden_students">
                    <div class="wd-card-title">Students</div>
                    <div class="wd-card-badge"><?= $totalStudents ?></div>
                </a>
                <a class="wd-card" href="<?= $baseUrl ?>index.php?action=warden_food">
                    <div class="wd-card-title">Food</div>
                </a>
                <a class="wd-card" href="<?= $baseUrl ?>index.php?action=warden_laundry">
                    <div class="wd-card-title">Laundry</div>
                </a>
                <a class="wd-card" href="<?= $baseUrl ?>index.php?action=warden_cleaning">
                    <div class="wd-card-title">Bathroom Cleaning</div>
                </a>
                <a class="wd-card" href="<?= $baseUrl ?>index.php?action=warden_timing">
                    <div class="wd-card-title">Timing</div>
                </a>
                <a class="wd-card" href="<?= $baseUrl ?>index.php?action=warden_notices">
                    <div class="wd-card-title">Notices</div>
                </a>
                <a class="wd-card" href="#wardenRecentComplaints">
                    <div class="wd-card-title">Complaints</div>
                    <div class="wd-card-badge"><?= $pendingComplaints ?></div>
                </a>
            </section>

            <section class="wd-dashboard-panels">
                <div class="wd-panel">
                    <div class="wd-panel-head">
                        <h2>Notices</h2>
                        <a href="<?= $baseUrl ?>index.php?action=warden_notices">View all</a>
                    </div>

                    <?php if (empty($recentNotices)): ?>
                        <div class="wd-empty">No notices available.</div>
                    <?php else: ?>
                        <div class="wd-summary-list">
                            <?php foreach ($recentNotices as $notice): ?>
                                <article class="wd-summary-item">
                                    <div class="wd-summary-top">
                                        <h3><?= htmlspecialchars((string) ($notice['title'] ?? 'Untitled notice')) ?></h3>
                                        <span><?= htmlspecialchars((string) ($notice['date'] ?? '')) ?></span>
                                    </div>
                                    <p><?= htmlspecialchars((string) ($notice['description'] ?? '')) ?></p>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="wd-panel" id="wardenRecentComplaints">
                    <div class="wd-panel-head">
                        <h2>Complaints</h2>
                    </div>

                    <?php if (empty($recentComplaints)): ?>
                        <div class="wd-empty">No complaints found.</div>
                    <?php else: ?>
                        <div class="wd-summary-list">
                            <?php foreach ($recentComplaints as $complaint): ?>
                                <?php
                                $studentName = trim(
                                    ($complaint['first_name'] ?? '') . ' ' .
                                    ($complaint['middle_name'] ?? '') . ' ' .
                                    ($complaint['last_name'] ?? '')
                                );
                                if ($studentName === '') {
                                    $studentName = 'N/A';
                                }
                                $status = (string) ($complaint['status'] ?? 'Pending');
                                ?>
                                <article class="wd-summary-item">
                                    <div class="wd-summary-top">
                                        <h3><?= htmlspecialchars((string) ($complaint['title'] ?? 'Complaint')) ?></h3>
                                        <span><?= htmlspecialchars($status) ?></span>
                                    </div>
                                    <p><?= htmlspecialchars((string) ($complaint['description'] ?? '')) ?></p>
                                    <div class="wd-summary-meta">
                                        <?= htmlspecialchars($studentName) ?>
                                        <?php if (!empty($complaint['room_number'])): ?>
                                            <span>Room <?= htmlspecialchars((string) $complaint['room_number']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</div>
<div id="toast" class="wd-toast">Updated</div>
<script>
    window.BASE_URL = <?= json_encode($baseUrl) ?>;
</script>
<script src="<?= $baseUrl ?>public/js/script.js?v=6"></script>
</body>
</html>
