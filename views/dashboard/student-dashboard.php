<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$studentName = "Abibsha Ghaju";
$joinedDate  = "Jan 15, 2024";
$roomType    = "single"; // "single" or "double"

$student = [
    "first_name"  => "Abibsha",
    "middle_name" => "----",
    "last_name"   => "Ghaju",
];

$room = [
    "number"      => "A34",
    "floor_block" => "3rd floor, Block A",
    "type"        => ($roomType === "double") ? "Double sitter" : "Single sitter",
    "roommate"    => "Full name",
];

$fees = [
    "total"   => "$670.00",
    "paid"    => "$500.00",
    "pending" => "$170.00",
    "status"  => "Overdue",
];

$complaints = [
    [
        "title"       => "Water Leakage",
        "description" => "Bathroom tap leaking continuously for 3 days",
        "room"        => "A34",
        "status"      => "Pending",
    ],
];

$notices = [
    [
        "title"       => "Laundry Room Operating Hours",
        "description" => "Following student feedback, the communal laundry room will now remain open until midnight on Fridays and Saturdays.",
        "date"        => "24 OCT 2026",
        "time"        => "10:00 AM",
        "author"      => "HOSTEL MANAGEMENT",
    ],
    [
        "title"       => "Updated Guest Policy",
        "description" => "New guidelines for overnight visitors have been published on the portal. Please review to avoid penalties.",
        "date"        => "24 OCT 2026",
        "time"        => "10:00 AM",
        "author"      => "HOSTEL MANAGEMENT",
    ],
];

function badgeClass($status) {
    switch (strtolower($status)) {
        case "paid":    return "badge-paid";
        case "overdue": return "badge-overdue";
        default:        return "badge-pending";
    }
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

    <!-- BODY ROW -->
    <div class="sd-body-row">

        <!-- SIDEBAR: logo at top, then profile, nav, sign out -->
        <aside class="sd-sidebar">

            <!-- Logo + hostel name: at the very top of sidebar -->
            <div class="sd-sidebar-logo" id="goDashboard" style="cursor: pointer;">
                <img src="/HostelManagementSystem/public/images/logo.png" alt="Pentatonic Hostel Logo">
                <span>Pentatonic hostel</span>
            </div>

            <!-- Avatar + name + role -->
            <div class="sd-sidebar-profile">
                <div class="sd-avatar">
                    <img src="/HostelManagementSystem/public/images/student-photo.jpg"
                         alt="<?= htmlspecialchars($studentName) ?>"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <span class="sd-avatar-fallback" style="display:none;">
                        <i class="ph-fill ph-user"></i>
                    </span>
                </div>
                <div class="sd-student-name"><?= htmlspecialchars($studentName) ?></div>
                <div class="sd-student-role">STUDENT</div>
            </div>

            <!-- Nav: stacked top to bottom -->
            <nav class="sd-sidebar-nav">
                <button class="sd-nav-btn active" data-page="dashboard">
                    <i class="ph-fill ph-squares-four"></i>
                    Dashboard
                </button>
                <button class="sd-nav-btn" data-page="complaints">
                    <i class="ph ph-megaphone"></i>
                    Complaints
                </button>
                <button class="sd-nav-btn" data-page="notice">
                    <i class="ph ph-warning"></i>
                    Notice
                </button>
            </nav>

            <div class="sd-sidebar-spacer"></div>

            <button class="sd-signout-btn"
                    onclick="window.location.href='/HostelManagementSystem/views/auth/login.php'">
                <i class="ph ph-sign-out"></i>
                Sign out
            </button>

        </aside>

        <!-- MAIN CONTENT -->
        <main class="sd-main">

            <!-- Welcome banner -->
            <div class="sd-welcome-card">
                <div>
                    <h1>WELCOME, &nbsp;<?= strtoupper(htmlspecialchars($studentName)) ?></h1>
                    <p>Feel like home</p>
                </div>
                <div class="sd-joined-badge">Joined <?= htmlspecialchars($joinedDate) ?></div>
            </div>

            <!-- PAGE: DASHBOARD -->
            <div class="sd-page active" id="page-dashboard">
                <div class="sd-blue-panel">

                    <!-- Student Details -->
                    <div class="sd-info-card">
                        <h3>Student details</h3>
                        <div class="sd-info-grid sd-cols-3">
                            <div class="sd-info-field">
                                <label>First name</label>
                                <span><?= htmlspecialchars($student["first_name"]) ?></span>
                            </div>
                            <div class="sd-info-field">
                                <label>Middle name</label>
                                <span><?= htmlspecialchars($student["middle_name"]) ?></span>
                            </div>
                            <div class="sd-info-field">
                                <label>Last name</label>
                                <span><?= htmlspecialchars($student["last_name"]) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Room Details -->
                    <?php if ($roomType === "double"): ?>

                    <div class="sd-room-outer">
                        <div class="sd-info-card sd-room-left-wrap">
                            <h3>Room details</h3>
                            <div class="sd-room-left-card">
                                <div class="sd-info-field">
                                    <label>Room number</label>
                                    <span><?= htmlspecialchars($room["number"]) ?></span>
                                </div>
                                <div class="sd-info-field">
                                    <label>Floor/ Block</label>
                                    <span><?= htmlspecialchars($room["floor_block"]) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="sd-room-right-card">
                            <div class="sd-info-field">
                                <label>Room type</label>
                                <span><?= htmlspecialchars($room["type"]) ?></span>
                            </div>
                            <div class="sd-info-field">
                                <label>Roommate</label>
                                <span><?= htmlspecialchars($room["roommate"]) ?></span>
                            </div>
                        </div>
                    </div>

                    <?php else: ?>

                    <div class="sd-info-card">
                        <h3>Room details</h3>
                        <div class="sd-info-grid sd-cols-3">
                            <div class="sd-info-field">
                                <label>Room number</label>
                                <span><?= htmlspecialchars($room["number"]) ?></span>
                            </div>
                            <div class="sd-info-field">
                                <label>Floor/ Block</label>
                                <span><?= htmlspecialchars($room["floor_block"]) ?></span>
                            </div>
                            <div class="sd-info-field">
                                <label>Room type</label>
                                <span><?= htmlspecialchars($room["type"]) ?></span>
                            </div>
                        </div>
                    </div>

                    <?php endif; ?>

                    <!-- Fees Status -->
                    <div class="sd-info-card">
                        <h3>Fees Status</h3>
                        <div class="sd-info-grid sd-cols-4">
                            <div class="sd-info-field">
                                <label>Total fees</label>
                                <span><?= htmlspecialchars($fees["total"]) ?></span>
                            </div>
                            <div class="sd-info-field">
                                <label>Paid amount</label>
                                <span><?= htmlspecialchars($fees["paid"]) ?></span>
                            </div>
                            <div class="sd-info-field">
                                <label>Pending amount</label>
                                <span><?= htmlspecialchars($fees["pending"]) ?></span>
                            </div>
                            <div class="sd-info-field">
                                <label>Status</label>
                                <span class="sd-badge <?= badgeClass($fees["status"]) ?>">
                                    <?= htmlspecialchars($fees["status"]) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- PAGE: COMPLAINTS -->
            <div class="sd-page" id="page-complaints">
                <div class="sd-blue-panel">

                    <div class="sd-complaint-hero">
                        <div class="sd-complaint-hero-text">
                            <h2>Something not right in your living space?</h2>
                            <p>Our maintenance atelier is ready to assist. Log your concern, and we'll ensure your residence remains a curated sanctuary for your studies.</p>
                        </div>
                        <button class="sd-write-btn" id="openComplaintForm">
                            WRITE YOUR COMPLAINT &nbsp;+
                        </button>
                    </div>

                    <div class="sd-complaint-list-box">
                        <div class="sd-complaint-list-header">
                            <div class="sd-section-pill">Your previous complaints</div>
                            <button class="sd-trash-btn" id="deleteComplaint" title="Delete selected">
                                <i class="ph ph-trash"></i>
                            </button>
                        </div>

                        <div class="sd-complaint-col-headers">
                            <span></span>
                            <span>Issue</span>
                            <span>Description</span>
                            <span>Room Number</span>
                            <span>Status</span>
                        </div>

                        <div id="sd-complaints-list">
                            <?php foreach ($complaints as $c): ?>
                            <div class="sd-complaint-item">
                                <input type="radio" name="selected-complaint">
                                <span class="sd-c-title"><?= htmlspecialchars($c["title"]) ?></span>
                                <span class="sd-c-desc"><?= htmlspecialchars($c["description"]) ?></span>
                                <span class="sd-c-room"><?= htmlspecialchars($c["room"]) ?></span>
                                <span class="sd-badge <?= badgeClass($c["status"]) ?>">
                                    <?= htmlspecialchars($c["status"]) ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            </div>

            <!-- PAGE: NOTICE -->
            <div class="sd-page" id="page-notice">
                <div class="sd-blue-panel">

                    <div class="sd-notice-header">
                        <h3>NOTICES &amp; ANNOUNCEMENTS</h3>
                        <p>Stay updated with the latest news, policy changes, and maintenance schedules for Pentatonic Hostel.</p>
                    </div>

                    <?php foreach ($notices as $n): ?>
                    <div class="sd-notice-card">
                        <div class="sd-notice-meta">
                            <span>
                                <i class="ph ph-calendar-blank"></i>
                                <?= htmlspecialchars($n["date"]) ?> | <?= htmlspecialchars($n["time"]) ?>
                            </span>
                            <span>
                                <i class="ph ph-user"></i>
                                <?= htmlspecialchars($n["author"]) ?>
                            </span>
                        </div>
                        <h4><?= htmlspecialchars($n["title"]) ?></h4>
                        <div class="sd-notice-body">
                            <?= htmlspecialchars($n["description"]) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <div class="sd-notice-footer">
                        <i class="ph ph-clock-counter-clockwise"></i>
                        <span>Viewing <?= count($notices) ?> notices from the past 7 days</span>
                    </div>

                </div>
            </div>

        </main>
    </div>

</div>

<!-- COMPLAINT MODAL -->
<div class="sd-modal-overlay" id="complaintModal">
    <div class="sd-modal-box">

        <h2>Write your complaints</h2>

        <div class="sd-form-row">
            <div class="sd-form-group">
                <label>Students name</label>
                <input type="text"
                       id="inputStudentName"
                       value="<?= htmlspecialchars($studentName) ?>"
                       placeholder="Your name">
            </div>
            <div class="sd-form-group sd-form-small">
                <label>Room number</label>
                <input type="text"
                       id="inputRoom"
                       value="<?= htmlspecialchars($room["number"]) ?>"
                       placeholder="Room">
            </div>
        </div>

        <div class="sd-form-group" style="margin-bottom: 14px;">
            <label>Issue</label>
            <input type="text" id="inputIssue" placeholder="Brief issue title">
        </div>

        <div class="sd-form-group">
            <label>Description</label>
            <textarea id="inputDesc" placeholder="Description here"></textarea>
        </div>

        <div class="sd-form-actions">
            <button class="sd-btn-back" id="closeComplaintForm">Back</button>
            <button class="sd-btn-submit" id="submitComplaint">Submit</button>
        </div>

    </div>
</div>

<script src="/HostelManagementSystem/public/js/student.js"></script>
</body>
</html>