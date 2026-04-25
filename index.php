<?php
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

// Helper function to check role
function requireRole($role) {
    if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== $role) {
        header('Location: ' . BASE_URL . 'index.php?action=login');
        exit;
    }
}

switch ($action) {
    case 'home':
        include 'views/index.php';
        break;

    case 'register':
        include 'views/auth/register.php';
        break;

    case 'register_step1':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $photoPath = null;
            if (!empty($_FILES['profile_photo']['tmp_name'])) {
                $uploadDir = __DIR__ . '/public/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                
                $fileName = uniqid() . '_' . basename($_FILES['profile_photo']['name']);
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $targetPath)) {
                    $photoPath = 'public/uploads/' . $fileName;
                }
            }

            $_SESSION['reg_data'] = [
                'first_name'           => $_POST['first_name'] ?? '',
                'middle_name'          => $_POST['middle_name'] ?? '',
                'last_name'            => $_POST['last_name'] ?? '',
                'date_of_birth'        => $_POST['date_of_birth'] ?? '',
                'contact_number'       => $_POST['contact_number'] ?? '',
                'email'                => $_POST['email'] ?? '',
                'profile_photo'        => $photoPath,
                'college_name'         => $_POST['college_name'] ?? '',
                'permanent_address'    => $_POST['permanent_address'] ?? '',
                'date_of_joining'      => $_POST['date_of_joining'] ?? '',
                'guardian_name'        => $_POST['guardian_name'] ?? '',
                'guardian_relationship'=> $_POST['guardian_relationship'] ?? '',
                'guardian_contact'     => $_POST['guardian_contact'] ?? '',
                'preferred_room_type'  => $_POST['preferred_room_type'] ?? 'double'
            ];

            header('Location: index.php?action=set_password');
            exit;
        }
        break;

    case 'set_password':
        include 'views/auth/set_password.php';
        break;

    case 'register_final':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if ($password !== $confirm || strlen($password) < 6) {
                header('Location: index.php?action=set_password&error=password');
                exit;
            }

            $_SESSION['reg_data']['password'] = password_hash($password, PASSWORD_DEFAULT);

            try {
                $studentController->register($_SESSION['reg_data']);
                unset($_SESSION['reg_data']);
                header('Location: index.php?action=login&registered=success');
                exit;
            } catch (Exception $e) {
                header('Location: index.php?action=register&error=' . urlencode($e->getMessage()));
                exit;
            }
        }
        break;

    // ========== LOGIN & DASHBOARDS ==========
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
?>