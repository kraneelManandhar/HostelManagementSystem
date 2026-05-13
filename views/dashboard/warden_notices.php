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
require_once __DIR__ . '/../../controllers/WardenController.php';

$pdo = DB::connect();

$studentModel = new Student($pdo);
$complaintModel = new Complaint($pdo);
$noticeModel = new Notice($pdo);
$wardenController = new WardenController($pdo);

$action = 'warden_notices';

$pageMap = [
    'warden_dashboard' => ['label' => 'Dashboard', 'icon' => 'ph-squares-four', 'title' => 'WARDEN DASHBOARD'],
    'warden_students' => ['label' => 'Students', 'icon' => 'ph-student', 'title' => 'STUDENTS'],
    'warden_food' => ['label' => 'Food', 'icon' => 'ph-bowl-food', 'title' => 'FOOD'],
    'warden_laundry' => ['label' => 'Laundry', 'icon' => 'ph-washing-machine', 'title' => 'WEEKLY LAUNDRY'],
    'warden_rooms' => ['label' => 'Rooms', 'icon' => 'ph-bed', 'title' => 'ROOM ASSIGNMENT'],
    'warden_cleaning' => ['label' => 'Bathroom cleaning', 'icon' => 'ph-broom', 'title' => 'BATHROOM CLEANING'],
    'warden_timing' => ['label' => 'Timing', 'icon' => 'ph-clock', 'title' => 'TIME OUT RECORDS'],
    'warden_notices' => ['label' => 'Notice', 'icon' => 'ph-warning', 'title' => 'NOTICES'],
];

$totalStudents = count($studentModel->getAll());
$totalRooms = (int) $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$pendingComplaints = (int) $pdo->query("SELECT COUNT(*) FROM complaints WHERE LOWER(status) = 'pending'")->fetchColumn();
$notices = $noticeModel->all();

$data = $notices;

$noticeEditId = (int) ($_GET['edit_notice'] ?? 0);
$editNotice = null;
foreach ($notices as $notice) {
    if ((int) ($notice['id'] ?? 0) === $noticeEditId) {
        $editNotice = $notice;
        break;
    }
}

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
    <title>Notices - Pentatonic Hostel</title>
    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $baseUrl ?>public/css/warden.css?v=2">
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

            <div class="wd-notice-editor">
                <form method="post" action="<?= $baseUrl ?>index.php?action=warden_save_notice">
                    <input type="hidden" name="notice_id" value="<?= (int) ($editNotice['id'] ?? 0) ?>">

                    <div class="wd-notice-field">
                        <label>Notice title</label>
                        <input type="text" name="title" placeholder="title" value="<?= htmlspecialchars($editNotice['title'] ?? '') ?>" required>
                    </div>

                    <div class="wd-notice-field">
                        <label>Description</label>
                        <textarea name="description" placeholder="message" required><?= htmlspecialchars($editNotice['description'] ?? '') ?></textarea>
                    </div>

                    <div class="wd-notice-actions">
                        <input type="date" name="date" value="<?= htmlspecialchars($editNotice['date'] ?? date('Y-m-d')) ?>">
                        <button
                            type="submit"
                            <?= $editNotice ? 'data-confirm="Are you sure you want to save changes to this notice?"' : '' ?>
                        ><?= $editNotice ? 'Update notice' : 'Send notice' ?></button>
                        <?php if ($editNotice): ?>
                            <a href="<?= $baseUrl ?>index.php?action=warden_notices">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="wd-notice-list">
                <div class="wd-toolbar notice-search">
                    <label class="wd-search">
                        <i class="ph ph-magnifying-glass"></i>
                        <input type="search" id="wardenSearch" placeholder="Search notices">
                    </label>
                </div>

                <?php foreach ($data as $notice): ?>
                    <article class="wd-notice-card searchable-row" data-search="<?= htmlspecialchars(strtolower(($notice['title'] ?? '') . ' ' . ($notice['description'] ?? '') . ' ' . ($notice['date'] ?? ''))) ?>">
                        <h4><?= htmlspecialchars($notice['title']) ?></h4>
                        <p><?= htmlspecialchars($notice['description']) ?></p>
                        <div class="wd-notice-meta">
                            <?= htmlspecialchars($notice['date']) ?> |
                            <?= htmlspecialchars($notice['author'] ?? 'HOSTEL MANAGEMENT') ?>
                            <a href="<?= $baseUrl ?>index.php?action=warden_notices&edit_notice=<?= (int) $notice['id'] ?>" data-confirm="Are you sure you want to edit this notice?">Edit</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</div>
<div id="toast" class="wd-toast">Updated</div>
<script>
    window.BASE_URL = <?= json_encode($baseUrl) ?>;
</script>
<script src="<?= $baseUrl ?>public/js/script.js"></script>
<script src="<?= $baseUrl ?>public/js/confirm-actions.js?v=1"></script>
</body>
</html>
