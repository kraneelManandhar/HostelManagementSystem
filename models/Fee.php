<?php

class Fee {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function findByStudent($student_id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM fees WHERE student_id=?
        ");
        $stmt->execute([$student_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Mark a fee record as fully paid.
     */
    public function markAsPaid($fee_id) {
        $stmt = $this->pdo->prepare("
            UPDATE fees SET paid = total, status = 'Paid' WHERE id = ?
        ");
        return $stmt->execute([$fee_id]);
    }
}