<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'];

    $stmt = $pdo->prepare("SELECT * FROM students WHERE email=?");
    $stmt->execute([$email]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        $_SESSION['student_id'] = $student['id'];

        header("Location: /HostelManagementSystem/index.php?page=student_dashboard");
        exit();
    } else {
        $error = "Invalid email";
    }
}
?>

<form method="POST">
    <input type="email" name="email" placeholder="Email" required>
    <button type="submit">Login</button>
</form>

<p><?= $error ?></p>