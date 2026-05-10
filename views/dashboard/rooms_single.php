<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$baseUrl = defined('BASE_URL') ? BASE_URL : '/HostelManagementSystem/';

if (
    empty($_SESSION['logged_in']) ||
    ($_SESSION['user_role'] ?? '') !== 'owner'
) {
    header("Location: {$baseUrl}index.php?action=login");
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Room.php';

$pdo = DB::connect();
$roomModel = new Room($pdo);

$stmt = $pdo->query("
    SELECT
        r.*,
        COUNT(s.id) AS occupants_count,
        GROUP_CONCAT(
            TRIM(CONCAT(
                COALESCE(s.first_name, ''),
                ' ',
                COALESCE(s.middle_name, ''),
                ' ',
                COALESCE(s.last_name, '')
            ))
            ORDER BY s.id SEPARATOR '||'
        ) AS occupant_names,
        GROUP_CONCAT(COALESCE(s.contact_number, '') ORDER BY s.id SEPARATOR '||') AS occupant_contacts
    FROM rooms r
    LEFT JOIN students s ON s.id IN (r.student1_id, r.student2_id) OR s.room_id = r.id
    GROUP BY r.id
    ORDER BY r.number ASC
");

$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
$singleRooms = [];

foreach ($rooms as $room) {
    $typeValue = strtolower(trim((string) ($room['type'] ?? $room['room_type'] ?? '')));
    $capacityValue = (int) ($room['capacity'] ?? 0);
    $occupantsCount = (int) ($room['occupants_count'] ?? 0);

    $isSingle = false;
    if ($typeValue !== '') {
        $isSingle = strpos($typeValue, 'single') !== false;
    } elseif ($capacityValue > 0) {
        $isSingle = $capacityValue === 1;
    } else {
        $isSingle = $occupantsCount <= 1;
    }

    if ($isSingle) {
        $singleRooms[] = $room;
    }
}

$selectedRoomId = (int) ($_GET['room_id'] ?? 0);
$selectedRoom = null;

foreach ($singleRooms as $room) {
    if ((int) $room['id'] === $selectedRoomId) {
        $selectedRoom = $room;
        break;
    }
}

if ($selectedRoom === null && !empty($singleRooms)) {
    $selectedRoom = $singleRooms[0];
    $selectedRoomId = (int) $selectedRoom['id'];
}

$managerName = trim((string) ($_SESSION['user_name'] ?? 'FULL NAME'));
if ($managerName === '') {
    $managerName = 'FULL NAME';
}

$occupantNames = [];
$occupantContacts = [];
if (!empty($selectedRoom['occupant_names'])) {
    $occupantNames = array_map('trim', explode('||', (string) $selectedRoom['occupant_names']));
}
if (!empty($selectedRoom['occupant_contacts'])) {
    $occupantContacts = array_map('trim', explode('||', (string) $selectedRoom['occupant_contacts']));
}

$studentName = $occupantNames[0] ?? 'students name';
$studentContact = $occupantContacts[0] ?? 'contact number';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Single Rooms - Pentatonic Hostel</title>
    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $baseUrl ?>public/css/owner.css?v=2">
</head>
<body>
<div class="mr-page-wrap">
    <div class="mr-body-row">
        <aside class="mr-sidebar">
            <div class="mr-sidebar-logo">
                <img src="<?= $baseUrl ?>public/images/logo.png" alt="Logo">
                <span>Pentatonic hostel</span>
            </div>

            <div class="mr-profile-name"><?= htmlspecialchars($managerName) ?></div>
            <div class="mr-profile-role">OWNER</div>

            <nav class="mr-sidebar-nav">
                <a class="mr-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_dashboard"><i class="ph-fill ph-squares-four"></i><span>Dashboard</span></a>
                <a class="mr-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_students"><i class="ph ph-student"></i><span>Students</span></a>
                <a class="mr-nav-btn active" href="<?= $baseUrl ?>index.php?action=owner_rooms"><i class="ph ph-bed"></i><span>Rooms</span></a>
                <a class="mr-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_fees"><i class="ph ph-money"></i><span>Fees</span></a>
                <a class="mr-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_complaints"><i class="ph ph-megaphone"></i><span>Complaints</span></a>
                <a class="mr-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_notices"><i class="ph ph-warning"></i><span>Notice</span></a>
                <a class="mr-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_staff"><i class="ph ph-users-three"></i><span>Staffs</span></a>
            </nav>

            <div class="mr-sidebar-spacer"></div>

            <a class="mr-signout-btn" href="<?= $baseUrl ?>index.php?action=logout">
                <i class="ph ph-sign-out"></i>
                <span>Sign out</span>
            </a>
        </aside>

        <main class="mr-main">
            <div class="mr-title-bar">ROOMS</div>

            <div class="mr-search">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" placeholder="Search">
            </div>

            <div class="mr-content">
                <section class="mr-room-list">
                    <div class="mr-list-title">Room no.</div>
                    <div class="mr-room-links">
                        <?php foreach ($singleRooms as $room): ?>
                            <a
                                class="mr-room-item <?= (int) $room['id'] === $selectedRoomId ? 'active' : '' ?>"
                                data-search="<?= htmlspecialchars(strtolower(($room['number'] ?? '') . ' single ' . ($room['occupant_names'] ?? '') . ' ' . ($room['occupant_contacts'] ?? ''))) ?>"
                                href="<?= $baseUrl ?>index.php?action=owner_rooms&room_id=<?= (int) $room['id'] ?>"
                            >
                                <?= htmlspecialchars((string) ($room['number'] ?? 'Room')) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="mr-room-panel">
                    <div class="mr-pill">Room number <?= htmlspecialchars((string) ($selectedRoom['number'] ?? '')) ?></div>
                    <div class="mr-pill small">Single sitter room</div>

                    <div class="mr-student-box">
                        <div class="mr-student-name"><?= htmlspecialchars($studentName) ?></div>
                        <div class="mr-student-contact"><?= htmlspecialchars($studentContact) ?></div>
                    </div>

                    <button class="mr-save-btn" type="button">Save</button>
                </section>
            </div>
        </main>
    </div>
</div>
<script src="<?= $baseUrl ?>public/js/owner-search.js"></script>
</body>
</html>


