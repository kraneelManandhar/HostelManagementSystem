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

$action = 'warden_rooms';

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

$data = $wardenController->getRooms();

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
    <title>Rooms - Pentatonic Hostel</title>
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

            <div class="wd-toolbar">
                <label class="wd-search">
                    <i class="ph ph-magnifying-glass"></i>
                    <input type="search" id="wardenSearch" placeholder="Search rooms">
                </label>
            </div>

            <div class="wd-room-add">
                <form method="post" action="<?= $baseUrl ?>index.php?action=warden_add_room">
                    <input type="text" name="number" placeholder="Room no." required>
                    <select name="type">
                        <option value="single">Single</option>
                        <option value="double" selected>Double</option>
                    </select>
                    <button type="submit">Add room</button>
                </form>
            </div>

            <div class="table-box wd-room-table">

                <div class="table-header rooms-header">
                    <span>Room</span>
                    <span>Type</span>
                    <span>Student 1</span>
                    <span>Student 2</span>
                    <span>Save</span>
                </div>

                <?php foreach($data as $room):
                    $roomId = (int) $room['id'];
                    $studentOptions = $wardenController->getRoomStudentOptions($roomId);
                ?>

                <form
                    class="row rooms-row searchable-row"
                    method="post"
                    action="<?= $baseUrl ?>index.php?action=warden_save_room"
                    data-room-number="<?= htmlspecialchars((string) ($room['number'] ?? '')) ?>"
                    data-search="<?= htmlspecialchars(strtolower('room ' . ($room['number'] ?? '') . ' ' . ($room['type'] ?? '') . ' ' . ($room['student1_name'] ?? '') . ' ' . ($room['student1_contact'] ?? '') . ' ' . ($room['student2_name'] ?? '') . ' ' . ($room['student2_contact'] ?? ''))) ?>"
                >
                    <input type="hidden" name="room_id" value="<?= $roomId ?>">

                    <input class="cell wd-input" type="text" name="number" value="<?= htmlspecialchars($room['number']) ?>" aria-label="Room number" required>

                    <select class="cell wd-select room-type-select" name="type" aria-label="Room type">
                        <option value="single" <?= ($room['type'] ?? '') === 'single' ? 'selected' : '' ?>>Single</option>
                        <option value="double" <?= ($room['type'] ?? '') === 'double' ? 'selected' : '' ?>>Double</option>
                    </select>

                    <select class="cell wd-select" name="student1_id" aria-label="Student one">
                        <option value="0">Unassigned</option>
                        <?php foreach ($studentOptions as $student): ?>
                            <option value="<?= (int) $student['id'] ?>" <?= (int) ($room['student1_id'] ?? 0) === (int) $student['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($student['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select class="cell wd-select second-student-select" name="student2_id" aria-label="Student two">
                        <option value="0">Unassigned</option>
                        <?php foreach ($studentOptions as $student): ?>
                            <option value="<?= (int) $student['id'] ?>" <?= (int) ($room['student2_id'] ?? 0) === (int) $student['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($student['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button class="wd-mini-btn" type="submit">Save</button>
                </form>

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
</body>
</html>
