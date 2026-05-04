<?php include(__DIR__ . '/../layout/header.php'); ?>

<main class="password-page">
    
    <div class="password-card">
        
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
                    <input type="password" name="password" id="password" required placeholder="••••••••" minlength="6">
                    <span onclick="togglePassword('password')" class="toggle-eye">
                        <i class="ph ph-eye"></i>
                    </span>
                </div>
            </div>

            <div class="form-group">
                <label>Re-type Password</label>
                <div class="password-wrapper">
                    <input type="password" name="confirm_password" id="confirm_password" required placeholder="••••••••" minlength="6">
                    <span onclick="togglePassword('confirm_password')" class="toggle-eye">
                        <i class="ph ph-eye"></i>
                    </span>
                </div>
            </div>

            <?php if (isset($_GET['error']) && $_GET['error'] === 'password'): ?>
                <p class="error-msg">Passwords must match and be at least 6 characters.</p>
            <?php endif; ?>

            <button type="submit" class="signup-btn">Sign up</button>

            <div class="back-link">
                <a href="<?= BASE_URL ?>index.php?action=register">← Back</a>
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
