<?php
$page_title = 'Reset Password';
include_once __DIR__ . '/../includes/header.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$rate_limit_error = '';
$rate_limit_retry_after = 0;
if (isset($_SESSION['rate_limit_error'])) {
    $rate_limit_error = $_SESSION['rate_limit_error'];
    $rate_limit_retry_after = (int)($_SESSION['rate_limit_retry_after'] ?? 0);
    unset($_SESSION['rate_limit_error']);
    unset($_SESSION['rate_limit_retry_after']);
}

$error = '';
$success = '';
$show_password_form = false;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please refresh and try again.';
    } else {
        if (isset($_POST['verify_code'])) {
            $reset_code = trim($_POST['reset_code'] ?? '');
            $email = trim($_POST['email'] ?? '');
            if (empty($reset_code) || empty($email)) {
                $error = 'Please fill in all fields.';
            } else {
                try {
                    $stmt = $pdo->prepare("
                        SELECT pr.user_id, u.full_name 
                        FROM password_resets pr 
                        JOIN users u ON pr.user_id = u.id 
                        WHERE pr.email = ? AND pr.reset_code = ? AND pr.expires_at >= NOW()
                        ORDER BY pr.created_at DESC 
                        LIMIT 1
                    ");
                    $stmt->execute([$email, $reset_code]);
                    $reset_data = $stmt->fetch();
                    if ($reset_data) {
                        $show_password_form = true;
                        $_SESSION['reset_user_id'] = $reset_data['user_id'];
                        $_SESSION['reset_email'] = $email;
                    } else {
                        $error = 'Invalid or expired reset code. Please try again.';
                    }
                } catch (PDOException $e) {
                    $error = 'A database error occurred. Please try again later.';
                    error_log('Database error in reset password verification: ' . $e->getMessage());
                }
            }
        } elseif (isset($_POST['reset_password'])) {
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            if (!isset($_SESSION['reset_user_id']) || !isset($_SESSION['reset_email'])) {
                $error = 'Session expired. Please start the password reset process again.';
            } elseif (empty($new_password) || empty($confirm_password)) {
                $error = 'Please fill in all fields.';
                $show_password_form = true;
            } elseif (strlen($new_password) < 12) {
                $error = 'Password must be at least 12 characters long.';
                $show_password_form = true;
            } elseif (!preg_match('/[A-Z]/', $new_password)) {
                $error = 'Password must contain at least one uppercase letter.';
                $show_password_form = true;
            } elseif (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $new_password)) {
                $error = 'Password must contain at least one special character (!@#$%^&*(),.?":{}|<>).';
                $show_password_form = true;
            } elseif (is_password_compromised($new_password)) {
                $error = 'This password has been exposed in a data breach. Please choose a different one.';
                $show_password_form = true;
            } elseif ($new_password !== $confirm_password) {
                $error = 'Passwords do not match.';
                $show_password_form = true;
            } else {
                try {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed_password, $_SESSION['reset_user_id']]);
                    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
                    $stmt->execute([$_SESSION['reset_email']]);
                    unset($_SESSION['reset_user_id']);
                    unset($_SESSION['reset_email']);
                    $success = 'Your password has been reset successfully. You can now log in with your new password.';
                } catch (PDOException $e) {
                    $error = 'Failed to reset password. Please try again.';
                    error_log('Database error in password reset: ' . $e->getMessage());
                    $show_password_form = true;
                }
            }
        }
    }
}

if (isset($_SESSION['reset_user_id']) && isset($_SESSION['reset_email']) && !$show_password_form && empty($success)) {
    $show_password_form = true;
    $email = $_SESSION['reset_email'];
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <img src="../assets/images/logo.png" alt="Logo" style="height: 80px;" class="mb-3">
                        <h2 class="card-title h3">Reset Password</h2>
                        <p class="text-muted">
                            <?php echo $show_password_form ? 'Enter your new password' : 'Enter the 5-digit code sent to your email'; ?>
                        </p>
                    </div>

                    <?php if (!empty($rate_limit_error)): ?>
                        <div class="alert alert-danger" id="rate-limit-alert" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($rate_limit_error); ?>
                            <?php if ($rate_limit_retry_after > 0): ?>
                                <br>
                                <span id="countdown-timer">Wait <strong><?php echo $rate_limit_retry_after; ?></strong> seconds before trying again.</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo htmlspecialchars($success); ?>
                            <div class="mt-3 text-center">
                                <a href="login.php" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i> Sign In Now
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($success)): ?>
                        <?php if (!$show_password_form): ?>
                            <form method="POST" id="reset-code-form">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control form-control-lg" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($email); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="reset_code" class="form-label">5-Digit Reset Code</label>
                                    <input type="text" class="form-control form-control-lg text-center" id="reset_code" name="reset_code" 
                                           maxlength="5" pattern="[0-9]{5}" placeholder="00000" required autofocus>
                                    <div class="form-text">Enter the 5-digit code sent to your email</div>
                                </div>

                                <div class="d-grid mb-3">
                                    <button type="submit" name="verify_code" class="btn btn-primary btn-lg" id="reset-code-submit-btn">
                                        <i class="fas fa-check me-2"></i> Verify Code
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <form method="POST" id="reset-password-form">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <div class="input-group input-group-lg">
                                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                                        <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('new_password', this)" aria-label="Show or hide new password">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Minimum 12 characters, 1 uppercase letter, 1 special character</div>
                                </div>

                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <div class="input-group input-group-lg">
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                        <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('confirm_password', this)" aria-label="Show or hide confirm password">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div id="password-match-indicator" class="form-text"></div>
                                </div>

                                <div class="d-grid mb-3">
                                    <button type="submit" name="reset_password" class="btn btn-primary btn-lg" id="reset-password-submit-btn">
                                        <i class="fas fa-key me-2"></i> Reset Password
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="text-center mt-4">
                        <p class="mb-0 text-muted">
                            Remember your password? 
                            <a href="login.php">Sign in here</a>
                        </p>
                        <p class="mb-0 text-muted mt-2">
                            Didn't receive the code? 
                            <a href="forgot_password.php">Send again</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('reset_code')?.addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
    if (this.value.length > 5) this.value = this.value.slice(0, 5);
});

function togglePasswordVisibility(fieldId, button) {
    const input = document.getElementById(fieldId);
    if (!input) return;
    const icon = button.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon?.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon?.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

function updatePasswordMatchStatus() {
    const newPw = document.getElementById('new_password');
    const confirmPw = document.getElementById('confirm_password');
    const indicator = document.getElementById('password-match-indicator');
    if (!newPw || !confirmPw || !indicator) return;
    const newVal = newPw.value, confirmVal = confirmPw.value;
    indicator.classList.remove('text-success', 'text-danger', 'text-muted');
    if (newVal === '' && confirmVal === '') { indicator.textContent = ''; return; }
    if (confirmVal === '') { indicator.textContent = 'Re-enter password to confirm.'; indicator.classList.add('text-muted'); return; }
    if (newVal === confirmVal) { indicator.textContent = 'Passwords match.'; indicator.classList.add('text-success'); }
    else { indicator.textContent = 'Passwords do not match.'; indicator.classList.add('text-danger'); }
}

document.getElementById('new_password')?.addEventListener('input', updatePasswordMatchStatus);
document.getElementById('confirm_password')?.addEventListener('input', updatePasswordMatchStatus);
document.getElementById('reset-password-form')?.addEventListener('submit', function(e) {
    const newPw = document.getElementById('new_password');
    const confirmPw = document.getElementById('confirm_password');
    if (newPw && confirmPw && newPw.value !== confirmPw.value) {
        e.preventDefault();
        updatePasswordMatchStatus();
    }
});

<?php if ($rate_limit_retry_after > 0): ?>
(function() {
    let secondsLeft = <?php echo $rate_limit_retry_after; ?>;
    const timerSpan = document.getElementById('countdown-timer');
    const submitBtn = document.getElementById('reset-code-submit-btn') || document.getElementById('reset-password-submit-btn');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-clock me-2"></i> Please wait...';
    }
    const interval = setInterval(function() {
        secondsLeft--;
        if (timerSpan) timerSpan.innerHTML = 'Wait <strong>' + secondsLeft + '</strong> seconds before trying again.';
        if (secondsLeft <= 0) {
            clearInterval(interval);
            if (timerSpan) timerSpan.innerHTML = 'You can now try again.';
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = submitBtn.id === 'reset-code-submit-btn' 
                    ? '<i class="fas fa-check me-2"></i> Verify Code'
                    : '<i class="fas fa-key me-2"></i> Reset Password';
            }
        }
    }, 1000);
})();
<?php endif; ?>
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>