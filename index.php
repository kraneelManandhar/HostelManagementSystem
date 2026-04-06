<?php
/**
 * index.php
 * The Front Controller (Router) for Pentatonic Hostel Management System.
 */

// 1. Load Database Configuration
require_once 'config/db.php';

// 2. Load Controllers
// Make sure these filenames match your folder structure exactly
require_once 'controllers/StudentController.php';

// 3. Initialize Controllers
// We pass the $pdo connection from db.php into the controllers
$studentController = new StudentController($pdo);

// 4. Capture the 'action' from the URL (defaults to 'home' if empty)
$action = $_GET['action'] ?? 'home';

// 5. Routing Logic
switch ($action) {
    
    // Case: Viewing the Landing Page (the code you shared earlier)
    case 'home':
        include 'views/index.php';
        break;

    // Case: Viewing the Registration Form
    case 'register':
        include 'views/auth/register.php';
        break;

    // Case: Submitting the Registration Form (POST request)
    case 'register_submit':
        $studentController->register();
        break;

    // Case: Staff Page
    case 'staff':
        include 'views/staff.php';
        break;

    // Case: Facilities Page
    case 'facilities':
        include 'views/facilities.php';
        break;

    case 'register_submit':
    // The $studentController was initialized at the top of index.php
    $studentController->register(); 
    break;

    // Default: 404 Not Found or redirect to home
    default:
        echo "404 - Page Not Found";
        // Or: include 'views/index.php';
        break;
}