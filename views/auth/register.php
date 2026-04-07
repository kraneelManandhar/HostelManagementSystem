<?php include(__DIR__ . '/../layout/header.php'); ?>

<main class="main-content">
    <div class="registration-card">
        <h2>Registration Form</h2>

        <form action="index.php?action=register_submit" method="POST" enctype="multipart/form-data">
            
            <div class="reg-section">
                <div class="reg-label-side">Personal Identity:</div>
                <div class="reg-input-grid">
                    <div class="full-row">
                        <label>Full name:</label>
                        <input type="text" name="full_name" required>
                    </div>
                    <div>
                        <label>Date of birth:</label>
                        <input type="date" name="date_of_birth" required>
                    </div>
                    <div>
                        <label>Contact number:</label>
                        <input type="text" name="contact_number" required>
                    </div>
                    <div class="full-row">
                        <label>Email address:</label>
                        <input type="email" name="email_address" required>
                    </div>
                </div>
                <div class="photo-box">
                    <label>Passport photo:</label>
                    <div class="photo-placeholder">👤</div>
                    <input type="file" name="passport_photo" accept="image/*">
                </div>
            </div>

            <div class="reg-section">
                <div class="reg-label-side">Institutional Details:</div>
                <div class="reg-input-grid">
                    <div class="full-row">
                        <label>College name:</label>
                        <input type="text" name="college_name">
                    </div>
                    <div>
                        <label>Permanent address:</label>
                        <input type="text" name="permanent_address">
                    </div>
                    <div>
                        <label>Enrolled date:</label>
                        <input type="date" name="enrolled_date">
                    </div>
                </div>
            </div>

            <div class="reg-section">
                <div class="reg-label-side">Emergency Contact:</div>
                <div class="reg-input-grid">
                    <div class="full-row">
                        <label>Guardian full name:</label>
                        <input type="text" name="guardian_full_name">
                    </div>
                    <div>
                        <label>Relationship:</label>
                        <input type="text" name="relationship">
                    </div>
                    <div>
                        <label>Contact number:</label>
                        <input type="text" name="guardian_contact_number">
                    </div>
                </div>
            </div>

            <div class="submit-container">
                <button type="submit" class="main-submit-btn">Submit</button>
            </div>
        </form>
    </div>
</main>

<?php include(__DIR__ . '/../layout/footer.php'); ?>