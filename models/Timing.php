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

    public function updateForStudent(int $studentId, ?string $checkIn, ?string $checkOut): array {
        $latest = $this->getByStudentId($studentId);
        $currentStatus = strtoupper((string) ($latest['status'] ?? 'IN'));

        if ($currentStatus === 'OUT') {
            return $this->checkStudentIn($studentId, $checkIn);
        }

        return $this->checkStudentOut($studentId, $checkOut);
    }

    private function checkStudentOut(int $studentId, ?string $checkOut): array {
        if ($checkOut === null) {
            return [
                'success' => false,
                'message' => 'Please enter check out time.',
            ];
        }

        if ($this->getOpenCheckout($studentId)) {
            return [
                'success' => false,
                'message' => 'You already have a pending checkout. Please check in first.',
            ];
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO timing (student_id, check_in, check_out, status)
            VALUES (?, NULL, ?, 'OUT')
        ");

        if (!$stmt->execute([$studentId, $checkOut])) {
            return [
                'success' => false,
                'message' => 'Could not save timing.',
            ];
        }

        return [
            'success' => true,
            'message' => 'Timing saved successfully.',
            'data' => $this->getByStudentId($studentId),
        ];
    }

    private function checkStudentIn(int $studentId, ?string $checkIn): array {
        if ($checkIn === null) {
            return [
                'success' => false,
                'message' => 'Please enter check in time.',
            ];
        }

        $openCheckout = $this->getOpenCheckout($studentId);

        if (!$openCheckout) {
            return [
                'success' => false,
                'message' => 'No pending checkout record found.',
            ];
        }

        if (!empty($openCheckout['check_out']) && strtotime($checkIn) < strtotime($openCheckout['check_out'])) {
            return [
                'success' => false,
                'message' => 'Check in cannot be before check out.',
            ];
        }

        $stmt = $this->pdo->prepare("
            UPDATE timing
            SET check_in = ?, status = 'IN'
            WHERE id = ?
        ");

        if (!$stmt->execute([$checkIn, $openCheckout['id']])) {
            return [
                'success' => false,
                'message' => 'Could not save timing.',
            ];
        }

        return [
            'success' => true,
            'message' => 'Timing saved successfully.',
            'data' => $this->getByStudentId($studentId),
        ];
    }

    private function getOpenCheckout(int $studentId): ?array {
        $stmt = $this->pdo->prepare("
            SELECT id, student_id, check_in, check_out, status
            FROM timing
            WHERE student_id = ?
              AND check_in IS NULL
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([$studentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }
}
