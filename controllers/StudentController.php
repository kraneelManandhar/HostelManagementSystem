<?php

require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Room.php';
require_once __DIR__ . '/../models/Fee.php';
require_once __DIR__ . '/../models/Complaint.php';
require_once __DIR__ . '/../models/Notice.php';

class StudentController {

    private PDO $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function dashboard() {

        if (!isset($_SESSION['student_id'])) {
            header("Location: /HostelManagementSystem/views/auth/login.php");
            exit();
        }

        try {

            $id = $_SESSION['student_id'];

            $studentModel = new Student($this->pdo);
            $roomModel = new Room($this->pdo);
            $feeModel = new Fee($this->pdo);
            $complaintModel = new Complaint($this->pdo);
            $noticeModel = new Notice($this->pdo);

            $student = $studentModel->find($id);
            $room = $roomModel->findByStudent($id);   
            $fees = $feeModel->findByStudent($id);
            $complaints = $complaintModel->allByStudent($id);
            $notices = $noticeModel->all();

            require __DIR__ . '/../views/dashboard/student_dashboard.php';

        } catch (PDOException $e) {
            die("Dashboard Error: " . $e->getMessage());
        }
    }
}