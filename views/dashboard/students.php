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

$pdo = DB::connect();
$studentModel = new Student($pdo);

function ownerRedirect(string $baseUrl, ?int $selectedId = null): void
{
    $target = $baseUrl . 'index.php?action=owner_students';
    if ($selectedId) {
        $target .= '&student_id=' . $selectedId;
    }
    header('Location: ' . $target);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'add') {
        $profilePhoto = null;
        if (!empty($_FILES['profile_photo']['name'])) {
            $uploadDir = __DIR__ . '/../../public/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('student_', true) . ($ext ? '.' . $ext : '');
            move_uploaded_file($_FILES['profile_photo']['tmp_name'], $uploadDir . $filename);
            $profilePhoto = $filename;
        }

        $studentModel->registerStudent([
            'first_name' => trim($_POST['first_name'] ?? ''),
            'middle_name' => trim($_POST['middle_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'date_of_birth' => $_POST['date_of_birth'] ?? null,
            'contact_number' => trim($_POST['contact_number'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => password_hash('student123', PASSWORD_DEFAULT),
            'profile_photo' => $profilePhoto,
            'college_name' => trim($_POST['college_name'] ?? ''),
            'permanent_address' => trim($_POST['permanent_address'] ?? ''),
            'date_of_joining' => $_POST['date_of_joining'] ?? null,
            'guardian_name' => trim($_POST['guardian_name'] ?? ''),
            'guardian_relationship' => trim($_POST['guardian_relationship'] ?? ''),
            'guardian_contact' => trim($_POST['guardian_contact'] ?? ''),
            'preferred_room_type' => $_POST['preferred_room_type'] ?? 'double',
            'room_id' => null,
        ]);

        ownerRedirect($baseUrl);
    }

    if ($formAction === 'edit') {
        $studentId = (int) ($_POST['student_id'] ?? 0);
        $currentPhoto = $_POST['current_photo'] ?? null;

        if (!empty($_FILES['profile_photo']['name'])) {
            $uploadDir = __DIR__ . '/../../public/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('student_', true) . ($ext ? '.' . $ext : '');
            move_uploaded_file($_FILES['profile_photo']['tmp_name'], $uploadDir . $filename);
            $currentPhoto = $filename;
        }

        $stmt = $pdo->prepare("
            UPDATE students
            SET first_name = :first_name,
                middle_name = :middle_name,
                last_name = :last_name,
                date_of_birth = :date_of_birth,
                contact_number = :contact_number,
                email = :email,
                profile_photo = :profile_photo,
                college_name = :college_name,
                permanent_address = :permanent_address,
                date_of_joining = :date_of_joining,
                guardian_name = :guardian_name,
                guardian_relationship = :guardian_relationship,
                guardian_contact = :guardian_contact,
                preferred_room_type = :preferred_room_type
            WHERE id = :id
        ");

        $stmt->execute([
            ':first_name' => trim($_POST['first_name'] ?? ''),
            ':middle_name' => trim($_POST['middle_name'] ?? ''),
            ':last_name' => trim($_POST['last_name'] ?? ''),
            ':date_of_birth' => $_POST['date_of_birth'] ?? null,
            ':contact_number' => trim($_POST['contact_number'] ?? ''),
            ':email' => trim($_POST['email'] ?? ''),
            ':profile_photo' => $currentPhoto ?: null,
            ':college_name' => trim($_POST['college_name'] ?? ''),
            ':permanent_address' => trim($_POST['permanent_address'] ?? ''),
            ':date_of_joining' => $_POST['date_of_joining'] ?? null,
            ':guardian_name' => trim($_POST['guardian_name'] ?? ''),
            ':guardian_relationship' => trim($_POST['guardian_relationship'] ?? ''),
            ':guardian_contact' => trim($_POST['guardian_contact'] ?? ''),
            ':preferred_room_type' => $_POST['preferred_room_type'] ?? 'double',
            ':id' => $studentId,
        ]);

        ownerRedirect($baseUrl, $studentId);
    }

    if ($formAction === 'delete') {
        $studentId = (int) ($_POST['student_id'] ?? 0);
        if ($studentId > 0) {
            $studentModel->delete($studentId);
        }
        ownerRedirect($baseUrl);
    }
}

$students = $studentModel->getAll();
$selectedId = (int) ($_GET['student_id'] ?? 0);
$selectedStudent = null;

foreach ($students as $studentItem) {
    if ((int) $studentItem['id'] === $selectedId) {
        $selectedStudent = $studentItem;
        break;
    }
}

if ($selectedStudent === null && !empty($students)) {
    $selectedStudent = $students[0];
    $selectedId = (int) $selectedStudent['id'];
}

$managerName = trim((string) ($_SESSION['user_name'] ?? 'FULL NAME'));
if ($managerName === '') {
    $managerName = 'FULL NAME';
}

$selectedPhoto = $selectedStudent['profile_photo'] ?? '';
if (!empty($selectedPhoto)) {
    $selectedPhoto = ltrim($selectedPhoto, '/');
    if (strpos($selectedPhoto, 'public/') === 0) {
        $selectedPhotoSrc = $baseUrl . htmlspecialchars($selectedPhoto);
    } elseif (strpos($selectedPhoto, 'uploads/') === 0) {
        $selectedPhotoSrc = $baseUrl . 'public/' . htmlspecialchars($selectedPhoto);
    } else {
        $selectedPhotoSrc = $baseUrl . 'public/uploads/' . htmlspecialchars($selectedPhoto);
    }
} else {
    $selectedPhotoSrc = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Students - Pentatonic Hostel</title>
    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: #f7f7f7;
            color: #121212;
        }
        .ms-page-wrap {
            min-height: 100vh;
            padding: 22px 16px;
        }
        .ms-body-row {
            display: flex;
            gap: 18px;
            min-height: calc(100vh - 44px);
        }
        .ms-sidebar {
            width: 235px;
            background: #082d59;
            color: #fff;
            padding: 18px 18px 22px;
            display: flex;
            flex-direction: column;
        }
        .ms-sidebar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 28px;
            font-size: 15px;
        }
        .ms-sidebar-logo img {
            width: 44px;
            height: 44px;
            object-fit: contain;
        }
        .ms-profile-name {
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .ms-profile-role {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 26px;
        }
        .ms-sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .ms-nav-btn,
        .ms-signout-btn {
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
        .ms-nav-btn.active {
            background: #000;
            color: #fff;
        }
        .ms-sidebar-spacer {
            flex: 1;
        }
        .ms-main {
            flex: 1;
            background: #fff;
            padding: 28px 34px;
        }
        .ms-title-bar {
            background: #082d59;
            border-radius: 14px;
            color: #fff;
            text-align: center;
            padding: 11px 20px;
            font-size: 20px;
            font-weight: 500;
            letter-spacing: 0.4px;
            margin-bottom: 28px;
        }
        .ms-content {
            display: grid;
            grid-template-columns: 270px 1fr;
            gap: 24px;
        }
        .ms-list-panel,
        .ms-section,
        .ms-room-panel {
            background: #e8f0ff;
            border-radius: 18px;
        }
        .ms-list-panel {
            padding: 16px 14px;
        }
        .ms-list-title {
            background: #ffc425;
            border-radius: 14px;
            padding: 14px 18px;
            text-align: center;
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 16px;
        }
        .ms-student-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-height: 610px;
            overflow-y: auto;
        }
        .ms-student-item {
            display: block;
            width: 100%;
            border: 0;
            border-radius: 10px;
            background: #fff;
            color: #121212;
            text-align: center;
            padding: 10px 12px;
            font-size: 14px;
            text-decoration: none;
        }
        .ms-student-item.active {
            background: #000;
            color: #fff;
        }
        .ms-right {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .ms-toolbar {
            display: flex;
            gap: 16px;
            align-items: center;
        }
        .ms-search {
            flex: 1;
            position: relative;
        }
        .ms-search i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #b7b7b7;
            font-size: 16px;
        }
        .ms-search input {
            width: 100%;
            border: 0;
            border-radius: 999px;
            background: #f1f3f8;
            padding: 14px 18px 14px 40px;
            font-size: 13px;
            color: #666;
        }
        .ms-action-btn {
            border: 0;
            border-radius: 999px;
            background: #ffc425;
            color: #1a1a1a;
            padding: 14px 22px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
        }
        .ms-section {
            padding: 16px;
        }
        .ms-grid-3,
        .ms-grid-2 {
            display: grid;
            gap: 14px;
        }
        .ms-grid-3 {
            grid-template-columns: 1fr 1fr 1fr 124px;
            align-items: start;
        }
        .ms-grid-2 {
            grid-template-columns: 1fr 1fr;
        }
        .ms-field label,
        .ms-photo-label {
            display: block;
            font-size: 13px;
            margin-bottom: 6px;
        }
        .ms-field input,
        .ms-field select {
            width: 100%;
            border: 0;
            background: #fff;
            border-radius: 999px;
            padding: 10px 14px;
            font-size: 13px;
        }
        .ms-field.full {
            grid-column: 1 / -1;
        }
        .ms-photo-box {
            background: #f2f5fb;
            border-radius: 12px;
            padding: 10px;
            min-height: 118px;
            text-align: center;
        }
        .ms-photo-preview {
            width: 100%;
            height: 88px;
            border-radius: 10px;
            background: #e6edf9;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-bottom: 8px;
        }
        .ms-photo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .ms-photo-preview i {
            font-size: 36px;
            color: #9ca3af;
        }
        .ms-photo-box input {
            width: 100%;
            font-size: 11px;
        }
        .ms-room-panel {
            padding: 12px 16px;
        }
        .ms-room-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }
        .ms-radio-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f2f5fb;
            border-radius: 999px;
            padding: 10px 16px;
            font-size: 13px;
        }
        .ms-footer-actions {
            display: flex;
            justify-content: space-between;
            gap: 16px;
        }
        .ms-delete-btn,
        .ms-edit-btn,
        .ms-save-btn {
            border: 0;
            border-radius: 999px;
            padding: 12px 24px;
            font-size: 13px;
            cursor: pointer;
        }
        .ms-delete-btn {
            background: #fff1f1;
            color: #d95d5d;
        }
        .ms-edit-btn {
            background: #efefef;
            color: #4a4a4a;
        }
        .ms-save-btn {
            background: #ffc425;
            color: #1a1a1a;
        }
        @media (max-width: 1100px) {
            .ms-content {
                grid-template-columns: 1fr;
            }
            .ms-grid-3 {
                grid-template-columns: 1fr 1fr;
            }
        }
        @media (max-width: 900px) {
            .ms-body-row {
                flex-direction: column;
            }
            .ms-sidebar {
                width: 100%;
            }
        }
        @media (max-width: 700px) {
            .ms-toolbar,
            .ms-grid-2,
            .ms-room-options,
            .ms-footer-actions {
                grid-template-columns: 1fr;
                flex-direction: column;
            }
            .ms-grid-3 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="ms-page-wrap">
    <div class="ms-body-row">
        <aside class="ms-sidebar">
            <div class="ms-sidebar-logo">
                <img src="<?= $baseUrl ?>public/images/logo.png" alt="Logo">
                <span>Pentatonic hostel</span>
            </div>

            <div class="ms-profile-name"><?= htmlspecialchars($managerName) ?></div>
            <div class="ms-profile-role">OWNER</div>

            <nav class="ms-sidebar-nav">
                <a class="ms-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_dashboard">
                    <i class="ph-fill ph-squares-four"></i>
                    <span>Dashboard</span>
                </a>
                <a class="ms-nav-btn active" href="<?= $baseUrl ?>index.php?action=owner_students">
                    <i class="ph ph-student"></i>
                    <span>Students</span>
                </a>
                <a class="ms-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_rooms_single"><i class="ph ph-bed"></i><span>Rooms</span></a>
                <a class="ms-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_fees"><i class="ph ph-money"></i><span>Fees</span></a>
                <a class="ms-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_complaints"><i class="ph ph-megaphone"></i><span>Complaints</span></a>
                <a class="ms-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_notices"><i class="ph ph-warning"></i><span>Notice</span></a>
                <a class="ms-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_staff"><i class="ph ph-users-three"></i><span>Staffs</span></a>
            </nav>

            <div class="ms-sidebar-spacer"></div>

            <a class="ms-signout-btn" href="<?= $baseUrl ?>index.php?action=logout">
                <i class="ph ph-sign-out"></i>
                <span>Sign out</span>
            </a>
        </aside>

        <main class="ms-main">
            <div class="ms-title-bar">STUDENTS</div>

            <div class="ms-content">
                <section class="ms-list-panel">
                    <div class="ms-list-title">Names of students</div>
                    <div class="ms-student-list">
                        <?php foreach ($students as $studentItem): ?>
                            <?php
                            $fullName = trim(($studentItem['first_name'] ?? '') . ' ' . ($studentItem['middle_name'] ?? '') . ' ' . ($studentItem['last_name'] ?? ''));
                            if ($fullName === '') {
                                $fullName = 'Full names';
                            }
                            ?>
                            <a
                                class="ms-student-item <?= (int) $studentItem['id'] === $selectedId ? 'active' : '' ?>"
                                href="<?= $baseUrl ?>index.php?action=owner_students&student_id=<?= (int) $studentItem['id'] ?>"
                            >
                                <?= htmlspecialchars($fullName) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="ms-right">
                    <div class="ms-toolbar">
                        <div class="ms-search">
                            <i class="ph ph-magnifying-glass"></i>
                            <input type="text" placeholder="Search">
                        </div>
                        <form method="get" action="<?= $baseUrl ?>index.php">
                            <input type="hidden" name="action" value="owner_students">
                            <button class="ms-action-btn" type="submit">REGISTER NOW</button>
                        </form>
                    </div>

                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="student_id" value="<?= (int) ($selectedStudent['id'] ?? 0) ?>">
                        <input type="hidden" name="current_photo" value="<?= htmlspecialchars($selectedStudent['profile_photo'] ?? '') ?>">

                        <section class="ms-section">
                            <div class="ms-grid-3">
                                <div class="ms-field">
                                    <label>First name</label>
                                    <input type="text" name="first_name" value="<?= htmlspecialchars($selectedStudent['first_name'] ?? '') ?>" required>
                                </div>
                                <div class="ms-field">
                                    <label>Middle name</label>
                                    <input type="text" name="middle_name" value="<?= htmlspecialchars($selectedStudent['middle_name'] ?? '') ?>">
                                </div>
                                <div class="ms-field">
                                    <label>Last name</label>
                                    <input type="text" name="last_name" value="<?= htmlspecialchars($selectedStudent['last_name'] ?? '') ?>" required>
                                </div>
                                <div class="ms-photo-box">
                                    <div class="ms-photo-label">Passport size photo</div>
                                    <div class="ms-photo-preview">
                                        <?php if ($selectedPhotoSrc): ?>
                                            <img src="<?= $selectedPhotoSrc ?>" alt="Student photo">
                                        <?php else: ?>
                                            <i class="ph ph-image"></i>
                                        <?php endif; ?>
                                    </div>
                                    <input type="file" name="profile_photo" accept="image/*">
                                </div>
                                <div class="ms-field">
                                    <label>Date of birth</label>
                                    <input type="date" name="date_of_birth" value="<?= htmlspecialchars($selectedStudent['date_of_birth'] ?? '') ?>">
                                </div>
                                <div class="ms-field">
                                    <label>Contact number</label>
                                    <input type="text" name="contact_number" value="<?= htmlspecialchars($selectedStudent['contact_number'] ?? '') ?>">
                                </div>
                                <div class="ms-field full">
                                    <label>Email address</label>
                                    <input type="email" name="email" value="<?= htmlspecialchars($selectedStudent['email'] ?? '') ?>" required>
                                </div>
                            </div>
                        </section>

                        <section class="ms-section">
                            <div class="ms-grid-2">
                                <div class="ms-field">
                                    <label>College name</label>
                                    <input type="text" name="college_name" value="<?= htmlspecialchars($selectedStudent['college_name'] ?? '') ?>">
                                </div>
                                <div class="ms-field">
                                    <label>Permanent address</label>
                                    <input type="text" name="permanent_address" value="<?= htmlspecialchars($selectedStudent['permanent_address'] ?? '') ?>">
                                </div>
                                <div class="ms-field">
                                    <label>Date of joining</label>
                                    <input type="date" name="date_of_joining" value="<?= htmlspecialchars($selectedStudent['date_of_joining'] ?? '') ?>">
                                </div>
                            </div>
                        </section>

                        <section class="ms-section">
                            <div class="ms-grid-2">
                                <div class="ms-field">
                                    <label>Relationship</label>
                                    <input type="text" name="guardian_relationship" value="<?= htmlspecialchars($selectedStudent['guardian_relationship'] ?? '') ?>">
                                </div>
                                <div class="ms-field">
                                    <label>Guardian full name</label>
                                    <input type="text" name="guardian_name" value="<?= htmlspecialchars($selectedStudent['guardian_name'] ?? '') ?>">
                                </div>
                                <div class="ms-field">
                                    <label>Contact number</label>
                                    <input type="text" name="guardian_contact" value="<?= htmlspecialchars($selectedStudent['guardian_contact'] ?? '') ?>">
                                </div>
                            </div>
                        </section>

                        <section class="ms-room-panel">
                            <?php $roomType = $selectedStudent['preferred_room_type'] ?? 'double'; ?>
                            <div class="ms-room-options">
                                <label class="ms-radio-pill">
                                    <input type="radio" name="preferred_room_type" value="double" <?= $roomType === 'double' ? 'checked' : '' ?>>
                                    <span>Double sitter</span>
                                </label>
                                <label class="ms-radio-pill">
                                    <input type="radio" name="preferred_room_type" value="single" <?= $roomType === 'single' ? 'checked' : '' ?>>
                                    <span>Single sitter</span>
                                </label>
                            </div>
                        </section>

                        <div class="ms-footer-actions">
                            <button class="ms-delete-btn" type="submit" name="form_action" value="delete" <?= $selectedId ? '' : 'disabled' ?>>
                                <i class="ph ph-trash"></i> DELETE RECORDS
                            </button>
                            <button class="ms-edit-btn" type="submit" name="form_action" value="edit" <?= $selectedId ? '' : 'disabled' ?>>
                                EDIT
                            </button>
                            <button class="ms-save-btn" type="submit" name="form_action" value="<?= $selectedId ? 'edit' : 'add' ?>">
                                SAVE CHANGES
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </main>
    </div>
</div>
</body>
</html>