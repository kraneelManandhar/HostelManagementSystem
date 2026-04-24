<?php
/**
 * index.php
 * The Front Controller (Router) for Pentatonic Hostel Management System.
 */

// 1. Load Database
require_once __DIR__ . '/config/db.php';

// 2. Load Controllers
require_once __DIR__ . '/controllers/studentController/StudentController.php';
require_once __DIR__ . '/controllers/studentController/PageController.php';

// 3. Initialize Controllers
$studentController = new StudentController($pdo);

// 4. Capture 'action' (defaults to 'home')
$action = $_GET['action'] ?? 'home';

// 5. Routing Logic
switch ($action) {
    
    case 'home':
        include './views/index.php';
        break;

    case 'register':
        include './views/auth/register.php';
        break;

    case 'register_submit':
        // Only need this once!
        $studentController->register();
        break;

    case 'staff':
        include './views/staff.php';
        break;

    case 'facilities':
        include './views/facilities.php';
        break;

    // Default: 404 Not Found
    default:
        http_response_code(404);
        echo "404 - Page Not Found";
        break;
}