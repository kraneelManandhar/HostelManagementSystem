<?php include(__DIR__ . '/../layout/header.php'); ?>

<main class="main-content">
    <div class="registration-card">
        <h2>Registration Form</h2>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'phone'): ?>
            <p class="error-msg">Contact numbers must start with 98 or 97 and contain exactly 10 digits.</p>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_token'): ?>
            <p class="error-msg">Invalid or expired verification link. Please register again.</p>
        <?php endif; ?>

        <!-- FIXED: Use BASE_URL instead of relative path -->
        <!-- views/auth/register.php -->
<form action="<?= BASE_URL ?>index.php?action=register_step1" method="POST" enctype="multipart/form-data">
            
           <div class="reg-section">
    <div class="reg-label-side">Personal Identity:</div>
    <div class="reg-input-grid">
        <div class="full-row">
            <label>First name:</label>
            <input type="text" name="first_name" required>
        </div>
        <div>
            <label>Middle name:</label>
            <input type="text" name="middle_name">
        </div>
        <div>
            <label>Last name:</label>
            <input type="text" name="last_name" required>
        </div>
        <div>
            <label>Date of birth:</label>
            <input type="date" name="date_of_birth" required>
        </div>
        <div>
            <label>Contact number:</label>
            <input type="tel" name="contact_number" inputmode="numeric" pattern="(98|97)[0-9]{8}" maxlength="10" title="Enter a 10-digit number starting with 98 or 97" required>
        </div>
        <div class="full-row">
            <label>Email address:</label>
            <input type="email" name="email" required>
        </div>
    </div>
    <div class="photo-box">
        <label>Passport photo:</label>
        <div class="photo-placeholder">
            <i class="ph ph-image-square"></i> </div>
        <input type="file" name="profile_photo" accept="image/*">
    </div>
</div>

<div class="reg-section">
    <div class="reg-label-side">Institutional Details:</div>
    <div class="reg-input-grid">
        <div class="full-row">
            <label>College name:</label>
            <input type="text" name="college_name" required>
        </div>
        <div class="full-row">
            <label>Permanent address:</label>
            <input type="text" name="permanent_address" required>
        </div>
        <div>
            <label>Date of joining:</label>
            <input type="date" name="date_of_joining" required>
        </div>
    </div>
</div>

<div class="reg-section">
    <div class="reg-label-side">Emergency Contact:</div>
    <div class="reg-input-grid">
        <div class="full-row">
            <label>Guardian full name:</label>
            <input type="text" name="guardian_name" required>
        </div>
        <div>
            <label>Relationship:</label>
            <select name="guardian_relationship">
                <option value="Parent">Parent</option>
                <option value="Sibling">Sibling</option>
                <option value="Relative">Relative</option>
                <option value="Other">Other</option>
            </select>
        </div>
        <div>
            <label>Contact number:</label>
            <input type="tel" name="guardian_contact" inputmode="numeric" pattern="(98|97)[0-9]{8}" maxlength="10" title="Enter a 10-digit number starting with 98 or 97" required>
        </div>
    </div>
</div>

<div class="reg-section">
    <div class="reg-label-side">Room type:</div>
    <div class="reg-input-grid">
        <div class="room-options">
            <label class="radio-card">
                <input type="radio" name="preferred_room_type" value="double" checked>
                <div class="card-content">
                    <strong>Double sitter</strong>
                    <span>Shared room with modern amenities.</span>
                </div>
            </label>
            <label class="radio-card">
                <input type="radio" name="preferred_room_type" value="single">
                <div class="card-content">
                    <strong>Single sitter</strong>
                    <span>Private sanctuary for focused growth.</span>
                </div>
            </label>
        </div>
    </div>
</div>

<div class="submit-container">
    <button type="submit" class="main-submit-btn">Continue →</button>
</div>
</form>
</main>

<?php include(__DIR__ . '/../layout/footer.php'); ?>
