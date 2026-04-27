<?php
// Define BASE_URL first
if (!defined('BASE_URL')) {
    define('BASE_URL', '/HOSTELMANAGEMENTSYSTEM/');
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Student.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/controllers/StudentController.php';
require_once __DIR__ . '/controllers/AuthController.php';

$studentController = new StudentController();
$action = $_GET['action'] ?? 'home';

// ========== AUTO-REDIRECT LOGGED-IN USERS ==========
// If user is already logged in and trying to access home/login/register pages
$publicPages = ['home', 'login', 'register', 'register_step1', 'set_password', 'register_final'];

if (isset($_SESSION['logged_in']) && in_array($action, $publicPages)) {
    // Redirect to appropriate dashboard based on role
    switch ($_SESSION['user_role']) {
        case 'admin':
            header('Location: ' . BASE_URL . 'index.php?action=admin_dashboard');
            exit;
        case 'warden':
            header('Location: ' . BASE_URL . 'index.php?action=warden_dashboard');
            exit;
        case 'student':
        default:
            header('Location: ' . BASE_URL . 'index.php?action=student_dashboard');
            exit;
    }
}

// ========== ROUTER ==========
switch ($action) {
    case 'home':
        include 'views/index.php';
        break;

    case 'register':
        include 'views/auth/register.php';
        break;

    case 'register_step1':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // ... existing code ...
        }
        break;

    case 'set_password':
        include 'views/auth/set_password.php';
        break;

    case 'register_final':
        // ... existing code ...
        break;

    case 'login':
        include 'views/auth/login.php';
        break;

    case 'logout':
        $auth = new AuthController();
        $auth->logout();
        header('Location: ' . BASE_URL . 'index.php?action=home');
        exit;

    case 'student_dashboard':
        requireRole('student');
        include 'views/dashboard/student_dashboard.php';
        break;

    case 'warden_dashboard':
        requireRole('warden');
        include 'views/dashboard/warden_dashboard.php';
        break;

    case 'admin_dashboard':
        requireRole('admin');
        include 'views/dashboard/admin_dashboard.php';
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

// Helper function
function requireRole($role) {
    if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== $role) {
        header('Location: ' . BASE_URL . 'index.php?action=login');
        exit;
    }
}
?>