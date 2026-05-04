<?php

class Laundry {

    private $pdo;

    public function __construct($pdo){
        $this->pdo = $pdo;
    }

    public function getAll(){

        $stmt = $this->pdo->query("
            SELECT s.id,
                s.first_name,
                s.last_name,
                CASE 
                    WHEN l.status = 'Completed' THEN 1 
                    ELSE 0 
                END AS laundry
            FROM students s
            LEFT JOIN laundry l 
                ON s.id = l.student_id
        ");

        return $stmt->fetchAll();
    }

    public function updateStatus($student_id, $status){

        $statusText = $status ? 'Completed' : 'Pending';

        $stmt = $this->pdo->prepare("
            INSERT INTO laundry (student_id, status)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE status = ?
        ");

        return $stmt->execute([$student_id, $statusText, $statusText]);
    }
}