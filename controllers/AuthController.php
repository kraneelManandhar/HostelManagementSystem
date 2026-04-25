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

    /**
     * Authenticate user - checks both students and users tables
     */
    public function login($email, $password) {
        // 1. Try to find in users table (admin/warden)
        $user = $this->userModel->findByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            // Admin or Warden found
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

        // 2. Try to find in students table
        $student = $this->studentModel->findByEmail($email);
        
        if ($student && password_verify($password, $student['password'])) {
            // Student found
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

        // 3. No match found
        return [
            'success' => false,
            'error' => 'Invalid email or password'
        ];
    }

    /**
     * Logout
     */
    public function logout() {
        session_destroy();
        return [
            'success' => true,
            'redirect' => 'index.php?action=home'
        ];
    }
}
?>