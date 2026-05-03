<?php

require_once __DIR__ . '/../config/mailer.php';
require_once __DIR__ . '/../config/db.php';

class PasswordResetController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->ensurePasswordResetTable();
    }

    public function showForgotForm(): void
    {
        include __DIR__ . '/../views/auth/forgot_password.php';
    }

    public function handleForgotPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'index.php?action=forgot_password');
            exit;
        }

        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: ' . BASE_URL . 'index.php?action=forgot_password&error=invalid_email');
            exit;
        }

        $account = $this->findAccountByEmail($email);

        // Always show success to prevent email enumeration.
        if (!$account) {
            header('Location: ' . BASE_URL . 'index.php?action=forgot_password&status=sent');
            exit;
        }

        $this->pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);

        $token = bin2hex(random_bytes(32));

        $stmt = $this->pdo->prepare(
            "INSERT INTO password_resets (email, token, expires_at)
             VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))"
        );
        $stmt->execute([$email, $token]);

        $resetUrl = $this->buildResetUrl($token);

        try {
            $mail = getMailer();
            $mail->addAddress($email);
            $mail->Subject = 'Password Reset Request - Hostel Management System';
            $mail->Body = $this->buildEmailBody($resetUrl);
            $mail->AltBody = "Reset your password using this link (valid for 1 hour):\n\n$resetUrl\n\nIf you did not request this, ignore this email.";
            $mail->send();
        } catch (\Exception $e) {
            error_log('PHPMailer error: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . 'index.php?action=forgot_password&status=sent');
        exit;
    }

    public function showResetForm(): void
    {
        $token = trim($_GET['token'] ?? '');

        if (empty($token) || !$this->isTokenValid($token)) {
            header('Location: ' . BASE_URL . 'index.php?action=forgot_password&error=invalid_token');
            exit;
        }

        include __DIR__ . '/../views/auth/reset_password.php';
    }

    public function handleResetPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'index.php?action=forgot_password');
            exit;
        }

        $token = trim($_POST['token'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $reset = $this->getValidToken($token);
        if (!$reset) {
            header('Location: ' . BASE_URL . 'index.php?action=forgot_password&error=invalid_token');
            exit;
        }

        if (empty($password) || strlen($password) < 6) {
            header('Location: ' . BASE_URL . 'index.php?action=reset_password&token=' . urlencode($token) . '&error=weak_password');
            exit;
        }

        if ($password !== $confirmPassword) {
            header('Location: ' . BASE_URL . 'index.php?action=reset_password&token=' . urlencode($token) . '&error=mismatch');
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        if (!$this->updateAccountPassword($reset['email'], $hashedPassword)) {
            header('Location: ' . BASE_URL . 'index.php?action=forgot_password&error=invalid_token');
            exit;
        }

        $this->pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = ?")->execute([$token]);

        header('Location: ' . BASE_URL . 'index.php?action=login&reset=success');
        exit;
    }

    private function ensurePasswordResetTable(): void
    {
        $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS password_resets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL,
                token VARCHAR(64) NOT NULL UNIQUE,
                expires_at DATETIME NOT NULL,
                used TINYINT(1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_password_resets_email (email),
                INDEX idx_password_resets_token (token)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }

    private function findAccountByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare("SELECT id, email FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($account) {
            return $account;
        }

        $stmt = $this->pdo->prepare("SELECT id, email FROM students WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        return $account ?: null;
    }

    private function updateAccountPassword(string $email, string $hashedPassword): bool
    {
        $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashedPassword, $email]);

        if ($stmt->rowCount() > 0) {
            return true;
        }

        $stmt = $this->pdo->prepare("UPDATE students SET password = ? WHERE email = ?");
        $stmt->execute([$hashedPassword, $email]);

        return $stmt->rowCount() > 0;
    }

    private function buildResetUrl(string $token): string
    {
        $path = rtrim(BASE_URL, '/') . '/index.php?action=reset_password&token=' . urlencode($token);

        if (empty($_SERVER['HTTP_HOST'])) {
            return $path;
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return $scheme . '://' . $_SERVER['HTTP_HOST'] . $path;
    }

    private function isTokenValid(string $token): bool
    {
        return $this->getValidToken($token) !== null;
    }

    private function getValidToken(string $token): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM password_resets
             WHERE token = ?
               AND used = 0
               AND expires_at > NOW()
             LIMIT 1"
        );
        $stmt->execute([$token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    private function buildEmailBody(string $resetUrl): string
    {
        $safeResetUrl = htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
    .container { max-width: 520px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.12); }
    .header { background: #2c3e50; color: #fff; padding: 28px 32px; }
    .header h2 { margin: 0; font-size: 20px; }
    .body { padding: 32px; color: #333; line-height: 1.6; }
    .btn { display: inline-block; margin: 24px 0 16px; padding: 12px 28px; background: #2c3e50; color: #fff !important; text-decoration: none; border-radius: 5px; font-size: 15px; }
    .footer { padding: 16px 32px; font-size: 12px; color: #888; background: #f9f9f9; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header"><h2>Password Reset Request</h2></div>
    <div class="body">
      <p>Hello,</p>
      <p>We received a request to reset the password for your Hostel Management System account. Click the button below to set a new password. This link is valid for <strong>1 hour</strong>.</p>
      <a href="{$safeResetUrl}" class="btn">Reset My Password</a>
      <p>If the button does not work, copy and paste this link into your browser:</p>
      <p style="word-break:break-all;font-size:13px;color:#555;">{$safeResetUrl}</p>
      <p>If you did not request a password reset, you can safely ignore this email. Your password will not change.</p>
    </div>
    <div class="footer">This email was sent by Hostel Management System. Do not reply to this email.</div>
  </div>
</body>
</html>
HTML;
    }
}
