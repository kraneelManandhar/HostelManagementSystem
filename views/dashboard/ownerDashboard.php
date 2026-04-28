<?php
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'owner') {
    header('Location: ' . BASE_URL . 'index.php?action=login');
    exit;
}
?>

<?php include(__DIR__ . '/../layout/header.php'); ?>

<main class="main-content">
    <h1>Owner Dashboard</h1>
    <p>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</p>
    
    <div class="dashboard-cards">
        <div class="card">
            <h3>Financial Overview</h3>
            <p>View hostel revenue and expenses</p>
        </div>
        <div class="card">
            <h3>Manage Wardens</h3>
            <p>Add or remove warden accounts</p>
        </div>
        <div class="card">
            <h3>Reports</h3>
            <p>View system-wide reports</p>
        </div>
        <div class="card">
            <h3>Settings</h3>
            <p>Configure hostel settings</p>
        </div>
    </div>
    
    <a href="<?= BASE_URL ?>index.php?action=logout" class="logout-btn">Logout</a>
</main>

<?php include(__DIR__ . '/../layout/footer.php'); ?>