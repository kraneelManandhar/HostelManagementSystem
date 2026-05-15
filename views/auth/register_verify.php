<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/HostelManagementSystem/');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Pentatonic Hostel</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css?v=3">
    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
</head>
<body>
<main class="password-page">
    <div class="password-card">
        <a class="login-back-btn" href="<?= BASE_URL ?>index.php?action=register" aria-label="Back to registration">
            <i class="ph ph-arrow-left" aria-hidden="true"></i>
        </a>

        <div class="password-logo">
            <img src="<?= BASE_URL ?>public/images/logo.png" alt="Logo">
            <span>Pentatonic Hostel</span>
        </div>

        <h2>Email Verification</h2>
        <p class="password-subtitle">A verification email has been sent to your registered email address.</p>
        <p class="success-msg">Please check your inbox and click the link to continue setting your password. If you do not see it, check your spam folder.</p>

        <div class="back-link">
            <a href="<?= BASE_URL ?>index.php?action=register">Back to registration</a>
        </div>
    </div>
</main>
</body>
</html>
