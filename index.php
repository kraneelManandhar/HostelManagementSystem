<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/HostelManagementSystem/');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Student.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Room.php';
require_once __DIR__ . '/models/Fee.php';
require_once __DIR__ . '/models/Notice.php';
require_once __DIR__ . '/models/Complaint.php';
require_once __DIR__ . '/controllers/StudentController.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/ComplaintController.php';
require_once __DIR__ . '/controllers/PasswordResetController.php';

$action = $_GET['action'] ?? 'home';

$publicPages = [
    'home',
    'about',
    'login',
    'register',
    'register_step1',
    'set_password',
    'register_final',
    'forgot_password',
    'forgot_password_submit',
    'reset_password',
    'reset_password_submit',
    'staff',
    'facilities'
];

if (isset($_SESSION['logged_in']) && in_array($action, $publicPages, true)) {
    switch ($_SESSION['user_role']) {
        case 'owner':
            header('Location: ' . BASE_URL . 'index.php?action=owner_dashboard');
            exit;
        case 'warden':
            header('Location: ' . BASE_URL . 'index.php?action=warden_dashboard');
            exit;
        case 'admin':
            header('Location: ' . BASE_URL . 'index.php?action=admin_dashboard');
            exit;
        default:
            header('Location: ' . BASE_URL . 'index.php?action=student_dashboard');
            exit;
    }
}

switch ($action) {
    case 'home':
    case 'about':
        include 'views/pages/about.php';
        break;

    case 'register':
        include 'views/auth/register.php';
        break;

    case 'register_step1':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_SESSION['reg_data'] = $_POST;

            if (!empty($_FILES['profile_photo']['name'])) {
                $uploadDir = __DIR__ . '/public/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '_' . rand(100000000, 999999999) . '.' . $ext;
                move_uploaded_file($_FILES['profile_photo']['tmp_name'], $uploadDir . $filename);
                $_SESSION['reg_data']['profile_photo'] = $filename;
            }

            header('Location: ' . BASE_URL . 'index.php?action=set_password');
            exit;
        }

        include 'views/auth/register.php';
        break;

    case 'set_password':
        include 'views/auth/set_password.php';
        break;

    case 'register_final':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($password) || $password !== $confirmPassword || strlen($password) < 6) {
                header('Location: ' . BASE_URL . 'index.php?action=set_password&error=password');
                exit;
            }

            $data = $_SESSION['reg_data'] ?? [];
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);

            $pdo = DB::connect();
            $studentController = new StudentController($pdo);
            $studentController->register($data);

            unset($_SESSION['reg_data']);
            header('Location: ' . BASE_URL . 'index.php?action=login&registered=success');
            exit;
        }
        break;

    case 'login':
        include 'views/auth/login.php';
        break;

    case 'logout':
        $auth = new AuthController();
        $auth->logout();
        header('Location: ' . BASE_URL . 'index.php?action=login');
        exit;

    case 'forgot_password':
        $pdo = DB::connect();
        $ctrl = new PasswordResetController($pdo);
        $ctrl->showForgotForm();
        break;

    case 'forgot_password_submit':
        $pdo = DB::connect();
        $ctrl = new PasswordResetController($pdo);
        $ctrl->handleForgotPassword();
        break;

    case 'reset_password':
        $pdo = DB::connect();
        $ctrl = new PasswordResetController($pdo);
        $ctrl->showResetForm();
        break;

    case 'reset_password_submit':
        $pdo = DB::connect();
        $ctrl = new PasswordResetController($pdo);
        $ctrl->handleResetPassword();
        break;

    case 'student_dashboard':
        requireRole('student');
        $pdo = DB::connect();
        $studentController = new StudentController($pdo);
        extract($studentController->getDashboardData($_SESSION['user_id']));
        include 'views/dashboard/student_dashboard.php';
        break;

    case 'complaint_add':
        requireRole('student');
        $pdo = DB::connect();
        $cc = new ComplaintController($pdo);
        $cc->store();
        break;

    case 'complaint_delete':
        requireRole('student');
        $pdo = DB::connect();
        $cc = new ComplaintController($pdo);
        $cc->delete();
        break;

    case 'warden_dashboard':
        requireRole('warden');
        include 'views/dashboard/wardenDashboard.php';
        break;

    case 'owner_dashboard':
        requireRole('owner');
        include 'views/dashboard/owner_dashboard.php';
        break;

    case 'admin_dashboard':
        requireRole('admin');
        include 'views/dashboard/admin_dashboard.php';
        break;

    case 'owner_students':
        requireRole('owner');
        include 'views/dashboard/students.php';
        break;

    case 'owner_rooms_single':
        requireRole('owner');
        include 'views/dashboard/rooms_single.php';
        break;

    case 'owner_rooms_double':
        requireRole('owner');
        include 'views/dashboard/rooms_double.php';
        break;

    case 'owner_fees':
        requireRole('owner');
        include 'views/dashboard/fees.php';
        break;

    case 'owner_complaints':
        requireRole('owner');
        include 'views/dashboard/complaints.php';
        break;

    case 'owner_notices':
        requireRole('owner');
        include 'views/dashboard/notices.php';
        break;

    case 'owner_staff':
        requireRole('owner');
        include 'views/dashboard/staff.php';
        break;

    case 'staff':
        include 'views/pages/staff.php';
        break;

    case 'facilities':
        include 'views/pages/facilities.php';
        break;

    default:
        echo "404 - Page Not Found";
        break;
}

function requireRole($role) {
    if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== $role) {
        header('Location: ' . BASE_URL . 'index.php?action=login');
        exit;
    }
}
?>
