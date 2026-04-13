<?php

session_start();
require_once __DIR__ . '/config/db.php';

require_once __DIR__ . '/controllers/PageController.php';
require_once __DIR__ . '/controllers/StudentController.php';
require_once __DIR__ . '/controllers/ComplaintController.php';

$page = $_GET['page'] ?? 'home';

$pageController = new PageController();
$studentController = new StudentController($pdo);
$complaintController = new ComplaintController($pdo);

switch ($page) {

    // PUBLIC PAGES
    case 'home':
    case 'about':
    case 'staff':
    case 'facilities':
        $pageController->$page();
        break;

    // STUDENT
    case 'student_dashboard':
        $studentController->dashboard();
        break;

    // COMPLAINTS
    case 'complaint_add':
        $complaintController->store();
        break;

    case 'complaint_delete':
        $complaintController->delete();
        break;

    default:
        echo "404 Page Not Found";
}