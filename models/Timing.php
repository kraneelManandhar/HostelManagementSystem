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

    public function getByStudentId(int $studentId): ?array {
        $stmt = $this->pdo->prepare("
            SELECT id, student_id, check_in, check_out, status
            FROM timing
            WHERE student_id = ?
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([$studentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function updateForStudent(int $studentId, ?string $checkIn, ?string $checkOut): bool {
        $existing = $this->getByStudentId($studentId);
        $status = ($checkOut && !$checkIn) ? 'OUT' : 'IN';

        if ($existing) {
            $stmt = $this->pdo->prepare("
                UPDATE timing
                SET check_in = ?, check_out = ?, status = ?
                WHERE id = ?
            ");

            return $stmt->execute([$checkIn, $checkOut, $status, $existing['id']]);
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO timing (student_id, check_in, check_out, status)
            VALUES (?, ?, ?, ?)
        ");

        return $stmt->execute([$studentId, $checkIn, $checkOut, $status]);
    }
}
