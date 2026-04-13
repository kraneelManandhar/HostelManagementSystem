<?php

class Room {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function findByStudent($student_id) {

        $stmt = $this->pdo->prepare("
            SELECT r.* 
            FROM rooms r
            JOIN students s ON s.room_id = r.id
            WHERE s.id = ?
        ");

        $stmt->execute([$student_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}