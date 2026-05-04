<?php
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Room.php';
require_once __DIR__ . '/../models/Fee.php';
require_once __DIR__ . '/../models/Notice.php';
require_once __DIR__ . '/../models/Complaint.php';

class StudentController {
    private $model;
    private $pdo;

    public function __construct($pdo = null) {
        $this->pdo   = $pdo ?? \DB::connect();
        $this->model = new Student($this->pdo);
    }

    public function index() {
        return $this->model->getAll();
    }

    public function add() {
        $this->model->add($_POST['name'], $_POST['email'], $_POST['contact']);
        header("Location: " . BASE_URL . "index.php");
    }

    public function delete() {
        $this->model->delete($_GET['id']);
        header("Location: " . BASE_URL . "index.php");
    }

    public function toggle($type) {
        $this->model->toggle($type, $_GET['id']);
        header("Location: " . BASE_URL . "index.php?page=" . $type);
    }

    public function timing() {
        $this->model->updateTime($_POST['id'], $_POST['in'], $_POST['out']);
        header("Location: " . BASE_URL . "index.php?page=timing");
    }

    public function register($data) {
        return $this->model->registerStudent($data);
    }

    // Room selection page (GET)
    // Shows rooms matching the student's preferred_room_type
    public function showRoomSelection() {
        $student_id = $_SESSION['user_id'] ?? 0;

        // Fetch student to read preferred_room_type
        $stmt = $this->pdo->prepare("SELECT preferred_room_type FROM students WHERE id = ?");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        $preferredType   = $student['preferred_room_type'] ?? 'single';
        $roomModel       = new Room($this->pdo);
        $availableRooms  = $roomModel->getAvailableByType($preferredType);

        // Render the view
        if (!defined('BASE_URL')) {
            define('BASE_URL', '/HostelManagementSystem/');
        }

        include __DIR__ . '/../views/dashboard/room_selection.php';
    }

    // Save room choice (POST from room_selection form)
    public function saveRoomSelection() {
        $student_id = $_SESSION['user_id'] ?? 0;
        $room_id    = (int) ($_POST['room_id']  ?? 0);
        $bed_slot   = trim($_POST['bed_slot'] ?? '');

        // Validate
        if (!$student_id || !$room_id || !in_array($bed_slot, ['student1', 'student2'], true)) {
            header("Location: " . BASE_URL . "index.php?action=room_selection&error=invalid");
            exit;
        }

        $roomModel = new Room($this->pdo);
        $ok        = $roomModel->assignStudent($room_id, $student_id, $bed_slot);

        if ($ok) {
            // Mark room as assigned in session so the redirect guard works
            $_SESSION['room_assigned'] = true;
            header("Location: " . BASE_URL . "index.php?action=student_dashboard");
        } else {
            // Slot was grabbed by someone else between page load and submit
            header("Location: " . BASE_URL . "index.php?action=room_selection&error=taken");
        }
        exit;
    }

    // Dashboard data
    public function getDashboardData($student_id) {
        // Student
        $stmt = $this->pdo->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        // Room
        $roomModel = new Room($this->pdo);
        $room      = $roomModel->findByStudent($student_id);

        // Fees
        $feeModel = new Fee($this->pdo);
        $fees     = $feeModel->findByStudent($student_id);

        // Notices
        $noticeModel = new Notice($this->pdo);
        $notices     = $noticeModel->all();

        // Complaints
        $complaintModel = new Complaint($this->pdo);
        $complaints     = $complaintModel->allByStudent($student_id);

        return compact('student', 'room', 'fees', 'notices', 'complaints');
    }
}
?>