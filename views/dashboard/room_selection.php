<?php
$initialRoom = !empty($availableRooms)
    ? ($availableRooms[$initialRoomIndex] ?? $availableRooms[0])
    : null;
?>

<!-- ROOM SELECTION MODAL : shown when student has no room yet.
     Cannot be dismissed; student must pick a room and Save. -->
<div class="rs-overlay" id="roomSelectionModal">
    <div class="rs-modal">
        <div class="rs-topbar">
            <?= strtoupper(htmlspecialchars($preferredType ?? 'ROOM')) ?> ROOM
        </div>

        <div class="rs-title-bar">SELECT YOUR ROOM</div>

        <div class="rs-left">
            <div class="rs-list-header">Room no.</div>
            <div class="rs-room-list" id="rsPillList">
                <?php if (empty($availableRooms)): ?>
                    <p class="rs-no-rooms">No rooms available.<br>Contact the warden.</p>
                <?php else: ?>
                    <?php foreach ($availableRooms as $i => $r): ?>
                        <?php
                            $isFull = (($r['type'] ?? '') === 'single' && !empty($r['student1_id']))
                                || (($r['type'] ?? '') === 'double' && !empty($r['student1_id']) && !empty($r['student2_id']));
                        ?>
                        <button
                            class="rs-room-pill <?= $isFull ? 'is-full' : 'is-available' ?> <?= $i === $initialRoomIndex ? 'active' : '' ?>"
                            data-room-id="<?= $r['id'] ?>"
                            data-s1="<?= $r['student1_id'] ? '1' : '0' ?>"
                            data-s2="<?= $r['student2_id'] ? '1' : '0' ?>"
                            data-type="<?= htmlspecialchars($r['type']) ?>"
                            data-number="<?= htmlspecialchars($r['number']) ?>"
                        ><?= htmlspecialchars($r['number']) ?></button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="rs-right">
            <?php if ($initialRoom): ?>
            <div class="rs-detail">
                <div class="rs-room-label" id="rsRoomLabel">Room number <?= htmlspecialchars($initialRoom['number']) ?></div>
                <div class="rs-room-type-label" id="rsTypeLabel"><?= htmlspecialchars($initialRoom['type']) ?> sitter room</div>
                <div class="rs-beds-wrap" id="rsBedsWrap"></div>

                <form method="POST" action="<?= BASE_URL ?>index.php?action=save_room" id="rsForm">
                    <input type="hidden" name="room_id" id="rsInputRoomId" value="<?= $initialRoom['id'] ?>">
                    <input type="hidden" name="bed_slot" id="rsInputBedSlot" value="">
                    <button type="submit" class="rs-save-btn" id="rsSaveBtn" disabled>Save</button>
                </form>
            </div>
            <?php else: ?>
            <div class="rs-detail rs-empty-state">
                <i class="ph ph-bed"></i>
                <p>No rooms available for your type.<br>Contact the warden to be assigned manually.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
