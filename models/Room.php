<?php

class Room {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Existing: find room assigned to a student (for dashboard)
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

    public function getByTypeWithOccupancy(string $type): array {
        $stmt = $this->pdo->prepare("
            SELECT
                r.*,
                TRIM(CONCAT(
                    COALESCE(s1.first_name, ''),
                    ' ',
                    COALESCE(s1.middle_name, ''),
                    ' ',
                    COALESCE(s1.last_name, '')
                )) AS student1_name,
                TRIM(CONCAT(
                    COALESCE(s2.first_name, ''),
                    ' ',
                    COALESCE(s2.middle_name, ''),
                    ' ',
                    COALESCE(s2.last_name, '')
                )) AS student2_name,
                CASE
                    WHEN r.type = 'single' AND r.student1_id IS NULL THEN 1
                    WHEN r.type = 'double' AND (r.student1_id IS NULL OR r.student2_id IS NULL) THEN 1
                    ELSE 0
                END AS has_free_bed
            FROM rooms r
            LEFT JOIN students s1 ON s1.id = r.student1_id
            LEFT JOIN students s2 ON s2.id = r.student2_id
            WHERE r.type = ?
            ORDER BY r.number ASC
        ");
        $stmt->execute([$type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // NEW: assign a student to a specific slot in a room.
    public function assignStudent(int $room_id, int $student_id, string $slot): bool {
        // Validate slot name
        if (!in_array($slot, ['student1', 'student2'], true)) {
            return false;
        }

        $col = $slot . '_id';   

        // Make sure the slot is still free 
        $check = $this->pdo->prepare("SELECT type, $col FROM rooms WHERE id = ?");
        $check->execute([$room_id]);
        $row = $check->fetch(PDO::FETCH_ASSOC);

        if (!$row || $row[$col] !== null) {
            return false;   
        }

        if ($row['type'] === 'single' && $slot !== 'student1') {
            return false;
        }

        // Write the room's slot column
        $update = $this->pdo->prepare("UPDATE rooms SET $col = ? WHERE id = ?");
        $update->execute([$student_id, $room_id]);

        // Also update the student's room_id foreign-key
        $updateStudent = $this->pdo->prepare("UPDATE students SET room_id = ? WHERE id = ?");
        $updateStudent->execute([$room_id, $student_id]);

        return true;
    }
}
