<?php
/**
 * index.php
 * The Front Controller (Router) for Pentatonic Hostel Management System.
 */

// 1. Load Database Configuration
require_once 'config/db.php';

// 2. Load Controllers
require_once 'controllers/StudentController.php';

// 3. Initialize Controllers
$studentController = new StudentController($pdo);

// 4. Capture the 'action' from the URL (default = home)
$action = $_GET['action'] ?? 'home';

// 5. Routing Logic
switch ($action) {

    // Homepage (Landing Page)
    case 'home':
        include 'views/index.php';
        break;

    // Show Register Form
    case 'register':
        include 'views/auth/register.php';
        break;

    // Handle Register Form Submission
    case 'register_submit':
        $studentController->register();
        break;

    // Staff Page
    case 'staff':
        include 'views/staff.php';
        break;

    // Facilities Page
    case 'facilities':
        include 'views/facilities.php';
        break;

    // Default: 404
    default:
        echo "404 - Page Not Found";
        break;
}