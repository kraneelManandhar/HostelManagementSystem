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
    <title>Create Password - Pentatonic Hostel</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css?v=3">
    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
</head>
<body>
<main class="password-page">
    <div class="password-card">
        <a class="login-back-btn" href="<?= BASE_URL ?>index.php?action=home" aria-label="Back to home">
            <i class="ph ph-arrow-left" aria-hidden="true"></i>
        </a>

        <div class="password-logo">
            <img src="<?= BASE_URL ?>public/images/logo.png" alt="Logo">
            <span>Pentatonic Hostel</span>
        </div>

        <h2>Create Your Password</h2>

        <form action="<?= BASE_URL ?>index.php?action=register_final" method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" value="<?= htmlspecialchars($_SESSION['reg_data']['email'] ?? '') ?>" readonly class="readonly-input">
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" required placeholder="Password" minlength="6" pattern="(?=.*[A-Za-z])(?=.*\d).{6,}" title="Use at least 6 characters with letters and numbers">
                    <span onclick="togglePassword('password')" class="toggle-eye">
                        <i class="ph ph-eye"></i>
                    </span>
                </div>
            </div>

            <div class="form-group">
                <label>Re-type Password</label>
                <div class="password-wrapper">
                    <input type="password" name="confirm_password" id="confirm_password" required placeholder="Confirm password" minlength="6" pattern="(?=.*[A-Za-z])(?=.*\d).{6,}" title="Use at least 6 characters with letters and numbers">
                    <span onclick="togglePassword('confirm_password')" class="toggle-eye">
                        <i class="ph ph-eye"></i>
                    </span>
                </div>
            </div>

            <?php if (isset($_GET['error']) && $_GET['error'] === 'password'): ?>
                <p class="error-msg">Passwords must match and contain both letters and numbers.</p>
            <?php endif; ?>

            <button type="submit" class="signup-btn">Sign up</button>

            <div class="back-link">
                <a href="<?= BASE_URL ?>index.php?action=register">Back to registration</a>
            </div>
        </form>
    </div>
</main>

<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
