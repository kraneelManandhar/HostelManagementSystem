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

function ownerRedirect(string $baseUrl, ?int $selectedId = null, string $msg = ''): void
{
    $target = $baseUrl . 'index.php?action=owner_students';
    if ($selectedId) {
        $target .= '&student_id=' . $selectedId;
    }
    if ($msg !== '') {
        $target .= '&msg=' . urlencode($msg);
    }
    header('Location: ' . $target);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? '';
    $studentId = (int) ($_POST['student_id'] ?? 0);

    if ($formAction === 'add' || $formAction === 'edit') {
        $contactNumber = trim($_POST['contact_number'] ?? '');
        $guardianContact = trim($_POST['guardian_contact'] ?? '');

        if (
            ($contactNumber !== '' && !preg_match('/^\d{10}$/', $contactNumber)) ||
            ($guardianContact !== '' && !preg_match('/^\d{10}$/', $guardianContact))
        ) {
            ownerRedirect($baseUrl, $studentId ?: null, 'invalid_phone');
        }

        $_POST['contact_number'] = $contactNumber;
        $_POST['guardian_contact'] = $guardianContact;
    }

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

$msg = $_GET['msg'] ?? '';

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
    <link rel="stylesheet" href="<?= $baseUrl ?>public/css/owner.css?v=2">
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
            <?php if ($msg === 'invalid_phone'): ?>
                <div class="ms-alert error">Contact numbers must contain exactly 10 digits.</div>
            <?php endif; ?>

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
                                data-search="<?= htmlspecialchars(strtolower($fullName . ' ' . ($studentItem['email'] ?? '') . ' ' . ($studentItem['contact_number'] ?? ''))) ?>"
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
                                    <input type="tel" name="contact_number" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" title="Enter exactly 10 digits" value="<?= htmlspecialchars($selectedStudent['contact_number'] ?? '') ?>">
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
                                    <input type="tel" name="guardian_contact" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" title="Enter exactly 10 digits" value="<?= htmlspecialchars($selectedStudent['guardian_contact'] ?? '') ?>">
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
<script src="<?= $baseUrl ?>public/js/owner-search.js"></script>
</body>
</html>

