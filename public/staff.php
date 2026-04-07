<?php
/**
 * warden.php
 * Staffs page — shows hostel staff members with photo, name, contact, and bio.
 * Included via the nav link "Staffs" in the header.
 */
include('../views/layout/header.php');
?>
<main class="main-content">

    <!-- Register Now button (top right) -->
    <div class="register-bar">
        <a href="/HostelManagementSystem/views/auth/register.php" class="register-btn">Register now !!</a>
    </div>

    <!-- ========== Staff Member 1: Image LEFT, text RIGHT ========== -->
    <div class="section-block">
        <img src="/HostelManagementSystem/public/images/staff1.jpg" alt="Ananya Arora">

        <div class="section-text">
            <h3 class="staff-name">Ananya Arora</h3>
            <p class="staff-contact">ananya@pentatonic.com</p>
            <p class="staff-contact">9847612502</p>
            <p class="staff-bio">
                Our team is the heart of Pentatonic Hostel. Each member brings a unique energy,
                local expertise, and genuine passion for making every guest feel at home. Whether
                it's recommending hidden gems around the city or ensuring your stay is
                comfortable and memorable, our staff goes above and beyond with warmth and
                dedication.
            </p>
        </div>
    </div>

    <!-- ========== Staff Member 2: Text LEFT, image RIGHT ========== -->
    <div class="section-block reverse">
        <img src="/HostelManagementSystem/public/images/staff2.jpg" alt="Marcus Lindberg">

        <div class="section-text">
            <h3 class="staff-name">Marcus Lindberg</h3>
            <p class="staff-contact">marcus@pentatonic.com</p>
            <p class="staff-contact">9876543210</p>
            <p class="staff-bio">
                Our team is the heart of Pentatonic Hostel. Each member brings a unique
                energy, local expertise, and genuine passion for making every guest feel at
                home. Whether it's recommending hidden gems around the city or ensuring
                your stay is comfortable and memorable, our staff goes above and beyond with
                warmth and dedication.
            </p>
        </div>
    </div>

    <!-- ========== Staff Member 3: Image LEFT, text RIGHT ========== -->
    <div class="section-block">
        <img src="/HostelManagementSystem/public/images/staff3.jpg" alt="Priya Tamrakar">

        <div class="section-text">
            <h3 class="staff-name">Priya Tamrakar</h3>
            <p class="staff-contact">priya@pentatonic.com</p>
            <p class="staff-contact">9876544902</p>
            <p class="staff-bio">
                Our team is the heart of Pentatonic Hostel. Each member brings a unique
                energy, local expertise, and genuine passion for making every guest feel at
                home. Whether it's recommending hidden gems around the city or ensuring
                your stay is comfortable and memorable, our staff goes above and beyond with
                warmth and dedication.
            </p>
        </div>
    </div>

</main>

<?php include('../views/layout/footer.php'); ?>
