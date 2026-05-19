<?php

class Complaint {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function allByStudent($id) {
        $stmt = $this->pdo->prepare("
            SELECT c.*, r.number AS room_number
            FROM complaints c
            LEFT JOIN rooms r ON r.id = c.room_id
            WHERE c.student_id=? AND c.student_deleted = 0
            ORDER BY c.id DESC
        ");
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch all complaints (for manager view)
     */
    public function all() {
        $stmt = $this->pdo->query("
            SELECT c.*, s.first_name, s.middle_name, s.last_name, r.number AS room_number
            FROM complaints c
            LEFT JOIN students s ON s.id = c.student_id
            LEFT JOIN rooms r ON r.id = c.room_id
            ORDER BY c.id DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a complaint. Accepts room_number string and resolves to room_id.
     */
    public function create($student_id, $title, $description, $room_number) {
        // Resolve room_number string to room_id
        $room_id = null;
        if (!empty($room_number)) {
            $stmt = $this->pdo->prepare("SELECT id FROM rooms WHERE number = ? LIMIT 1");
            $stmt->execute([$room_number]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $room_id = $row['id'];
            }
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO complaints (student_id, title, description, room_id, status)
            VALUES (?, ?, ?, ?, 'Pending')
        ");

        $stmt->execute([
            $student_id,
            $title,
            $description,
            $room_id
        ]);

        return $this->pdo->lastInsertId();
    }

    public function delete($id, $student_id) {
        $stmt = $this->pdo->prepare("
            UPDATE complaints
            SET student_deleted = 1
            WHERE id=? AND student_id=?
        ");
        return $stmt->execute([$id, $student_id]);
    }
}
