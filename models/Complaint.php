<?php

class Complaint {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function allByStudent($id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM complaints 
            WHERE student_id=? 
            ORDER BY id DESC
        ");
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($student_id, $title, $description, $room_number) {

    $stmt = $this->pdo->prepare("
        INSERT INTO complaints (student_id, title, description, room_number, status)
        VALUES (?, ?, ?, ?, 'Pending')
    ");

    $stmt->execute([
        $student_id,
        $title,
        $description,
        $room_number
    ]);

    return $this->pdo->lastInsertId();
    }

    public function delete($id, $student_id) {
        $stmt = $this->pdo->prepare("
            DELETE FROM complaints 
            WHERE id=? AND student_id=?
        ");
        return $stmt->execute([$id, $student_id]);
    }
}