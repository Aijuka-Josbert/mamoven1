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

$email = $_SESSION['verify_email'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ---------------------------------------------------------
    // 1. HANDLE VERIFY CODE
    // ---------------------------------------------------------
    if (isset($_POST['verify_code'])) {
        $code = trim($_POST['code']);
        
        $stmt = $pdo->prepare("SELECT id, verification_code FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && $user['verification_code'] === $code) {
            $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, verification_code = NULL WHERE email = ?");
            $stmt->execute([$email]);
            
            // Auto login
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user_data = $stmt->fetch();
            
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['username'] = $user_data['username'];
            $_SESSION['full_name'] = $user_data['full_name'];
            $_SESSION['role'] = $user_data['role'];
            $remember_me = !empty($_SESSION['pending_remember_me']);
            apply_auth_session_preferences($remember_me);
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
    
    // ---------------------------------------------------------
    // 2. HANDLE RESEND CODE
    // ---------------------------------------------------------
    if (isset($_POST['resend_code'])) {
        $new_code = sprintf("%06d", mt_rand(100000, 999999));
        
        // Update user with new code
        $stmt = $pdo->prepare("UPDATE users SET verification_code = ? WHERE email = ?");
        $stmt->execute([$new_code, $email]);
        
        // Fetch user full name
        $stmt = $pdo->prepare("SELECT full_name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user_full_name = $stmt->fetchColumn();

        // Send Email
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
                                   <p style='margin-top: 30px;'>
                                       Best regards,<br>
                                       <strong>The " . SITE_NAME . " Team</strong>
                                   </p>
                               </div>
                           </body>
                           </html>";
            $mail->AltBody = "Your new verification code is: $new_code";

            if (send_mail_with_fallback($mail)) {
                $success = "A new verification code has been sent to your email.";
            } else {
                $error = "Failed to send the code. Please try again later.";
            }
        } catch (Exception $e) {
            $error = "Failed to send the code. Please try again later.";
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
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger bg-danger bg-opacity-10 border-danger border-opacity-25 text-danger d-flex align-items-center" role="alert">
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

                    <form method="POST" action="">
                        <div class="mb-4">
                            <label for="code" class="form-label text-muted small text-uppercase fw-bold">Verification Code</label>
                            <input type="text" class="form-control form-control-lg text-center" id="code" name="code" 
                                   maxlength="6" autocomplete="off" required 
                                   style="letter-spacing: 0.5em; font-size: 1.5rem;"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>

                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" name="verify_code" class="btn btn-primary btn-lg w-100">Verify Email</button>
                            <!-- Note the formnovalidate attribute below! -->
                            <button type="submit" name="resend_code" id="resendBtn" class="btn btn-outline-secondary w-100" formnovalidate>Resend Code</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Timer script to prevent spamming the Resend button
document.addEventListener("DOMContentLoaded", function() {
    let resendBtn = document.getElementById("resendBtn");
    
    // Only engage the timer if the page loads after a resend button click, 
    // or you can enable it every time the page loads. 
    <?php if (isset($_POST['resend_code'])): ?>
    let timeLeft = 60; // 60 seconds countdown
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
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
