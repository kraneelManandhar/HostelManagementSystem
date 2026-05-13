<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BASE_URL')) {
    define('BASE_URL', '/HostelManagementSystem/');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Pentatonic Hostel</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css?v=4">
    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
</head>
<body>
<main class="password-page">
    <div class="password-card">
        <a class="login-back-btn" href="<?= BASE_URL ?>index.php?action=login" aria-label="Back to login">
            <i class="ph ph-arrow-left" aria-hidden="true"></i>
        </a>

        <div class="password-logo">
            <img src="<?= BASE_URL ?>public/images/logo.png" alt="Logo">
            <span>Pentatonic Hostel</span>
        </div>

        <h2>Forgot Password?</h2>
        <p class="password-subtitle">Enter your registered email and we will send you a reset link.</p>

        <?php if (isset($_GET['status']) && $_GET['status'] === 'sent'): ?>
            <p class="success-msg">
                If that email exists in our system, a reset link has been sent. Please check your inbox and spam folder.
            </p>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <?php if ($_GET['error'] === 'invalid_email'): ?>
                <p class="error-msg">Please enter a valid email address.</p>
            <?php elseif ($_GET['error'] === 'invalid_token'): ?>
                <p class="error-msg">This reset link is invalid or has expired. Please request a new one.</p>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!isset($_GET['status'])): ?>
            <form action="<?= BASE_URL ?>index.php?action=forgot_password_submit" method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        required
                    >
                </div>

                <button type="submit" class="signup-btn">Send Reset Link</button>
            </form>
        <?php endif; ?>

        <div class="back-link">
            <a href="<?= BASE_URL ?>index.php?action=login">Back to Login</a>
        </div>
    </div>
</main>
</body>
</html>
