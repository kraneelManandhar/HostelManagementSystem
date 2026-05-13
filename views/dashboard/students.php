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

$pdo = DB::connect();
$studentModel = new Student($pdo);

function ownerAssignStudentRoom(PDO $pdo, int $studentId, int $roomId, string $bedSlot): bool
{
    if ($studentId <= 0 || $roomId <= 0 || !in_array($bedSlot, ['student1', 'student2'], true)) {
        return false;
    }

    $pdo->beginTransaction();

    try {
        $roomStmt = $pdo->prepare("SELECT id, type, student1_id, student2_id FROM rooms WHERE id = ? FOR UPDATE");
        $roomStmt->execute([$roomId]);
        $room = $roomStmt->fetch(PDO::FETCH_ASSOC);

        if (!$room) {
            $pdo->rollBack();
            return false;
        }

        if (($room['type'] ?? '') === 'single' && $bedSlot !== 'student1') {
            $pdo->rollBack();
            return false;
        }

        $targetColumn = $bedSlot . '_id';
        $targetOccupant = (int) ($room[$targetColumn] ?? 0);

        if ($targetOccupant !== 0 && $targetOccupant !== $studentId) {
            $pdo->rollBack();
            return false;
        }

        $clearRooms = $pdo->prepare("
            UPDATE rooms
            SET student1_id = CASE WHEN student1_id = ? THEN NULL ELSE student1_id END,
                student2_id = CASE WHEN student2_id = ? THEN NULL ELSE student2_id END
        ");
        $clearRooms->execute([$studentId, $studentId]);

        $assignRoom = $pdo->prepare("UPDATE rooms SET {$targetColumn} = ? WHERE id = ?");
        $assignRoom->execute([$studentId, $roomId]);

        $assignStudent = $pdo->prepare("UPDATE students SET room_id = ?, preferred_room_type = ? WHERE id = ?");
        $assignStudent->execute([$roomId, $room['type'], $studentId]);

        $pdo->commit();
        return true;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return false;
    }
}

function ownerRoomsForPicker(PDO $pdo): array
{
    $stmt = $pdo->query("
        SELECT
            r.*,
            s1.id AS student1_id,
            TRIM(CONCAT(COALESCE(s1.first_name, ''), ' ', COALESCE(s1.middle_name, ''), ' ', COALESCE(s1.last_name, ''))) AS student1_name,
            s2.id AS student2_id,
            TRIM(CONCAT(COALESCE(s2.first_name, ''), ' ', COALESCE(s2.middle_name, ''), ' ', COALESCE(s2.last_name, ''))) AS student2_name
        FROM rooms r
        LEFT JOIN students s1 ON s1.id = r.student1_id
        LEFT JOIN students s2 ON s2.id = r.student2_id
        ORDER BY r.type ASC, r.number ASC
    ");

    $rooms = ['double' => [], 'single' => []];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $room) {
        $type = $room['type'] ?? 'double';
        if (!isset($rooms[$type])) {
            $rooms[$type] = [];
        }
        $rooms[$type][] = $room;
    }

    return $rooms;
}

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
        $changeRoom = ($_POST['change_room'] ?? '') === '1';
        $newRoomId = (int) ($_POST['new_room_id'] ?? 0);
        $newBedSlot = trim($_POST['new_bed_slot'] ?? '');

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

        if ($changeRoom && $newRoomId > 0 && $newBedSlot !== '') {
            ownerAssignStudentRoom($pdo, $studentId, $newRoomId, $newBedSlot);
        }

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
$roomsByType = ownerRoomsForPicker($pdo);

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
    <link rel="stylesheet" href="<?= $baseUrl ?>public/css/owner.css?v=11">
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
                <a class="ms-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_rooms"><i class="ph ph-bed"></i><span>Rooms</span></a>
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
                                data-room-number="<?= htmlspecialchars((string) ($studentItem['room_number'] ?? '')) ?>"
                                data-search="<?= htmlspecialchars(strtolower($fullName . ' id ' . (int) $studentItem['id'] . ' ' . ($studentItem['email'] ?? '') . ' ' . ($studentItem['contact_number'] ?? '') . ' room ' . ($studentItem['room_number'] ?? '') . ' ' . ($studentItem['room_type'] ?? ''))) ?>"
                                href="<?= $baseUrl ?>index.php?action=owner_students&student_id=<?= (int) $studentItem['id'] ?>"
                            >
                                <?= htmlspecialchars($fullName) ?>
                                <small style="display:block;color:#667085;font-size:11px;margin-top:2px;">ID #<?= (int) $studentItem['id'] ?></small>
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
                        <a class="ms-action-btn" href="<?= $baseUrl ?>index.php?action=register">REGISTER NOW</a>
                    </div>

                    <form class="ms-student-form" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="student_id" value="<?= (int) ($selectedStudent['id'] ?? 0) ?>">
                        <input type="hidden" name="current_photo" value="<?= htmlspecialchars($selectedStudent['profile_photo'] ?? '') ?>">

                        <fieldset class="ms-view-fieldset" disabled>
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
                                    <div class="ms-field">
                                        <label>Student ID</label>
                                        <input type="text" value="#<?= (int) ($selectedStudent['id'] ?? 0) ?>" readonly>
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
                                <?php $currentRoomId = (int) ($selectedStudent['room_id'] ?? 0); ?>
                                <?php $currentStudentId = (int) ($selectedStudent['id'] ?? 0); ?>
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

                                <input type="hidden" name="change_room" value="0" id="msChangeRoomInput">
                                <input type="hidden" name="new_room_id" value="" id="msNewRoomInput">
                                <input type="hidden" name="new_bed_slot" value="" id="msNewBedInput">

                                <button class="ms-change-room-btn" type="button" id="msChangeRoomBtn">
                                    Change room
                                </button>

                                <div class="ms-room-picker" id="msRoomPicker" hidden>
                                    <div class="ms-picker-title">Select room</div>

                                    <?php foreach (['double' => 'Double sitter', 'single' => 'Single sitter'] as $type => $label): ?>
                                        <div class="ms-room-picker-pane" data-room-type="<?= htmlspecialchars($type) ?>" <?= $type === $roomType ? '' : 'hidden' ?>>
                                            <?php if (empty($roomsByType[$type])): ?>
                                                <div class="ms-room-empty">No <?= htmlspecialchars(strtolower($label)) ?> rooms found.</div>
                                            <?php else: ?>
                                                <div class="ms-room-choice-grid">
                                                    <?php foreach ($roomsByType[$type] as $roomOption): ?>
                                                        <?php
                                                        $roomId = (int) $roomOption['id'];
                                                        $student1Id = (int) ($roomOption['student1_id'] ?? 0);
                                                        $student2Id = (int) ($roomOption['student2_id'] ?? 0);
                                                        $student1Available = $student1Id === 0 || $student1Id === $currentStudentId;
                                                        $student2Available = $type === 'double' && ($student2Id === 0 || $student2Id === $currentStudentId);
                                                        $roomAvailable = $student1Available || $student2Available;
                                                        ?>
                                                        <div
                                                            class="ms-room-choice <?= $roomId === $currentRoomId ? 'current' : '' ?> <?= $roomAvailable ? '' : 'is-full' ?>"
                                                            data-room-id="<?= $roomId ?>"
                                                            data-room-number="<?= htmlspecialchars((string) ($roomOption['number'] ?? '')) ?>"
                                                        >
                                                            <div class="ms-room-choice-head">
                                                                <span>Room <?= htmlspecialchars((string) ($roomOption['number'] ?? '')) ?></span>
                                                                <?php if ($roomId === $currentRoomId): ?>
                                                                    <small>Current</small>
                                                                <?php elseif (!$roomAvailable): ?>
                                                                    <small>Full</small>
                                                                <?php endif; ?>
                                                            </div>

                                                            <div class="ms-bed-options">
                                                                <button
                                                                    type="button"
                                                                    class="ms-bed-option"
                                                                    data-room-id="<?= $roomId ?>"
                                                                    data-bed-slot="student1"
                                                                    <?= $student1Available ? '' : 'disabled' ?>
                                                                >
                                                                    Bed 1
                                                                    <?php if (!$student1Available && !empty($roomOption['student1_name'])): ?>
                                                                        <small>#<?= (int) ($roomOption['student1_id'] ?? 0) ?> <?= htmlspecialchars((string) $roomOption['student1_name']) ?></small>
                                                                    <?php endif; ?>
                                                                </button>

                                                                <?php if ($type === 'double'): ?>
                                                                    <button
                                                                        type="button"
                                                                        class="ms-bed-option"
                                                                        data-room-id="<?= $roomId ?>"
                                                                        data-bed-slot="student2"
                                                                        <?= $student2Available ? '' : 'disabled' ?>
                                                                    >
                                                                        Bed 2
                                                                        <?php if (!$student2Available && !empty($roomOption['student2_name'])): ?>
                                                                            <small>#<?= (int) ($roomOption['student2_id'] ?? 0) ?> <?= htmlspecialchars((string) $roomOption['student2_name']) ?></small>
                                                                        <?php endif; ?>
                                                                    </button>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </section>
                        </fieldset>

                        <div class="ms-footer-actions">
                            <button class="ms-delete-btn" type="submit" name="form_action" value="delete" data-confirm="Are you sure you want to delete this student record?" <?= $selectedId ? '' : 'disabled' ?>>
                                <i class="ph ph-trash"></i> DELETE RECORDS
                            </button>
                            <button class="ms-edit-btn" type="button" data-edit-student data-confirm="Are you sure you want to edit this student record?" <?= $selectedId ? '' : 'disabled' ?>>
                                EDIT
                            </button>
                            <button class="ms-save-btn" type="submit" name="form_action" value="<?= $selectedId ? 'edit' : 'add' ?>" data-confirm="Are you sure you want to save changes to this student record?" disabled>
                                SAVE CHANGES
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </main>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('.ms-student-form');
    if (!form) return;

    const fieldset = form.querySelector('.ms-view-fieldset');
    const editButton = form.querySelector('[data-edit-student]');
    const saveButton = form.querySelector('.ms-save-btn');
    const changeRoomButton = form.querySelector('#msChangeRoomBtn');
    const roomPicker = form.querySelector('#msRoomPicker');
    const changeRoomInput = form.querySelector('#msChangeRoomInput');
    const newRoomInput = form.querySelector('#msNewRoomInput');
    const newBedInput = form.querySelector('#msNewBedInput');

    if (!fieldset || !editButton || !saveButton) return;

    function openRoomPicker() {
        if (!roomPicker || !changeRoomInput) return;
        roomPicker.hidden = false;
        changeRoomInput.value = '1';
        syncRoomTypePane();
    }

    function resetRoomSelection() {
        if (newRoomInput) newRoomInput.value = '';
        if (newBedInput) newBedInput.value = '';
        form.querySelectorAll('.ms-bed-option.selected').forEach(function (button) {
            button.classList.remove('selected');
        });
        form.querySelectorAll('.ms-room-choice.selected').forEach(function (room) {
            room.classList.remove('selected');
        });
    }

    function syncRoomTypePane() {
        const selectedType = form.querySelector('input[name="preferred_room_type"]:checked')?.value || 'double';

        form.querySelectorAll('.ms-room-picker-pane').forEach(function (pane) {
            pane.hidden = pane.dataset.roomType !== selectedType;
        });
    }

    editButton.addEventListener('click', function () {
        fieldset.disabled = false;
        form.classList.add('is-editing');
        saveButton.disabled = false;
        editButton.disabled = true;

        const firstField = fieldset.querySelector('input:not([type="file"]), select, textarea');
        if (firstField) {
            firstField.focus();
        }
    });

    if (changeRoomButton) {
        changeRoomButton.addEventListener('click', openRoomPicker);
    }

    form.querySelectorAll('input[name="preferred_room_type"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            if (fieldset.disabled) return;
            resetRoomSelection();
            openRoomPicker();
        });
    });

    form.querySelectorAll('.ms-bed-option').forEach(function (button) {
        button.addEventListener('click', function () {
            if (fieldset.disabled || button.disabled) return;

            resetRoomSelection();
            button.classList.add('selected');

            const room = button.closest('.ms-room-choice');
            if (room) {
                room.classList.add('selected');
            }

            if (newRoomInput) newRoomInput.value = button.dataset.roomId || '';
            if (newBedInput) newBedInput.value = button.dataset.bedSlot || '';
            if (changeRoomInput) changeRoomInput.value = '1';
        });
    });
});
</script>
<script src="<?= $baseUrl ?>public/js/owner-search.js?v=5"></script>
<script src="<?= $baseUrl ?>public/js/confirm-actions.js?v=1"></script>
</body>
</html>

