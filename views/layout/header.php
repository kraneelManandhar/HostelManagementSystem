<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BASE_URL')) {
    define('BASE_URL', '/HOSTELMANAGEMENTSYSTEM/');
}
// Fix active page
$currentPage = $_GET['page'] ?? 'about';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pentatonic Hostel</title>

    <link rel="stylesheet" href="/HOSTELMANAGEMENTSYSTEM/public/css/style.css">


    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
</head>

<body>

<header class="navbar">
    <div class="nav-container">

        <!-- Logo -->
        <div class="logo">
            <a href="/HOSTELMANAGEMENTSYSTEM/index.php">
                <img src="/HOSTELMANAGEMENTSYSTEM/public/images/logo.png" alt="Logo">
                <span>Pentatonic Hostel</span>
            </a>
        </div>

        <!-- Navigation -->
        <nav class="nav-links">
            <a href="/HOSTELMANAGEMENTSYSTEM/index.php?action=home"
               class="<?= ($currentPage == 'about') ? 'active' : '' ?>">
               About Us
            </a>

            <a href="/HOSTELMANAGEMENTSYSTEM/index.php?action=staff"
               class="<?= ($currentPage == 'staff') ? 'active' : '' ?>">
               Staffs
            </a>

            <a href="/HOSTELMANAGEMENTSYSTEM/index.php?action=facilities"
               class="<?= ($currentPage == 'facilities') ? 'active' : '' ?>">
               Facilities
            </a>
        </nav>

        <!-- Login -->
        <div class="nav-actions">
            <a href="/HOSTELMANAGEMENTSYSTEM/views/auth/login.php" class="login-btn">
                Login
            </a>
        </div>

    </div>
</header>