<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (
    empty($_SESSION['logged_in']) ||
    ($_SESSION['user_role'] ?? '') !== 'student'
) {
    header("Location: " . BASE_URL . "index.php?action=login");
    exit;
}

// Show room selection modal if no room assigned yet
$showRoomModal  = empty($room);
$availableRooms = [];
$preferredType  = 'single';
$initialRoomIndex = 0;
$sdFlash = $_SESSION['sd_flash'] ?? null;
unset($_SESSION['sd_flash']);
$timingCheckIn = !empty($timing['check_in']) ? date('Y-m-d\TH:i', strtotime($timing['check_in'])) : '';
$timingCheckOut = !empty($timing['check_out']) ? date('Y-m-d\TH:i', strtotime($timing['check_out'])) : '';
$timingStatus = $timing['status'] ?? (!empty($timingCheckOut) ? 'OUT' : 'IN');

if ($showRoomModal) {
    $preferredType  = $student['preferred_room_type'] ?? 'single';
    $roomModel      = new Room($GLOBALS['pdo']);
    $availableRooms = $roomModel->getByTypeWithOccupancy($preferredType);

    foreach ($availableRooms as $idx => $candidateRoom) {
        $candidateFull = (($candidateRoom['type'] ?? '') === 'single' && !empty($candidateRoom['student1_id']))
            || (($candidateRoom['type'] ?? '') === 'double' && !empty($candidateRoom['student1_id']) && !empty($candidateRoom['student2_id']));

        if (!$candidateFull) {
            $initialRoomIndex = $idx;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard – Pentatonic Hostel</title>
    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/student.css">
</head>

<body>
<div class="sd-page-wrap">
<div class="sd-body-row">

    <!-- SIDEBAR -->
    <aside class="sd-sidebar">
        <div class="sd-sidebar-logo" id="goDashboard" style="cursor:pointer;"
             onclick="window.location.href='<?= BASE_URL ?>index.php?action=student_dashboard'">
            <img src="<?= BASE_URL ?>public/images/logo.png" alt="Logo">
            <span>Pentatonic hostel</span>
        </div>

        <div class="sd-sidebar-profile">
            <div class="sd-avatar">
                <?php
                    $photo = $student['profile_photo'] ?? '';
                    if (!empty($photo)) {
                        $photo = ltrim($photo, '/');
                        if (strpos($photo, 'public/') === 0)       $src = BASE_URL . htmlspecialchars($photo);
                        elseif (strpos($photo, 'uploads/') === 0)  $src = BASE_URL . 'public/' . htmlspecialchars($photo);
                        else                                        $src = BASE_URL . 'public/uploads/' . htmlspecialchars($photo);
                    } else {
                        $src = BASE_URL . 'public/images/default-avatar.png';
                    }
                ?>
                <img src="<?= $src ?>" alt="Student"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <span class="sd-avatar-fallback" style="display:none;">
                    <i class="ph-fill ph-user"></i>
                </span>
            </div>
            <div class="sd-student-name">
                <?= htmlspecialchars($student['first_name'] ?? '') ?>
                <?= htmlspecialchars($student['last_name']  ?? '') ?>
            </div>
            <div class="sd-student-role" style="margin-top:4px;">ID #<?= (int) ($student['id'] ?? 0) ?></div>
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
            <button class="sd-nav-btn" data-page="timing">
                <i class="ph ph-clock"></i> Timing
            </button>
            <button class="sd-nav-btn" data-page="profile">
                <i class="ph ph-user-circle"></i> My Profile
            </button>
        </nav>

        <div class="sd-sidebar-spacer"></div>

        <button class="sd-signout-btn"
                onclick="window.location.href='<?= BASE_URL ?>index.php?action=logout'">
            <i class="ph ph-sign-out"></i> Sign out
        </button>
    </aside>

    <!-- MAIN -->
    <main class="sd-main">

        <div class="sd-welcome-card">
            <div>
                <h1>WELCOME, <?= strtoupper(htmlspecialchars($student['first_name'] ?? '')) ?></h1>
                <p>Feel like home</p>
                <div style="margin-top:8px;font-size:12px;color:#667085;">Student ID #<?= (int) ($student['id'] ?? 0) ?></div>
            </div>
            <div class="sd-joined-badge">
                Joined <?= htmlspecialchars(date("F j, Y", strtotime($student['created_at']))) ?>
            </div>
        </div>

        <!-- PAGE: DASHBOARD -->
        <div class="sd-page active" id="page-dashboard">
        <div class="sd-blue-panel">

            <!-- Student Details -->
            <div class="sd-info-card">
                <h3>Student details</h3>
                <div class="sd-info-grid sd-cols-3">
                    <div class="sd-info-field">
                        <label>Student ID</label>
                        <span>#<?= (int) ($student['id'] ?? 0) ?></span>
                    </div>
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

            <!-- Room Details — layout depends on room type -->
            <?php if (!empty($room)): ?>
                <?php $isDouble = strtolower($room['type'] ?? '') === 'double'; ?>

                <?php if ($isDouble): ?>
                <!-- DOUBLE SITTER: left panel + right panel -->
                <div class="sd-room-outer">
                    <div class="sd-room-left-wrap">
                        <div class="sd-info-card sd-room-left-card">
                            <div>
                                <h3>Room details</h3>
                                <div class="sd-info-field" style="margin-top:12px;">
                                    <label>Room number</label>
                                    <span><?= htmlspecialchars($room['number'] ?? '—') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sd-room-right-card">
                        <div class="sd-info-field">
                            <label>Room type</label>
                            <span><?= htmlspecialchars($room['type'] ?? '—') ?> room</span>
                        </div>
                        <div class="sd-info-field">
                            <label>Roommate</label>
                            <span><?= !empty($room['roommate_names']) ? htmlspecialchars($room['roommate_names']) : '—' ?></span>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <!-- SINGLE: standard card -->
                <div class="sd-info-card">
                    <h3>Room details</h3>
                    <div class="sd-info-grid sd-cols-3">
                        <div class="sd-info-field">
                            <label>Room number</label>
                            <span><?= htmlspecialchars($room['number'] ?? '—') ?></span>
                        </div>
                        <div class="sd-info-field">
                            <label>Room type</label>
                            <span><?= htmlspecialchars($room['type'] ?? '—') ?> room</span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="sd-info-card">
                    <h3>Room details</h3>
                    <p style="font-size:13px;color:#888;">No room assigned yet.</p>
                </div>
            <?php endif; ?>

            <!-- Fees Status -->
            <div class="sd-info-card">
                <h3>Fees Status</h3>
                <?php if (!empty($fees)): ?>
                <div class="sd-info-grid sd-cols-4">
                    <div class="sd-info-field">
                        <label>Total</label>
                        <span>Rs. <?= htmlspecialchars(number_format($fees['total'], 2)) ?></span>
                    </div>
                    <div class="sd-info-field">
                        <label>Paid</label>
                        <span>Rs. <?= htmlspecialchars(number_format($fees['paid'], 2)) ?></span>
                    </div>
                    <div class="sd-info-field">
                        <label>Pending</label>
                        <span>Rs. <?= htmlspecialchars(number_format($fees['pending'], 2)) ?></span>
                    </div>
                    <div class="sd-info-field">
                        <label>Status</label>
                        <?php
                            $status = $fees['status'] ?? '';
                            $badgeClass = match(strtolower($status)) {
                                'overdue' => 'badge-overdue',
                                'pending' => 'badge-pending',
                                'paid'    => 'badge-paid',
                                default   => ''
                            };
                        ?>
                        <span class="sd-badge <?= $badgeClass ?>"><?= htmlspecialchars($status) ?></span>
                    </div>
                </div>
                <?php else: ?>
                    <p style="font-size:13px;color:#888;">No fee record found.</p>
                <?php endif; ?>
            </div>

        </div>
        </div>

        <!-- PAGE: COMPLAINTS -->
        <div class="sd-page" id="page-complaints">
        <div class="sd-blue-panel">
            <div class="sd-complaint-hero">
                <div class="sd-complaint-hero-text">
                    <h2>Something not right in your living space?</h2>
                    <p>Log your concern and we'll fix it quickly.</p>
                </div>
                <button class="sd-write-btn" id="openComplaintForm">WRITE YOUR COMPLAINT +</button>
            </div>

            <div class="sd-complaint-list-box">
                <div class="sd-complaint-list-header">
                    <div class="sd-section-pill">Your complaints</div>
                    <button class="sd-trash-btn" id="deleteComplaint"><i class="ph ph-trash"></i></button>
                </div>
                <div class="sd-complaint-col-headers">
                    <span></span><span>Issue</span><span>Description</span><span>Room</span><span>Status</span>
                </div>
                <div id="sd-complaints-list">
                    <?php if (!empty($complaints)): ?>
                        <?php foreach ($complaints as $c): ?>
                            <div class="sd-complaint-item">
                                <input type="radio" name="selected-complaint" value="<?= $c['id'] ?>">
                                <span class="sd-c-title"><?= htmlspecialchars($c['title']) ?></span>
                                <span class="sd-c-desc"><?= htmlspecialchars($c['description']) ?></span>
                                <span class="sd-c-room"><?= htmlspecialchars($c['room_number'] ?? '—') ?></span>
                                <span class="sd-badge"><?= htmlspecialchars($c['status']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="font-size:13px;color:#888;padding:12px 8px;">No complaints found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        </div>

        <!-- PAGE: NOTICE -->
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
                        <div class="sd-notice-body"><?= htmlspecialchars($n['description']) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="font-size:13px;color:#888;padding:12px;">No notices available.</p>
            <?php endif; ?>
        </div>
        </div>

        <!-- PAGE: TIMING -->
        <div class="sd-page" id="page-timing">
        <div class="sd-blue-panel">
            <div class="sd-timing-head">
                <div>
                    <h3>CHECK IN & CHECK OUT</h3>
                    <p>Keep your hostel movement timing updated.</p>
                </div>
                <span class="sd-timing-status <?= strtolower($timingStatus) === 'out' ? 'is-out' : 'is-in' ?>" id="studentTimingStatus">
                    <?= htmlspecialchars($timingStatus) ?>
                </span>
            </div>

            <form class="sd-timing-card" id="studentTimingForm">
                <div class="sd-timing-grid">
                    <label class="sd-timing-field">
                        <span>Check out</span>
                        <input type="datetime-local" name="check_out" value="<?= htmlspecialchars($timingCheckOut) ?>">
                    </label>
                    <label class="sd-timing-field">
                        <span>Check in</span>
                        <input type="datetime-local" name="check_in" value="<?= htmlspecialchars($timingCheckIn) ?>">
                    </label>
                </div>
                <div class="sd-timing-summary">
                    <div>
                        <span>Last check out</span>
                        <strong id="studentTimingOutText">
                            <?= !empty($timing['check_out']) ? htmlspecialchars(date('F j, Y g:i A', strtotime($timing['check_out']))) : 'Not set' ?>
                        </strong>
                    </div>
                    <div>
                        <span>Last check in</span>
                        <strong id="studentTimingInText">
                            <?= !empty($timing['check_in']) ? htmlspecialchars(date('F j, Y g:i A', strtotime($timing['check_in']))) : 'Not set' ?>
                        </strong>
                    </div>
                </div>
                <div class="sd-timing-actions">
                    <button type="submit" class="sd-btn-submit">Save timing</button>
                </div>
            </form>
        </div>
        </div>

        <!-- PAGE: PROFILE -->
        <div class="sd-page" id="page-profile">
        <div class="sd-blue-panel">
            <div class="sd-profile-head">
                <div class="sd-profile-photo">
                    <img src="<?= $src ?>" alt="Student profile photo"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <span class="sd-avatar-fallback" style="display:none;">
                        <i class="ph-fill ph-user"></i>
                    </span>
                </div>
                <div>
                    <h2><?= htmlspecialchars(trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''))) ?></h2>
                    <p><?= htmlspecialchars($student['email'] ?? '') ?></p>
                </div>
            </div>

            <form class="sd-profile-form" id="studentProfileForm" method="POST" action="<?= BASE_URL ?>index.php?action=student_profile_update" enctype="multipart/form-data">
                <div class="sd-profile-section">
                    <h3>Personal information</h3>
                    <div class="sd-profile-grid">
                        <div class="sd-form-group">
                            <label>First name</label>
                            <input type="text" name="first_name" value="<?= htmlspecialchars($student['first_name'] ?? '') ?>" required>
                        </div>
                        <div class="sd-form-group">
                            <label>Middle name</label>
                            <input type="text" name="middle_name" value="<?= htmlspecialchars($student['middle_name'] ?? '') ?>">
                        </div>
                        <div class="sd-form-group">
                            <label>Last name</label>
                            <input type="text" name="last_name" value="<?= htmlspecialchars($student['last_name'] ?? '') ?>" required>
                        </div>
                        <div class="sd-form-group">
                            <label>Contact number</label>
                            <input type="tel" name="contact_number" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" value="<?= htmlspecialchars($student['contact_number'] ?? '') ?>" required>
                        </div>
                        <div class="sd-form-group">
                            <label>Email</label>
                            <input type="email" value="<?= htmlspecialchars($student['email'] ?? '') ?>" readonly>
                        </div>
                        <div class="sd-form-group">
                            <label>Joined date</label>
                            <input type="text" value="<?= !empty($student['created_at']) ? htmlspecialchars(date('F j, Y', strtotime($student['created_at']))) : '—' ?>" readonly>
                        </div>
                        <div class="sd-form-group full">
                            <label>Permanent address</label>
                            <textarea name="permanent_address" rows="3" required><?= htmlspecialchars($student['permanent_address'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="sd-profile-section">
                    <h3>Hostel registration details</h3>
                    <div class="sd-profile-grid">
                        <div class="sd-form-group">
                            <label>College name</label>
                            <input type="text" name="college_name" value="<?= htmlspecialchars($student['college_name'] ?? '') ?>" required>
                        </div>
                        <div class="sd-form-group">
                            <label>Preferred room type</label>
                            <input type="text" value="<?= htmlspecialchars($student['preferred_room_type'] ?? '—') ?>" readonly>
                        </div>
                        <div class="sd-form-group">
                            <label>Current room</label>
                            <input type="text" value="<?= htmlspecialchars($room['number'] ?? 'Not assigned') ?>" readonly>
                        </div>
                        <div class="sd-form-group">
                            <label>Room type</label>
                            <input type="text" value="<?= htmlspecialchars($room['type'] ?? 'Not assigned') ?>" readonly>
                        </div>
                    </div>
                </div>

                <div class="sd-profile-section">
                    <h3>Guardian details</h3>
                    <div class="sd-profile-grid">
                        <div class="sd-form-group">
                            <label>Guardian name</label>
                            <input type="text" name="guardian_name" value="<?= htmlspecialchars($student['guardian_name'] ?? '') ?>" required>
                        </div>
                        <div class="sd-form-group">
                            <label>Relationship</label>
                            <input type="text" name="guardian_relationship" value="<?= htmlspecialchars($student['guardian_relationship'] ?? '') ?>" required>
                        </div>
                        <div class="sd-form-group">
                            <label>Guardian contact</label>
                            <input type="tel" name="guardian_contact" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" value="<?= htmlspecialchars($student['guardian_contact'] ?? '') ?>" required>
                        </div>
                        <div class="sd-form-group">
                            <label>Profile photo</label>
                            <label class="sd-file-field">
                                <i class="ph ph-upload-simple"></i>
                                <span>Upload new photo</span>
                                <input type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp">
                            </label>
                        </div>
                    </div>
                </div>

                <div class="sd-profile-actions">
                    <button type="submit" class="sd-btn-submit">Save profile</button>
                </div>
            </form>
        </div>
        </div>

    </main>
</div>
</div>

<div class="sd-toast" id="sdToast" role="status" aria-live="polite"></div>

<div class="sd-confirm-overlay" id="studentConfirmDialog" aria-hidden="true">
<div class="sd-confirm-box" role="dialog" aria-modal="true" aria-labelledby="studentConfirmTitle">
    <i class="ph ph-warning-circle sd-confirm-icon"></i>
    <h3 id="studentConfirmTitle">Are you sure you want to delete the complaint?</h3>
    <p id="studentConfirmMessage" hidden></p>
    <div class="sd-confirm-actions">
        <button type="button" class="sd-confirm-cancel" id="studentConfirmCancel">Cancel</button>
        <button type="button" class="sd-confirm-ok" id="studentConfirmOk">Delete</button>
    </div>
</div>
</div>

<!-- COMPLAINT MODAL -->
<div class="sd-modal-overlay" id="complaintModal">
<div class="sd-modal-box">
    <h2>Write your complaints</h2>
    <form id="complaintForm">
        <div class="sd-form-row">
            <div class="sd-form-group">
                <label>Student name</label>
                <input type="text" value="<?= htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?>" readonly>
            </div>
            <div class="sd-form-group">
                <label>Room number</label>
                <input type="text" name="room_number" value="<?= htmlspecialchars($room['number'] ?? '') ?>" readonly>
            </div>
        </div>
        <div class="sd-form-group">
            <label>Issue</label>
            <input type="text" name="title" placeholder="Topic" required>
        </div>
        <div class="sd-form-group">
            <label>Description</label>
            <textarea name="description" placeholder="Provide as much detail as possible..." required></textarea>
        </div>
        <div class="sd-form-actions">
            <button id="closeComplaintForm" type="button" class="sd-btn-back">Back</button>
            <button id="submitComplaint"    type="button" class="sd-btn-submit">Submit</button>
        </div>
    </form>
</div>
</div>

<?php if ($showRoomModal): ?>
    <?php include __DIR__ . '/room_selection.php'; ?>
<?php endif; ?>

<script>
    const BASE_URL = "<?= BASE_URL ?>";
    const RS_ROOMS = <?= json_encode($availableRooms ?? [], JSON_HEX_TAG) ?>;
    const RS_SHOW  = <?= $showRoomModal ? 'true' : 'false' ?>;
    const RS_INITIAL_ROOM_ID = <?= json_encode($availableRooms[$initialRoomIndex]['id'] ?? null) ?>;
    const SD_FLASH = <?= json_encode($sdFlash, JSON_HEX_TAG) ?>;
    const SD_INITIAL_TAB = <?= json_encode($_GET['tab'] ?? 'dashboard') ?>;
</script>
<script src="<?= BASE_URL ?>public/js/student.js"></script>
</body>
</html>
