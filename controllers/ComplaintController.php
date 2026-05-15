<?php
require_once __DIR__ . '/../models/Complaint.php';

class ComplaintController {

    private PDO $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function store() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $student_id = $_SESSION['user_id']   ?? null;
        $role       = $_SESSION['user_role'] ?? null;

        if (!$student_id || $role !== 'student') {
            $this->redirectWithFlash('error', 'Not authenticated as a student.');
            return;
        }

        $title       = trim($_POST['title']       ?? '');
        $description = trim($_POST['description'] ?? '');
        $room        = trim($_POST['room_number'] ?? '');

        if (!$title) {
            $this->redirectWithFlash('error', 'Issue is required.');
            return;
        }

        if (!$description) {
            $this->redirectWithFlash('error', 'Description is required.');
            return;
        }

        $model = new Complaint($this->pdo);
        $model->create($student_id, $title, $description, $room);

        $this->redirectWithFlash('success', 'Complaint submitted successfully.');
    }

    public function delete() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id         = $_POST['complaint_id'] ?? null;
        $student_id = $_SESSION['user_id']   ?? null;
        $role       = $_SESSION['user_role'] ?? null;

        if (!$id || !$student_id || $role !== 'student') {
            $this->redirectWithFlash('error', 'Please select a complaint first.');
            return;
        }

        $model = new Complaint($this->pdo);
        $model->delete($id, $student_id);

        $this->redirectWithFlash('success', 'Complaint deleted successfully.');
    }

    private function redirectWithFlash(string $type, string $message): void {
        $_SESSION['sd_flash'] = [
            'type' => $type,
            'message' => $message,
        ];

        header('Location: ' . BASE_URL . 'index.php?action=student_dashboard&tab=complaints');
        exit;
    }
}
