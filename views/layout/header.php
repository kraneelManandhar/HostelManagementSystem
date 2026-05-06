<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BASE_URL')) {
    define('BASE_URL', '/HostelManagementSystem/');
}
// Fix active page detection - use 'action' parameter (main router uses this)
$currentAction = $_GET['action'] ?? 'home';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pentatonic Hostel</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css?v=2">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/chatbot.css?v=2">

    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
</head>

<body>

<header class="navbar">
    <div class="nav-container">

        <!-- Logo -->
        <div class="logo">
            <a href="<?= BASE_URL ?>index.php">
                <img src="<?= BASE_URL ?>public/images/logo.png" alt="Logo">
                <span>Pentatonic Hostel</span>
            </a>
        </div>

        <!-- Navigation -->
        <nav class="nav-links">
            <a href="<?= BASE_URL ?>index.php?action=home"
               class="<?= in_array($currentAction, ['home', 'about']) ? 'active' : '' ?>">
               About Us
            </a>

            <a href="<?= BASE_URL ?>index.php?action=staff"
               class="<?= ($currentAction == 'staff') ? 'active' : '' ?>">
               Staffs
            </a>

            <a href="<?= BASE_URL ?>index.php?action=facilities"
               class="<?= ($currentAction == 'facilities') ? 'active' : '' ?>">
               Facilities
            </a>
        </nav>

        <!-- Login -->
        <div class="nav-actions">
            <a href="<?= BASE_URL ?>index.php?action=login" class="login-btn">
                Login
            </a>
        </div>

    </div>
</header>

