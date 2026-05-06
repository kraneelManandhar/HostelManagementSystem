<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
if (!defined('BASE_URL')) {
    define('BASE_URL', '/HostelManagementSystem/');
}
$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password";
    } else {
        $auth = new AuthController();
        $result = $auth->login($email, $password);
        if ($result['success']) {
            header("Location: " . BASE_URL . $result['redirect']);
            exit;
        } else {
            $error = $result['error'];
        }
    }
}
?>
<?php include(__DIR__ . '/../layout/header.php'); ?>

<style>
  /* ── Reset & Base ── */
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  .login-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e8ecf0;
    font-family: 'Segoe UI', system-ui, sans-serif;
  }

  /* ── Card ── */
  .login-card {
    display: flex;
    width: 860px;
    min-height: 500px;
    background: #dce5f0;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.18);
    position: relative;
  }

  /* ── Back button ── */
  .back-btn {
    position: absolute;
    top: 22px;
    left: 24px;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    color: #4a5568;
    text-decoration: none;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    z-index: 10;
    transition: color 0.2s;
  }
  .back-btn:hover { color: #1a2a4a; }
  .back-btn svg { width: 16px; height: 16px; }

  /* ── Characters panel ── */
  .characters-panel {
    flex: 0 0 46%;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    padding: 40px 20px 0;
    position: relative;
    overflow: hidden;
  }

  #characters-svg {
    width: 100%;
    max-width: 340px;
    display: block;
  }

  /* ── Form panel ── */
  .form-panel {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 50px 48px 50px 32px;
    background: #dce5f0;
  }

  .form-panel h2 {
    font-size: 28px;
    font-weight: 700;
    color: #1a2a4a;
    margin-bottom: 8px;
    text-align: center;
  }

  .form-subtitle {
    font-size: 13.5px;
    color: #6b7a99;
    text-align: center;
    margin-bottom: 28px;
    line-height: 1.5;
  }

  /* ── Form fields ── */
  .field-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.1em;
    color: #4a5568;
    text-transform: uppercase;
    margin-bottom: 6px;
    display: block;
  }

  .field-group {
    margin-bottom: 18px;
  }

  .field-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
  }

  .forgot-link {
    font-size: 12px;
    color: #6b7a99;
    text-decoration: none;
  }
  .forgot-link:hover { color: #1a2a4a; text-decoration: underline; }

  .input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
  }

  .input-icon {
    position: absolute;
    left: 14px;
    color: #9aaac4;
    display: flex;
    align-items: center;
  }

  .input-wrapper input {
    width: 100%;
    padding: 13px 44px 13px 42px;
    background: #eef1f7;
    border: 1.5px solid transparent;
    border-radius: 10px;
    font-size: 14px;
    color: #1a2a4a;
    outline: none;
    transition: border-color 0.2s, background 0.2s;
  }
  .input-wrapper input::placeholder { color: #b0bdd4; }
  .input-wrapper input:focus {
    border-color: #3d6be4;
    background: #f5f7fc;
  }

  .toggle-eye {
    position: absolute;
    right: 14px;
    cursor: pointer;
    color: #9aaac4;
    display: flex;
    align-items: center;
    transition: color 0.2s;
  }
  .toggle-eye:hover { color: #4a5568; }

  /* ── Login button ── */
  .login-submit-btn {
    width: 100%;
    padding: 15px;
    background: #1a2a4a;
    color: #fff;
    border: none;
    border-radius: 50px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 8px;
    letter-spacing: 0.03em;
    transition: background 0.2s, transform 0.1s;
  }
  .login-submit-btn:hover { background: #243660; }
  .login-submit-btn:active { transform: scale(0.98); }

  /* ── Bottom links ── */
  .bottom-section {
    text-align: center;
    margin-top: 24px;
  }
  .bottom-section p {
    font-size: 13px;
    color: #6b7a99;
    margin-bottom: 12px;
  }

  .create-account-btn {
    display: inline-block;
    padding: 11px 36px;
    border: 1.5px solid #a0aec0;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 600;
    color: #1a2a4a;
    text-decoration: none;
    background: transparent;
    cursor: pointer;
    transition: border-color 0.2s, background 0.2s;
  }
  .create-account-btn:hover { background: #eef1f7; border-color: #6b7a99; }

  /* ── Alerts ── */
  .login-error {
    background: #fff0f0;
    color: #c53030;
    border: 1px solid #feb2b2;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 13px;
    margin-bottom: 16px;
    text-align: center;
  }
  .login-success {
    background: #f0fff4;
    color: #276749;
    border: 1px solid #9ae6b4;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 13px;
    margin-bottom: 16px;
    text-align: center;
  }

  /* ── Responsive ── */
  @media (max-width: 700px) {
    .login-card { flex-direction: column; width: 95vw; }
    .characters-panel { padding: 30px 20px 0; flex: 0 0 auto; }
    .form-panel { padding: 30px 28px; }
  }
</style>

<main class="login-page">
  <div class="login-card">

    <a href="javascript:history.back()" class="back-btn">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M19 12H5M5 12l7-7M5 12l7 7"/>
      </svg>
      Back
    </a>

    <!-- Characters Panel -->
    <div class="characters-panel">
      <svg id="characters-svg" viewBox="0 0 340 320" xmlns="http://www.w3.org/2000/svg" overflow="visible">

        <!-- Shadow ground -->
        <ellipse cx="185" cy="315" rx="150" ry="8" fill="rgba(0,0,0,0.07)"/>

        <!-- Character 1: Red (small, leftmost) -->
        <g class="char" id="c1">
          <rect x="18" y="218" width="52" height="96" rx="26" fill="#c0392b"/>
          <rect x="18" y="218" width="52" height="52" rx="26" fill="#c0392b"/>
          <circle cx="33" cy="243" r="9" fill="white"/>
          <circle class="pupil" id="c1-lp" cx="33" cy="243" r="4.5" fill="#1a1a2e"/>
          <circle cx="35.5" cy="240.5" r="2" fill="white" opacity="0.7"/>
          <circle cx="57" cy="243" r="9" fill="white"/>
          <circle class="pupil" id="c1-rp" cx="57" cy="243" r="4.5" fill="#1a1a2e"/>
          <circle cx="59.5" cy="240.5" r="2" fill="white" opacity="0.7"/>
          <path d="M32 262 Q44 272 56 262" stroke="#8b2020" stroke-width="2" fill="none" stroke-linecap="round"/>
        </g>

        <!-- Character 2: Olive tall -->
        <g class="char" id="c2">
          <rect x="68" y="148" width="58" height="166" rx="29" fill="#8a8c2a"/>
          <rect x="68" y="148" width="58" height="58" rx="29" fill="#8a8c2a"/>
          <circle cx="84" cy="178" r="10" fill="white"/>
          <circle class="pupil" id="c2-lp" cx="84" cy="178" r="5" fill="#1a1a2e"/>
          <circle cx="86.5" cy="175" r="2.2" fill="white" opacity="0.7"/>
          <circle cx="110" cy="178" r="10" fill="white"/>
          <circle class="pupil" id="c2-rp" cx="110" cy="178" r="5" fill="#1a1a2e"/>
          <circle cx="112.5" cy="175" r="2.2" fill="white" opacity="0.7"/>
          <path d="M83 200 Q97 212 111 200" stroke="#5a5c10" stroke-width="2" fill="none" stroke-linecap="round"/>
        </g>

        <!-- Character 3: Sage medium -->
        <g class="char" id="c3">
          <rect x="118" y="178" width="54" height="136" rx="27" fill="#7d9070"/>
          <rect x="118" y="178" width="54" height="54" rx="27" fill="#7d9070"/>
          <circle cx="133" cy="205" r="9" fill="white"/>
          <circle class="pupil" id="c3-lp" cx="133" cy="205" r="4.5" fill="#1a1a2e"/>
          <circle cx="135.5" cy="202" r="2" fill="white" opacity="0.7"/>
          <circle cx="159" cy="205" r="9" fill="white"/>
          <circle class="pupil" id="c3-rp" cx="159" cy="205" r="4.5" fill="#1a1a2e"/>
          <circle cx="161.5" cy="202" r="2" fill="white" opacity="0.7"/>
          <path d="M132 226 Q145 237 158 226" stroke="#4d6040" stroke-width="2" fill="none" stroke-linecap="round"/>
        </g>

        <!-- Character 4: Black small -->
        <g class="char" id="c4">
          <rect x="164" y="228" width="48" height="86" rx="24" fill="#2d2d2d"/>
          <rect x="164" y="228" width="48" height="48" rx="24" fill="#2d2d2d"/>
          <circle cx="178" cy="252" r="8.5" fill="white"/>
          <circle class="pupil" id="c4-lp" cx="178" cy="252" r="4" fill="#1a1a2e"/>
          <circle cx="180" cy="249.5" r="1.8" fill="white" opacity="0.7"/>
          <circle cx="200" cy="252" r="8.5" fill="white"/>
          <circle class="pupil" id="c4-rp" cx="200" cy="252" r="4" fill="#1a1a2e"/>
          <circle cx="202" cy="249.5" r="1.8" fill="white" opacity="0.7"/>
          <path d="M177 270 Q189 280 201 270" stroke="#111" stroke-width="2" fill="none" stroke-linecap="round"/>
        </g>

        <!-- Character 5: Bright yellow-green (tallest) -->
        <g class="char" id="c5">
          <rect x="203" y="120" width="64" height="194" rx="32" fill="#a0a824"/>
          <rect x="203" y="120" width="64" height="64" rx="32" fill="#a0a824"/>
          <circle cx="221" cy="153" r="11" fill="white"/>
          <circle class="pupil" id="c5-lp" cx="221" cy="153" r="5.5" fill="#1a1a2e"/>
          <circle cx="224" cy="149.5" r="2.5" fill="white" opacity="0.7"/>
          <circle cx="249" cy="153" r="11" fill="white"/>
          <circle class="pupil" id="c5-rp" cx="249" cy="153" r="5.5" fill="#1a1a2e"/>
          <circle cx="252" cy="149.5" r="2.5" fill="white" opacity="0.7"/>
          <path d="M220 178 Q235 192 250 178" stroke="#6a7010" stroke-width="2.5" fill="none" stroke-linecap="round"/>
        </g>

        <!-- Character 6: Blue/slate tall -->
        <g class="char" id="c6">
          <rect x="259" y="155" width="58" height="159" rx="29" fill="#6b84b0"/>
          <rect x="259" y="155" width="58" height="58" rx="29" fill="#6b84b0"/>
          <circle cx="275" cy="184" r="10" fill="white"/>
          <circle class="pupil" id="c6-lp" cx="275" cy="184" r="5" fill="#1a1a2e"/>
          <circle cx="277.5" cy="181" r="2.2" fill="white" opacity="0.7"/>
          <circle cx="301" cy="184" r="10" fill="white"/>
          <circle class="pupil" id="c6-rp" cx="301" cy="184" r="5" fill="#1a1a2e"/>
          <circle cx="303.5" cy="181" r="2.2" fill="white" opacity="0.7"/>
          <path d="M274 207 Q288 219 302 207" stroke="#3d5480" stroke-width="2" fill="none" stroke-linecap="round"/>
        </g>

        <!-- Character 7: Sage small (rightmost) -->
        <g class="char" id="c7">
          <rect x="307" y="230" width="44" height="84" rx="22" fill="#8fa880"/>
          <rect x="307" y="230" width="44" height="44" rx="22" fill="#8fa880"/>
          <circle cx="319" cy="252" r="7.5" fill="white"/>
          <circle class="pupil" id="c7-lp" cx="319" cy="252" r="3.8" fill="#1a1a2e"/>
          <circle cx="321" cy="249.5" r="1.6" fill="white" opacity="0.7"/>
          <circle cx="339" cy="252" r="7.5" fill="white"/>
          <circle class="pupil" id="c7-rp" cx="339" cy="252" r="3.8" fill="#1a1a2e"/>
          <circle cx="341" cy="249.5" r="1.6" fill="white" opacity="0.7"/>
          <path d="M318 268 Q329 277 340 268" stroke="#5a7850" stroke-width="1.8" fill="none" stroke-linecap="round"/>
        </g>

      </svg>
    </div>

    <!-- Form Panel -->
    <div class="form-panel">
      <h2>Welcome Back</h2>
      <p class="form-subtitle">Please enter your details to access<br>your portal.</p>

      <?php if (!empty($error)): ?>
        <p class="login-error"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>
      <?php if (isset($_GET['registered']) && $_GET['registered'] === 'success'): ?>
        <p class="login-success">Registration successful! Please login.</p>
      <?php endif; ?>

      <form method="POST" action="<?= BASE_URL ?>index.php?action=login">

        <div class="field-group">
          <label class="field-label">Email Address</label>
          <div class="input-wrapper">
            <span class="input-icon">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
              </svg>
            </span>
            <input type="email" name="email" placeholder="student@university.edu" required>
          </div>
        </div>

        <div class="field-group">
          <div class="field-row">
            <label class="field-label" style="margin-bottom:0">Password</label>
            <a href="<?= BASE_URL ?>index.php?action=forgot_password" class="forgot-link">forgot password?</a>
          </div>
          <div class="input-wrapper" style="margin-top:6px">
            <span class="input-icon">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
            </span>
            <input type="password" name="password" id="password" placeholder="••••••••" required>
            <span class="toggle-eye" onclick="togglePassword('password')" title="Show/hide password">
              <svg id="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
              </svg>
            </span>
          </div>
        </div>

        <button type="submit" class="login-submit-btn">Login</button>
      </form>

      <div class="bottom-section">
        <p>Haven't signed up yet?</p>
        <a href="<?= BASE_URL ?>index.php?action=register" class="create-account-btn">Create an Account</a>
      </div>
    </div>

  </div>
</main>

<script>
// ── Password toggle ──
function togglePassword(id) {
  var input = document.getElementById(id);
  var icon  = document.getElementById('eye-icon');
  if (input.type === 'password') {
    input.type = 'text';
    icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
  } else {
    input.type = 'password';
    icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
  }
}

// ── Mouse-tracking eyes ──
(function () {
  var svg = document.getElementById('characters-svg');
  if (!svg) return;

  // [leftPupilId, rightPupilId, leftEyeCx, leftEyeCy, rightEyeCx, rightEyeCy, maxRadius]
  var chars = [
    ['c1-lp', 'c1-rp',  33, 243,  57, 243, 4.0],
    ['c2-lp', 'c2-rp',  84, 178, 110, 178, 4.5],
    ['c3-lp', 'c3-rp', 133, 205, 159, 205, 4.0],
    ['c4-lp', 'c4-rp', 178, 252, 200, 252, 3.5],
    ['c5-lp', 'c5-rp', 221, 153, 249, 153, 5.0],
    ['c6-lp', 'c6-rp', 275, 184, 301, 184, 4.5],
    ['c7-lp', 'c7-rp', 319, 252, 339, 252, 3.2],
  ];

  function toSVGPoint(clientX, clientY) {
    var pt = svg.createSVGPoint();
    pt.x = clientX;
    pt.y = clientY;
    var ctm = svg.getScreenCTM();
    if (!ctm) return { x: 0, y: 0 };
    return pt.matrixTransform(ctm.inverse());
  }

  function movePupil(pupilEl, eyeCx, eyeCy, tx, ty, maxR) {
    var dx = tx - eyeCx;
    var dy = ty - eyeCy;
    var dist = Math.sqrt(dx * dx + dy * dy);
    var r = Math.min(dist, maxR);
    var angle = Math.atan2(dy, dx);
    pupilEl.setAttribute('cx', (eyeCx + Math.cos(angle) * r).toFixed(2));
    pupilEl.setAttribute('cy', (eyeCy + Math.sin(angle) * r).toFixed(2));
  }

  function handleMove(clientX, clientY) {
    var sp = toSVGPoint(clientX, clientY);
    chars.forEach(function (c) {
      var lp = document.getElementById(c[0]);
      var rp = document.getElementById(c[1]);
      if (lp) movePupil(lp, c[2], c[3], sp.x, sp.y, c[6]);
      if (rp) movePupil(rp, c[4], c[5], sp.x, sp.y, c[6]);
    });
  }

  document.addEventListener('mousemove', function (e) {
    handleMove(e.clientX, e.clientY);
  });

  document.addEventListener('touchmove', function (e) {
    if (e.touches.length > 0) handleMove(e.touches[0].clientX, e.touches[0].clientY);
  }, { passive: true });
})();
</script>

<?php include(__DIR__ . '/../layout/footer.php'); ?>
