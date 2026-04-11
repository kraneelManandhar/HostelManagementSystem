<?php

require_once __DIR__ . '/controllers/PageController.php';

$controller = new PageController();

$page = $_GET['page'] ?? 'home';

if (method_exists($controller, $page)) {
    $controller->$page();
} else {
    echo "404 - Page Not Found";
}