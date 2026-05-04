<?php
class Food {

    private $pdo;

    public function __construct($pdo){
        $this->pdo = $pdo;
    }

    // GET today's food status
    public function getToday($meal_type){
        $stmt = $this->pdo->prepare("
            SELECT s.id,
                   CONCAT(s.first_name,' ',s.last_name) AS name,
                   IFNULL(f.status,0) AS status
            FROM students s
            LEFT JOIN food f 
                ON s.id = f.student_id 
                AND f.date = CURDATE()
                AND f.meal_type = ?
        ");
        $stmt->execute([$meal_type]);
        return $stmt->fetchAll();
    }

    // TOGGLE
    public function toggle($student_id, $meal_type, $status){
        $stmt = $this->pdo->prepare("
            INSERT INTO food (student_id, meal_type, status, date)
            VALUES (?, ?, ?, CURDATE())
            ON DUPLICATE KEY UPDATE status = ?
        ");
        return $stmt->execute([$student_id, $meal_type, $status, $status]);
    }
}