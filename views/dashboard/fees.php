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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? 'status';
    $feeId = (int) ($_POST['fee_id'] ?? 0);

    if (in_array($formAction, ['add', 'edit'], true)) {
        $studentId = (int) ($_POST['student_id'] ?? 0);
        $total = max(0, (float) ($_POST['total'] ?? 0));
        $paid = max(0, (float) ($_POST['paid'] ?? 0));
        $paid = min($paid, $total);
        $pending = max(0, $total - $paid);
        $status = trim($_POST['status'] ?? '');

        if (!in_array($status, ['Paid', 'Pending'], true)) {
            $status = $pending <= 0 ? 'Paid' : 'Pending';
        }

        if ($status === 'Paid') {
            $paid = $total;
            $pending = 0;
        }

        if ($formAction === 'add' && $studentId > 0) {
            $stmt = $pdo->prepare("
                INSERT INTO fees (student_id, total, paid, pending, status)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$studentId, $total, $paid, $pending, $status]);
        }

        if ($formAction === 'edit' && $feeId > 0 && $studentId > 0) {
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

    if ($formAction === 'status' && $feeId > 0) {
        $status = $_POST['status'] ?? '';
        if (in_array($status, ['Paid', 'Pending'], true)) {
            if ($status === 'Paid') {
                $stmt = $pdo->prepare("UPDATE fees SET paid = total, pending = 0, status = 'Paid' WHERE id = ?");
                $stmt->execute([$feeId]);
            } else {
                $stmt = $pdo->prepare("UPDATE fees SET pending = GREATEST(total - paid, 0), status = 'Pending' WHERE id = ?");
                $stmt->execute([$feeId]);
            }
        }

        header('Location: ' . $baseUrl . 'index.php?action=owner_fees');
        exit;
    }
}

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
$showForm = isset($_GET['form']) || $editId > 0;
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

                <a class="mf-add-fee-btn" href="<?= $baseUrl ?>index.php?action=owner_fees&form=1">
                    <i class="ph ph-plus"></i>
                    <span>Add Fees</span>
                </a>
            </div>

            <?php if ($showForm): ?>
                <?php
                $formFee = $editFee ?? [];
                $formAction = $editFee ? 'edit' : 'add';
                ?>
                <section class="mf-fee-form-panel">
                    <div class="mf-form-title"><?= $editFee ? 'Edit fee' : 'Add fee' ?></div>
                    <form class="mf-fee-form" method="post">
                        <input type="hidden" name="form_action" value="<?= htmlspecialchars($formAction) ?>">
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
                            <span>Total</span>
                            <input type="number" name="total" min="0" step="0.01" value="<?= htmlspecialchars((string) ($formFee['total'] ?? '0.00')) ?>" required>
                        </label>

                        <label class="mf-form-field">
                            <span>Paid</span>
                            <input type="number" name="paid" min="0" step="0.01" value="<?= htmlspecialchars((string) ($formFee['paid'] ?? '0.00')) ?>" required>
                        </label>

                        <label class="mf-form-field">
                            <span>Status</span>
                            <select name="status">
                                <?php $formStatus = strtolower((string) ($formFee['status'] ?? 'Pending')); ?>
                                <option value="Pending" <?= $formStatus !== 'paid' ? 'selected' : '' ?>>Pending</option>
                                <option value="Paid" <?= $formStatus === 'paid' ? 'selected' : '' ?>>Paid</option>
                            </select>
                        </label>

                        <div class="mf-form-actions">
                            <a class="mf-cancel-btn" href="<?= $baseUrl ?>index.php?action=owner_fees">Cancel</a>
                            <button class="mf-save-fee-btn" type="submit"><?= $editFee ? 'Update fee' : 'Save fee' ?></button>
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
                                <th>Total</th>
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
                                $status = strtolower(trim((string) ($fee['status'] ?? 'pending')));
                                $isPaid = $status === 'paid';
                                $total = (float) ($fee['total'] ?? 0);
                                $paid = (float) ($fee['paid'] ?? 0);
                                $pending = array_key_exists('pending', $fee) ? (float) $fee['pending'] : max(0, $total - $paid);
                                ?>
                                <tr
                                    class="searchable-row"
                                    data-search="<?= htmlspecialchars(strtolower($studentName . ' ' . ($fee['contact_number'] ?? '') . ' ' . $total . ' ' . $paid . ' ' . $pending . ' ' . ($fee['status'] ?? ''))) ?>"
                                >
                                    <td><span class="mf-fee-cell"><?= htmlspecialchars($studentName) ?></span></td>
                                    <td><span class="mf-fee-cell"><?= htmlspecialchars((string) ($fee['contact_number'] ?? '')) ?></span></td>
                                    <td><span class="mf-fee-cell">Rs.<?= htmlspecialchars(number_format($total, 2)) ?></span></td>
                                    <td><span class="mf-fee-cell">Rs.<?= htmlspecialchars(number_format($paid, 2)) ?></span></td>
                                    <td><span class="mf-fee-cell">Rs.<?= htmlspecialchars(number_format($pending, 2)) ?></span></td>
                                    <td>
                                        <form class="mf-status-form" method="post">
                                            <input type="hidden" name="form_action" value="status">
                                            <input type="hidden" name="fee_id" value="<?= (int) $fee['id'] ?>">
                                            <select class="mf-status-select <?= $isPaid ? 'paid' : 'pending' ?>" name="status" onchange="this.form.submit()">
                                                <option value="Paid" <?= $isPaid ? 'selected' : '' ?>>Paid</option>
                                                <option value="Pending" <?= !$isPaid ? 'selected' : '' ?>>Unpaid</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <div class="mf-actions">
                                            <a class="mf-icon-btn" href="<?= $baseUrl ?>index.php?action=owner_fees&edit_id=<?= (int) $fee['id'] ?>" aria-label="Edit fee">
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
<script src="<?= $baseUrl ?>public/js/owner-search.js?v=4"></script>
</body>
</html>

