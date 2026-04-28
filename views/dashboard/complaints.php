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
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Inter', sans-serif; background: #f7f7f7; color: #121212; }
        .mc-page-wrap { min-height: 100vh; padding: 22px 16px; }
        .mc-body-row { display: flex; gap: 18px; min-height: calc(100vh - 44px); }
        .mc-sidebar { width: 235px; background: #082d59; color: #fff; padding: 18px 18px 22px; display: flex; flex-direction: column; }
        .mc-sidebar-logo { display: flex; align-items: center; gap: 10px; margin-bottom: 28px; font-size: 15px; }
        .mc-sidebar-logo img { width: 44px; height: 44px; object-fit: contain; }
        .mc-profile-name { font-size: 14px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px; }
        .mc-profile-role { font-size: 12px; color: rgba(255, 255, 255, 0.9); margin-bottom: 26px; }
        .mc-sidebar-nav { display: flex; flex-direction: column; gap: 16px; }
        .mc-nav-btn, .mc-signout-btn {
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
        .mc-nav-btn.active { background: #000; color: #fff; }
        .mc-sidebar-spacer { flex: 1; }
        .mc-main { flex: 1; background: #fff; padding: 28px 28px 30px; }
        .mc-title-bar {
            background: #082d59;
            border-radius: 14px;
            color: #fff;
            text-align: center;
            padding: 11px 20px;
            font-size: 20px;
            font-weight: 500;
            margin-bottom: 20px;
        }
        .mc-toolbar {
            display: flex;
            gap: 14px;
            margin-bottom: 14px;
        }
        .mc-search, .mc-filter {
            position: relative;
        }
        .mc-search { flex: 1; }
        .mc-search i, .mc-filter i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #b7b7b7;
            font-size: 16px;
        }
        .mc-search input, .mc-filter select {
            width: 100%;
            border: 0;
            border-radius: 999px;
            background: #f1f3f8;
            padding: 14px 18px 14px 40px;
            font-size: 13px;
        }
        .mc-filter { width: 200px; }
        .mc-filter select { appearance: none; }
        .mc-panel {
            background: #e8f0ff;
            border-radius: 18px;
            padding: 18px;
        }
        .mc-header-row, .mc-row {
            display: grid;
            grid-template-columns: 1.25fr 1fr 1.6fr 1fr 1fr;
            gap: 18px;
            align-items: center;
        }
        .mc-header-row {
            background: #fff;
            border-radius: 18px;
            padding: 14px 14px;
            margin-bottom: 18px;
        }
        .mc-header-pill, .mc-cell, .mc-status-select {
            border-radius: 999px;
            text-align: center;
            font-size: 13px;
        }
        .mc-header-pill {
            background: #ffc425;
            padding: 10px 12px;
        }
        .mc-row {
            background: #fff;
            border-radius: 18px;
            padding: 14px;
            margin-bottom: 18px;
        }
        .mc-cell {
            background: #eef3ff;
            padding: 10px 12px;
            color: #353535;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .mc-status-form {
            margin: 0;
        }
        .mc-status-select {
            width: 100%;
            border: 0;
            background: #c9f0dc;
            padding: 10px 12px;
            color: #2b4a38;
            cursor: pointer;
        }
        .mc-empty {
            text-align: center;
            padding: 28px 14px;
            font-size: 14px;
            color: #5b6470;
        }
        @media (max-width: 1000px) {
            .mc-header-row, .mc-row {
                grid-template-columns: 1fr;
            }
            .mc-toolbar {
                flex-direction: column;
            }
            .mc-filter {
                width: 100%;
            }
        }
        @media (max-width: 900px) {
            .mc-body-row { flex-direction: column; }
            .mc-sidebar { width: 100%; }
        }
    </style>
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
                <a class="mc-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_rooms_single"><i class="ph ph-bed"></i><span>Rooms</span></a>
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
                        <div class="mc-row">
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
</body>
</html>
