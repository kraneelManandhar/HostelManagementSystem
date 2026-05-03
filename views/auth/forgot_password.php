<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Forgot Password – Hostel Management System</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css" />
  <style>
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #f0f2f5;
      font-family: Arial, sans-serif;
    }
    .card {
      background: #fff;
      padding: 40px 36px;
      border-radius: 10px;
      box-shadow: 0 4px 16px rgba(0,0,0,.12);
      width: 100%;
      max-width: 420px;
    }
    .card h2 { margin: 0 0 8px; color: #2c3e50; font-size: 22px; }
    .card p.sub  { color: #666; font-size: 14px; margin: 0 0 24px; }
    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #444; }
    .form-group input {
      width: 100%; padding: 10px 12px; border: 1px solid #ccc;
      border-radius: 6px; font-size: 14px; box-sizing: border-box;
    }
    .form-group input:focus { border-color: #2c3e50; outline: none; }
    .btn-primary {
      width: 100%; padding: 11px; background: #2c3e50; color: #fff;
      border: none; border-radius: 6px; font-size: 15px; cursor: pointer;
    }
    .btn-primary:hover { background: #1a252f; }
    .alert { padding: 10px 14px; border-radius: 6px; font-size: 13px; margin-bottom: 18px; }
    .alert-success { background: #d4edda; color: #155724; }
    .alert-danger  { background: #f8d7da; color: #721c24; }
    .back-link { text-align: center; margin-top: 18px; font-size: 13px; }
    .back-link a { color: #2c3e50; text-decoration: none; }
    .back-link a:hover { text-decoration: underline; }
  </style>
</head>
<body>

<div class="card">
  <h2>🔑 Forgot Password?</h2>
  <p class="sub">Enter your registered email address and we'll send you a password reset link.</p>

  <?php if (isset($_GET['status']) && $_GET['status'] === 'sent'): ?>
    <div class="alert alert-success">
      ✅ If that email exists in our system, a reset link has been sent. Please check your inbox (and spam folder).
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['error'])): ?>
    <?php if ($_GET['error'] === 'invalid_email'): ?>
      <div class="alert alert-danger">⚠️ Please enter a valid email address.</div>
    <?php elseif ($_GET['error'] === 'invalid_token'): ?>
      <div class="alert alert-danger">⚠️ This reset link is invalid or has expired. Please request a new one.</div>
    <?php endif; ?>
  <?php endif; ?>

  <?php if (!isset($_GET['status'])): ?>
  <form action="<?= BASE_URL ?>index.php?action=forgot_password_submit" method="POST">
    <div class="form-group">
      <label for="email">Email Address</label>
      <input type="email" id="email" name="email" placeholder="you@example.com"
             value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required />
    </div>
    <button type="submit" class="btn-primary">Send Reset Link</button>
  </form>
  <?php endif; ?>

  <div class="back-link">
    <a href="<?= BASE_URL ?>index.php?action=login">← Back to Login</a>
  </div>
</div>

</body>
</html>