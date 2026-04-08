<div class="content">

<!-- TOP BAR -->
<div class="topbar">DASHBOARD</div>

<!-- DASHBOARD CARDS -->
<div class="dashboard-grid">

    <div class="card">
        Students
        <span class="badge"><?= count($data) ?></span>
    </div>

    <div class="card" onclick="location.href='index.php?page=food'">Food</div>
    <div class="card">Laundry</div>
    <div class="card">Bathroom Cleaning</div>
    <div class="card">Timing</div>

</div>

<!-- ADD STUDENT -->
<h3 style="margin-top:30px;">Add Student</h3>

<form method="POST" class="form">
    <input name="name" placeholder="Full Name" required>
    <input name="email" placeholder="Email" required>
    <input name="contact" placeholder="Contact Number" required>
    <button name="add">Add</button>
</form>

<!-- STUDENT TABLE -->
<h3 style="margin-top:20px;">Students</h3>

<div class="table">

    <!-- HEADER -->
    <div class="row header">
        <span>Name</span>
        <span>Email</span>
        <span>Contact</span>
        <span>Action</span>
    </div>

    <!-- DATA -->
    <?php foreach($data as $s): ?>
    <div class="row">

        <span><?= $s['name'] ?></span>
        <span><?= $s['email'] ?></span>
        <span><?= $s['contact'] ?></span>

        <span>
            <a href="index.php?delete&id=<?= $s['id'] ?>">Delete</a>
        </span>

    </div>
    <?php endforeach; ?>

</div>

</div>