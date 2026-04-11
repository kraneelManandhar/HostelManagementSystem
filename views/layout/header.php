<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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

    <link rel="stylesheet" href="/HostelManagementSystem/public/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
</head>

<body>

<header class="navbar">
    <div class="nav-container">

        <!-- Logo -->
        <div class="logo">
            <a href="/HostelManagementSystem/index.php">
                <img src="/HostelManagementSystem/public/images/logo.png" alt="Logo">
                <span>Pentatonic Hostel</span>
            </a>
        </div>

        <!-- Navigation -->
        <nav class="nav-links">
            <a href="/HostelManagementSystem/index.php?page=about"
               class="<?= ($currentPage == 'about') ? 'active' : '' ?>">
               About Us
            </a>

            <a href="/HostelManagementSystem/index.php?page=staff"
               class="<?= ($currentPage == 'staff') ? 'active' : '' ?>">
               Staffs
            </a>

            <a href="/HostelManagementSystem/index.php?page=facilities"
               class="<?= ($currentPage == 'facilities') ? 'active' : '' ?>">
               Facilities
            </a>
        </nav>

        <!-- Login -->
        <div class="nav-actions">
            <a href="/HostelManagementSystem/views/auth/login.php" class="login-btn">
                Login
            </a>
        </div>

    </div>
</header>