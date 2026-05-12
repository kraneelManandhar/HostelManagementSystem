<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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

<main class="login-page" style="background: radial-gradient(circle at center, #ffffff 0%, #a5c1e5 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center;">
    <div class="login-card">
        <button type="button" class="login-back-btn" onclick="goBack()" aria-label="Go back">
            <i class="ph ph-arrow-left" aria-hidden="true"></i>
            <span>Back</span>
        </button>
        
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

        <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css?v=2">

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
                    <button type="button" onclick="togglePassword('password', this)" class="toggle-eye" aria-label="Show password">
                        <i class="ph ph-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="login-submit-btn">Login</button>

            <div class="login-links">
                <p><a href="<?= BASE_URL ?>index.php?action=forgot_password">Forgot password?</a></p>
                <p>Don't have an account? <a href="<?= BASE_URL ?>index.php?action=register">Register here</a></p>
            </div>

        </form>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
<script>
function goBack() {
    window.location.href = "<?= BASE_URL ?>index.php?action=home";
}

function togglePassword(id, button) {
    const input = document.getElementById(id);
    const icon = button.querySelector('i');
    const isHidden = input.type === 'password';

    input.type = isHidden ? 'text' : 'password';
    button.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
    icon.className = isHidden ? 'ph ph-eye-slash' : 'ph ph-eye';
}
</script>
