<?php
require_once __DIR__ . '/../models/Studnet.php';

class StudentController {
    private $studentModel;

    public function __construct($db) {
        $this->studentModel = new Student($db);
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle image upload
            $photoName = null;
            if (!empty($_FILES['passport_photo']['name'])) {
                $photoName = time() . "_" . $_FILES['passport_photo']['name'];
                move_uploaded_file($_FILES['passport_photo']['tmp_name'], __DIR__ . "/../public/images/" . $photoName);
            }

            // Map POST data to Model array
            $data = [
                ':full_name' => $_POST['full_name'],
                ':dob'       => $_POST['date_of_birth'],
                ':contact'   => $_POST['contact_number'],
                ':email'     => $_POST['email_address'],
                ':photo'     => $photoName,
                ':college'   => $_POST['college_name'],
                ':address'   => $_POST['permanent_address'],
                ':enrolled'  => $_POST['enrolled_date'],
                ':g_name'    => $_POST['guardian_full_name'],
                ':rel'       => $_POST['relationship'],
                ':g_contact' => $_POST['guardian_contact_number']
            ];

            if ($this->studentModel->create($data)) {
                header("Location: index.php?msg=RegistrationSuccessful");
            }
        }
    }
}