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
require_once __DIR__ . '/../../models/Fee.php';

$pdo = DB::connect();
$feeModel = new Fee($pdo);
$supportsMarkPaid = method_exists($feeModel, 'markAsPaid');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $supportsMarkPaid) {
    $feeId = (int) ($_POST['fee_id'] ?? 0);
    if ($feeId > 0) {
        $feeModel->markAsPaid($feeId);
    }
    header('Location: ' . $baseUrl . 'index.php?action=manager_fees');
    exit;
}

$stmt = $pdo->query("
    SELECT f.*, s.first_name, s.middle_name, s.last_name, s.contact_number
    FROM fees f
    LEFT JOIN students s ON s.id = f.student_id
    ORDER BY f.id DESC
");
$fees = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Manager Fees - Pentatonic Hostel</title>
    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Inter', sans-serif; background: #f7f7f7; color: #121212; }
        .mf-page-wrap { min-height: 100vh; padding: 22px 16px; }
        .mf-body-row { display: flex; gap: 18px; min-height: calc(100vh - 44px); }
        .mf-sidebar { width: 235px; background: #082d59; color: #fff; padding: 18px 18px 22px; display: flex; flex-direction: column; }
        .mf-sidebar-logo { display: flex; align-items: center; gap: 10px; margin-bottom: 28px; font-size: 15px; }
        .mf-sidebar-logo img { width: 44px; height: 44px; object-fit: contain; }
        .mf-profile-name { font-size: 14px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px; }
        .mf-profile-role { font-size: 12px; color: rgba(255, 255, 255, 0.9); margin-bottom: 26px; }
        .mf-sidebar-nav { display: flex; flex-direction: column; gap: 16px; }
        .mf-nav-btn, .mf-signout-btn {
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
        .mf-nav-btn.active { background: #000; color: #fff; }
        .mf-sidebar-spacer { flex: 1; }
        .mf-main { flex: 1; background: #fff; padding: 28px 28px 30px; }
        .mf-title-bar {
            background: #082d59;
            border-radius: 14px;
            color: #fff;
            text-align: center;
            padding: 11px 20px;
            font-size: 20px;
            font-weight: 500;
            margin-bottom: 20px;
        }
        .mf-search {
            position: relative;
            margin-bottom: 18px;
        }
        .mf-search i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #b7b7b7;
            font-size: 16px;
        }
        .mf-search input {
            width: 100%;
            border: 0;
            border-radius: 999px;
            background: #f1f3f8;
            padding: 14px 18px 14px 40px;
            font-size: 13px;
        }
        .mf-panel {
            background: #e8f0ff;
            border-radius: 18px;
            padding: 18px;
        }
        .mf-table {
            display: grid;
            grid-template-columns: 1.2fr 1fr 1fr;
            gap: 18px;
        }
        .mf-column {
            background: #fff;
            border-radius: 18px;
            padding: 18px 16px;
        }
        .mf-header-pill {
            background: #ffc425;
            border-radius: 999px;
            text-align: center;
            padding: 10px 12px;
            font-size: 13px;
            margin-bottom: 16px;
        }
        .mf-cell {
            background: #eef3ff;
            border-radius: 999px;
            text-align: center;
            padding: 10px 12px;
            font-size: 13px;
            color: #353535;
            margin-bottom: 14px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .mf-status-form {
            margin-bottom: 14px;
        }
        .mf-status-pill {
            width: 100%;
            border: 0;
            border-radius: 999px;
            text-align: center;
            padding: 10px 12px;
            font-size: 13px;
            cursor: default;
        }
        .mf-status-pill.paid {
            background: #c9f0dc;
            color: #2b4a38;
        }
        .mf-status-pill.unpaid {
            background: #edb4b8;
            color: #5c2f36;
        }
        .mf-empty {
            text-align: center;
            padding: 24px 14px 6px;
            color: #5b6470;
            font-size: 14px;
        }
        @media (max-width: 1000px) {
            .mf-table {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 900px) {
            .mf-body-row { flex-direction: column; }
            .mf-sidebar { width: 100%; }
        }
    </style>
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
                <a class="mf-nav-btn" href="<?= $baseUrl ?>index.php?action=manager_dashboard"><i class="ph-fill ph-squares-four"></i><span>Dashboard</span></a>
                <a class="mf-nav-btn" href="<?= $baseUrl ?>index.php?action=manager_students"><i class="ph ph-student"></i><span>Students</span></a>
                <a class="mf-nav-btn" href="<?= $baseUrl ?>index.php?action=manager_rooms_single"><i class="ph ph-bed"></i><span>Rooms</span></a>
                <a class="mf-nav-btn active" href="<?= $baseUrl ?>index.php?action=manager_fees"><i class="ph ph-money"></i><span>Fees</span></a>
                <a class="mf-nav-btn" href="<?= $baseUrl ?>index.php?action=manager_complaints"><i class="ph ph-megaphone"></i><span>Complaints</span></a>
                <a class="mf-nav-btn" href="<?= $baseUrl ?>index.php?action=manager_notices"><i class="ph ph-warning"></i><span>Notice</span></a>
                <a class="mf-nav-btn" href="<?= $baseUrl ?>index.php?action=manager_staff"><i class="ph ph-users-three"></i><span>Staffs</span></a>
            </nav>

            <div class="mf-sidebar-spacer"></div>

            <a class="mf-signout-btn" href="<?= $baseUrl ?>index.php?action=logout">
                <i class="ph ph-sign-out"></i>
                <span>Sign out</span>
            </a>
        </aside>

        <main class="mf-main">
            <div class="mf-title-bar">FEES</div>

            <div class="mf-search">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" placeholder="Search">
            </div>

            <section class="mf-panel">
                <?php if (empty($fees)): ?>
                    <div class="mf-empty">No fee records found.</div>
                <?php else: ?>
                    <div class="mf-table">
                        <div class="mf-column">
                            <div class="mf-header-pill">Student name</div>
                            <?php foreach ($fees as $fee): ?>
                                <?php
                                $studentName = trim(
                                    ($fee['first_name'] ?? '') . ' ' .
                                    ($fee['middle_name'] ?? '') . ' ' .
                                    ($fee['last_name'] ?? '')
                                );
                                if ($studentName === '') {
                                    $studentName = 'N/A';
                                }
                                ?>
                                <div class="mf-cell"><?= htmlspecialchars($studentName) ?></div>
                            <?php endforeach; ?>
                        </div>

                        <div class="mf-column">
                            <div class="mf-header-pill">Contact number</div>
                            <?php foreach ($fees as $fee): ?>
                                <div class="mf-cell"><?= htmlspecialchars((string) ($fee['contact_number'] ?? '')) ?></div>
                            <?php endforeach; ?>
                        </div>

                        <div class="mf-column">
                            <div class="mf-header-pill">Fee status</div>
                            <?php foreach ($fees as $fee): ?>
                                <?php
                                $status = strtolower(trim((string) ($fee['status'] ?? 'unpaid')));
                                $isPaid = $status === 'paid';
                                ?>
                                <?php if ($supportsMarkPaid && !$isPaid): ?>
                                    <form class="mf-status-form" method="post">
                                        <input type="hidden" name="fee_id" value="<?= (int) $fee['id'] ?>">
                                        <button class="mf-status-pill unpaid" type="submit">Unpaid</button>
                                    </form>
                                <?php else: ?>
                                    <div class="mf-status-form">
                                        <button class="mf-status-pill <?= $isPaid ? 'paid' : 'unpaid' ?>" type="button">
                                            <?= $isPaid ? 'Paid' : 'Unpaid' ?>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</div>
</body>
</html>
