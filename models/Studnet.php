<?php
class Student {
    private $pdo;

    public function __construct($db) {
        $this->pdo = $db;
    }

    public function create($data) {
        $sql = "INSERT INTO students (
                    full_name, date_of_birth, contact_number, email_address, 
                    passport_size_photo, college_name, permanent_address, 
                    enrolled_date, guardian_full_name, relationship, guardian_contact_number
                ) VALUES (
                    :full_name, :dob, :contact, :email, 
                    :photo, :college, :address, 
                    :enrolled, :g_name, :rel, :g_contact
                )";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }
}