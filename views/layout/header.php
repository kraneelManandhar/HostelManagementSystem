<?php
// Start session if needed
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current page for active nav highlight
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pentatonic Hostel</title>

    <!-- CSS -->
    <link rel="stylesheet" href="./public/css/style.css">

    <!-- Icons -->
    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
</head>

<body>

<header class="navbar">
    <div class="nav-container">

        <!-- Logo -->
        <div class="logo">
            <img src="./public/images/logo.png" alt="Logo">
            <span>Pentatonic hostel</span>
        </div>

        <!-- Navigation -->
        <nav class="nav-links">
            <a href="./index.php"
               class="<?= ($currentPage == 'index.php') ? 'active' : '' ?>">About us</a>

            <a href="./public/staff.php"
               class="<?= ($currentPage == 'staff.php') ? 'active' : '' ?>">Staffs</a>

            <a href="./public/facilities.php"
               class="<?= ($currentPage == 'facilities.php') ? 'active' : '' ?>">Facilities</a>
        </nav>

        <!-- Login Button -->
        <div class="nav-actions">
            <a href="./views/auth/login.php" class="login-btn">Login</a>
        </div>

    </div>
</header>