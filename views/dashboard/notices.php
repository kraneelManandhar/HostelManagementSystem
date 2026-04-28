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
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Inter', sans-serif; background: #dfe9fb; color: #121212; }
        .mn-page-wrap { min-height: 100vh; padding: 22px 16px; }
        .mn-body-row { display: flex; gap: 18px; min-height: calc(100vh - 44px); }
        .mn-sidebar { width: 235px; background: #082d59; color: #fff; padding: 18px 18px 22px; display: flex; flex-direction: column; }
        .mn-sidebar-logo { display: flex; align-items: center; gap: 10px; margin-bottom: 28px; font-size: 15px; }
        .mn-sidebar-logo img { width: 44px; height: 44px; object-fit: contain; }
        .mn-profile-name { font-size: 14px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px; }
        .mn-profile-role { font-size: 12px; color: rgba(255, 255, 255, 0.9); margin-bottom: 26px; }
        .mn-sidebar-nav { display: flex; flex-direction: column; gap: 16px; }
        .mn-nav-btn, .mn-signout-btn {
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
        .mn-nav-btn.active { background: #000; color: #fff; }
        .mn-sidebar-spacer { flex: 1; }
        .mn-main { flex: 1; background: transparent; padding: 16px 10px; }
        .mn-title-bar {
            max-width: 600px;
            margin: 0 auto 26px;
            background: #082d59;
            border-radius: 14px;
            color: #fff;
            text-align: center;
            padding: 11px 20px;
            font-size: 20px;
            font-weight: 500;
        }
        .mn-panel {
            max-width: 640px;
            margin: 0 auto;
            background: #fff;
            border-radius: 20px;
            padding: 16px 18px 20px;
        }
        .mn-open-btn {
            border: 0;
            border-radius: 999px;
            background: #ffc425;
            color: #1a1a1a;
            padding: 11px 18px;
            font-size: 14px;
            cursor: pointer;
            margin-bottom: 14px;
        }
        .mn-card {
            background: #e8f0ff;
            border-radius: 18px;
            padding: 14px 14px 12px;
            margin-bottom: 16px;
        }
        .mn-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }
        .mn-card-title {
            background: #fff;
            border-radius: 999px;
            padding: 9px 14px;
            font-size: 13px;
            flex: 1;
        }
        .mn-delete-btn {
            border: 0;
            border-radius: 999px;
            background: #fff5f5;
            color: #d16f6f;
            padding: 8px 14px;
            font-size: 11px;
            cursor: pointer;
        }
        .mn-description {
            background: #fff;
            border-radius: 16px;
            padding: 14px;
            font-size: 13px;
            color: #444;
            min-height: 58px;
            margin-bottom: 10px;
        }
        .mn-card-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }
        .mn-date {
            font-size: 12px;
            color: #667085;
        }
        .mn-edit-link {
            border: 0;
            border-radius: 999px;
            background: #fff;
            color: #1f2937;
            padding: 8px 14px;
            font-size: 12px;
            text-decoration: none;
        }
        .mn-modal-wrap {
            max-width: 950px;
            margin: 0 auto;
            background: linear-gradient(180deg, #dfe9fb 0%, #6a8bb3 100%);
            border-radius: 28px;
            padding: 32px 38px 28px;
        }
        .mn-form-title {
            max-width: 820px;
            margin: 0 auto 38px;
            background: #082d59;
            border-radius: 18px;
            color: #fff;
            text-align: center;
            padding: 14px 20px;
            font-size: 20px;
            font-weight: 500;
        }
        .mn-form-card {
            background: #e8f0ff;
            border-radius: 22px;
            padding: 22px 20px;
            margin-bottom: 28px;
        }
        .mn-form-card label {
            display: block;
            font-size: 18px;
            margin-bottom: 18px;
        }
        .mn-form-card input,
        .mn-form-card textarea {
            width: 100%;
            border: 0;
            border-radius: 999px;
            background: #fff;
            padding: 16px 20px;
            font-size: 16px;
        }
        .mn-form-card textarea {
            border-radius: 24px;
            min-height: 190px;
            resize: vertical;
        }
        .mn-form-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }
        .mn-back-link {
            color: #fff;
            text-decoration: none;
            font-size: 18px;
        }
        .mn-submit-btn {
            border: 0;
            border-radius: 999px;
            background: #ffc425;
            color: #1a1a1a;
            padding: 14px 42px;
            font-size: 18px;
            cursor: pointer;
        }
        .mn-date-input {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fff;
            border-radius: 999px;
            padding: 10px 18px;
        }
        .mn-date-input input {
            border: 0;
            padding: 0;
            font-size: 16px;
            background: transparent;
        }
        @media (max-width: 900px) {
            .mn-body-row { flex-direction: column; }
            .mn-sidebar { width: 100%; }
            .mn-form-footer { flex-direction: column; align-items: stretch; }
        }
    </style>
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
                <a class="mn-nav-btn" href="<?= $baseUrl ?>index.php?action=mowner_rooms_single"><i class="ph ph-bed"></i><span>Rooms</span></a>
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

                    <?php if (empty($notices)): ?>
                        <div class="mn-card">
                            <div class="mn-description" style="margin-bottom:0;">No notices available.</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($notices as $notice): ?>
                            <div class="mn-card">
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
</body>
</html>
