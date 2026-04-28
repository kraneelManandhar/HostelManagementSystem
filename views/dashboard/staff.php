<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$baseUrl = defined('BASE_URL') ? BASE_URL : '/HostelManagementSystem-main/';

if (
    empty($_SESSION['logged_in']) ||
    ($_SESSION['user_role'] ?? '') !== 'manager'
) {
    header("Location: {$baseUrl}index.php?action=login");
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/User.php';

$pdo = DB::connect();
$userModel = new User($pdo);
$allowedRoles = ['staff', 'warden', 'admin', 'manager'];

function staffRedirect(string $baseUrl, ?int $selectedId = null): void
{
    $target = $baseUrl . 'index.php?action=manager_staff';
    if ($selectedId) {
        $target .= '&user_id=' . $selectedId;
    }
    header('Location: ' . $target);
    exit;
}

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

    if ($formAction === 'add' && $firstName !== '' && $lastName !== '' && $email !== '') {
        $userModel->add([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => $_POST['password'] ?? 'staff123',
            'role' => $role,
            'contact_number' => $contactNumber,
        ]);
        staffRedirect($baseUrl);
    }

    if ($formAction === 'edit' && $userId > 0 && $firstName !== '' && $lastName !== '' && $email !== '') {
        if (!empty($_POST['password'])) {
            $stmt = $pdo->prepare("
                UPDATE users
                SET first_name = ?, last_name = ?, email = ?, password = ?, role = ?, contact_number = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $firstName,
                $lastName,
                $email,
                password_hash($_POST['password'], PASSWORD_DEFAULT),
                $role,
                $contactNumber,
                $userId,
            ]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE users
                SET first_name = ?, last_name = ?, email = ?, role = ?, contact_number = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $firstName,
                $lastName,
                $email,
                $role,
                $contactNumber,
                $userId,
            ]);
        }
        staffRedirect($baseUrl, $userId);
    }

    if ($formAction === 'delete' && $userId > 0) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        staffRedirect($baseUrl);
    }
}

$placeholders = implode(',', array_fill(0, count($allowedRoles), '?'));
$stmt = $pdo->prepare("
    SELECT * FROM users
    WHERE role IN ($placeholders)
    ORDER BY id DESC
");
$stmt->execute($allowedRoles);
$staffUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$selectedId = (int) ($_GET['user_id'] ?? 0);
$selectedUser = null;

foreach ($staffUsers as $staffUser) {
    if ((int) $staffUser['id'] === $selectedId) {
        $selectedUser = $staffUser;
        break;
    }
}

if ($selectedUser === null && !empty($staffUsers)) {
    $selectedUser = $staffUsers[0];
    $selectedId = (int) $selectedUser['id'];
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
    <title>Manager Staff - Pentatonic Hostel</title>
    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Inter', sans-serif; background: #f7f7f7; color: #121212; }
        .sf-page-wrap { min-height: 100vh; padding: 22px 16px; }
        .sf-body-row { display: flex; gap: 18px; min-height: calc(100vh - 44px); }
        .sf-sidebar { width: 235px; background: #082d59; color: #fff; padding: 18px 18px 22px; display: flex; flex-direction: column; }
        .sf-sidebar-logo { display: flex; align-items: center; gap: 10px; margin-bottom: 28px; font-size: 15px; }
        .sf-sidebar-logo img { width: 44px; height: 44px; object-fit: contain; }
        .sf-profile-name { font-size: 14px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px; }
        .sf-profile-role { font-size: 12px; color: rgba(255, 255, 255, 0.9); margin-bottom: 26px; }
        .sf-sidebar-nav { display: flex; flex-direction: column; gap: 16px; }
        .sf-nav-btn, .sf-signout-btn {
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
        .sf-nav-btn.active { background: #000; color: #fff; }
        .sf-sidebar-spacer { flex: 1; }
        .sf-main { flex: 1; background: #fff; padding: 28px 28px 30px; }
        .sf-title-bar {
            background: #082d59;
            border-radius: 14px;
            color: #fff;
            text-align: center;
            padding: 11px 20px;
            font-size: 20px;
            font-weight: 500;
            margin-bottom: 20px;
        }
        .sf-search {
            position: relative;
            margin-bottom: 18px;
        }
        .sf-search i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #b7b7b7;
            font-size: 16px;
        }
        .sf-search input {
            width: 100%;
            border: 0;
            border-radius: 999px;
            background: #f1f3f8;
            padding: 14px 18px 14px 40px;
            font-size: 13px;
        }
        .sf-table-wrap {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
            margin-bottom: 22px;
        }
        .sf-row {
            display: grid;
            grid-template-columns: 1.4fr 1fr 1fr;
            gap: 18px;
            align-items: center;
            background: #e8f0ff;
            border-radius: 18px;
            padding: 16px 18px;
            text-decoration: none;
            color: #202020;
        }
        .sf-row.active {
            outline: 2px solid #082d59;
        }
        .sf-cell {
            text-align: center;
            font-size: 14px;
        }
        .sf-form {
            background: #e8f0ff;
            border-radius: 18px;
            padding: 18px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
        }
        .sf-field label {
            display: block;
            font-size: 13px;
            margin-bottom: 6px;
        }
        .sf-field input,
        .sf-field select {
            width: 100%;
            border: 0;
            border-radius: 999px;
            background: #fff;
            padding: 11px 14px;
            font-size: 13px;
        }
        .sf-field.full {
            grid-column: 1 / -1;
        }
        .sf-actions {
            grid-column: 1 / -1;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-top: 4px;
        }
        .sf-btn {
            border: 0;
            border-radius: 999px;
            padding: 12px 22px;
            font-size: 13px;
            cursor: pointer;
        }
        .sf-btn.delete { background: #fff1f1; color: #d95d5d; }
        .sf-btn.edit { background: #efefef; color: #4a4a4a; }
        .sf-btn.save { background: #ffc425; color: #1a1a1a; }
        @media (max-width: 900px) {
            .sf-body-row { flex-direction: column; }
            .sf-sidebar { width: 100%; }
            .sf-row { grid-template-columns: 1fr; }
            .sf-form { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="sf-page-wrap">
    <div class="sf-body-row">
        <aside class="sf-sidebar">
            <div class="sf-sidebar-logo">
                <img src="<?= $baseUrl ?>public/images/logo.png" alt="Logo">
                <span>Pentatonic hostel</span>
            </div>

            <div class="sf-profile-name"><?= htmlspecialchars($managerName) ?></div>
            <div class="sf-profile-role">OWNER</div>

            <nav class="sf-sidebar-nav">
                <a class="sf-nav-btn" href="<?= $baseUrl ?>index.php?action=manager_dashboard"><i class="ph-fill ph-squares-four"></i><span>Dashboard</span></a>
                <a class="sf-nav-btn" href="<?= $baseUrl ?>index.php?action=manager_students"><i class="ph ph-student"></i><span>Students</span></a>
                <a class="sf-nav-btn" href="<?= $baseUrl ?>index.php?action=manager_rooms_single"><i class="ph ph-bed"></i><span>Rooms</span></a>
                <a class="sf-nav-btn" href="<?= $baseUrl ?>index.php?action=manager_fees"><i class="ph ph-money"></i><span>Fees</span></a>
                <a class="sf-nav-btn" href="<?= $baseUrl ?>index.php?action=manager_complaints"><i class="ph ph-megaphone"></i><span>Complaints</span></a>
                <a class="sf-nav-btn" href="<?= $baseUrl ?>index.php?action=manager_notices"><i class="ph ph-warning"></i><span>Notice</span></a>
                <a class="sf-nav-btn active" href="<?= $baseUrl ?>index.php?action=manager_staff"><i class="ph ph-users-three"></i><span>Staffs</span></a>
            </nav>

            <div class="sf-sidebar-spacer"></div>

            <a class="sf-signout-btn" href="<?= $baseUrl ?>index.php?action=logout">
                <i class="ph ph-sign-out"></i>
                <span>Sign out</span>
            </a>
        </aside>

        <main class="sf-main">
            <div class="sf-title-bar">STAFFS</div>

            <div class="sf-search">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" placeholder="Search">
            </div>

            <div class="sf-table-wrap">
                <div class="sf-row">
                    <div class="sf-cell">Full name</div>
                    <div class="sf-cell">Number</div>
                    <div class="sf-cell">Role</div>
                </div>

                <?php foreach ($staffUsers as $staffUser): ?>
                    <?php $fullName = trim(($staffUser['first_name'] ?? '') . ' ' . ($staffUser['last_name'] ?? '')); ?>
                    <a
                        class="sf-row <?= (int) $staffUser['id'] === $selectedId ? 'active' : '' ?>"
                        href="<?= $baseUrl ?>index.php?action=manager_staff&user_id=<?= (int) $staffUser['id'] ?>"
                    >
                        <div class="sf-cell"><?= htmlspecialchars($fullName ?: 'Full name') ?></div>
                        <div class="sf-cell"><?= htmlspecialchars((string) ($staffUser['contact_number'] ?? '')) ?></div>
                        <div class="sf-cell"><?= htmlspecialchars(ucfirst((string) ($staffUser['role'] ?? 'staff'))) ?></div>
                    </a>
                <?php endforeach; ?>
            </div>

            <form class="sf-form" method="post">
                <input type="hidden" name="user_id" value="<?= (int) ($selectedUser['id'] ?? 0) ?>">

                <div class="sf-field">
                    <label>First name</label>
                    <input type="text" name="first_name" value="<?= htmlspecialchars($selectedUser['first_name'] ?? '') ?>" required>
                </div>

                <div class="sf-field">
                    <label>Last name</label>
                    <input type="text" name="last_name" value="<?= htmlspecialchars($selectedUser['last_name'] ?? '') ?>" required>
                </div>

                <div class="sf-field full">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($selectedUser['email'] ?? '') ?>" required>
                </div>

                <div class="sf-field">
                    <label>Number</label>
                    <input type="text" name="contact_number" value="<?= htmlspecialchars($selectedUser['contact_number'] ?? '') ?>">
                </div>

                <div class="sf-field">
                    <label>Role</label>
                    <select name="role">
                        <?php foreach ($allowedRoles as $role): ?>
                            <option value="<?= $role ?>" <?= (($selectedUser['role'] ?? 'staff') === $role) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($role)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="sf-field full">
                    <label>Password <?= $selectedId ? '(leave blank to keep current)' : '' ?></label>
                    <input type="password" name="password" placeholder="<?= $selectedId ? 'Optional password update' : 'Enter password' ?>">
                </div>

                <div class="sf-actions">
                    <button class="sf-btn delete" type="submit" name="form_action" value="delete" <?= $selectedId ? '' : 'disabled' ?>>
                        DELETE
                    </button>
                    <button class="sf-btn edit" type="submit" name="form_action" value="edit" <?= $selectedId ? '' : 'disabled' ?>>
                        EDIT
                    </button>
                    <button class="sf-btn save" type="submit" name="form_action" value="<?= $selectedId ? 'edit' : 'add' ?>">
                        SAVE CHANGES
                    </button>
                </div>
            </form>
        </main>
    </div>
</div>
</body>
</html>
