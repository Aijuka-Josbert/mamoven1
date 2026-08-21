<?php
$page_title = 'Verify Your Account';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['verify_email'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
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

$email = $_SESSION['verify_email'];
$error = '';
$success = '';
$emailSentStatus = isset($_SESSION['verification_failed']) ? false : true;
unset($_SESSION['verification_failed']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please refresh and try again.';
    } else {
        if (isset($_POST['verify_code'])) {
            $code = trim($_POST['code']);
            if (empty($code) || !preg_match('/^\d{6}$/', $code)) {
                $error = 'Please enter a valid 6-digit code.';
            } else {
                $stmt = $pdo->prepare("SELECT id, verification_code FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                if ($user && $user['verification_code'] === $code) {
                    $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, verification_code = NULL WHERE email = ?");
                    $stmt->execute([$email]);
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    $user_data = $stmt->fetch();
                    $_SESSION['user_id'] = $user_data['id'];
                    $_SESSION['username'] = $user_data['username'];
                    $_SESSION['full_name'] = $user_data['full_name'];
                    $_SESSION['role'] = $user_data['role'];
                    $remember_me = !empty($_SESSION['pending_remember_me']);
                    apply_auth_session_preferences($remember_me);
                    record_user_session($user_data['id'], $user_data['role']);
                    log_audit_event('customer_verified', 'user', $user_data['id']);
                    unset($_SESSION['verify_email']);
                    unset($_SESSION['pending_remember_me']);
                    $redirect = $_SESSION['redirect_after_login'] ?? BASE_URL . '/index.php?registered=1';
                    unset($_SESSION['redirect_after_login']);
                    echo "<script>window.location.href='{$redirect}';</script>";
                    exit;
                } else {
                    $error = 'Invalid verification code. Please try again.';
                }
            }
        }
        if (isset($_POST['resend_code'])) {
            $new_code = sprintf("%06d", random_int(100000, 999999));
            $stmt = $pdo->prepare("UPDATE users SET verification_code = ? WHERE email = ?");
            $stmt->execute([$new_code, $email]);
            $stmt = $pdo->prepare("SELECT full_name FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user_full_name = $stmt->fetchColumn();
            try {
                $mail = new PHPMailer(true);
                configure_mailer_transport($mail);
                $mail->setFrom(default_mail_from_address(), SITE_NAME);
                $mail->addAddress($email, $user_full_name);
                $mail->isHTML(true);
                $mail->Subject = 'New Verification Code - ' . SITE_NAME;
                $mail->Body = "<html>
                               <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                                   <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                                       <div style='text-align: center; margin-bottom: 30px;'>
                                           " . email_logo_html($mail, 60) . "
                                           <h2 style='color: #8B4513; margin: 0;'>Email Verification</h2>
                                       </div>
                                       <p>Your new 6-digit verification code is:</p>
                                       <div style='background: #f9f6f0; padding: 20px; border-radius: 8px; margin: 25px 0; text-align: center;'>
                                           <h1 style='color: #8B4513; margin: 0; font-size: 32px; letter-spacing: 5px;'>$new_code</h1>
                                       </div>
                                       <p>Please enter this code on the verification page to activate your account.</p>
                                       <p>If you did not request this, please ignore this email.</p>
                                       <p style='margin-top: 30px;'>Best regards,<br><strong>The " . SITE_NAME . " Team</strong></p>
                                   </div>
                               </body>
                               </html>";
                $mail->AltBody = "Your new verification code is: $new_code";
                if (send_mail_with_fallback($mail)) {
                    $success = "A new verification code has been sent to your email.";
                } else {
                    $error = "Failed to send the code. Please try again later.";
                }
            } catch (\Throwable $e) {
                $error = "Failed to send the code. Please try again later.";
                error_log('Resend verification code failed: ' . $e->getMessage());
            }
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0 border-top border-primary border-4">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-envelope-open-text text-primary" style="font-size: 3rem;"></i>
                        <h2 class="auth-title mt-3 mb-1">Verify Email</h2>
                        <p class="text-muted mb-0">We sent a 6-digit code to <strong><?php echo htmlspecialchars($email); ?></strong></p>
                        <?php if (!$emailSentStatus): ?>
                            <div class="alert alert-warning mt-2">The initial email may not have been delivered. Use the "Resend Code" button below.</div>
                        <?php endif; ?>
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

                    <?php if ($error): ?>
                        <div class="alert alert-danger d-flex align-items-center" role="alert">
                            <i class="fas fa-exclamation-circle flex-shrink-0 me-2"></i>
                            <div><?php echo htmlspecialchars($error); ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success d-flex align-items-center" role="alert">
                            <i class="fas fa-check-circle flex-shrink-0 me-2"></i>
                            <div><?php echo htmlspecialchars($success); ?></div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="verify-form">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <div class="mb-4">
                            <label for="code" class="form-label text-muted small text-uppercase fw-bold">Verification Code</label>
                            <input type="text" class="form-control form-control-lg text-center" id="code" name="code" 
                                   maxlength="6" autocomplete="off" required 
                                   style="letter-spacing: 0.5em; font-size: 1.5rem;"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>

                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" name="verify_code" class="btn btn-primary btn-lg w-100" id="verify-submit-btn">
                                Verify Email
                            </button>
                            <button type="submit" name="resend_code" id="resendBtn" class="btn btn-outline-secondary w-100" formnovalidate>
                                Resend Code
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let resendBtn = document.getElementById("resendBtn");
    <?php if (isset($_POST['resend_code'])): ?>
    let timeLeft = 60;
    resendBtn.disabled = true;
    let originalText = "Resend Code";
    let timer = setInterval(function() {
        if (timeLeft <= 0) {
            clearInterval(timer);
            resendBtn.disabled = false;
            resendBtn.innerHTML = originalText;
        } else {
            resendBtn.innerHTML = "Resend Code (" + timeLeft + "s)";
            timeLeft--;
        }
    }, 1000);
    <?php endif; ?>
});

<?php if ($rate_limit_retry_after > 0): ?>
(function() {
    let secondsLeft = <?php echo $rate_limit_retry_after; ?>;
    const timerSpan = document.getElementById('countdown-timer');
    const submitBtn = document.getElementById('verify-submit-btn');
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
                submitBtn.innerHTML = 'Verify Email';
            }
        }
    }, 1000);
})();
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>