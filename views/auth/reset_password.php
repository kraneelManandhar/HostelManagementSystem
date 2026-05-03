<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reset Password – Hostel Management System</title>
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
    .card p.sub { color: #666; font-size: 14px; margin: 0 0 24px; }
    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #444; }
    .form-group input {
      width: 100%; padding: 10px 12px; border: 1px solid #ccc;
      border-radius: 6px; font-size: 14px; box-sizing: border-box;
    }
    .form-group input:focus { border-color: #2c3e50; outline: none; }
    .password-wrapper { position: relative; }
    .password-wrapper input { padding-right: 40px; }
    .toggle-eye {
      position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
      cursor: pointer; font-size: 16px; color: #888; user-select: none;
    }
    .requirements { font-size: 12px; color: #888; margin-top: 6px; }
    .btn-primary {
      width: 100%; padding: 11px; background: #2c3e50; color: #fff;
      border: none; border-radius: 6px; font-size: 15px; cursor: pointer;
    }
    .btn-primary:hover { background: #1a252f; }
    .alert { padding: 10px 14px; border-radius: 6px; font-size: 13px; margin-bottom: 18px; }
    .alert-danger { background: #f8d7da; color: #721c24; }
    .back-link { text-align: center; margin-top: 18px; font-size: 13px; }
    .back-link a { color: #2c3e50; text-decoration: none; }
    .back-link a:hover { text-decoration: underline; }
  </style>
</head>
<body>

<div class="card">
  <h2>🔐 Set New Password</h2>
  <p class="sub">Enter a new password for your account. Make it strong!</p>

  <?php if (isset($_GET['error'])): ?>
    <?php if ($_GET['error'] === 'weak_password'): ?>
      <div class="alert alert-danger">⚠️ Password must be at least 6 characters long.</div>
    <?php elseif ($_GET['error'] === 'mismatch'): ?>
      <div class="alert alert-danger">⚠️ Passwords do not match. Please try again.</div>
    <?php endif; ?>
  <?php endif; ?>

  <form action="<?= BASE_URL ?>index.php?action=reset_password_submit" method="POST">
    <!-- Hidden token -->
    <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>" />

    <div class="form-group">
      <label for="password">New Password</label>
      <div class="password-wrapper">
        <input type="password" id="password" name="password" placeholder="Minimum 6 characters" required />
        <span class="toggle-eye" onclick="toggleVisibility('password', this)">👁</span>
      </div>
      <p class="requirements">At least 6 characters.</p>
    </div>

    <div class="form-group">
      <label for="confirm_password">Confirm Password</label>
      <div class="password-wrapper">
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter new password" required />
        <span class="toggle-eye" onclick="toggleVisibility('confirm_password', this)">👁</span>
      </div>
    </div>

    <button type="submit" class="btn-primary">Reset Password</button>
  </form>

  <div class="back-link">
    <a href="<?= BASE_URL ?>index.php?action=login">← Back to Login</a>
  </div>
</div>

<script>
function toggleVisibility(fieldId, eyeEl) {
  const input = document.getElementById(fieldId);
  if (input.type === 'password') {
    input.type = 'text';
    eyeEl.textContent = '🙈';
  } else {
    input.type = 'password';
    eyeEl.textContent = '👁';
  }
}
</script>

</body>
</html>