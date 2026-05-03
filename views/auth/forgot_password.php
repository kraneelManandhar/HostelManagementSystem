<?php include(__DIR__ . '/../layout/header.php'); ?>

<main class="password-page">
    <div class="password-card">
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
                    <label>Email</label>
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
            <a href="<?= BASE_URL ?>index.php?action=login">← Back to Login</a>
        </div>
    </div>
</main>

<?php include(__DIR__ . '/../layout/footer.php'); ?>
