<?php
/**
 * Legacy warden entry point.
 * Redirects all requests to the main router at /index.php
 * which handles authentication and role-based routing.
 */
if (!defined('BASE_URL')) {
    define('BASE_URL', '/HostelManagementSystem-main/');
}

// Redirect to main router
header('Location: ' . BASE_URL . 'index.php');
exit;
?>