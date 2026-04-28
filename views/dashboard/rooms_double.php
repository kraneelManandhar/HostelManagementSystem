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
    LEFT JOIN students s ON s.room_id = r.id
    GROUP BY r.id
    ORDER BY r.number ASC
");

$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
$doubleRooms = [];

foreach ($rooms as $room) {
    $typeValue = strtolower(trim((string) ($room['type'] ?? $room['room_type'] ?? '')));
    $capacityValue = (int) ($room['capacity'] ?? 0);
    $occupantsCount = (int) ($room['occupants_count'] ?? 0);

    $isDouble = false;
    if ($typeValue !== '') {
        $isDouble = strpos($typeValue, 'double') !== false;
    } elseif ($capacityValue > 0) {
        $isDouble = $capacityValue >= 2;
    } else {
        $isDouble = $occupantsCount >= 2;
    }

    if ($isDouble) {
        $doubleRooms[] = $room;
    }
}

$selectedRoomId = (int) ($_GET['room_id'] ?? 0);
$selectedRoom = null;

foreach ($doubleRooms as $room) {
    if ((int) $room['id'] === $selectedRoomId) {
        $selectedRoom = $room;
        break;
    }
}

if ($selectedRoom === null && !empty($doubleRooms)) {
    $selectedRoom = $doubleRooms[0];
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

$studentOneName = $occupantNames[0] ?? 'students name';
$studentOneContact = $occupantContacts[0] ?? 'number';
$studentTwoName = $occupantNames[1] ?? 'students name';
$studentTwoContact = $occupantContacts[1] ?? 'number';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Double Rooms - Pentatonic Hostel</title>
    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Inter', sans-serif; background: #f7f7f7; color: #121212; }
        .mr-page-wrap { min-height: 100vh; padding: 22px 16px; }
        .mr-body-row { display: flex; gap: 18px; min-height: calc(100vh - 44px); }
        .mr-sidebar { width: 235px; background: #082d59; color: #fff; padding: 18px 18px 22px; display: flex; flex-direction: column; }
        .mr-sidebar-logo { display: flex; align-items: center; gap: 10px; margin-bottom: 28px; font-size: 15px; }
        .mr-sidebar-logo img { width: 44px; height: 44px; object-fit: contain; }
        .mr-profile-name { font-size: 14px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px; }
        .mr-profile-role { font-size: 12px; color: rgba(255, 255, 255, 0.9); margin-bottom: 26px; }
        .mr-sidebar-nav { display: flex; flex-direction: column; gap: 16px; }
        .mr-nav-btn, .mr-signout-btn {
            width: 100%;
            border: 0;
            border-radius: 999px;
            background: #fff;
            color: #161616;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 18px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
        }
        .mr-nav-btn.active { background: #000; color: #fff; }
        .mr-sidebar-spacer { flex: 1; }
        .mr-main { flex: 1; background: #fff; padding: 28px 34px; }
        .mr-title-bar {
            background: #082d59;
            border-radius: 14px;
            color: #fff;
            text-align: center;
            padding: 11px 20px;
            font-size: 20px;
            font-weight: 500;
            margin-bottom: 22px;
        }
        .mr-search {
            position: relative;
            margin-bottom: 26px;
        }
        .mr-search i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #b7b7b7;
            font-size: 16px;
        }
        .mr-search input {
            width: 100%;
            border: 0;
            border-radius: 999px;
            background: #f1f3f8;
            padding: 14px 18px 14px 40px;
            font-size: 13px;
        }
        .mr-content { display: grid; grid-template-columns: 124px 1fr; gap: 36px; }
        .mr-room-list, .mr-room-panel { background: #e8f0ff; border-radius: 18px; }
        .mr-room-list { padding: 16px 10px; }
        .mr-list-title {
            background: #ffc425;
            border-radius: 14px;
            text-align: center;
            padding: 10px 8px;
            font-size: 13px;
            margin-bottom: 14px;
        }
        .mr-room-links { display: flex; flex-direction: column; gap: 10px; }
        .mr-room-item {
            display: block;
            width: 100%;
            background: #fff;
            border-radius: 999px;
            text-align: center;
            padding: 7px 10px;
            color: #111;
            text-decoration: none;
            font-size: 13px;
        }
        .mr-room-item.active { background: #000; color: #fff; }
        .mr-room-panel {
            padding: 36px 26px 28px;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 384px;
        }
        .mr-pill {
            background: #fff;
            border-radius: 999px;
            padding: 12px 30px;
            font-size: 15px;
            text-align: center;
        }
        .mr-pill.small {
            margin-top: 12px;
            padding: 9px 26px;
            font-size: 12px;
        }
        .mr-occupants {
            margin-top: 78px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 36px;
        }
        .mr-occupant {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 18px;
        }
        .mr-student-name {
            min-width: 140px;
            background: #fff;
            border-radius: 16px;
            padding: 16px 18px;
            text-align: center;
            font-size: 15px;
        }
        .mr-student-contact {
            min-width: 84px;
            background: #fff;
            border-radius: 999px;
            padding: 8px 14px;
            text-align: center;
            font-size: 11px;
        }
        .mr-save-btn {
            margin-top: auto;
            border: 0;
            border-radius: 999px;
            background: #ffc425;
            color: #1a1a1a;
            padding: 10px 26px;
            font-size: 13px;
        }
        @media (max-width: 900px) {
            .mr-body-row { flex-direction: column; }
            .mr-sidebar { width: 100%; }
            .mr-content { grid-template-columns: 1fr; }
            .mr-occupants { grid-template-columns: 1fr; }
        }
    </style>
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
                <a class="mr-nav-btn active" href="<?= $baseUrl ?>index.php?action=owner_rooms_double"><i class="ph ph-bed"></i><span>Rooms</span></a>
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
            <div class="mr-title-bar">Rooms</div>

            <div class="mr-search">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" placeholder="Search">
            </div>

            <div class="mr-content">
                <section class="mr-room-list">
                    <div class="mr-list-title">Room no.</div>
                    <div class="mr-room-links">
                        <?php foreach ($doubleRooms as $room): ?>
                            <a
                                class="mr-room-item <?= (int) $room['id'] === $selectedRoomId ? 'active' : '' ?>"
                                href="<?= $baseUrl ?>index.php?action=owner_rooms_double&room_id=<?= (int) $room['id'] ?>"
                            >
                                <?= htmlspecialchars((string) ($room['number'] ?? 'Room')) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="mr-room-panel">
                    <div class="mr-pill">Room number <?= htmlspecialchars((string) ($selectedRoom['number'] ?? '')) ?></div>
                    <div class="mr-pill small">double sitter room</div>

                    <div class="mr-occupants">
                        <div class="mr-occupant">
                            <div class="mr-student-name"><?= htmlspecialchars($studentOneName) ?></div>
                            <div class="mr-student-contact"><?= htmlspecialchars($studentOneContact) ?></div>
                        </div>
                        <div class="mr-occupant">
                            <div class="mr-student-name"><?= htmlspecialchars($studentTwoName) ?></div>
                            <div class="mr-student-contact"><?= htmlspecialchars($studentTwoContact) ?></div>
                        </div>
                    </div>

                    <button class="mr-save-btn" type="button">Save</button>
                </section>
            </div>
        </main>
    </div>
</div>
</body>
</html>