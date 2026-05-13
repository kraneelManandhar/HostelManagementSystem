<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/HostelManagementSystem/');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form - Pentatonic Hostel</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css?v=3">
    <script src="https://cdn.jsdelivr.net/npm/@phosphor-icons/web"></script>
</head>
<body>

<main class="main-content registration-page">
    <div class="registration-topbar">
        <a class="login-back-btn registration-home-back" href="<?= BASE_URL ?>index.php?action=home" aria-label="Back to home">
            <i class="ph ph-arrow-left" aria-hidden="true"></i>
        </a>

        <a class="registration-brand" href="<?= BASE_URL ?>index.php">
            <img src="<?= BASE_URL ?>public/images/logo.png" alt="Pentatonic Hostel logo">
            <span>Pentatonic hostel</span>
        </a>
    </div>

    <div class="registration-card">
        <h2>Registration</h2>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'phone'): ?>
            <p class="error-msg">Contact numbers must start with 98 or 97 and contain exactly 10 digits.</p>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'email'): ?>
            <p class="error-msg">This email is already registered. Please use another email or login.</p>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'email_invalid'): ?>
            <p class="error-msg">Please enter a valid email address.</p>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'photo'): ?>
            <p class="error-msg">Please upload a valid JPG, PNG, or WEBP passport photo.</p>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_token'): ?>
            <p class="error-msg">Invalid or expired verification link. Please register again.</p>
        <?php endif; ?>

<form action="<?= BASE_URL ?>index.php?action=register_step1" method="POST" enctype="multipart/form-data">
            
           <div class="reg-section">
    <div class="reg-label-side"><i class="ph ph-user"></i> Personal identity</div>
    <div class="reg-input-grid">
        <div>
            <label>First name</label>
            <input type="text" name="first_name" placeholder="e.g. John" required>
        </div>
        <div>
            <label>Middle name</label>
            <input type="text" name="middle_name" placeholder="Optional">
        </div>
        <div>
            <label>Last name</label>
            <input type="text" name="last_name" placeholder="e.g. Doe" required>
        </div>
        <div>
            <label>Date of birth</label>
            <input type="date" name="date_of_birth" required>
        </div>
        <div>
            <label>Contact number</label>
            <input type="tel" name="contact_number" inputmode="numeric" pattern="(98|97)[0-9]{8}" maxlength="10" title="Enter a 10-digit number starting with 98 or 97" placeholder="+977 9841243263" required>
        </div>
        <div>
            <label>Email address</label>
            <input type="email" name="email" placeholder="john.doe@university.edu" required>
        </div>
    </div>
    <div class="photo-box">
        <label>Passport size photo</label>
        <label class="photo-placeholder" id="photoPreview" for="profilePhotoInput">
            <i class="ph ph-camera-plus"></i>
            <span>Upload Photo</span>
            <small>Max 2MB, JPG/PNG</small>
            <img id="photoPreviewImg" alt="Preview of uploaded passport photo">
        </label>
        <input id="profilePhotoInput" type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp">
    </div>
</div>

<div class="reg-section">
    <div class="reg-label-side"><i class="ph ph-graduation-cap"></i> Institutional details</div>
    <div class="reg-input-grid">
        <div class="full-row">
            <label>College name</label>
            <input type="text" name="college_name" placeholder="Enter your current educational institution" required>
        </div>
        <div class="full-row">
            <label>Permanent address</label>
            <input type="text" name="permanent_address" placeholder="Street, City, State, ZIP" required>
        </div>
        <div>
            <label>Date of joining</label>
            <input type="date" name="date_of_joining" required>
        </div>
    </div>
</div>

<div class="reg-section">
    <div class="reg-label-side"><i class="ph ph-asterisk"></i> Emergency contact</div>
    <div class="reg-input-grid">
        <div>
            <label>Relationship</label>
            <select name="guardian_relationship">
                <option value="Parent">Parent</option>
                <option value="Sibling">Sibling</option>
                <option value="Relative">Relative</option>
                <option value="Other">Other</option>
            </select>
        </div>
        <div>
            <label>Full name</label>
            <input type="text" name="guardian_name" placeholder="Full name" required>
        </div>
        <div>
            <label>Contact number</label>
            <input type="tel" name="guardian_contact" inputmode="numeric" pattern="(98|97)[0-9]{8}" maxlength="10" title="Enter a 10-digit number starting with 98 or 97" placeholder="+977 9841243263" required>
        </div>
    </div>
</div>

<div class="reg-section">
    <div class="reg-label-side"><i class="ph ph-bed"></i> Room type:</div>
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
    <button type="submit" class="main-submit-btn">Submit Registration <i class="ph ph-paper-plane-tilt"></i></button>
</div>
    </form>
</main>
<script>
    (function() {
        const fileInput = document.getElementById('profilePhotoInput');
        const preview = document.getElementById('photoPreview');
        const previewImg = document.getElementById('photoPreviewImg');

        if (!fileInput || !preview || !previewImg) {
            return;
        }

        fileInput.addEventListener('change', function() {
            const file = this.files && this.files[0];
            if (!file || !file.type.startsWith('image/')) {
                preview.classList.remove('has-image');
                previewImg.src = '';
                return;
            }

            const objectUrl = URL.createObjectURL(file);
            previewImg.src = objectUrl;
            preview.classList.add('has-image');
            previewImg.onload = function() {
                URL.revokeObjectURL(objectUrl);
            };
        });
    })();
</script>
</body>
</html>
