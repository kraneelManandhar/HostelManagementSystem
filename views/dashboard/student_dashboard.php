<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Pentatonic Hostel</title>

    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/HostelManagementSystem/public/css/student.css">
</head>

<body>

<div class="sd-page-wrap">

    <div class="sd-body-row">

        <!-- SIDEBAR -->
        <aside class="sd-sidebar">

            <div class="sd-sidebar-logo" id="goDashboard" style="cursor:pointer;">
                <img src="/HostelManagementSystem/public/images/logo.png" alt="Logo">
                <span>Pentatonic hostel</span>
            </div>

            <div class="sd-sidebar-profile">
                <div class="sd-avatar">
                    <img src="/HostelManagementSystem/public/images/student-photo.jpg"
                         alt="Student"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

                    <span class="sd-avatar-fallback" style="display:none;">
                        <i class="ph-fill ph-user"></i>
                    </span>
                </div>

                <div class="sd-student-name">
                    <?= htmlspecialchars($student['first_name'] ?? '') ?>
                    <?= htmlspecialchars($student['last_name'] ?? '') ?>
                </div>

                <div class="sd-student-role">STUDENT</div>
            </div>

            <nav class="sd-sidebar-nav">
                <button class="sd-nav-btn active" data-page="dashboard">
                    <i class="ph-fill ph-squares-four"></i> Dashboard
                </button>

                <button class="sd-nav-btn" data-page="complaints">
                    <i class="ph ph-megaphone"></i> Complaints
                </button>

                <button class="sd-nav-btn" data-page="notice">
                    <i class="ph ph-warning"></i> Notice
                </button>
            </nav>

            <div class="sd-sidebar-spacer"></div>

            <button class="sd-signout-btn"
                onclick="window.location.href='/HostelManagementSystem/views/auth/login.php'">
                <i class="ph ph-sign-out"></i> Sign out
            </button>

        </aside>

        <!-- MAIN -->
        <main class="sd-main">

            <!-- WELCOME -->
            <div class="sd-welcome-card">
                <div>
                    <h1>
                        WELCOME, <?= strtoupper($student['first_name'] ?? '') ?>
                    </h1>
                    <p>Feel like home</p>
                </div>

                <div class="sd-joined-badge">
                    Joined <?= htmlspecialchars(date("F j, Y", strtotime($student['created_at']))) ?>
                </div>
            </div>

            <!-- DASHBOARD -->
            <div class="sd-page active" id="page-dashboard">

                <div class="sd-blue-panel">

                    <!-- STUDENT -->
                    <div class="sd-info-card">
                        <h3>Student details</h3>

                        <div class="sd-info-grid sd-cols-3">
                            <div class="sd-info-field">
                                <label>First name</label>
                                <span><?= htmlspecialchars($student['first_name'] ?? '') ?></span>
                            </div>

                            <div class="sd-info-field">
                                <label>Middle name</label>
                                <span><?= htmlspecialchars($student['middle_name'] ?? '—') ?></span>
                            </div>

                            <div class="sd-info-field">
                                <label>Last name</label>
                                <span><?= htmlspecialchars($student['last_name'] ?? '') ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- ROOM -->
                    <div class="sd-info-card">
                        <h3>Room details</h3>

                        <div class="sd-info-grid sd-cols-3">
                            <div class="sd-info-field">
                                <label>Room number</label>
                                <span><?= htmlspecialchars($room['number'] ?? '') ?></span>
                            </div>

                            <div class="sd-info-field">
                                <label>Floor / Block</label>
                                <span><?= htmlspecialchars($room['floor_block'] ?? '') ?></span>
                            </div>

                            <div class="sd-info-field">
                                <label>Room type</label>
                                <span><?= htmlspecialchars($room['type'] ?? '') ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- FEES -->
                    <div class="sd-info-card">
                        <h3>Fees Status</h3>

                        <div class="sd-info-grid sd-cols-4">
                            <div class="sd-info-field">
                                <label>Total</label>
                                <span><?= htmlspecialchars($fees['total'] ?? '') ?></span>
                            </div>

                            <div class="sd-info-field">
                                <label>Paid</label>
                                <span><?= htmlspecialchars($fees['paid'] ?? '') ?></span>
                            </div>

                            <div class="sd-info-field">
                                <label>Pending</label>
                                <span><?= htmlspecialchars($fees['pending'] ?? '') ?></span>
                            </div>

                            <div class="sd-info-field">
                                <label>Status</label>
                                <span class="sd-badge">
                                    <?= htmlspecialchars($fees['status'] ?? '') ?>
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- COMPLAINTS -->
            <div class="sd-page" id="page-complaints">

                <div class="sd-blue-panel">

                    <div class="sd-complaint-hero">
                        <div class="sd-complaint-hero-text">
                            <h2>Something not right in your living space?</h2>
                            <p>Log your concern and we’ll fix it quickly.</p>
                        </div>

                        <button class="sd-write-btn" id="openComplaintForm">
                            WRITE YOUR COMPLAINT +
                        </button>
                    </div>

                    <div class="sd-complaint-list-box">

                        <div class="sd-complaint-list-header">
                            <div class="sd-section-pill">Your complaints</div>

                            <button class="sd-trash-btn" id="deleteComplaint">
                                <i class="ph ph-trash"></i>
                            </button>
                        </div>

                        <div class="sd-complaint-col-headers">
                            <span></span>
                            <span>Issue</span>
                            <span>Description</span>
                            <span>Room</span>
                            <span>Status</span>
                        </div>

                        <div id="sd-complaints-list">

                            <?php if (!empty($complaints)): ?>
                                <?php foreach ($complaints as $c): ?>
                                    <div class="sd-complaint-item">

                                        <input type="radio" name="selected-complaint" value="<?= $c['id'] ?>">

                                        <span class="sd-c-title">
                                            <?= htmlspecialchars($c['title']) ?>
                                        </span>

                                        <span class="sd-c-desc">
                                            <?= htmlspecialchars($c['description']) ?>
                                        </span>

                                        <span class="sd-c-room">
                                            <?= htmlspecialchars($c['room_number']) ?>
                                        </span>

                                        <span class="sd-badge">
                                            <?= htmlspecialchars($c['status']) ?>
                                        </span>

                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No complaints found.</p>
                            <?php endif; ?>

                        </div>

                    </div>

                </div>
            </div>

            <!-- NOTICE -->
            <div class="sd-page" id="page-notice">

                <div class="sd-blue-panel">

                    <div class="sd-notice-header">
                        <h3>NOTICES & ANNOUNCEMENTS</h3>
                        <p>Stay updated with hostel news</p>
                    </div>

                    <?php if (!empty($notices)): ?>
                        <?php foreach ($notices as $n): ?>
                            <div class="sd-notice-card">

                                <div class="sd-notice-meta">
                                    <span><?= htmlspecialchars($n['date']) ?> | <?= htmlspecialchars($n['time']) ?></span>
                                    <span><?= htmlspecialchars($n['author']) ?></span>
                                </div>

                                <h4><?= htmlspecialchars($n['title']) ?></h4>

                                <div class="sd-notice-body">
                                    <?= htmlspecialchars($n['description']) ?>
                                </div>

                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No notices available.</p>
                    <?php endif; ?>

                </div>
            </div>

        </main>
    </div>
</div>

<!-- MODAL -->
<div class="sd-modal-overlay" id="complaintModal">
<div class="sd-modal-box">

<h2>Write your complaints</h2>

<form method="POST" action="/HostelManagementSystem/index.php?page=complaint_add">

    <!-- NAME + ROOM -->
    <div class="sd-form-row">

        <div class="sd-form-group">
            <label>Student name</label>
            <input type="text"
                value="<?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>"
                readonly>
        </div>

        <div class="sd-form-group">
            <label>Room number</label>
            <input type="text"
                name="room_number"
                value="<?= htmlspecialchars($room['number']) ?>"
                readonly>
        </div>

    </div>

    <!-- ISSUE -->
    <div class="sd-form-group">
        <label>Issue</label>
        <input type="text" name="title" placeholder="Topic" required>
    </div>

    <!-- DESCRIPTION -->
    <div class="sd-form-group">
        <label>Description</label>
        <textarea name="description"
            placeholder="Provide as much detail as possible..." required></textarea>
    </div>

    <!-- BUTTONS -->
    <div class="sd-form-actions">
        <button id="closeComplaintForm" class="sd-btn-back">Back</button>
        <button id="submitComplaint" type="button" class="sd-btn-submit">Submit</button>
    </div>

</form>

</div>
</div>

<script src="/HostelManagementSystem/public/js/student.js"></script>
</body>
</html>