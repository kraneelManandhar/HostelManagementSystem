<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

if (!defined('BASE_URL')) {
    define('BASE_URL', '/HostelManagementSystem/');
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password";
    } else {
        $auth = new AuthController();
        $result = $auth->login($email, $password);
        
        if ($result['success']) {
            header("Location: " . BASE_URL . $result['redirect']);
            exit;
        } else {
            $error = $result['error'];
        }
    }
}
?>

<?php include(__DIR__ . '/../layout/header.php'); ?>

<main class="login-page">
    <div class="login-card">
        
        <div class="login-logo">
            <img src="<?= BASE_URL ?>public/images/logo.png" alt="Logo">
            <span>Pentatonic Hostel</span>
        </div>

        <h2>Welcome Back</h2>

        <?php if (!empty($error)): ?>
            <p class="login-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if (isset($_GET['registered']) && $_GET['registered'] === 'success'): ?>
            <p class="login-success">Registration successful! Please login.</p>
        <?php endif; ?>

        <!-- FIXED: Added hidden action field -->
        <form method="POST" action="<?= BASE_URL ?>index.php?action=login">
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" placeholder="••••••••" required>
                    <span onclick="togglePassword('password')" class="toggle-eye">
                        <i class="ph ph-eye"></i>
                    </span>
                </div>
            </div>

            <button type="submit" class="login-submit-btn">Login</button>

            <div class="login-links">
                <p>Don't have an account? <a href="<?= BASE_URL ?>index.php?action=register">Register here</a></p>
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