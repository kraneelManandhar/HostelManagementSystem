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
require_once __DIR__ . '/../../models/Fee.php';

$pdo = DB::connect();
$feeModel = new Fee($pdo);
$supportsMarkPaid = method_exists($feeModel, 'markAsPaid');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $supportsMarkPaid) {
    $feeId = (int) ($_POST['fee_id'] ?? 0);
    if ($feeId > 0) {
        $feeModel->markAsPaid($feeId);
    }
    header('Location: ' . $baseUrl . 'index.php?action=owner_fees');
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
    <title>Fees - Pentatonic Hostel</title>
    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $baseUrl ?>public/css/owner.css">
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
                <a class="mf-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_rooms_single"><i class="ph ph-bed"></i><span>Rooms</span></a>
                <a class="mf-nav-btn active" href="<?= $baseUrl ?>index.php?action=owner_fees"><i class="ph ph-money"></i><span>Fees</span></a>
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
