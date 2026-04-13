<?php

class Notice {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function all() {

        $stmt = $this->pdo->query("
            SELECT * FROM notices ORDER BY date DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}