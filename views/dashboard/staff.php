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

    if (!in_array($role, $allowedRoles, true)) {
        $role = 'staff';
    }

    // ADD new user
    if ($formAction === 'add' && $firstName !== '' && $lastName !== '' && $email !== '' && !empty($_POST['password'])) {
        $userModel->add([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => $_POST['password'],
            'role' => $role,
            'contact_number' => $contactNumber,
        ]);
        header('Location: ' . $baseUrl . 'index.php?action=owner_staff&msg=added');
        exit;
    }

    // EDIT existing user
    if ($formAction === 'edit' && $userId > 0 && $firstName !== '' && $lastName !== '' && $email !== '') {
        if (!empty($_POST['password'])) {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET first_name = ?, last_name = ?, email = ?, password = ?, role = ?, contact_number = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $firstName, $lastName, $email,
                password_hash($_POST['password'], PASSWORD_DEFAULT),
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

// Get search term
$search = trim($_GET['search'] ?? '');

// Build query with search
if ($search !== '') {
    $searchTerm = '%' . $search . '%';
    $stmt = $pdo->prepare("
        SELECT * FROM users 
        WHERE role IN ('staff', 'warden', 'admin')
        AND (
            first_name LIKE ? OR 
            last_name LIKE ? OR 
            email LIKE ? OR 
            contact_number LIKE ?
        )
        ORDER BY id DESC
    ");
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
} else {
    $stmt = $pdo->query("
        SELECT * FROM users 
        WHERE role IN ('staff', 'warden', 'admin')
        ORDER BY id DESC
    ");
}
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
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Inter', sans-serif; background: #f7f7f7; color: #121212; }
        
        /* Layout */
        .sf-page-wrap { min-height: 100vh; padding: 22px 16px; }
        .sf-body-row { display: flex; gap: 18px; min-height: calc(100vh - 44px); }
        
        /* Sidebar */
        .sf-sidebar { 
            width: 260px; 
            background: #082d59; 
            color: #fff; 
            padding: 18px 18px 22px; 
            display: flex; 
            flex-direction: column;
            border-radius: 16px;
        }
        .sf-sidebar-logo { display: flex; align-items: center; gap: 10px; margin-bottom: 28px; font-size: 15px; }
        .sf-sidebar-logo img { width: 44px; height: 44px; object-fit: contain; filter: invert(1); }
        .sf-profile-name { font-size: 14px; font-weight: 600; text-transform: uppercase; margin-bottom: 4px; }
        .sf-profile-role { font-size: 12px; color: rgba(255,255,255,0.7); margin-bottom: 26px; }
        .sf-sidebar-nav { display: flex; flex-direction: column; gap: 8px; }
        .sf-nav-btn, .sf-signout-btn {
            width: 100%;
            border: 0;
            border-radius: 999px;
            background: transparent;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 16px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        .sf-nav-btn:hover { background: rgba(255,255,255,0.1); }
        .sf-nav-btn.active { background: #fff; color: #082d59; font-weight: 600; }
        .sf-sidebar-spacer { flex: 1; }
        .sf-signout-btn { background: rgba(255,255,255,0.15); margin-top: 12px; }
        .sf-signout-btn:hover { background: rgba(255,255,255,0.25); }
        
        /* Main Content */
        .sf-main { flex: 1; background: #fff; padding: 28px; border-radius: 16px; }
        .sf-title-bar {
            background: #082d59;
            border-radius: 14px;
            color: #fff;
            text-align: center;
            padding: 14px 20px;
            font-size: 20px;
            font-weight: 500;
            margin-bottom: 24px;
        }
        
        /* Alert Messages */
        .sf-alert {
            padding: 12px 18px;
            border-radius: 10px;
            margin-bottom: 18px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .sf-alert.success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .sf-alert.error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        
        /* Search & Add Button */
        .sf-toolbar {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            align-items: center;
        }
        .sf-search {
            position: relative;
            flex: 1;
        }
        .sf-search i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 16px;
        }
        .sf-search input {
            width: 100%;
            border: 1.5px solid #e2e8f0;
            border-radius: 999px;
            background: #fff;
            padding: 12px 18px 12px 42px;
            font-size: 13px;
            transition: all 0.2s;
        }
        .sf-search input:focus {
            outline: none;
            border-color: #082d59;
            box-shadow: 0 0 0 3px rgba(8,45,89,0.08);
        }
        .sf-btn-add {
            background: #082d59;
            color: #fff;
            border: 0;
            border-radius: 999px;
            padding: 12px 22px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .sf-btn-add:hover { background: #0a3a73; transform: translateY(-1px); }
        
        /* Table */
        .sf-table-header {
            display: grid;
            grid-template-columns: 2fr 1.2fr 1fr 1fr;
            gap: 16px;
            padding: 12px 18px;
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .sf-table-body {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .sf-row {
            display: grid;
            grid-template-columns: 2fr 1.2fr 1fr 1fr;
            gap: 16px;
            align-items: center;
            background: #f8fafc;
            border-radius: 12px;
            padding: 14px 18px;
            text-decoration: none;
            color: #202020;
            transition: all 0.2s;
            border: 2px solid transparent;
        }
        .sf-row:hover { background: #f1f5f9; }
        .sf-row.active {
            background: #e8f0ff;
            border-color: #082d59;
        }
        .sf-cell { font-size: 13px; }
        .sf-cell.name { font-weight: 500; }
        .sf-cell.role span {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
        }
        .sf-cell.role .role-admin { background: #fee2e2; color: #991b1b; }
        .sf-cell.role .role-warden { background: #fef3c7; color: #92400e; }
        .sf-cell.role .role-staff { background: #dbeafe; color: #1e40af; }
        .sf-no-results {
            text-align: center;
            padding: 40px;
            color: #94a3b8;
            font-size: 14px;
        }
        
        /* Form */
        .sf-form-section {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
        }
        .sf-form-title {
            font-size: 16px;
            font-weight: 600;
            color: #082d59;
            margin-bottom: 16px;
        }
        .sf-form {
            background: #f8fafc;
            border-radius: 16px;
            padding: 22px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
        .sf-field label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: #475569;
            margin-bottom: 6px;
        }
        .sf-field input,
        .sf-field select {
            width: 100%;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            background: #fff;
            padding: 11px 14px;
            font-size: 13px;
            transition: all 0.2s;
        }
        .sf-field input:focus,
        .sf-field select:focus {
            outline: none;
            border-color: #082d59;
            box-shadow: 0 0 0 3px rgba(8,45,89,0.08);
        }
        .sf-field.full { grid-column: 1 / -1; }
        
        /* Form Actions */
        .sf-actions {
            grid-column: 1 / -1;
            display: flex;
            gap: 10px;
            margin-top: 8px;
        }
        .sf-btn {
            border: 0;
            border-radius: 999px;
            padding: 12px 24px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        .sf-btn:hover { transform: translateY(-1px); }
        .sf-btn.save { background: #082d59; color: #fff; }
        .sf-btn.save:hover { background: #0a3a73; }
        .sf-btn.cancel { background: #f1f5f9; color: #475569; }
        .sf-btn.cancel:hover { background: #e2e8f0; }
        .sf-btn.delete { background: #fef2f2; color: #dc2626; margin-left: auto; }
        .sf-btn.delete:hover { background: #fee2e2; }
        .sf-btn.delete:disabled { opacity: 0.5; cursor: not-allowed; }
        
        @media (max-width: 900px) {
            .sf-body-row { flex-direction: column; }
            .sf-sidebar { width: 100%; }
            .sf-table-header,
            .sf-row { grid-template-columns: 1fr; gap: 4px; }
        }
    </style>
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
                <a class="sf-nav-btn" href="<?= $baseUrl ?>index.php?action=owner_rooms_single"><i class="ph ph-bed"></i><span>Rooms</span></a>
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
                        <?= $search ? 'No staff found matching "' . htmlspecialchars($search) . '"' : 'No staff members found' ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($staffUsers as $staffUser): ?>
                        <?php 
                            $fullName = trim(($staffUser['first_name'] ?? '') . ' ' . ($staffUser['last_name'] ?? ''));
                            $roleClass = 'role-' . ($staffUser['role'] ?? 'staff');
                        ?>
                        <a class="sf-row <?= (int) $staffUser['id'] === $selectedId ? 'active' : '' ?>"
                           href="<?= $baseUrl ?>index.php?action=owner_staff&user_id=<?= (int) $staffUser['id'] ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                            <div class="sf-cell name"><?= htmlspecialchars($fullName ?: 'N/A') ?></div>
                            <div class="sf-cell"><?= htmlspecialchars((string) ($staffUser['contact_number'] ?? '—')) ?></div>
                            <div class="sf-cell role"><span class="<?= $roleClass ?>"><?= htmlspecialchars(ucfirst((string) ($staffUser['role'] ?? 'staff'))) ?></span></div>
                            <div class="sf-cell"><?= htmlspecialchars((string) ($staffUser['email'] ?? '—')) ?></div>
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
                        <input type="text" name="contact_number" value="<?= htmlspecialchars($selectedUser['contact_number'] ?? '') ?>" placeholder="+977-XXXXXXXXXX">
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
                        <input type="password" name="password" placeholder="<?= $isEditing ? 'Enter new password (optional)' : 'Enter password' ?>" <?= $isEditing ? '' : 'required' ?>>
                    </div>

                    <div class="sf-actions">
                        <?php if ($isEditing): ?>
                            <button class="sf-btn delete" type="submit" name="form_action" value="delete" onclick="return confirm('Are you sure you want to delete this staff member?')">
                                <i class="ph ph-trash"></i> Delete
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($isEditing): ?>
                            <button class="sf-btn cancel" type="button" onclick="window.location.href='<?= $baseUrl ?>index.php?action=owner_staff'">
                                Cancel
                            </button>
                        <?php endif; ?>
                        
                        <button class="sf-btn save" type="submit" name="form_action" value="<?= $isEditing ? 'edit' : 'add' ?>">
                            <i class="ph ph-floppy-disk"></i> <?= $isEditing ? 'Update' : 'Add Staff' ?>
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>
</body>
</html>