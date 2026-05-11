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
        r.id,
        r.number,
        r.type,
        COALESCE(CONCAT(s1.first_name, ' ', COALESCE(s1.middle_name, ''), ' ', s1.last_name), 'Unassigned') AS student1_name,
        COALESCE(s1.contact_number, '-') AS student1_contact,
        CASE WHEN r.type = 'single' THEN '-' ELSE COALESCE(CONCAT(s2.first_name, ' ', COALESCE(s2.middle_name, ''), ' ', s2.last_name), 'Unassigned') END AS student2_name,
        CASE WHEN r.type = 'single' THEN '-' ELSE COALESCE(s2.contact_number, '-') END AS student2_contact
    FROM rooms r
    LEFT JOIN students s1 ON s1.id = r.student1_id
    LEFT JOIN students s2 ON s2.id = r.student2_id
    ORDER BY CAST(r.number AS UNSIGNED) ASC, r.id ASC
");

$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

$managerName = trim((string) ($_SESSION['user_name'] ?? 'FULL NAME'));
if ($managerName === '') {
    $managerName = 'FULL NAME';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Rooms - Pentatonic Hostel</title>
    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $baseUrl ?>public/css/owner.css?v=2">
</head>
<body>
<div class="mf-page-wrap">
    <div class="mf-body-row">
        <aside class="mf-sidebar">
            <div class="mf-sidebar-logo">
                <img src="<?= $baseUrl ?>public/images/logo.png" alt="Logo">
                <span>Pentatonic hostel</span>
            </div>

            <div class="mf-profile-name"><?= htmlspecialchars($managerName) ?></div>
            <div class="mf-profile-role">OWNER</div>

            <nav class="mf-sidebar-nav">
                <a class="mf-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_dashboard"><i class="ph-fill ph-squares-four"></i><span>Dashboard</span></a>
                <a class="mf-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_students"><i class="ph ph-student"></i><span>Students</span></a>
                <a class="mf-nav-btn active" href="<?= $baseUrl ?>index.php?action=owner_rooms"><i class="ph ph-bed"></i><span>Rooms</span></a>
                <a class="mf-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_fees"><i class="ph ph-money"></i><span>Fees</span></a>
                <a class="mf-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_complaints"><i class="ph ph-megaphone"></i><span>Complaints</span></a>
                <a class="mf-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_notices"><i class="ph ph-warning"></i><span>Notice</span></a>
                <a class="mf-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_staff"><i class="ph ph-users-three"></i><span>Staffs</span></a>
            </nav>

            <div class="mf-sidebar-spacer"></div>

            <a class="mf-signout-btn" href="<?= $baseUrl ?>index.php?action=logout">
                <i class="ph ph-sign-out"></i>
                <span>Sign out</span>
            </a>
        </aside>

        <main class="mf-main">
            <div class="mf-title-bar">ALL ROOMS</div>

            <div class="mf-search">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" placeholder="Search rooms or students">
            </div>

            <section class="mf-panel">
                <?php if (empty($rooms)): ?>
                    <div class="mf-empty">No rooms found.</div>
                <?php else: ?>
                    <table class="mf-table">
                        <thead>
                            <tr>
                                <th>Room Number</th>
                                <th>Type</th>
                                <th>Student 1</th>
                                <th>Contact 1</th>
                                <th>Student 2</th>
                                <th>Contact 2</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rooms as $room): ?>
                            <tr
                                class="searchable-row"
                                data-room-number="<?= htmlspecialchars((string) ($room['number'] ?? '')) ?>"
                                data-search="<?= htmlspecialchars(strtolower('room ' . $room['number'] . ' ' . $room['type'] . ' ' . $room['student1_name'] . ' ' . $room['student1_contact'] . ' ' . $room['student2_name'] . ' ' . $room['student2_contact'])) ?>"
                            >
                                <td><?= htmlspecialchars((string) ($room['number'] ?? '')) ?></td>
                                <td><?= htmlspecialchars(ucfirst((string) ($room['type'] ?? ''))) ?></td>
                                <td><?= htmlspecialchars((string) ($room['student1_name'] ?? 'Unassigned')) ?></td>
                                <td><?= htmlspecialchars((string) ($room['student1_contact'] ?? '-')) ?></td>
                                <td><?= htmlspecialchars((string) ($room['student2_name'] ?? '-')) ?></td>
                                <td><?= htmlspecialchars((string) ($room['student2_contact'] ?? '-')) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
        </main>
    </div>
</div>
<script>
    window.BASE_URL = <?= json_encode($baseUrl) ?>;
</script>
<script src="<?= $baseUrl ?>public/js/owner-search.js"></script>
</body>
</html>
