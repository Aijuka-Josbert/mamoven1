<?php
$page_title = 'Forgot Password';
include_once __DIR__ . '/../includes/header.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id, username, full_name FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate 5-digit code
                $reset_code = sprintf('%05d', mt_rand(10000, 99999));
                $expirySeconds = (int) PASSWORD_RESET_EXPIRY;
                
                // Store reset code using database time to avoid PHP/DB timezone mismatch.
                $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, email, reset_code, expires_at, created_at)
                                     VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL {$expirySeconds} SECOND), CURRENT_TIMESTAMP)
                                     ON DUPLICATE KEY UPDATE reset_code = VALUES(reset_code),
                                     expires_at = DATE_ADD(NOW(), INTERVAL {$expirySeconds} SECOND),
                                     created_at = CURRENT_TIMESTAMP");
                $stmt->execute([$user['id'], $email, $reset_code]);
                
                // Send email with reset code
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
                            
                            <p style='margin-top: 30px;'>
                                Best regards,<br>
                                <strong>The " . SITE_NAME . " Team</strong>
                            </p>
                        </div>
                    </body>
                    </html>";

                    $mail->AltBody = "Hello {$user['full_name']},\n\n"
                        . "Your password reset code is: {$reset_code}\n"
                        . "This code expires in 2 hours.\n\n"
                        . "If you did not request this, please ignore this email.\n\n"
                        . "The " . SITE_NAME . " Team";

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
                // Don't reveal if email exists for security
                $message = 'If an account with that email exists, a reset code has been sent.';
            }
        } catch (PDOException $e) {
            $error = 'A database error occurred. Please try again later.';
            error_log('Database error in forgot password: ' . $e->getMessage());
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
                        <img src="../assets/images/logo.jpeg" alt="Logo" style="height: 80px;" class="mb-3">
                        <h2 class="card-title h3">Forgot Password</h2>
                        <p class="text-muted">Enter your email to receive a reset code</p>
                    </div>

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
                    <form method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control form-control-lg" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required autofocus>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
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

<?php include_once __DIR__ . '/../includes/footer.php'; ?>