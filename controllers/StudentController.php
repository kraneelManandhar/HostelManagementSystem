<?php
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Room.php';
require_once __DIR__ . '/../models/Fee.php';
require_once __DIR__ . '/../models/Notice.php';
require_once __DIR__ . '/../models/Complaint.php';
require_once __DIR__ . '/../models/Timing.php';

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

        // Timing
        $timingModel = new Timing($this->pdo);
        $timing      = $timingModel->getByStudentId((int) $student_id);

        return compact('student', 'room', 'fees', 'notices', 'complaints', 'timing');
    }

    public function updateOwnTiming(int $student_id): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'index.php?action=student_dashboard&tab=timing');
            exit;
        }

        $checkInValue = trim((string) ($_POST['check_in'] ?? ''));
        $checkOutValue = trim((string) ($_POST['check_out'] ?? ''));

        if ($checkInValue === '' && $checkOutValue === '') {
            $_SESSION['sd_flash'] = [
                'type' => 'error',
                'message' => 'Please enter at least one timing.'
            ];
            header('Location: ' . BASE_URL . 'index.php?action=student_dashboard&tab=timing');
            exit;
        }

        $checkIn = $checkInValue !== '' ? $this->normalizeDateTime($checkInValue) : null;
        $checkOut = $checkOutValue !== '' ? $this->normalizeDateTime($checkOutValue) : null;

        if (($checkInValue !== '' && $checkIn === null) || ($checkOutValue !== '' && $checkOut === null)) {
            $_SESSION['sd_flash'] = [
                'type' => 'error',
                'message' => 'Please enter a valid date and time.'
            ];
            header('Location: ' . BASE_URL . 'index.php?action=student_dashboard&tab=timing');
            exit;
        }

        $timingModel = new Timing($this->pdo);
        $result = $timingModel->updateForStudent($student_id, $checkIn, $checkOut);

        $_SESSION['sd_flash'] = [
            'type' => $result['success'] ? 'success' : 'error',
            'message' => $result['message'],
        ];

        header('Location: ' . BASE_URL . 'index.php?action=student_dashboard&tab=timing');
        exit;
    }

    private function normalizeDateTime(string $value): ?string {
        $timestamp = strtotime($value);

        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    public function updateProfile(int $student_id): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'index.php?action=student_dashboard');
            exit;
        }

        $contactNumber = trim($_POST['contact_number'] ?? '');
        $guardianContact = trim($_POST['guardian_contact'] ?? '');
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');

        if ($firstName === '' || $lastName === '') {
            $_SESSION['sd_flash'] = [
                'type' => 'error',
                'message' => 'First name and last name are required.'
            ];
            header('Location: ' . BASE_URL . 'index.php?action=student_dashboard&tab=profile');
            exit;
        }

        if (
            !preg_match('/^\d{10}$/', $contactNumber) ||
            !preg_match('/^\d{10}$/', $guardianContact)
        ) {
            $_SESSION['sd_flash'] = [
                'type' => 'error',
                'message' => 'Contact numbers must be exactly 10 digits.'
            ];
            header('Location: ' . BASE_URL . 'index.php?action=student_dashboard&tab=profile');
            exit;
        }

        $profilePhoto = null;
        if (
            isset($_FILES['profile_photo']) &&
            $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK &&
            is_uploaded_file($_FILES['profile_photo']['tmp_name'])
        ) {
            $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $mimeType = mime_content_type($_FILES['profile_photo']['tmp_name']);

            if (!isset($allowedTypes[$mimeType])) {
                $_SESSION['sd_flash'] = [
                    'type' => 'error',
                    'message' => 'Please upload a JPG, PNG, or WEBP image.'
                ];
                header('Location: ' . BASE_URL . 'index.php?action=student_dashboard&tab=profile');
                exit;
            }

            $uploadDir = __DIR__ . '/../public/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $profilePhoto = uniqid('student_', true) . '.' . $allowedTypes[$mimeType];
            move_uploaded_file($_FILES['profile_photo']['tmp_name'], $uploadDir . $profilePhoto);
        }

        $this->model->updateSecondaryInfo($student_id, [
            'first_name' => $firstName,
            'middle_name' => trim($_POST['middle_name'] ?? ''),
            'last_name' => $lastName,
            'contact_number' => $contactNumber,
            'college_name' => trim($_POST['college_name'] ?? ''),
            'permanent_address' => trim($_POST['permanent_address'] ?? ''),
            'guardian_name' => trim($_POST['guardian_name'] ?? ''),
            'guardian_relationship' => trim($_POST['guardian_relationship'] ?? ''),
            'guardian_contact' => $guardianContact,
            'profile_photo' => $profilePhoto,
        ]);

        $_SESSION['sd_flash'] = [
            'type' => 'success',
            'message' => 'Profile updated successfully.'
        ];
        header('Location: ' . BASE_URL . 'index.php?action=student_dashboard&tab=profile');
        exit;
    }
}
?>
