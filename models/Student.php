<?php
require_once '../config/db.php';

class Student {
    private $conn;

    public function __construct(){
        $this->conn = DB::connect();
    }

    public function getAll(){
        return $this->conn->query("
            SELECT s.*, 
            f.status as food,
            l.status as laundry,
            b.status as bathroom,
            t.time_in, t.time_out
            FROM students s
            LEFT JOIN food f ON s.id=f.student_id
            LEFT JOIN laundry l ON s.id=l.student_id
            LEFT JOIN bathroom b ON s.id=b.student_id
            LEFT JOIN timing t ON s.id=t.student_id
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add($name,$email,$contact){
        $this->conn->prepare("INSERT INTO students(name,email,contact) VALUES(?,?,?)")
            ->execute([$name,$email,$contact]);

        $id = $this->conn->lastInsertId();

        $this->conn->prepare("INSERT INTO food VALUES(?,0)")->execute([$id]);
        $this->conn->prepare("INSERT INTO laundry VALUES(?,0)")->execute([$id]);
        $this->conn->prepare("INSERT INTO bathroom VALUES(?,0)")->execute([$id]);
        $this->conn->prepare("INSERT INTO timing VALUES(?,NULL,NULL)")->execute([$id]);
    }

    public function delete($id){
        $this->conn->prepare("DELETE FROM students WHERE id=?")->execute([$id]);
    }

    public function toggle($table,$id){
        $this->conn->query("UPDATE $table SET status = NOT status WHERE student_id=$id");
    }

    public function updateTime($id,$in,$out){
        $stmt = $this->conn->prepare("UPDATE timing SET time_in=?, time_out=? WHERE student_id=?");
        $stmt->execute([$in,$out,$id]);
    }
}
?>