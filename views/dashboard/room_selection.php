<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only students who are logged in but have NO room assigned reach this page
if (
    empty($_SESSION['logged_in']) ||
    ($_SESSION['user_role'] ?? '') !== 'student'
) {
    header("Location: " . BASE_URL . "index.php?action=login");
    exit;
}

// If student already has a room, send them straight to dashboard
if (!empty($_SESSION['room_assigned'])) {
    header("Location: " . BASE_URL . "index.php?action=student_dashboard");
    exit;
}

$preferred = $preferredType ?? 'single';   
$rooms     = $availableRooms ?? [];       
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Your Room – Pentatonic Hostel</title>

    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/student.css">
</head>
<body class="rs-body">

<div class="rs-wrap">

    <!-- Logo strip -->
    <div class="rs-topbar">
        <img src="<?= BASE_URL ?>public/images/logo.png" alt="Logo">
        <span>Pentatonic Hostel</span>
    </div>

    <div class="rs-container">

        <!-- LEFT: room list -->
        <div class="rs-left">
            <div class="rs-list-header">Room no.</div>

            <div class="rs-room-list" id="roomList">
                <?php if (empty($rooms)): ?>
                    <p class="rs-no-rooms">No <?= htmlspecialchars($preferred) ?> rooms available.<br>Please contact the warden.</p>
                <?php else: ?>
                    <?php foreach ($rooms as $i => $r): ?>
                        <button
                            class="rs-room-pill <?= $i === 0 ? 'active' : '' ?>"
                            data-room-id="<?= $r['id'] ?>"
                            data-room-number="<?= htmlspecialchars($r['number']) ?>"
                            data-room-type="<?= htmlspecialchars($r['type']) ?>"
                            data-slot1="<?= $r['student1_id'] ? 'occupied' : 'empty' ?>"
                            data-slot2="<?= ($r['type'] === 'double') ? ($r['student2_id'] ? 'occupied' : 'empty') : 'na' ?>"
                        >
                            <?= htmlspecialchars($r['number']) ?>
                        </button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- RIGHT: room detail panel -->
        <div class="rs-right">
            <div class="rs-title-bar" id="roomTitle">
                SELECT YOUR ROOM
            </div>

            <?php if (!empty($rooms)): ?>
            <div class="rs-detail" id="roomDetail">

                <div class="rs-room-label" id="roomLabel">
                    Room number <?= htmlspecialchars($rooms[0]['number']) ?>
                </div>
                <div class="rs-room-type-label" id="roomTypeLabel">
                    <?= htmlspecialchars($rooms[0]['type']) ?> sitter room
                </div>

                <!-- Bed visual -->
                <div class="rs-beds-wrap" id="bedsWrap">
                    <!-- JS fills this -->
                </div>

                <!-- Hidden form -->
                <form method="POST" action="<?= BASE_URL ?>index.php?action=save_room" id="roomForm">
                    <input type="hidden" name="room_id"  id="inputRoomId"   value="<?= $rooms[0]['id'] ?>">
                    <input type="hidden" name="bed_slot" id="inputBedSlot"  value="">
                    <button type="submit" class="rs-save-btn" id="saveBtn">Save</button>
                </form>

            </div>
            <?php else: ?>
                <div class="rs-detail rs-empty-state">
                    <i class="ph ph-bed"></i>
                    <p>No rooms available for your room type.<br>Contact the warden to be assigned manually.</p>
                    <a href="<?= BASE_URL ?>index.php?action=logout" class="rs-save-btn" style="text-decoration:none;display:inline-block;">Sign out</a>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
const rooms = <?= json_encode($rooms, JSON_HEX_TAG) ?>;
const preferred = <?= json_encode($preferred) ?>;

const pillList      = document.getElementById('roomList');
const roomLabel     = document.getElementById('roomLabel');
const roomTypeLabel = document.getElementById('roomTypeLabel');
const bedsWrap      = document.getElementById('bedsWrap');
const inputRoomId   = document.getElementById('inputRoomId');
const inputBedSlot  = document.getElementById('inputBedSlot');

/* helpers */
function bedSVG(occupied) {
    const color = occupied ? '#e05252' : '#222';
    return `<svg viewBox="0 0 120 70" xmlns="http://www.w3.org/2000/svg" class="rs-bed-svg">
        <!-- frame -->
        <rect x="5"  y="28" width="110" height="36" rx="4" fill="none" stroke="${color}" stroke-width="5"/>
        <!-- headboard -->
        <rect x="5"  y="10" width="18"  height="54" rx="3" fill="none" stroke="${color}" stroke-width="5"/>
        <!-- pillow -->
        <rect x="28" y="34" width="30"  height="20" rx="4" fill="none" stroke="${color}" stroke-width="3"/>
        <!-- legs -->
        <rect x="5"  y="58" width="8"   height="10" rx="2" fill="${color}"/>
        <rect x="108" y="58" width="8"  height="10" rx="2" fill="${color}"/>
    </svg>`;
}

function statusLabel(occupied) {
    return `<div class="rs-bed-status ${occupied ? 'occupied' : 'empty'}">
        ${occupied ? 'OCCUPIED' : 'EMPTY'}
    </div>`;
}

function renderBeds(room) {
    const s1 = room.student1_id ? true : false;
    const s2 = room.student2_id ? true : false;

    if (room.type === 'single') {
        bedsWrap.innerHTML = `
            <div class="rs-bed-block">
                ${statusLabel(s1)}
                ${bedSVG(s1)}
            </div>`;
        // For single: bed_slot is always 'student1'
        if (!s1) {
            inputBedSlot.value = 'student1';
        } else {
            inputBedSlot.value = '';   
        }
    } else {
        bedsWrap.innerHTML = `
            <div class="rs-bed-block">
                ${statusLabel(s1)}
                ${bedSVG(s1)}
            </div>
            <div class="rs-bed-block">
                ${statusLabel(s2)}
                ${bedSVG(s2)}
            </div>`;
        // auto-pick first empty slot
        if (!s1)      inputBedSlot.value = 'student1';
        else if (!s2) inputBedSlot.value = 'student2';
        else          inputBedSlot.value = '';
    }

    // Disable Save if no free slot
    const saveBtn = document.getElementById('saveBtn');
    if (!inputBedSlot.value) {
        saveBtn.disabled = true;
        saveBtn.style.opacity = '0.4';
    } else {
        saveBtn.disabled = false;
        saveBtn.style.opacity = '1';
    }
}

function selectRoom(room) {
    roomLabel.textContent      = 'Room number ' + room.number;
    roomTypeLabel.textContent  = room.type + ' sitter room';
    inputRoomId.value          = room.id;
    renderBeds(room);
}

/* init */
if (rooms.length > 0) {
    selectRoom(rooms[0]);
}

/* pill clicks */
pillList.addEventListener('click', function(e) {
    const btn = e.target.closest('.rs-room-pill');
    if (!btn) return;

    document.querySelectorAll('.rs-room-pill').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');

    const room = rooms.find(r => r.id == btn.dataset.roomId);
    if (room) selectRoom(room);
});
</script>
</body>
</html>