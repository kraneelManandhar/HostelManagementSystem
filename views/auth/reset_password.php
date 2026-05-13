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
    <title>Reset Password - Pentatonic Hostel</title>
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

        <h2>Set New Password</h2>
        <p class="password-subtitle">Create a new password for your account.</p>

        <?php if (isset($_GET['error'])): ?>
            <?php if ($_GET['error'] === 'weak_password'): ?>
                <p class="error-msg">Password must contain both letters and numbers.</p>
            <?php elseif ($_GET['error'] === 'mismatch'): ?>
                <p class="error-msg">Passwords do not match. Please try again.</p>
            <?php endif; ?>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>index.php?action=reset_password_submit" method="POST">
            <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">

            <div class="form-group">
                <label for="password">New Password</label>
                <div class="password-wrapper">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="New password"
                        minlength="6"
                        pattern="(?=.*[A-Za-z])(?=.*\d).{6,}"
                        title="Use at least 6 characters with letters and numbers"
                        required
                    >
                    <button type="button" onclick="togglePassword('password', this)" class="toggle-eye" aria-label="Show password">
                        <i class="ph ph-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="password-wrapper">
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Confirm password"
                        minlength="6"
                        pattern="(?=.*[A-Za-z])(?=.*\d).{6,}"
                        title="Use at least 6 characters with letters and numbers"
                        required
                    >
                    <button type="button" onclick="togglePassword('confirm_password', this)" class="toggle-eye" aria-label="Show password">
                        <i class="ph ph-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="signup-btn">Reset Password</button>

            <div class="back-link">
                <a href="<?= BASE_URL ?>index.php?action=login">Back to Login</a>
            </div>
        </form>
    </div>
</main>

<script>
function togglePassword(id, button) {
    const input = document.getElementById(id);
    const icon = button.querySelector('i');
    const isHidden = input.type === 'password';

    input.type = isHidden ? 'text' : 'password';
    button.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
    icon.className = isHidden ? 'ph ph-eye-slash' : 'ph ph-eye';
}
</script>
</body>
</html>
