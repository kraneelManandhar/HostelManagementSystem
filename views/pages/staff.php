<?php
include(__DIR__ . '/../layout/header.php'); 
?>
<main class="main-content">

    <!-- Register Now button -->
    <div class="register-bar">
        <a href="<?= BASE_URL ?>index.php?action=register" class="register-btn">Register now !!</a>
    </div>

    <!-- Staff Member 1: Details -->
    <div class="section-block">
        <img src="<?= BASE_URL ?>public/images/staff1.jpg" alt="Ananya Arora">

        <div class="section-text">
            <h3 class="staff-name">Ananya Arora</h3>
            <p class="staff-contact">ananya@pentatonic.com</p>
            <p class="staff-contact">9847612502</p>
            <p class="staff-bio">
                Ananya is responsible for guest relations and front desk management. 
                She ensures smooth check-ins and check-outs while handling guest queries with patience and professionalism. 
                Her friendly attitude makes every visitor feel welcomed from the moment they arrive.
            </p>
        </div>
    </div>

    <!-- Staff Member 2: Details -->
    <div class="section-block reverse">
        <img src="<?= BASE_URL ?>public/images/staff2.jpg" alt="Marcus Lindberg">

        <div class="section-text">
            <h3 class="staff-name">Marcus Lindberg</h3>
            <p class="staff-contact">marcus@pentatonic.com</p>
            <p class="staff-contact">9876543210</p>
            <p class="staff-bio">
                Marcus manages hostel operations and maintenance coordination. 
                With strong organizational skills, he ensures all facilities are running smoothly and efficiently.
                He is known for quickly resolving issues and maintaining a clean, safe environment for all individuals.
            </p>
        </div>
    </div>

    <!-- Staff Member 3: Details -->
    <div class="section-block">
        <img src="<?= BASE_URL ?>public/images/staff3.jpg" alt="Priya Tamrakar">

        <div class="section-text">
            <h3 class="staff-name">Priya Tamrakar</h3>
            <p class="staff-contact">priya@pentatonic.com</p>
            <p class="staff-contact">9876544902</p>
            <p class="staff-bio">
                Priya is part of the housekeeping team responsible for maintaining cleanliness 
                and hygiene across the hostel. She ensures that rooms, bathrooms, and common 
                areas are cleaned regularly and kept in good condition for a comfortable
                stay for all residents.
            </p>
        </div>
    </div>
</main>

<?php include(__DIR__ . '/../layout/footer.php'); ?>
