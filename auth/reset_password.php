<?php
$page_title = 'Reset Password';
include_once __DIR__ . '/../includes/header.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$success = '';
$show_password_form = false;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verify_code'])) {
        // Step 1: Verify the reset code
        $reset_code = trim($_POST['reset_code'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        if (empty($reset_code) || empty($email)) {
            $error = 'Please fill in all fields.';
        } else {
            try {
                // Check if code is valid and not expired
                $stmt = $pdo->prepare("SELECT pr.user_id, u.full_name FROM password_resets pr 
                                     JOIN users u ON pr.user_id = u.id 
                                     WHERE pr.email = ? AND pr.reset_code = ? AND pr.expires_at > NOW()");
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
        // Step 2: Reset the password
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (!isset($_SESSION['reset_user_id']) || !isset($_SESSION['reset_email'])) {
            $error = 'Session expired. Please start the password reset process again.';
        } elseif (empty($new_password) || empty($confirm_password)) {
            $error = 'Please fill in all fields.';
            $show_password_form = true;
        } elseif (strlen($new_password) < 8) {
            $error = 'Password must be at least 8 characters long.';
            $show_password_form = true;
        } elseif (!preg_match('/[A-Z]/', $new_password)) {
            $error = 'Password must contain at least one uppercase letter.';
            $show_password_form = true;
        } elseif (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $new_password)) {
            $error = 'Password must contain at least one special character (!@#$%^&*(),.?":{}|<>).';
            $show_password_form = true;
        } elseif ($new_password !== $confirm_password) {
            $error = 'Passwords do not match.';
            $show_password_form = true;
        } else {
            try {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $_SESSION['reset_user_id']]);
                
                // Delete the used reset code
                $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
                $stmt->execute([$_SESSION['reset_email']]);
                
                // Clear session variables
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

// Check if we have session data to show password form
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
                        <img src="../assets/images/logo.jpeg" alt="Logo" style="height: 80px;" class="mb-3">
                        <h2 class="card-title h3">Reset Password</h2>
                        <p class="text-muted">
                            <?php echo $show_password_form ? 'Enter your new password' : 'Enter the 5-digit code sent to your email'; ?>
                        </p>
                    </div>

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
                            <!-- Step 1: Verify reset code -->
                            <form method="POST">
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
                                    <button type="submit" name="verify_code" class="btn btn-primary btn-lg">
                                        <i class="fas fa-check me-2"></i> Verify Code
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <!-- Step 2: Set new password -->
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control form-control-lg" id="new_password" name="new_password" required>
                                    <div class="form-text">Minimum 8 characters, 1 uppercase letter, 1 special character</div>
                                </div>

                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control form-control-lg" id="confirm_password" name="confirm_password" required>
                                </div>

                                <div class="d-grid mb-3">
                                    <button type="submit" name="reset_password" class="btn btn-primary btn-lg">
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
// Auto-format reset code input
document.getElementById('reset_code')?.addEventListener('input', function(e) {
    // Only allow numbers
    this.value = this.value.replace(/[^0-9]/g, '');
    
    if (this.value.length > 5) {
        this.value = this.value.slice(0, 5);
    }
});
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>