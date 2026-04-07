<?php
/**
 * facilities.php
 * Location: public/facilities.php
 * Facilities page — 2-column grid of hostel amenities.
 */
include('../views/layout/header.php');
?>

<main class="main-content">

    <!-- Register Now button (top right) -->
    <div class="register-bar">
        <a href="/HostelManagementSystem/views/auth/register.php" class="register-btn">Register now !!</a>
    </div>

    <!-- 2-column facilities grid -->
    <div class="facilities-grid">

        <div class="facility-card">
            <img src="/HostelManagementSystem/public/images/study-section.jpg" alt="Study Section">
            <p>Study section</p>
        </div>

        <div class="facility-card">
            <img src="/HostelManagementSystem/public/images/laundry.jpg" alt="Laundry Room">
            <p>Laundry Room</p>
        </div>

        <div class="facility-card">
            <img src="/HostelManagementSystem/public/images/game-room.jpg" alt="Game Room">
            <p>Game Room</p>
        </div>

        <div class="facility-card">
            <img src="/HostelManagementSystem/public/images/parking-area.jpg" alt="Parking Area">
            <p>Parking Area</p>
        </div>

        <div class="facility-card">
            <img src="/HostelManagementSystem/public/images/gym.jpg" alt="Gym">
            <p>Gym</p>
        </div>

        <div class="facility-card">
            <img src="/HostelManagementSystem/public/images/rooftop-sitting.jpg" alt="Roof Sitting Area">
            <p>Roof Sitting Area</p>
        </div>

    </div>

</main>

<?php include('../views/layout/footer.php'); ?>
