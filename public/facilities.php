<?php
/**
 * owner.php
 * Facilities page — shows hostel amenities in a 2-column image grid.
 * Included via the nav link "Facilities" in the header.
 */
include('../views/layout/header.php');
?>

<main class="main-content">

    <!-- Register Now button (top right) -->
    <div class="register-bar">
        <a href="/HOSTELMANAGEMENTSYSTEM/views/auth/register.php" class="register-btn">Register now !!</a>
    </div>

    <!-- ========== Facilities Grid ========== -->
    <!-- 2-column grid, each cell has an image + label below -->
    <div class="facilities-grid">

        <!-- Study Section -->
        <div class="facility-card">
            <img src="/HOSTELMANAGEMENTSYSTEM/public/images/study-section.jpg" alt="Study Section">
            <p>Study section</p>
        </div>

        <!-- Laundry Room -->
        <div class="facility-card">
            <img src="/HOSTELMANAGEMENTSYSTEM/public/images/laundry.jpg" alt="Laundry Room">
            <p>Laundry Room</p>
        </div>

        <!-- Game Room -->
        <div class="facility-card">
            <img src="/HOSTELMANAGEMENTSYSTEM/public/images/game-room.jpg" alt="Game Room">
            <p>Game Room</p>
        </div>

        <!-- Bike Rentals -->
        <div class="facility-card">
            <img src="/HOSTELMANAGEMENTSYSTEM/public/images/bike-rentals.jpg" alt="Bike Rentals">
            <p>Bike rentals</p>
        </div>

        <!-- Gym -->
        <div class="facility-card">
            <img src="/HOSTELMANAGEMENTSYSTEM/public/images/gym.jpg" alt="Gym">
            <p>Gym</p>
        </div>

        <!-- Roof Sitting Area -->
        <div class="facility-card">
            <img src="/HOSTELMANAGEMENTSYSTEM/public/images/rooftop-sitting.jpg" alt="Roof Sitting Area">
            <p>Roof Sitting Area</p>
        </div>

    </div>

</main>

<?php include('../views/layout/footer.php'); ?>