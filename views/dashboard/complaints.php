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
require_once __DIR__ . '/../../models/Complaint.php';

$pdo = DB::connect();
$complaintModel = new Complaint($pdo);

$allowedStatuses = ['Pending', 'In Progress', 'Resolved'];
$selectedStatus = $_GET['status'] ?? 'All';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $complaintId = (int) ($_POST['complaint_id'] ?? 0);
    $newStatus = trim($_POST['status'] ?? '');

    if ($complaintId > 0 && in_array($newStatus, $allowedStatuses, true)) {
        $stmt = $pdo->prepare("UPDATE complaints SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $complaintId]);
    }

    $redirectUrl = $baseUrl . 'index.php?action=owner_complaints';
    if ($selectedStatus !== 'All') {
        $redirectUrl .= '&status=' . urlencode($selectedStatus);
    }
    header('Location: ' . $redirectUrl);
    exit;
}

$sql = "
    SELECT c.*, s.first_name, s.middle_name, s.last_name, r.number AS room_number
    FROM complaints c
    LEFT JOIN students s ON s.id = c.student_id
    LEFT JOIN rooms r ON r.id = c.room_id
";
$params = [];

if (in_array($selectedStatus, $allowedStatuses, true)) {
    $sql .= " WHERE c.status = ?";
    $params[] = $selectedStatus;
}

$sql .= " ORDER BY c.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Management Complaints - Pentatonic Hostel</title>
    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $baseUrl ?>public/css/owner.css?v=2">
</head>
<body>
<div class="mc-page-wrap">
    <div class="mc-body-row">
        <aside class="mc-sidebar">
            <div class="mc-sidebar-logo">
                <img src="<?= $baseUrl ?>public/images/logo.png" alt="Logo">
                <span>Pentatonic hostel</span>
            </div>

            <div class="mc-profile-name"><?= htmlspecialchars($managerName) ?></div>
            <div class="mc-profile-role">OWNER</div>

            <nav class="mc-sidebar-nav">
                <a class="mc-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_dashboard"><i class="ph-fill ph-squares-four"></i><span>Dashboard</span></a>
                <a class="mc-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_students"><i class="ph ph-student"></i><span>Students</span></a>
                <a class="mc-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_rooms"><i class="ph ph-bed"></i><span>Rooms</span></a>
                <a class="mc-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_fees"><i class="ph ph-money"></i><span>Fees</span></a>
                <a class="mc-nav-btn active" href="<?= $baseUrl ?>index.php?action=owner_complaints"><i class="ph ph-megaphone"></i><span>Complaints</span></a>
                <a class="mc-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_notices"><i class="ph ph-warning"></i><span>Notice</span></a>
                <a class="mc-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_staff"><i class="ph ph-users-three"></i><span>Staffs</span></a>
            </nav>

            <div class="mc-sidebar-spacer"></div>

            <a class="mc-signout-btn" href="<?= $baseUrl ?>index.php?action=logout">
                <i class="ph ph-sign-out"></i>
                <span>Sign out</span>
            </a>
        </aside>

        <main class="mc-main">
            <div class="mc-title-bar">ALL COMPLAINTS</div>

            <div class="mc-toolbar">
                <div class="mc-search">
                    <i class="ph ph-magnifying-glass"></i>
                    <input type="text" placeholder="Search">
                </div>

                <form class="mc-filter" method="get" action="<?= $baseUrl ?>index.php">
                    <input type="hidden" name="action" value="owner_complaints">
                    <i class="ph ph-funnel"></i>
                    <select name="status" onchange="this.form.submit()">
                        <option value="All" <?= $selectedStatus === 'All' ? 'selected' : '' ?>>All Status</option>
                        <option value="Pending" <?= $selectedStatus === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="In Progress" <?= $selectedStatus === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="Resolved" <?= $selectedStatus === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
                    </select>
                </form>
            </div>

            <section class="mc-panel">
                <div class="mc-header-row">
                    <div class="mc-header-pill">Student</div>
                    <div class="mc-header-pill">Issue</div>
                    <div class="mc-header-pill">Description</div>
                    <div class="mc-header-pill">Room number</div>
                    <div class="mc-header-pill">Status</div>
                </div>

                <?php if (empty($complaints)): ?>
                    <div class="mc-empty">No complaints found.</div>
                <?php else: ?>
                    <?php foreach ($complaints as $complaint): ?>
                        <?php
                        $studentName = trim(
                            ($complaint['first_name'] ?? '') . ' ' .
                            ($complaint['middle_name'] ?? '') . ' ' .
                            ($complaint['last_name'] ?? '')
                        );
                        if ($studentName === '') {
                            $studentName = 'N/A';
                        }
                        ?>
                        <div class="mc-row" data-search="<?= htmlspecialchars(strtolower($studentName . ' ' . ($complaint['title'] ?? '') . ' ' . ($complaint['description'] ?? '') . ' ' . ($complaint['room_number'] ?? '') . ' ' . ($complaint['status'] ?? ''))) ?>">
                            <div class="mc-cell"><?= htmlspecialchars($studentName) ?></div>
                            <div class="mc-cell"><?= htmlspecialchars((string) ($complaint['title'] ?? '')) ?></div>
                            <div class="mc-cell"><?= htmlspecialchars((string) ($complaint['description'] ?? '')) ?></div>
                            <div class="mc-cell"><?= htmlspecialchars((string) ($complaint['room_number'] ?? '')) ?></div>
                            <form class="mc-status-form" method="post">
                                <input type="hidden" name="complaint_id" value="<?= (int) $complaint['id'] ?>">
                                <input type="hidden" name="current_filter" value="<?= htmlspecialchars($selectedStatus) ?>">
                                <select class="mc-status-select" name="status" onchange="this.form.submit()">
                                    <option value="Pending" <?= ($complaint['status'] ?? '') === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="In Progress" <?= ($complaint['status'] ?? '') === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="Resolved" <?= ($complaint['status'] ?? '') === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
                                </select>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </main>
    </div>
</div>
<script src="<?= $baseUrl ?>public/js/owner-search.js"></script>
</body>
</html>


