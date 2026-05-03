<?php
// Ensure this points to the file where your DB class is defined
require_once __DIR__ . '/../config/db.php'; 

class PasswordReset {
    private $db;

    public function __construct() {
        // Use your existing static connection
        $this->db = DB::connect();
    }

    public function createToken($email) {
        // Clear old tokens for this email
        $stmt = $this->db->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->execute([$email]);

        // Generate 64-character token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $this->db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $expires]);

        return $token;
    }

    public function validateToken($token) {
        $stmt = $this->db->prepare("
            SELECT email FROM password_resets 
            WHERE token = ? AND used = 0 AND expires_at > NOW()
            LIMIT 1
        ");
        $stmt->execute([$token]);
        $row = $stmt->fetch();

        return $row ? $row['email'] : false;
    }

    public function markUsed($token) {
        $stmt = $this->db->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
        $stmt->execute([$token]);
    }
}