<?php include(__DIR__ . '/../layout/header.php'); ?>

<main class="password-page">
    <div class="password-card">
        <div class="password-logo">
            <img src="<?= BASE_URL ?>public/images/logo.png" alt="Logo">
            <span>Pentatonic Hostel</span>
        </div>

        <h2>Set New Password</h2>
        <p class="password-subtitle">Create a new password for your account.</p>

        <?php if (isset($_GET['error'])): ?>
            <?php if ($_GET['error'] === 'weak_password'): ?>
                <p class="error-msg">Password must be at least 6 characters long.</p>
            <?php elseif ($_GET['error'] === 'mismatch'): ?>
                <p class="error-msg">Passwords do not match. Please try again.</p>
            <?php endif; ?>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>index.php?action=reset_password_submit" method="POST">
            <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">

            <div class="form-group">
                <label>New Password</label>
                <div class="password-wrapper">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="••••••••"
                        minlength="6"
                        required
                    >
                    <span onclick="togglePassword('password')" class="toggle-eye">
                        <i class="ph ph-eye"></i>
                    </span>
                </div>
            </div>

            <div class="form-group">
                <label>Re-type Password</label>
                <div class="password-wrapper">
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="••••••••"
                        minlength="6"
                        required
                    >
                    <span onclick="togglePassword('confirm_password')" class="toggle-eye">
                        <i class="ph ph-eye"></i>
                    </span>
                </div>
            </div>

            <button type="submit" class="signup-btn">Reset Password</button>

            <div class="back-link">
                <a href="<?= BASE_URL ?>index.php?action=login">← Back to Login</a>
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

<?php include(__DIR__ . '/../layout/footer.php'); ?>

