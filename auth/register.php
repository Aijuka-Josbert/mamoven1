<?php
$page_title = 'Create Account';
ini_set('pcre.jit', '0'); // Fix for PHPMailer JIT memory allocation issue
include_once __DIR__ . '/../includes/header.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ' . '../index.php');
    exit;
}

$errors = [];
$inputs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputs['full_name'] = $_POST['full_name'];
    $inputs['username'] = $_POST['username'];
    $inputs['email'] = $_POST['email'];
    $inputs['phone'] = $_POST['phone'] ?? '';
    $inputs['password'] = $_POST['password'];
    $inputs['confirm_password'] = $_POST['confirm_password'];

    $full_name = $inputs['full_name'];
    $username = $inputs['username'];
    $email = $inputs['email'];
    $phone = trim($inputs['phone']);
    $password = $inputs['password'];
    $confirm_password = $inputs['confirm_password'];

    // --- Validation ---
    if (empty($full_name)) $errors[] = 'Full name is required.';
    if (empty($username)) $errors[] = 'Username is required.';
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }
    if (empty($phone)) {
        $errors[] = 'Phone number is required.';
    } elseif (!preg_match('/^[\+]?[0-9\s\(\)\-\.]+$/', $phone) || strlen(preg_replace('/\D/', '', $phone)) < 7) {
        $errors[] = 'Invalid phone number format (minimum 7 digits required).';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $errors[] = 'Password must contain at least one special character (!@#$%^&*(),.?":{}|<>).';
    }
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }
    
    // --- Check for existing user if basic validation passes ---
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email OR full_name = :full_name");
            $stmt->execute(['username' => $inputs['username'], 'email' => $inputs['email'], 'full_name' => $inputs['full_name']]);
            if ($stmt->fetch()) {
                $errors[] = 'An account with this username/full_name or email already exists.';
            }
        } catch (PDOException $e) {
            $errors[] = 'A database error occurred. Please try again.';
        }
    }
    
    // --- Register user if no errors ---
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($inputs['password'], PASSWORD_DEFAULT);
            $verification_code = sprintf("%06d", mt_rand(100000, 999999));
            
            $stmt = $pdo->prepare(
                "INSERT INTO users (full_name, username, email, phone, password, role, verification_code, is_verified) VALUES (:full_name, :username, :email, :phone, :password, 'customer', :verification_code, 0)"
            );
            
            $stmt->execute([
                'full_name' => $inputs['full_name'],
                'username' => $inputs['username'],
                'email' => $inputs['email'],
                'phone' => $phone,
                'password' => $hashed_password,
                'verification_code' => $verification_code
            ]);
            
            // Get the new user's ID
            $user_id = $pdo->lastInsertId();
            
            // Send verification email
            try {
                $mail = new PHPMailer(true);
                
                // SMTP configuration
                $mail->isSMTP();
                $mail->Host = SMTP_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = SMTP_USER;
                $mail->Password = SMTP_PASS;
                $mail->SMTPSecure = SMTP_SECURE;
                $mail->Port = SMTP_PORT;

                $mail->setFrom(SMTP_USER, SITE_NAME);
                $mail->addReplyTo(SMTP_USER, SITE_NAME);
                $mail->addAddress($inputs['email'], $inputs['full_name']);

                // SPAM Prevention: Always provide a Plain Text version along with the HTML
                $mail->isHTML(true);
                $mail->Subject = "Your Account Verification Code - " . SITE_NAME;
                
                // Plain Text Version
                $plainTextCode = "Welcome to " . SITE_NAME . "!\n\n";
                $plainTextCode .= "Dear {$inputs['full_name']},\n\n";
                $plainTextCode .= "Your 6-digit verification code is: {$verification_code}\n\n";
                $plainTextCode .= "Please enter this code on the verification page to activate your account.\n\n";
                $plainTextCode .= "Link: " . BASE_URL . "/auth/verify.php?email=" . urlencode($inputs['email']) . "\n\n";
                $plainTextCode .= "If you did not request this, please ignore this email.\n\n";
                $plainTextCode .= "Best regards,\nThe " . SITE_NAME . " Team";
                
                $mail->AltBody = $plainTextCode;

                $mail->Body = "
                <html>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                        <div style='text-align: center; margin-bottom: 30px;'>
                            <h1 style='color: #8B4513; margin-bottom: 10px;'>Verify Your Account!</h1>
                        </div>
                        
                        <p>Dear {$inputs['full_name']},</p>
                        
                        <p>Your 6-digit verification code is: <strong style='font-size: 24px; color: #8B4513;'>{$verification_code}</strong></p>
                        
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='" . BASE_URL . "/auth/verify.php?email=" . urlencode($inputs['email']) . "' style='background: #8B4513; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;'>Go to Verification Page</a>
                        </div>

                        <p>If you didn't create an account, you can safely ignore this email.</p>
                        
                        <p style='margin-top: 30px;'>
                            Welcome to Mama's Oven!<br>
                            <strong>The Mama's Oven Team</strong>
                        </p>
                    </div>
                </body>
                </html>";

                if(!$mail->send()) {
                    error_log('Verification email failed! ErrorInfo: ' . $mail->ErrorInfo . ', User/Pass: ' . SMTP_USER . ' / ' . (empty(SMTP_PASS) ? 'Empty' : 'Set'));
                }
            } catch (Exception $e) {
                // Log email error but don't fail the registration
                error_log('Verification email failed Exception: ' . $e->getMessage());
            }
            
            // Do NOT login immediately. They must verify.
            session_regenerate_id(true);
            $_SESSION['verify_email'] = $inputs['email'];
            
            // Redirect to verify page
            header('Location: ' . BASE_URL . '/auth/verify.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Registration failed due to a server error. Please try again.';
        }
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <img src="../assets/images/logo.jpeg" alt="Logo" style="height: 60px;" class="mb-3">
                        <h2 class="card-title h3">Create Your Account</h2>
                        <p class="text-muted">Join us to start ordering delicious treats!</p>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <p class="mb-0"><strong>Please fix the following errors:</strong></p>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       value="<?php echo htmlspecialchars($inputs['full_name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($inputs['username'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($inputs['email'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                   placeholder="07XXXXXXXX" 
                                   value="<?php echo htmlspecialchars($inputs['phone'] ?? ''); ?>" required>
                            <div class="form-text">Required for delivery. Format: 07XXXXXXXX</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                                <div class="form-text">Minimum 8 characters, 1 uppercase letter, 1 special character</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    <span class="input-group-text" id="toggleConfirmPassword" style="cursor: pointer;">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="agree_terms" required>
                            <label class="form-check-label" for="agree_terms">
                                I agree to the <a href="#">Terms & Conditions</a>
                            </label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i> Create Account
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-0 text-muted">Already have an account? 
                            <a href="../auth/login.php">Sign in</a>
                        </p>
                    </div>
                    
                    <!-- Password visibility toggle script -->
                    <script>
                        document.getElementById('togglePassword').addEventListener('click', function() {
                            const passwordInput = document.getElementById('password');
                            const icon = this.querySelector('i');
                            
                            if (passwordInput.type === 'password') {
                                passwordInput.type = 'text';
                                icon.classList.remove('fa-eye');
                                icon.classList.add('fa-eye-slash');
                            } else {
                                passwordInput.type = 'password';
                                icon.classList.remove('fa-eye-slash');
                                icon.classList.add('fa-eye');
                            }
                        });

                        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
                            const passwordInput = document.getElementById('confirm_password');
                            const icon = this.querySelector('i');
                            
                            if (passwordInput.type === 'password') {
                                passwordInput.type = 'text';
                                icon.classList.remove('fa-eye');
                                icon.classList.add('fa-eye-slash');
                            } else {
                                passwordInput.type = 'password';
                                icon.classList.remove('fa-eye-slash');
                                icon.classList.add('fa-eye');
                            }
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>