<?php
require_once __DIR__ . '/../config/db.php';

class User {
    private $conn;

    public function __construct($pdo = null){
        if ($pdo !== null) {
            $this->conn = $pdo;
        } else {
            $this->conn = DB::connect();
        }
    }

    /**
     * Find user by email (for admin/warden login)
     */
    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all wardens
     */
    public function getAllWardens() {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE role = 'warden'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add new admin/warden (for admin use)
     */
    public function add($data) {
        $sql = "INSERT INTO users (first_name, last_name, email, password, role, contact_number) 
                VALUES (:first_name, :last_name, :email, :password, :role, :contact_number)";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':first_name'    => $data['first_name'],
            ':last_name'     => $data['last_name'],
            ':email'         => $data['email'],
            ':password'      => password_hash($data['password'], PASSWORD_DEFAULT),
            ':role'          => $data['role'],
            ':contact_number'=> $data['contact_number'] ?? null
        ]);
    }
}
?>