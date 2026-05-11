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
require_once __DIR__ . '/../../models/Notice.php';

$pdo = DB::connect();
$noticeModel = new Notice($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? '';
    $noticeId = (int) ($_POST['notice_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $date = $_POST['date'] ?? date('Y-m-d');

    if ($formAction === 'add' && $title !== '' && $description !== '') {
        $stmt = $pdo->prepare("INSERT INTO notices (title, description, date, time) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $description, $date, date('H:i:s')]);
    }

    if ($formAction === 'edit' && $noticeId > 0 && $title !== '' && $description !== '') {
        $stmt = $pdo->prepare("UPDATE notices SET title = ?, description = ?, date = ? WHERE id = ?");
        $stmt->execute([$title, $description, $date, $noticeId]);
    }

    if ($formAction === 'delete' && $noticeId > 0) {
        $stmt = $pdo->prepare("DELETE FROM notices WHERE id = ?");
        $stmt->execute([$noticeId]);
    }

    header('Location: ' . $baseUrl . 'index.php?action=owner_notices');
    exit;
}

$notices = $noticeModel->all();
$editId = (int) ($_GET['edit_id'] ?? 0);
$editNotice = null;

foreach ($notices as $notice) {
    if ((int) $notice['id'] === $editId) {
        $editNotice = $notice;
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
    <title>Management Notices - Pentatonic Hostel</title>
    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $baseUrl ?>public/css/owner.css?v=2">
</head>
<body>
<div class="mn-page-wrap">
    <div class="mn-body-row">
        <aside class="mn-sidebar">
            <div class="mn-sidebar-logo">
                <img src="<?= $baseUrl ?>public/images/logo.png" alt="Logo">
                <span>Pentatonic hostel</span>
            </div>

            <div class="mn-profile-name"><?= htmlspecialchars($managerName) ?></div>
            <div class="mn-profile-role">OWNER</div>

            <nav class="mn-sidebar-nav">
                <a class="mn-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_dashboard"><i class="ph-fill ph-squares-four"></i><span>Dashboard</span></a>
                <a class="mn-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_students"><i class="ph ph-student"></i><span>Students</span></a>
                <a class="mn-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_rooms"><i class="ph ph-bed"></i><span>Rooms</span></a>
                <a class="mn-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_fees"><i class="ph ph-money"></i><span>Fees</span></a>
                <a class="mn-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_complaints"><i class="ph ph-megaphone"></i><span>Complaints</span></a>
                <a class="mn-nav-btn active" href="<?= $baseUrl ?>index.php?action=owner_notices"><i class="ph ph-warning"></i><span>Notice</span></a>
                <a class="mn-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_staff"><i class="ph ph-users-three"></i><span>Staffs</span></a>
            </nav>

            <div class="mn-sidebar-spacer"></div>

            <a class="mn-signout-btn" href="<?= $baseUrl ?>index.php?action=logout">
                <i class="ph ph-sign-out"></i>
                <span>Sign out</span>
            </a>
        </aside>

        <main class="mn-main">
            <?php if (isset($_GET['form']) || $editNotice !== null): ?>
                <div class="mn-modal-wrap">
                    <div class="mn-form-title">POST NOTICE</div>

                    <form method="post">
                        <input type="hidden" name="form_action" value="<?= $editNotice ? 'edit' : 'add' ?>">
                        <input type="hidden" name="notice_id" value="<?= (int) ($editNotice['id'] ?? 0) ?>">

                        <div class="mn-form-card">
                            <label>Notice title</label>
                            <input
                                type="text"
                                name="title"
                                placeholder="title"
                                value="<?= htmlspecialchars($editNotice['title'] ?? '') ?>"
                                required
                            >
                        </div>

                        <div class="mn-form-card">
                            <label>Description</label>
                            <textarea name="description" placeholder="message" required><?= htmlspecialchars($editNotice['description'] ?? '') ?></textarea>
                        </div>

                        <div class="mn-form-footer">
                            <a class="mn-back-link" href="<?= $baseUrl ?>index.php?action=owner_notices">
                                <i class="ph ph-arrow-left"></i> BACK
                            </a>
                            <button class="mn-submit-btn" type="submit"><?= $editNotice ? 'Update notice' : 'Send notice' ?></button>
                            <div class="mn-date-input">
                                <input type="date" name="date" value="<?= htmlspecialchars($editNotice['date'] ?? date('Y-m-d')) ?>">
                                <i class="ph ph-calendar-blank"></i>
                            </div>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="mn-title-bar">NOTICES</div>

                <section class="mn-panel">
                    <a class="mn-open-btn" href="<?= $baseUrl ?>index.php?action=owner_notices&form=1">Post notice +</a>
                    <div class="mn-search">
                        <i class="ph ph-magnifying-glass"></i>
                        <input type="text" placeholder="Search">
                    </div>

                    <?php if (empty($notices)): ?>
                        <div class="mn-card">
                            <div class="mn-description" style="margin-bottom:0;">No notices available.</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($notices as $notice): ?>
                            <div class="mn-card searchable-owner-row" data-search="<?= htmlspecialchars(strtolower(($notice['title'] ?? '') . ' ' . ($notice['description'] ?? '') . ' ' . ($notice['date'] ?? '') . ' ' . ($notice['author'] ?? ''))) ?>">
                                <div class="mn-card-top">
                                    <div class="mn-card-title"><?= htmlspecialchars((string) ($notice['title'] ?? '')) ?></div>
                                    <form method="post" style="margin:0;">
                                        <input type="hidden" name="form_action" value="delete">
                                        <input type="hidden" name="notice_id" value="<?= (int) $notice['id'] ?>">
                                        <button class="mn-delete-btn" type="submit">
                                            <i class="ph ph-trash"></i> DELETE
                                        </button>
                                    </form>
                                </div>

                                <div class="mn-description"><?= nl2br(htmlspecialchars((string) ($notice['description'] ?? ''))) ?></div>

                                <div class="mn-card-actions">
                                    <div class="mn-date"><?= htmlspecialchars((string) ($notice['date'] ?? '')) ?></div>
                                    <a class="mn-edit-link" href="<?= $baseUrl ?>index.php?action=owner_notices&edit_id=<?= (int) $notice['id'] ?>">Edit</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        </main>
    </div>
</div>
<script src="<?= $baseUrl ?>public/js/owner-search.js"></script>
</body>
</html>


