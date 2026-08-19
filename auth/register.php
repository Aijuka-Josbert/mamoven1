<?php
$page_title = 'Create Account';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/index.php');
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

$errors = [];
$inputs = [
    'full_name' => '',
    'username' => '',
    'email' => '',
    'phone' => '',
    'address' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please refresh the page and try again.';
    } else {
        $inputs['full_name'] = trim($_POST['full_name'] ?? '');
        $inputs['username'] = trim($_POST['username'] ?? '');
        $inputs['email'] = trim($_POST['email'] ?? '');
        $inputs['phone'] = trim($_POST['phone'] ?? '');
        $inputs['address'] = trim($_POST['address'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $agree_terms = isset($_POST['agree_terms']);

        // --- Validation ---
        if (empty($inputs['full_name'])) {
            $errors[] = 'Full name is required.';
        }

        if (empty($inputs['username'])) {
            $errors[] = 'Username is required.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $inputs['username'])) {
            $errors[] = 'Username must be 3-30 characters and contain only letters, numbers, and underscores.';
        }

        if (empty($inputs['email'])) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($inputs['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }

        if (!empty($inputs['phone']) && (!preg_match('/^[\+]?[0-9\s\(\)\-\.]+$/', $inputs['phone']) || strlen(preg_replace('/\D/', '', $inputs['phone'])) < 7)) {
            $errors[] = 'Please enter a valid phone number.';
        }

        if (empty($password)) {
            $errors[] = 'Password is required.';
        } elseif (strlen($password) < 12) {
            $errors[] = 'Password must be at least 12 characters.';
        } elseif ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match.';
        } elseif (is_password_compromised($password)) {
            $errors[] = 'That password has appeared in a known data breach. Please choose a different one.';
        }

        if (!$agree_terms) {
            $errors[] = 'You must agree to the Terms of Service and Privacy Policy.';
        }

        // --- Uniqueness check ---
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$inputs['username'], $inputs['email']]);
                if ($stmt->fetch()) {
                    $errors[] = 'That username or email is already registered.';
                }
            } catch (PDOException $e) {
                error_log('Register uniqueness check failed: ' . $e->getMessage());
                $errors[] = 'A database error occurred. Please try again.';
            }
        }

        // --- Create the account ---
        if (empty($errors)) {
            try {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $verification_code = sprintf('%06d', mt_rand(100000, 999999));

                $stmt = $pdo->prepare("
                    INSERT INTO users (username, email, password, full_name, phone, address, role, verification_code, is_verified)
                    VALUES (?, ?, ?, ?, ?, ?, 'customer', ?, 0)
                ");
                $stmt->execute([
                    $inputs['username'],
                    $inputs['email'],
                    $hashed_password,
                    $inputs['full_name'],
                    $inputs['phone'],
                    $inputs['address'],
                    $verification_code,
                ]);

                // Send the verification email; registration still succeeds even if this fails —
                // the user can request a new code from the verify page.
                $emailSent = true;
                try {
                    $mail = new PHPMailer(true);
                    configure_mailer_transport($mail);
                    $mail->setFrom(default_mail_from_address(), SITE_NAME);
                    $mail->addAddress($inputs['email'], $inputs['full_name']);
                    $mail->isHTML(true);
                    $mail->Subject = 'Welcome to ' . SITE_NAME . ' - Verify Your Email';
                    $mail->Body = "<html>
                                   <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                                       <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                                           <div style='text-align: center; margin-bottom: 30px;'>
                                               " . email_logo_html($mail, 60) . "
                                               <h2 style='color: #8B4513; margin: 0;'>Welcome, " . htmlspecialchars($inputs['full_name']) . "!</h2>
                                           </div>
                                           <p>Thanks for creating an account with " . SITE_NAME . ". Please verify your email using the code below:</p>
                                           <div style='background: #f9f6f0; padding: 20px; border-radius: 8px; margin: 25px 0; text-align: center;'>
                                               <h1 style='color: #8B4513; margin: 0; font-size: 32px; letter-spacing: 5px;'>$verification_code</h1>
                                           </div>
                                           <p>Enter this code on the verification page to activate your account.</p>
                                           <p style='margin-top: 30px;'>Best regards,<br><strong>The " . SITE_NAME . " Team</strong></p>
                                       </div>
                                   </body>
                                   </html>";
                    $mail->AltBody = "Welcome to " . SITE_NAME . "! Your verification code is: $verification_code";
                    $emailSent = send_mail_with_fallback($mail);
                } catch (Exception $e) {
                    error_log('Registration verification email failed: ' . $e->getMessage());
                    $emailSent = false;
                }

                session_regenerate_id(true);
                $_SESSION['verify_email'] = $inputs['email'];
                $_SESSION['pending_remember_me'] = 0;
                if (!$emailSent) {
                    $_SESSION['verification_failed'] = true;
                }
                header('Location: ' . BASE_URL . '/auth/verify.php');
                exit;
            } catch (PDOException $e) {
                error_log('Registration failed: ' . $e->getMessage());
                $errors[] = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm border-0 auth-card">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <img src="../assets/images/logo.png" alt="Logo" style="height: 70px;" class="mb-3">
                        <h2 class="card-title h3">Create Account</h2>
                        <p class="text-muted">Join us to start ordering delicious treats!</p>
                    </div>

                    <?php if (!empty($rate_limit_error)): ?>
                        <div class="alert alert-warning" role="alert">
                            <?php echo htmlspecialchars($rate_limit_error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name"
                                           value="<?php echo htmlspecialchars($inputs['full_name']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username *</label>
                                    <input type="text" class="form-control" id="username" name="username"
                                           value="<?php echo htmlspecialchars($inputs['username']); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?php echo htmlspecialchars($inputs['email']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone"
                                   value="<?php echo htmlspecialchars($inputs['phone']); ?>"
                                   placeholder="+256 700 123456">
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2"
                                     placeholder="Your delivery address"><?php echo htmlspecialchars($inputs['address']); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password *</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password', 'password-icon')">
                                            <i class="fas fa-eye" id="password-icon"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Minimum 12 characters</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm Password *</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password', 'confirm-password-icon')">
                                            <i class="fas fa-eye" id="confirm-password-icon"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="agree_terms" name="agree_terms" required>
                            <label class="form-check-label" for="agree_terms">
                                I agree to the <a href="../footer_pages/terms_of_service.php" class="text-primary">Terms of Service</a> and
                                <a href="../footer_pages/privacy_policy.php" class="text-primary">Privacy Policy</a>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-user-plus me-2"></i> Create Account
                        </button>
                    </form>

                    <div class="text-center">
                        <p class="mb-0">Already have an account?
                            <a href="login.php" class="text-primary">Sign in here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId, iconId) {
    const passwordField = document.getElementById(fieldId);
    const passwordIcon = document.getElementById(iconId);

    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        passwordIcon.className = 'fas fa-eye-slash';
    } else {
        passwordField.type = 'password';
        passwordIcon.className = 'fas fa-eye';
    }
}

document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    if (password !== this.value) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
