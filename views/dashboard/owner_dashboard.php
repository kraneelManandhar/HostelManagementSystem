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
require_once __DIR__ . '/../../models/Student.php';
require_once __DIR__ . '/../../models/Room.php';
require_once __DIR__ . '/../../models/Fee.php';
require_once __DIR__ . '/../../models/Complaint.php';
require_once __DIR__ . '/../../models/Notice.php';
require_once __DIR__ . '/../../models/User.php';

$pdo = DB::connect();

$studentModel = new Student($pdo);
$roomModel = new Room($pdo);
$feeModel = new Fee($pdo);
$complaintModel = new Complaint($pdo);
$noticeModel = new Notice($pdo);
$userModel = new User($pdo);

$totalStudents = count($studentModel->getAll());
$totalRooms = (int) $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$occupiedRooms = (int) $pdo->query("
    SELECT COUNT(*)
    FROM rooms r
    WHERE r.student1_id IS NOT NULL
       OR r.student2_id IS NOT NULL
       OR EXISTS (SELECT 1 FROM students s WHERE s.room_id = r.id)
")->fetchColumn();
$pendingComplaints = (int) $pdo->query("SELECT COUNT(*) FROM complaints WHERE LOWER(status) = 'pending'")->fetchColumn();
$unpaidFees = (int) $pdo->query("SELECT COUNT(*) FROM fees WHERE LOWER(status) <> 'paid' OR status IS NULL")->fetchColumn();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Dashboard - Pentatonic Hostel</title>
    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $baseUrl ?>public/css/owner.css">
</head>
<body>
<div class="md-page-wrap">
    <div class="md-body-row">
        <aside class="md-sidebar">
            <div class="md-sidebar-logo">
                <img src="<?= $baseUrl ?>public/images/logo.png" alt="Logo">
                <span>Pentatonic hostel</span>
            </div>

            <div class="md-profile-name">Management Dashboard</div>
            <div class="md-profile-role">OWNER</div>

            <nav class="md-sidebar-nav">
                <a class="md-nav-btn active" href="<?= $baseUrl ?>index.php?action=owner_dashboard">
                    <i class="ph-fill ph-squares-four"></i>
                    <span>Dashboard</span>
                </a>
                <a class="md-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_students">
                    <i class="ph ph-student"></i>
                    <span>Students</span>
                </a>
                <a class="md-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_rooms_single">
                    <i class="ph ph-bed"></i>
                    <span>Rooms</span>
                </a>
                <a class="md-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_fees">
                    <i class="ph ph-money"></i>
                    <span>Fees</span>
                </a>
                <a class="md-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_complaints">
                    <i class="ph ph-megaphone"></i>
                    <span>Complaints</span>
                </a>
                <a class="md-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_notices">
                    <i class="ph ph-warning"></i>
                    <span>Notice</span>
                </a>
                <a class="md-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_staff">
                    <i class="ph ph-users-three"></i>
                    <span>Staffs</span>
                </a>
            </nav>

            <div class="md-sidebar-spacer"></div>

            <a class="md-signout-btn" href="<?= $baseUrl ?>index.php?action=logout">
                <i class="ph ph-sign-out"></i>
                <span>Sign out</span>
            </a>
        </aside>

        <main class="md-main">
            <div class="md-title-bar">DASHBOARD</div>

            <section class="md-grid">
                <div class="md-card">
                    <div class="md-card-title">Students</div>
                    <div class="md-card-badge"><?= $totalStudents ?></div>
                </div>

                <div class="md-card">
                    <div class="md-card-title">Rooms</div>
                    <div class="md-card-badge"><?= $totalRooms ?></div>
                </div>

                <div class="md-card">
                    <div class="md-card-title">Occupied Rooms</div>
                    <div class="md-card-badge"><?= $occupiedRooms ?></div>
                </div>

                <div class="md-card">
                    <div class="md-card-title">Complaints</div>
                    <div class="md-card-badge"><?= $pendingComplaints ?></div>
                </div>

                <div class="md-card">
                    <div class="md-card-title">Fees</div>
                    <div class="md-card-badge"><?= $unpaidFees ?></div>
                </div>
            </section>
        </main>
    </div>
</div>
</body>
</html>

