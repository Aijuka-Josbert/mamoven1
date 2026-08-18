<?php
$page_title = 'Forgot Password';
include_once __DIR__ . '/../includes/header.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/../vendor/autoload.php';

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

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please refresh and try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id, username, full_name FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                if ($user) {
                    $reset_code = sprintf('%05d', mt_rand(10000, 99999));
                    $expirySeconds = (int) PASSWORD_RESET_EXPIRY;
                    $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, email, reset_code, expires_at, created_at)
                                         VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL {$expirySeconds} SECOND), NOW())
                                         ON DUPLICATE KEY UPDATE reset_code = VALUES(reset_code),
                                         expires_at = DATE_ADD(NOW(), INTERVAL {$expirySeconds} SECOND),
                                         created_at = NOW()");
                    $stmt->execute([$user['id'], $email, $reset_code]);
                    try {
                        $mail = new PHPMailer(true);
                        configure_mailer_transport($mail);
                        $mail->setFrom(default_mail_from_address(), SITE_NAME);
                        $mail->addAddress($email, $user['full_name']);
                        $mail->isHTML(true);
                        $mail->Subject = "Password Reset Code - " . SITE_NAME;
                        $mail->Body = "
                        <html>
                        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                                <div style='text-align: center; margin-bottom: 30px;'>
                                    <h1 style='color: #8B4513;'>Password Reset Request</h1>
                                    " . email_logo_html($mail, 80) . "
                                </div>
                                <p>Hello {$user['full_name']},</p>
                                <p>We received a request to reset your password for your " . SITE_NAME . " account.</p>
                                <div style='background: #f9f6f0; padding: 20px; border-radius: 8px; margin: 25px 0; text-align: center;'>
                                    <h2 style='color: #8B4513; margin: 0; font-size: 32px; letter-spacing: 5px;'>{$reset_code}</h2>
                                    <p style='margin: 10px 0 0 0; color: #666;'>This code expires in 2 hours</p>
                                </div>
                                <p>Enter this code on the password reset page to create a new password.</p>
                                <p>If you didn't request this password reset, please ignore this email. Your password will remain unchanged.</p>
                                <p style='margin-top: 30px;'>Best regards,<br><strong>The " . SITE_NAME . " Team</strong></p>
                            </div>
                        </body>
                        </html>";
                        $mail->AltBody = "Your password reset code is: {$reset_code}\nExpires in 2 hours.";
                        if (send_mail_with_fallback($mail)) {
                            $message = 'A 5-digit reset code has been sent to your email address. Please check your inbox.';
                        } else {
                            $error = 'Failed to send reset email. Please try again later.';
                        }
                    } catch (Exception $e) {
                        $error = 'Failed to send reset email. Please try again later.';
                        error_log('Password reset email failed: ' . $e->getMessage());
                    }
                } else {
                    // Don't reveal whether email exists
                    $message = 'If an account with that email exists, a reset code has been sent.';
                }
            } catch (PDOException $e) {
                $error = 'A database error occurred. Please try again later.';
                error_log('Database error in forgot password: ' . $e->getMessage());
            }
        }
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <img src="../assets/images/logo.png" alt="Logo" style="height: 80px;" class="mb-3">
                        <h2 class="card-title h3">Forgot Password</h2>
                        <p class="text-muted">Enter your email to receive a reset code</p>
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

                    <?php if (!empty($message)): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <div class="mt-3">
                                <a href="reset_password.php" class="btn btn-sm btn-primary">Enter Reset Code</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($message)): ?>
                    <form method="POST" id="forgot-password-form">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control form-control-lg" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required autofocus>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg" id="forgot-password-submit-btn">
                                <i class="fas fa-paper-plane me-2"></i> Send Reset Code
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>

                    <div class="text-center mt-4">
                        <p class="mb-0 text-muted">
                            Remember your password? 
                            <a href="login.php">Sign in here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
<?php if ($rate_limit_retry_after > 0): ?>
(function() {
    let secondsLeft = <?php echo $rate_limit_retry_after; ?>;
    const timerSpan = document.getElementById('countdown-timer');
    const submitBtn = document.getElementById('forgot-password-submit-btn');
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
                submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i> Send Reset Code';
            }
        }
    }, 1000);
})();
<?php endif; ?>
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>