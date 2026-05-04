<?php

class Timing {

    private $pdo;

    public function __construct($pdo){
        $this->pdo = $pdo;
    }

    public function getTiming(){

        $stmt = $this->pdo->query("
            SELECT s.id,
                   CONCAT(s.first_name,' ',s.last_name) AS name,
                   TIME(t.check_in) AS check_in,
                   TIME(t.check_out) AS check_out
            FROM students s
            LEFT JOIN timing t 
                ON s.id = t.student_id
        ");

        return $stmt->fetchAll();
    }

    public function update($id, $in, $out){

        $stmt = $this->pdo->prepare("
            INSERT INTO timing (student_id, check_in, check_out)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE
                check_in = VALUES(check_in),
                check_out = VALUES(check_out)
        ");

        return $stmt->execute([$id, $in, $out]);
    }
}