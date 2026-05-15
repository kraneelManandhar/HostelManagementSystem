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

function getFeeStatus(float $paid, float $total): string {
    if ($total <= 0 || ($paid / $total) >= 0.8) {
        return 'Paid';
    }
    if ($paid <= 0) {
        return 'Pending';
    }
    return 'Partial';
}

$pdo = DB::connect();
$feeModel = new Fee($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? 'edit';
    $feeId = (int) ($_POST['fee_id'] ?? 0);

    if ($formAction === 'edit') {
        $studentId = (int) ($_POST['student_id'] ?? 0);
        $total = max(0, (float) ($_POST['total'] ?? 0));
        $paid = max(0, (float) ($_POST['paid'] ?? 0));
        $paid = min($paid, $total);
        $pending = max(0, $total - $paid);
        $status = getFeeStatus($paid, $total);

        if ($feeId > 0 && $studentId > 0) {
            $stmt = $pdo->prepare("
                UPDATE fees
                SET student_id = ?, total = ?, paid = ?, pending = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([$studentId, $total, $paid, $pending, $status, $feeId]);
        }

        header('Location: ' . $baseUrl . 'index.php?action=owner_fees');
        exit;
    }
}

$pdo->exec("
    UPDATE fees
    SET
        paid = LEAST(GREATEST(COALESCE(paid, 0), 0), GREATEST(COALESCE(total, 0), 0)),
        pending = GREATEST(GREATEST(COALESCE(total, 0), 0) - LEAST(GREATEST(COALESCE(paid, 0), 0), GREATEST(COALESCE(total, 0), 0)), 0),
        status = CASE
            WHEN GREATEST(COALESCE(total, 0), 0) <= 0
                OR LEAST(GREATEST(COALESCE(paid, 0), 0), GREATEST(COALESCE(total, 0), 0)) / NULLIF(GREATEST(COALESCE(total, 0), 0), 0) >= 0.8
            THEN 'Paid'
            WHEN LEAST(GREATEST(COALESCE(paid, 0), 0), GREATEST(COALESCE(total, 0), 0)) = 0
            THEN 'Pending'
            ELSE 'Partial'
        END
");

$stmt = $pdo->query("
    SELECT f.*, s.first_name, s.middle_name, s.last_name, s.contact_number
    FROM fees f
    LEFT JOIN students s ON s.id = f.student_id
    ORDER BY f.id DESC
");
$fees = $stmt->fetchAll(PDO::FETCH_ASSOC);

$students = $pdo->query("
    SELECT id, first_name, middle_name, last_name, contact_number
    FROM students
    ORDER BY first_name ASC, last_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

$editId = (int) ($_GET['edit_id'] ?? 0);
$showForm = $editId > 0;
$editFee = null;

foreach ($fees as $fee) {
    if ((int) $fee['id'] === $editId) {
        $editFee = $fee;
        break;
    }
}

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
    <link rel="stylesheet" href="<?= $baseUrl ?>public/css/owner.css?v=11">
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
                <a class="mf-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_rooms"><i class="ph ph-bed"></i><span>Rooms</span></a>
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

            <div class="mf-fees-toolbar">
                <div class="mf-search">
                    <i class="ph ph-magnifying-glass"></i>
                    <input type="text" placeholder="Search">
                </div>
            </div>

            <?php if ($showForm && $editFee): ?>
                <?php
                $formFee = $editFee;
                ?>
                <section class="mf-fee-form-panel">
                    <div class="mf-form-title">Edit fee</div>
                    <form class="mf-fee-form" method="post">
                        <input type="hidden" name="form_action" value="edit">
                        <input type="hidden" name="fee_id" value="<?= (int) ($formFee['id'] ?? 0) ?>">

                        <label class="mf-form-field">
                            <span>Student</span>
                            <select name="student_id" required>
                                <option value="">Select student</option>
                                <?php foreach ($students as $student): ?>
                                    <?php
                                    $studentName = trim(
                                        ($student['first_name'] ?? '') . ' ' .
                                        ($student['middle_name'] ?? '') . ' ' .
                                        ($student['last_name'] ?? '')
                                    );
                                    ?>
                                    <option value="<?= (int) $student['id'] ?>" <?= (int) ($formFee['student_id'] ?? 0) === (int) $student['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($studentName ?: 'Student #' . $student['id']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label class="mf-form-field">
                            <span>Due</span>
                            <input type="number" name="total" min="0" step="0.01" value="<?= htmlspecialchars((string) ($formFee['total'] ?? '0.00')) ?>" required>
                        </label>

                        <label class="mf-form-field">
                            <span>Paid</span>
                            <input type="number" name="paid" min="0" step="0.01" value="<?= htmlspecialchars((string) ($formFee['paid'] ?? '0.00')) ?>" required>
                        </label>

                        <div class="mf-form-actions">
                            <a class="mf-cancel-btn" href="<?= $baseUrl ?>index.php?action=owner_fees">Cancel</a>
                            <button
                                class="mf-save-fee-btn"
                                type="submit"
                                data-confirm="Are you sure you want to save changes to this fee record?"
                            >Update fee</button>
                        </div>
                    </form>
                </section>
            <?php endif; ?>

            <section class="mf-panel">
                <?php if (empty($fees)): ?>
                    <div class="mf-empty">No fee records found.</div>
                <?php else: ?>
                    <table class="mf-fees-table">
                        <thead>
                            <tr>
                                <th>Student name</th>
                                <th>Contact number</th>
                                <th>Due</th>
                                <th>Paid</th>
                                <th>Pending</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
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
                                $total = max(0, (float) ($fee['total'] ?? 0));
                                $paid = (float) ($fee['paid'] ?? 0);
                                $paid = min(max(0, $paid), $total);
                                $pending = max(0, $total - $paid);
                                $statusLabel = getFeeStatus($paid, $total);
                                $statusClass = $statusLabel === 'Paid' ? 'paid' : ($statusLabel === 'Partial' ? 'partial' : 'unpaid');
                                ?>
                                <tr
                                    class="searchable-row"
                                    data-search="<?= htmlspecialchars(strtolower($studentName . ' ' . ($fee['contact_number'] ?? '') . ' ' . $total . ' ' . $paid . ' ' . $pending . ' ' . $statusLabel)) ?>"
                                >
                                    <td><span class="mf-fee-cell"><?= htmlspecialchars($studentName) ?></span></td>
                                    <td><span class="mf-fee-cell"><?= htmlspecialchars((string) ($fee['contact_number'] ?? '')) ?></span></td>
                                    <td><span class="mf-fee-cell">Rs.<?= htmlspecialchars(number_format($total, 2)) ?></span></td>
                                    <td><span class="mf-fee-cell">Rs.<?= htmlspecialchars(number_format($paid, 2)) ?></span></td>
                                    <td><span class="mf-fee-cell">Rs.<?= htmlspecialchars(number_format($pending, 2)) ?></span></td>
                                    <td>
                                        <span class="mf-status-pill <?= $statusClass ?>"><?= htmlspecialchars($statusLabel) ?></span>
                                    </td>
                                    <td>
                                        <div class="mf-actions">
                                            <a class="mf-icon-btn" href="<?= $baseUrl ?>index.php?action=owner_fees&edit_id=<?= (int) $fee['id'] ?>" aria-label="Edit fee" data-confirm="Are you sure you want to edit this fee record?">
                                                <i class="ph ph-pencil-simple"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
        </main>
    </div>
</div>
<script src="<?= $baseUrl ?>public/js/owner-search.js?v=5"></script>
<script src="<?= $baseUrl ?>public/js/confirm-actions.js?v=1"></script>
</body>
</html>

