<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$baseUrl = defined('BASE_URL') ? BASE_URL : '/HostelManagementSystem-main/';

if (
    empty($_SESSION['logged_in']) ||
    ($_SESSION['user_role'] ?? '') !== 'manager'
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
$occupiedRooms = (int) $pdo->query("SELECT COUNT(DISTINCT room_id) FROM students WHERE room_id IS NOT NULL")->fetchColumn();
$pendingComplaints = (int) $pdo->query("SELECT COUNT(*) FROM complaints WHERE LOWER(status) = 'pending'")->fetchColumn();
$unpaidFees = (int) $pdo->query("SELECT COUNT(*) FROM fees WHERE LOWER(status) <> 'paid' OR status IS NULL")->fetchColumn();

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
    <title>Manager Dashboard - Pentatonic Hostel</title>
    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: #f7f7f7;
            color: #111827;
        }

        .md-page-wrap {
            min-height: 100vh;
            padding: 22px 16px;
        }

        .md-body-row {
            display: flex;
            gap: 18px;
            min-height: calc(100vh - 44px);
        }

        .md-sidebar {
            width: 235px;
            background: #082d59;
            color: #fff;
            border-radius: 0;
            padding: 18px 18px 22px;
            display: flex;
            flex-direction: column;
        }

        .md-sidebar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 28px;
            font-size: 15px;
            font-weight: 400;
        }

        .md-sidebar-logo img {
            width: 44px;
            height: 44px;
            object-fit: contain;
        }

        .md-profile-name {
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .md-profile-role {
            font-size: 12px;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 26px;
        }

        .md-sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .md-nav-btn,
        .md-signout-btn {
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

        .md-nav-btn i,
        .md-signout-btn i {
            font-size: 16px;
        }

        .md-nav-btn.active {
            background: #000;
            color: #fff;
        }

        .md-sidebar-spacer {
            flex: 1;
        }

        .md-main {
            flex: 1;
            background: #fff;
            padding: 30px 34px;
        }

        .md-title-bar {
            width: 100%;
            max-width: 600px;
            margin: 0 auto 38px;
            background: #082d59;
            border-radius: 14px;
            color: #fff;
            text-align: center;
            padding: 11px 20px;
            font-size: 20px;
            font-weight: 500;
            letter-spacing: 0.4px;
        }

        .md-grid {
            width: 100%;
            max-width: 760px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(2, minmax(240px, 1fr));
            gap: 42px 54px;
        }

        .md-card {
            background: #e8f0ff;
            border-radius: 22px;
            min-height: 176px;
            padding: 36px 28px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .md-card-title {
            font-size: 27px;
            font-weight: 400;
            color: #181818;
        }

        .md-card-badge {
            position: absolute;
            right: 22px;
            bottom: 20px;
            min-width: 72px;
            padding: 8px 16px;
            border-radius: 999px;
            background: #ffc425;
            text-align: center;
            font-size: 14px;
            font-weight: 500;
            color: #222;
        }

        @media (max-width: 900px) {
            .md-body-row {
                flex-direction: column;
            }

            .md-sidebar {
                width: 100%;
            }

            .md-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="md-page-wrap">
    <div class="md-body-row">
        <aside class="md-sidebar">
            <div class="md-sidebar-logo">
                <img src="<?= $baseUrl ?>public/images/logo.png" alt="Logo">
                <span>Pentatonic hostel</span>
            </div>

            <div class="md-profile-name"><?= htmlspecialchars($managerName) ?></div>
            <div class="md-profile-role">OWNER</div>

            <nav class="md-sidebar-nav">
                <a class="md-nav-btn active" href="<?= $baseUrl ?>index.php?action=manager_dashboard">
                    <i class="ph-fill ph-squares-four"></i>
                    <span>Dashboard</span>
                </a>
                <a class="md-nav-btn" href="<?= $baseUrl ?>index.php?action=manager_students">
                    <i class="ph ph-student"></i>
                    <span>Students</span>
                </a>
                <a class="md-nav-btn" href="<?= $baseUrl ?>index.php?action=manager_rooms_single">
                    <i class="ph ph-bed"></i>
                    <span>Rooms</span>
                </a>
                <a class="md-nav-btn" href="<?= $baseUrl ?>index.php?action=manager_fees">
                    <i class="ph ph-money"></i>
                    <span>Fees</span>
                </a>
                <a class="md-nav-btn" href="<?= $baseUrl ?>index.php?action=manager_complaints">
                    <i class="ph ph-megaphone"></i>
                    <span>Complaints</span>
                </a>
                <a class="md-nav-btn" href="<?= $baseUrl ?>index.php?action=manager_notices">
                    <i class="ph ph-warning"></i>
                    <span>Notice</span>
                </a>
                <a class="md-nav-btn" href="<?= $baseUrl ?>index.php?action=manager_staff">
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
