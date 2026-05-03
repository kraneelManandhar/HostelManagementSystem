<?php
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $studentModel;
    private $userModel;

    public function __construct(){
        $this->studentModel = new Student();
        $this->userModel = new User();
    }

    public function login($email, $password) {
        // Hardcoded warden login
        if ($email === 'warden@pentatonic.com' && $password === 'Warden123') {
            $_SESSION['user_id'] = 'warden_001';
            $_SESSION['user_email'] = 'warden@pentatonic.com';
            $_SESSION['user_name'] = 'Hostel Warden';
            $_SESSION['user_role'] = 'warden';
            $_SESSION['logged_in'] = true;

            return [
                'success' => true,
                'role' => 'warden',
                'redirect' => 'index.php?action=warden_dashboard'
            ];
        }

        // Hardcoded owner login
        if ($email === 'owner@pentatonic.com' && $password === 'Owner123') {
            $_SESSION['user_id'] = 'owner_001';
            $_SESSION['user_email'] = 'owner@pentatonic.com';
            $_SESSION['user_name'] = 'Hostel Owner';
            $_SESSION['user_role'] = 'owner';
            $_SESSION['logged_in'] = true;

            return [
                'success' => true,
                'role' => 'owner',
                'redirect' => 'index.php?action=ownerDashboard'
            ];
        }

        // Database users: admin/warden from users table
        $user = $this->userModel->findByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            
            return [
                'success' => true,
                'role' => $user['role'],
                'redirect' => 'index.php?action=' . $user['role'] . '_dashboard'
            ];
        }

        // Database students
        $student = $this->studentModel->findByEmail($email);
        
        if ($student && password_verify($password, $student['password'])) {
            $_SESSION['user_id'] = $student['id'];
            $_SESSION['user_email'] = $student['email'];
            $_SESSION['user_name'] = $student['first_name'] . ' ' . $student['last_name'];
            $_SESSION['user_role'] = 'student';
            $_SESSION['logged_in'] = true;
            
            return [
                'success' => true,
                'role' => 'student',
                'redirect' => 'index.php?action=student_dashboard'
            ];
        }

        return [
            'success' => false,
            'error' => 'Invalid email or password'
        ];
    }

    public function logout() {
        $_SESSION = [];

        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
        }

        session_destroy();

        return [
            'success' => true,
            'redirect' => 'index.php?action=home'
        ];
    }
}
?>
