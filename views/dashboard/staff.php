<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$baseUrl = defined('BASE_URL') ? BASE_URL : '/HostelManagementSystem/';

if (empty($_SESSION['logged_in']) || ($_SESSION['user_role'] ?? '') !== 'owner') {
    header("Location: {$baseUrl}index.php?action=login");
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/User.php';

$pdo = DB::connect();
$userModel = new User($pdo);
$allowedRoles = ['staff', 'warden', 'admin'];

// Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? '';
    $userId = (int) ($_POST['user_id'] ?? 0);
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contactNumber = trim($_POST['contact_number'] ?? '');
    $role = trim($_POST['role'] ?? 'staff');
    $password = $_POST['password'] ?? '';

    if (!in_array($role, $allowedRoles, true)) {
        $role = 'staff';
    }

    if ($contactNumber !== '' && !preg_match('/^\d{10}$/', $contactNumber)) {
        header('Location: ' . $baseUrl . 'index.php?action=owner_staff&msg=invalid_phone');
        exit;
    }

    if (($formAction === 'add' || $password !== '') && !preg_match('/^(?=.*[A-Za-z])(?=.*\d).{6,}$/', $password)) {
        header('Location: ' . $baseUrl . 'index.php?action=owner_staff&msg=invalid_password');
        exit;
    }

    // ADD new user
    if ($formAction === 'add' && $firstName !== '' && $lastName !== '' && $email !== '' && $password !== '') {
        $userModel->add([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => $password,
            'role' => $role,
            'contact_number' => $contactNumber,
        ]);
        header('Location: ' . $baseUrl . 'index.php?action=owner_staff&msg=added');
        exit;
    }

    // EDIT existing user
    if ($formAction === 'edit' && $userId > 0 && $firstName !== '' && $lastName !== '' && $email !== '') {
        if ($password !== '') {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET first_name = ?, last_name = ?, email = ?, password = ?, role = ?, contact_number = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $firstName, $lastName, $email,
                password_hash($password, PASSWORD_DEFAULT),
                $role, $contactNumber, $userId
            ]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET first_name = ?, last_name = ?, email = ?, role = ?, contact_number = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $firstName, $lastName, $email,
                $role, $contactNumber, $userId
            ]);
        }
        header('Location: ' . $baseUrl . 'index.php?action=owner_staff&user_id=' . $userId . '&msg=updated');
        exit;
    }

    // DELETE user
    if ($formAction === 'delete' && $userId > 0) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        header('Location: ' . $baseUrl . 'index.php?action=owner_staff&msg=deleted');
        exit;
    }
}

$search = trim($_GET['search'] ?? '');

$stmt = $pdo->query("
    SELECT * FROM users
    WHERE role IN ('staff', 'warden', 'admin')
    ORDER BY id DESC
");
$staffUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get selected user for editing
$selectedId = (int) ($_GET['user_id'] ?? 0);
$selectedUser = null;
$isEditing = false;

if ($selectedId > 0) {
    foreach ($staffUsers as $user) {
        if ((int) $user['id'] === $selectedId) {
            $selectedUser = $user;
            $isEditing = true;
            break;
        }
    }
}

$managerName = trim((string) ($_SESSION['user_name'] ?? 'FULL NAME'));
if ($managerName === '') {
    $managerName = 'FULL NAME';
}

// Success messages
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff - Pentatonic Hostel</title>
    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $baseUrl ?>public/css/owner.css?v=2">
</head>
<body>
<div class="sf-page-wrap">
    <div class="sf-body-row">
        <!-- Sidebar -->
        <aside class="sf-sidebar">
            <div class="sf-sidebar-logo">
                <img src="<?= $baseUrl ?>public/images/logo.png" alt="Logo">
                <span>Pentatonic hostel</span>
            </div>

            <div class="sf-profile-name"><?= htmlspecialchars($managerName) ?></div>
            <div class="sf-profile-role">OWNER</div>

            <nav class="sf-sidebar-nav">
                <a class="sf-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_dashboard"><i class="ph-fill ph-squares-four"></i><span>Dashboard</span></a>
                <a class="sf-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_students"><i class="ph ph-student"></i><span>Students</span></a>
                <a class="sf-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_rooms"><i class="ph ph-bed"></i><span>Rooms</span></a>
                <a class="sf-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_fees"><i class="ph ph-money"></i><span>Fees</span></a>
                <a class="sf-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_complaints"><i class="ph ph-megaphone"></i><span>Complaints</span></a>
                <a class="sf-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_notices"><i class="ph ph-warning"></i><span>Notice</span></a>
                <a class="sf-nav-btn active" href="<?= $baseUrl ?>index.php?action=owner_staff"><i class="ph ph-users-three"></i><span>Staffs</span></a>
            </nav>

            <div class="sf-sidebar-spacer"></div>

            <a class="sf-signout-btn" href="<?= $baseUrl ?>index.php?action=logout">
                <i class="ph ph-sign-out"></i>
                <span>Sign out</span>
            </a>
        </aside>

        <!-- Main Content -->
        <main class="sf-main">
            <div class="sf-title-bar">STAFF MANAGEMENT</div>

            <!-- Success/Error Messages -->
            <?php if ($msg === 'added'): ?>
                <div class="sf-alert success"><i class="ph-fill ph-check-circle"></i> Staff member added successfully!</div>
            <?php elseif ($msg === 'updated'): ?>
                <div class="sf-alert success"><i class="ph-fill ph-check-circle"></i> Staff member updated successfully!</div>
            <?php elseif ($msg === 'deleted'): ?>
                <div class="sf-alert success"><i class="ph-fill ph-check-circle"></i> Staff member deleted successfully!</div>
            <?php elseif ($msg === 'invalid_phone'): ?>
                <div class="sf-alert error"><i class="ph-fill ph-warning-circle"></i> Contact number must contain exactly 10 digits.</div>
            <?php elseif ($msg === 'invalid_password'): ?>
                <div class="sf-alert error"><i class="ph-fill ph-warning-circle"></i> Password must contain both letters and numbers.</div>
            <?php endif; ?>

            <!-- Search & Add -->
            <div class="sf-toolbar">
                <div class="sf-search">
                    <i class="ph ph-magnifying-glass"></i>
                    <form method="get" style="margin:0;">
                        <input type="hidden" name="action" value="owner_staff">
                        <input type="text" name="search" placeholder="Search by name, email, or phone..." value="<?= htmlspecialchars($search) ?>">
                    </form>
                </div>
                <?php if ($isEditing): ?>
                    <button class="sf-btn-add" onclick="window.location.href='<?= $baseUrl ?>index.php?action=owner_staff'">
                        <i class="ph ph-plus"></i> Add New
                    </button>
                <?php endif; ?>
            </div>

            <!-- Staff Table -->
            <div class="sf-table-header">
                <div>Full Name</div>
                <div>Contact</div>
                <div>Role</div>
                <div>Email</div>
            </div>
            
            <div class="sf-table-body">
                <?php if (empty($staffUsers)): ?>
                    <div class="sf-no-results">
                        <i class="ph ph-users" style="font-size: 32px; margin-bottom: 8px; display: block;"></i>
                        No staff members found
                    </div>
                <?php else: ?>
                    <?php foreach ($staffUsers as $staffUser): ?>
                        <?php 
                            $fullName = trim(($staffUser['first_name'] ?? '') . ' ' . ($staffUser['last_name'] ?? ''));
                            $roleClass = 'role-' . ($staffUser['role'] ?? 'staff');
                        ?>
                        <a class="sf-row <?= (int) $staffUser['id'] === $selectedId ? 'active' : '' ?>"
                           data-search="<?= htmlspecialchars(strtolower($fullName . ' ' . ($staffUser['contact_number'] ?? '') . ' ' . ($staffUser['role'] ?? '') . ' ' . ($staffUser['email'] ?? ''))) ?>"
                           href="<?= $baseUrl ?>index.php?action=owner_staff&user_id=<?= (int) $staffUser['id'] ?>"
                           data-confirm="Are you sure you want to edit this staff member?">
                            <div class="sf-cell name"><?= htmlspecialchars($fullName ?: 'N/A') ?></div>
                            <div class="sf-cell"><?= htmlspecialchars((string) ($staffUser['contact_number'] ?? '-')) ?></div>
                            <div class="sf-cell role"><span class="<?= $roleClass ?>"><?= htmlspecialchars(ucfirst((string) ($staffUser['role'] ?? 'staff'))) ?></span></div>
                            <div class="sf-cell"><?= htmlspecialchars((string) ($staffUser['email'] ?? '-')) ?></div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Add/Edit Form -->
            <div class="sf-form-section">
                <div class="sf-form-title"><?= $isEditing ? 'Edit Staff Member' : 'Add New Staff Member' ?></div>
                
                <form class="sf-form" method="post">
                    <input type="hidden" name="user_id" value="<?= (int) ($selectedUser['id'] ?? 0) ?>">

                    <div class="sf-field">
                        <label>First name *</label>
                        <input type="text" name="first_name" value="<?= htmlspecialchars($selectedUser['first_name'] ?? '') ?>" required>
                    </div>

                    <div class="sf-field">
                        <label>Last name *</label>
                        <input type="text" name="last_name" value="<?= htmlspecialchars($selectedUser['last_name'] ?? '') ?>" required>
                    </div>

                    <div class="sf-field full">
                        <label>Email *</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($selectedUser['email'] ?? '') ?>" required>
                    </div>

                    <div class="sf-field">
                        <label>Contact Number</label>
                        <input type="tel" name="contact_number" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" title="Enter exactly 10 digits" value="<?= htmlspecialchars($selectedUser['contact_number'] ?? '') ?>" placeholder="10 digit phone number">
                    </div>

                    <div class="sf-field">
                        <label>Role *</label>
                        <select name="role" required>
                            <?php foreach ($allowedRoles as $role): ?>
                                <option value="<?= $role ?>" <?= (($selectedUser['role'] ?? 'staff') === $role) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(ucfirst($role)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="sf-field full">
                        <label>Password <?= $isEditing ? '(leave blank to keep current)' : '*' ?></label>
                        <input type="password" name="password" minlength="6" pattern="(?=.*[A-Za-z])(?=.*\d).{6,}" title="Use at least 6 characters with letters and numbers" placeholder="<?= $isEditing ? 'Enter new password (optional)' : 'Enter password' ?>" <?= $isEditing ? '' : 'required' ?>>
                    </div>

                    <div class="sf-actions">
                        <?php if ($isEditing): ?>
                            <button class="sf-btn delete" type="submit" name="form_action" value="delete" data-confirm="Are you sure you want to delete this staff member?">
                                <i class="ph ph-trash"></i> Delete
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($isEditing): ?>
                            <button class="sf-btn cancel" type="button" onclick="window.location.href='<?= $baseUrl ?>index.php?action=owner_staff'">
                                Cancel
                            </button>
                        <?php endif; ?>
                        
                        <button
                            class="sf-btn save"
                            type="submit"
                            name="form_action"
                            value="<?= $isEditing ? 'edit' : 'add' ?>"
                            <?= $isEditing ? 'data-confirm="Are you sure you want to save changes to this staff member?"' : '' ?>
                        >
                            <i class="ph ph-floppy-disk"></i> <?= $isEditing ? 'Update' : 'Add Staff' ?>
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>
<script src="<?= $baseUrl ?>public/js/owner-search.js"></script>
<script src="<?= $baseUrl ?>public/js/confirm-actions.js?v=1"></script>
</body>
</html>

