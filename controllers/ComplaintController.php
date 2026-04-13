<?php

require_once __DIR__ . '/../models/Complaint.php';

class ComplaintController {

    private PDO $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function store() {

    header("Content-Type: application/json");

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $student_id = $_SESSION['student_id'] ?? null;

    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $room = $_POST['room_number'] ?? '';

    if (!$student_id || !$title) {
        echo json_encode([
            "success" => false,
            "message" => "Issue is required"
        ]);
        return;
    }

    $model = new Complaint($this->pdo);

    $id = $model->create($student_id, $title, $description, $room);

    echo json_encode([
        "success" => true,
        "data" => [
            "id" => $id,
            "issue" => $title,
            "description" => $description,
            "room" => $room
        ]
    ]);
    }

    public function delete() {

        header("Content-Type: application/json");

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $input = json_decode(file_get_contents("php://input"), true);

        $id = $input['id'] ?? null;
        $student_id = $_SESSION['student_id'] ?? null;

        if (!$id || !$student_id) {
            echo json_encode(["success" => false]);
            return;
        }

        $model = new Complaint($this->pdo);
        $model->delete($id, $student_id);

        echo json_encode(["success" => true]);
    }
}