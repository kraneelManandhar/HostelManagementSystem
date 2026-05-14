<?php

class Fee {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function findByStudent($student_id) {
        $stmt = $this->pdo->prepare("
            SELECT
                f.*,
                GREATEST(GREATEST(COALESCE(f.total, 0), 0) - LEAST(GREATEST(COALESCE(f.paid, 0), 0), GREATEST(COALESCE(f.total, 0), 0)), 0) AS pending,
                CASE
                    WHEN GREATEST(COALESCE(f.total, 0), 0) <= 0
                        OR LEAST(GREATEST(COALESCE(f.paid, 0), 0), GREATEST(COALESCE(f.total, 0), 0)) / NULLIF(GREATEST(COALESCE(f.total, 0), 0), 0) >= 0.8
                    THEN 'Paid'
                    WHEN LEAST(GREATEST(COALESCE(f.paid, 0), 0), GREATEST(COALESCE(f.total, 0), 0)) = 0
                    THEN 'Pending'
                    ELSE 'Partial'
                END AS status
            FROM fees f
            WHERE f.student_id=?
        ");
        $stmt->execute([$student_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Mark a fee record as fully paid.
     */
    public function markAsPaid($fee_id) {
        $stmt = $this->pdo->prepare("
            UPDATE fees SET paid = total, pending = 0, status = 'Paid' WHERE id = ?
        ");
        return $stmt->execute([$fee_id]);
    }

    /**
     * Toggle fee status between Paid and Unpaid.
     */
    public function toggleStatus($fee_id) {
        $stmt = $this->pdo->prepare("
            SELECT status FROM fees WHERE id = ?
        ");
        $stmt->execute([$fee_id]);
        $current = $stmt->fetchColumn();
        
        $newStatus = strtolower($current) === 'paid' ? 'Unpaid' : 'Paid';
        $paidAmount = $newStatus === 'Paid' ? 'total' : '0';
        
        $stmt = $this->pdo->prepare("
            UPDATE fees SET status = ?, paid = $paidAmount WHERE id = ?
        ");
        return $stmt->execute([$newStatus, $fee_id]);
    }
}
