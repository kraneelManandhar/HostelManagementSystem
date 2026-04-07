<?php
include('views/layout/header.php');
?>

<main class="main-content">

    <!-- Register Now button -->
    <div class="register-bar">
        <a href="/HostelManagementSystem/views/auth/register.php" class="register-btn">Register now !!</a>
    </div>

    <!-- Section 1: Your Home Away From Home -->
    <div class="section-block">
        <img src="/HostelManagementSystem/public/images/common-area.png" alt="Common area of Pentatonic Hostel">

        <div class="section-text">
            <h2>Your Home Away From Home</h2>
            <p>
                Pentatonic Hostel offers a unique blend of comfort and community for travelers,
                backpackers, and students from around the world. Located in the heart of the city,
                our hostel provides clean, affordable accommodation with a warm and welcoming
                atmosphere that makes every guest feel at home. Whether you're here for a night
                or a month, you'll find everything you need to rest, connect, and explore.
            </p>
            <p>
                We believe travel should be accessible to everyone. That's why we've created
                spaces designed for connection — from our vibrant common areas to our cozy
                dorms and private rooms.
            </p>
        </div>
    </div>

    <!-- Section 2: Rest Well, Travel Better -->
    <div class="section-block reverse">
        <img src="/HostelManagementSystem/public/images/dorm-room.png" alt="Clean dorm room with bunk beds">

        <div class="section-text">
            <h2>Rest Well, Travel Better</h2>
            <p>
                Our rooms are designed with your comfort in mind. From spacious
                dorms with personal lockers and reading lights to private rooms for
                those seeking extra privacy — every space is kept immaculately clean
                and thoughtfully furnished.
            </p>
            <p>
                Each bed comes with fresh linens, a power outlet, and blackout curtains
                so you can sleep soundly no matter the hour.
            </p>
        </div>
    </div>

    <!-- Section 3: Connect and Unwind -->
    <div class="section-block">
        <img src="/HostelManagementSystem/public/images/living-area.jpg" alt="Rooftop terrace at sunset">

        <div class="section-text">
            <h2>Connect and Unwind</h2>
            <p>
                Our rooftop terrace is the perfect place to meet fellow travelers,
                enjoy the sunset, or simply relax after a day of exploring. With
                comfortable seating, string lights, and stunning city views, it's
                become the heartbeat of our hostel community.
            </p>
            <p>
                We regularly host events, movie nights, and communal dinners to
                bring guests together and create lasting memories.
            </p>
        </div>
    </div>

</main>

<?php include('views/layout/footer.php'); ?>
