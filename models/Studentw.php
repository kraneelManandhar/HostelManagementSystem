<?php

class Studentw {

    private $pdo;

    public function __construct($pdo){
        $this->pdo = $pdo;
    }

    public function getAll(){
        return $this->pdo->query("
            SELECT 
                id,
                CONCAT(first_name,' ',last_name) AS name,
                email,
                contact_number
            FROM students
        ")->fetchAll();
    }
}
