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
        $this->pdo = $pdo ?? \DB::connect();
        $this->model = new Student($this->pdo);
    }

    public function index() {
        return $this->model->getAll();
    }

    public function add() {
        $this->model->add($_POST['name'], $_POST['email'], $_POST['contact']);
        header("Location: index.php");
    }

    public function delete() {
        $this->model->delete($_GET['id']);
        header("Location: index.php");
    }

    public function toggle($type) {
        $this->model->toggle($type, $_GET['id']);
        header("Location: index.php?action=warden_" . $type);
    }

    public function timing() {
        $this->model->updateTime($_POST['id'], $_POST['in'], $_POST['out']);
        header("Location: index.php?action=warden_timing");
    }

    public function register($data) {
        return $this->model->registerStudent($data);
    }

    /**
     * Fetch all data needed for the student dashboard.
     */
    public function getDashboardData($student_id) {
        // Student
        $stmt = $this->pdo->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        // Room
        $roomModel = new Room($this->pdo);
        $room = $roomModel->findByStudent($student_id);

        // Fees
        $feeModel = new Fee($this->pdo);
        $fees = $feeModel->findByStudent($student_id);

        // Notices
        $noticeModel = new Notice($this->pdo);
        $notices = $noticeModel->all();

        // Complaints
        $complaintModel = new Complaint($this->pdo);
        $complaints = $complaintModel->allByStudent($student_id);

        return compact('student', 'room', 'fees', 'notices', 'complaints');
    }
}
?>
