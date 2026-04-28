<?php
require_once __DIR__ . '/../config/db.php';

class Student {
    private $conn;

    public function __construct($pdo = null){
        if ($pdo !== null) {
            $this->conn = $pdo;
        } else {
            $this->conn = DB::connect();
        }
    }

    public function getAll(){
        $sql = "SELECT s.*, r.number as room_number, r.floor_block 
                FROM students s 
                LEFT JOIN rooms r ON s.room_id = r.id";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add($fname, $mname, $lname, $email, $password, $room_id){
        $sql = "INSERT INTO students (first_name, middle_name, last_name, email, password, room_id) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$fname, $mname, $lname, $email, $password, $room_id]);
        return $this->conn->lastInsertId();
    }

    public function delete($id){
        $stmt = $this->conn->prepare("DELETE FROM students WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function toggle($table, $student_id){
        $allowed_tables = ['food', 'laundry', 'bathroom'];
        if (in_array($table, $allowed_tables)) {
            $sql = "UPDATE $table SET status = NOT status WHERE student_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$student_id]);
        }
    }

    public function updateTime($id, $in, $out){
        $stmt = $this->conn->prepare("UPDATE timing SET check_in = ?, check_out = ? WHERE student_id = ?");
        $stmt->execute([$in, $out, $id]);
    }

    /**
     * Find student by email (for student login)
     */
    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM students WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function registerStudent($data) {
        if (empty($data['password'])) {
            throw new Exception("Password is required");
        }

        $sql = "INSERT INTO students (
            first_name, middle_name, last_name, date_of_birth,
            contact_number, email, password, profile_photo,
            college_name, permanent_address, date_of_joining,
            guardian_name, guardian_relationship, guardian_contact,
            preferred_room_type, room_id, created_at
        ) VALUES (
            :first_name, :middle_name, :last_name, :date_of_birth,
            :contact_number, :email, :password, :profile_photo,
            :college_name, :permanent_address, :date_of_joining,
            :guardian_name, :guardian_relationship, :guardian_contact,
            :preferred_room_type, :room_id, NOW()
        )";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':first_name'            => $data['first_name'] ?? '',
            ':middle_name'           => !empty($data['middle_name']) ? $data['middle_name'] : null,
            ':last_name'             => $data['last_name'] ?? '',
            ':date_of_birth'         => $data['date_of_birth'] ?? null,
            ':contact_number'        => $data['contact_number'] ?? '',
            ':email'                 => $data['email'] ?? '',
            ':password'              => $data['password'],
            ':profile_photo'         => $data['profile_photo'] ?? null,
            ':college_name'          => $data['college_name'] ?? '',
            ':permanent_address'     => $data['permanent_address'] ?? '',
            ':date_of_joining'       => $data['date_of_joining'] ?? null,
            ':guardian_name'         => $data['guardian_name'] ?? '',
            ':guardian_relationship' => $data['guardian_relationship'] ?? '',
            ':guardian_contact'      => $data['guardian_contact'] ?? '',
            ':preferred_room_type'   => $data['preferred_room_type'] ?? 'double',
            ':room_id'               => $data['room_id'] ?? null
        ]);
    }
}
?>