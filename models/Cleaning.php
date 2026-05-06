<?php
class Cleaning {

    private $pdo;

    public function __construct($pdo){
        $this->pdo = $pdo;
    }

    public function getCleaning(){

        $stmt = $this->pdo->query("
            SELECT r.id AS room_id,
                   r.number AS room_number,
                   CASE 
                       WHEN c.status = 'Done' THEN 1 
                       ELSE 0 
                   END AS cleaning
            FROM rooms r
            LEFT JOIN cleaning c 
                ON r.id = c.room_id
        ");

        return $stmt->fetchAll();
    }

    public function updateStatus($room_id, $status){

        $statusText = $status ? 'Done' : 'Pending';

        $stmt = $this->pdo->prepare("
            INSERT INTO cleaning (room_id, status, cleaned_at)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
                status = ?, 
                cleaned_at = NOW()
        ");

        return $stmt->execute([$room_id, $statusText, $statusText]);
    }
}