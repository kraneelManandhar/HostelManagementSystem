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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $date = $_POST['date'] ?? date('Y-m-d');

    if ($formAction === 'add_notice' && $title !== '' && $description !== '') {
        $stmt = $pdo->prepare("INSERT INTO notices (title, description, date, time) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $description, $date, date('H:i:s')]);
    }

    header('Location: ' . $baseUrl . 'index.php?action=owner_dashboard');
    exit;
}

$totalStudents = count($studentModel->getAll());
$totalRooms = (int) $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$occupiedRooms = (int) $pdo->query("
    SELECT COUNT(*)
    FROM rooms r
    WHERE r.student1_id IS NOT NULL
       OR r.student2_id IS NOT NULL
       OR EXISTS (SELECT 1 FROM students s WHERE s.room_id = r.id)
")->fetchColumn();
$unpaidFees = (int) $pdo->query("SELECT COUNT(*) FROM fees WHERE LOWER(status) <> 'paid' OR status IS NULL")->fetchColumn();
$recentNotices = array_slice($noticeModel->all(), 0, 5);
$recentComplaints = array_slice($complaintModel->all(), 0, 5);
$showNoticeModal = isset($_GET['post_notice']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Dashboard - Pentatonic Hostel</title>
    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $baseUrl ?>public/css/owner.css?v=11">
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
                <a class="md-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_rooms">
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
                <a class="md-card" href="<?= $baseUrl ?>index.php?action=owner_students">
                    <div class="md-card-title">Students</div>
                    <div class="md-card-badge"><?= $totalStudents ?></div>
                </a>

                <a class="md-card" href="<?= $baseUrl ?>index.php?action=owner_rooms">
                    <div class="md-card-title">Rooms</div>
                    <div class="md-card-badge"><?= $totalRooms ?></div>
                </a>

                <a class="md-card" href="<?= $baseUrl ?>index.php?action=owner_rooms">
                    <div class="md-card-title">Occupied Rooms</div>
                    <div class="md-card-badge"><?= $occupiedRooms ?></div>
                </a>

                <a class="md-card" href="<?= $baseUrl ?>index.php?action=owner_fees">
                    <div class="md-card-title">Fees</div>
                    <div class="md-card-badge"><?= $unpaidFees ?></div>
                </a>
            </section>

            <section class="md-dashboard-panels">
                <div class="md-panel">
                    <div class="md-panel-head">
                        <h2>Notices</h2>
                        <div class="md-panel-actions">
                            <a class="js-owner-notice-open" href="<?= $baseUrl ?>index.php?action=owner_dashboard&post_notice=1">Post notice</a>
                            <a href="<?= $baseUrl ?>index.php?action=owner_notices">View all</a>
                        </div>
                    </div>

                    <?php if (empty($recentNotices)): ?>
                        <div class="md-empty">No notices available.</div>
                    <?php else: ?>
                        <div class="md-list">
                            <?php foreach ($recentNotices as $notice): ?>
                                <article class="md-list-item">
                                    <div class="md-item-top">
                                        <h3><?= htmlspecialchars((string) ($notice['title'] ?? 'Untitled notice')) ?></h3>
                                        <span><?= htmlspecialchars((string) ($notice['date'] ?? '')) ?></span>
                                    </div>
                                    <p><?= htmlspecialchars((string) ($notice['description'] ?? '')) ?></p>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="md-panel">
                    <div class="md-panel-head">
                        <h2>Complaints</h2>
                        <a href="<?= $baseUrl ?>index.php?action=owner_complaints">View all</a>
                    </div>

                    <?php if (empty($recentComplaints)): ?>
                        <div class="md-empty">No complaints found.</div>
                    <?php else: ?>
                        <div class="md-list">
                            <?php foreach ($recentComplaints as $complaint): ?>
                                <?php
                                $studentName = trim(
                                    ($complaint['first_name'] ?? '') . ' ' .
                                    ($complaint['middle_name'] ?? '') . ' ' .
                                    ($complaint['last_name'] ?? '')
                                );
                                if ($studentName === '') {
                                    $studentName = 'N/A';
                                }
                                $status = (string) ($complaint['status'] ?? 'Pending');
                                ?>
                                <article class="md-list-item">
                                    <div class="md-item-top">
                                        <h3><?= htmlspecialchars((string) ($complaint['title'] ?? 'Complaint')) ?></h3>
                                        <span class="md-status"><?= htmlspecialchars($status) ?></span>
                                    </div>
                                    <p><?= htmlspecialchars((string) ($complaint['description'] ?? '')) ?></p>
                                    <div class="md-item-meta">
                                        <?= htmlspecialchars($studentName) ?>
                                        <?php if (!empty($complaint['room_number'])): ?>
                                            <span>Room <?= htmlspecialchars((string) $complaint['room_number']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <div class="mn-modal-overlay <?= $showNoticeModal ? 'open' : '' ?>" id="ownerNoticeModal" role="dialog" aria-modal="true" aria-labelledby="noticeModalTitle">
                <a class="mn-modal-backdrop js-owner-notice-close" href="<?= $baseUrl ?>index.php?action=owner_dashboard" aria-label="Close notice form"></a>
                <div class="mn-modal-wrap">
                    <div class="mn-form-title" id="noticeModalTitle">Post notice</div>

                    <form method="post">
                        <input type="hidden" name="form_action" value="add_notice">

                        <div class="mn-form-row">
                            <div class="mn-form-card">
                                <label>Notice title</label>
                                <input type="text" name="title" placeholder="Title" required>
                            </div>

                            <div class="mn-form-card">
                                <label>Date</label>
                                <input type="date" name="date" value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>

                        <div class="mn-form-card">
                            <label>Description</label>
                            <textarea name="description" placeholder="Write notice details..." required></textarea>
                        </div>

                        <div class="mn-form-footer">
                            <a class="mn-back-link js-owner-notice-close" href="<?= $baseUrl ?>index.php?action=owner_dashboard">Back</a>
                            <button class="mn-submit-btn" type="submit">Send notice</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('ownerNoticeModal');
    const openButtons = document.querySelectorAll('.js-owner-notice-open');
    const closeButtons = document.querySelectorAll('.js-owner-notice-close');

    if (!modal) {
        return;
    }

    openButtons.forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            modal.classList.add('open');
        });
    });

    closeButtons.forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            modal.classList.remove('open');
        });
    });
});
</script>
</body>
</html>
