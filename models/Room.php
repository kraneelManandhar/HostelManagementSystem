<?php

class Room {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function findByStudent($student_id) {

        $stmt = $this->pdo->prepare("
            SELECT
                r.*,
                CASE
                    WHEN r.student1_id = ? THEN 'Student 1'
                    WHEN r.student2_id = ? THEN 'Student 2'
                    ELSE ''
                END AS bed_slot,
                GROUP_CONCAT(
                    TRIM(CONCAT(
                        COALESCE(roommates.first_name, ''),
                        ' ',
                        COALESCE(roommates.middle_name, ''),
                        ' ',
                        COALESCE(roommates.last_name, '')
                    ))
                    ORDER BY roommates.id SEPARATOR ', '
                ) AS roommate_names
            FROM rooms r
            LEFT JOIN students roommates
                ON roommates.id IN (r.student1_id, r.student2_id)
               AND roommates.id <> ?
            WHERE r.student1_id = ? OR r.student2_id = ? OR r.id = (
                SELECT room_id FROM students WHERE id = ? LIMIT 1
            )
            GROUP BY r.id
        ");

        $stmt->execute([
            $student_id,
            $student_id,
            $student_id,
            $student_id,
            $student_id,
            $student_id
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
