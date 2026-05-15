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
$wardenController = new WardenController($pdo);

$action = 'warden_students';

// This map builds the sidebar links and the current page title.
$pageMap = [
    'warden_dashboard' => ['label' => 'Dashboard', 'icon' => 'ph-squares-four', 'title' => 'WARDEN DASHBOARD'],
    'warden_students' => ['label' => 'Students', 'icon' => 'ph-student', 'title' => 'STUDENTS'],
    'warden_food' => ['label' => 'Food', 'icon' => 'ph-bowl-food', 'title' => 'FOOD'],
    'warden_laundry' => ['label' => 'Laundry', 'icon' => 'ph-washing-machine', 'title' => 'WEEKLY LAUNDRY'],
    'warden_cleaning' => ['label' => 'Bathroom cleaning', 'icon' => 'ph-broom', 'title' => 'BATHROOM CLEANING'],
    'warden_timing' => ['label' => 'Timing', 'icon' => 'ph-clock', 'title' => 'TIME OUT RECORDS'],
    'warden_notices' => ['label' => 'Notice', 'icon' => 'ph-warning', 'title' => 'NOTICES'],
];

// Fetch page data before rendering the HTML table.
$totalStudents = count($studentModel->getAll());
$pendingComplaints = (int) $pdo->query("SELECT COUNT(*) FROM complaints WHERE LOWER(status) = 'pending'")->fetchColumn();
$notices = $noticeModel->all();

$data = $wardenController->getStudents();

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
    <title>Students - Pentatonic Hostel</title>
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

        <main class="wd-main">
            <div class="wd-title-bar"><?= htmlspecialchars($pageMap[$action]['title']) ?></div>

            <div class="wd-toolbar wd-students-toolbar">
                <label class="wd-search wd-students-search">
                    <i class="ph ph-magnifying-glass"></i>
                    <input type="search" id="wardenSearch" class="warden-search" placeholder="Search">
                </label>
            </div>

            <div class="table-box wd-students-table">
                <div class="table-header students-header">
                    <span>Student ID</span>
                    <span>Student's name</span>
                    <span>Email</span>
                    <span>Contact</span>
                    <span>Room</span>
                </div>

                <?php foreach($data as $s): ?>
                <div
                    class="row students-row searchable-row"
                    data-room-number="<?= htmlspecialchars((string) ($s['room_number'] ?? '')) ?>"
                    data-search="<?= htmlspecialchars(strtolower('id ' . (int) ($s['id'] ?? 0) . ' ' . ($s['name'] ?? '') . ' ' . ($s['email'] ?? '') . ' ' . ($s['contact_number'] ?? '') . ' room ' . ($s['room_number'] ?? ''))) ?>"
                >
                    <div class="cell">#<?= (int) ($s['id'] ?? 0) ?></div>
                    <div class="cell">
                        <?= htmlspecialchars($s['name']) ?>
                    </div>
                    <div class="cell wd-email-cell">
                        <?= htmlspecialchars($s['email']) ?>
                    </div>

                    <div class="cell">
                        <?= htmlspecialchars($s['contact_number']) ?>
                    </div>

                    <div class="cell">
                        <?= htmlspecialchars($s['room_number'] ?? 'Unassigned') ?>
                    </div>
                </div>
                <?php endforeach; ?>

            </div>
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
