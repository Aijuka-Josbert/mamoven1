<?php
$page_title = 'Create Account';
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
    $inputs['password'] = $_POST['password'];
    $inputs['confirm_password'] = $_POST['confirm_password'];

    $full_name = $inputs['full_name'];
    $username = $inputs['username'];
    $email = $inputs['email'];
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
            
            $stmt = $pdo->prepare(
                "INSERT INTO users (full_name, username, email, password, role) VALUES (:full_name, :username, :email, :password, 'customer')"
            );
            
            $stmt->execute([
                'full_name' => $inputs['full_name'],
                'username' => $inputs['username'],
                'email' => $inputs['email'],
                'password' => $hashed_password
            ]);
            
            // Get the new user's ID
            $user_id = $pdo->lastInsertId();
            
            // Send welcome email
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
                $mail->addAddress($inputs['email'], $inputs['full_name']);

                $mail->isHTML(true);
                $mail->Subject = "Welcome to " . SITE_NAME . "!";
                
                $mail->Body = "
                <html>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                        <div style='text-align: center; margin-bottom: 30px;'>
                            <h1 style='color: #8B4513; margin-bottom: 10px;'>Welcome to Mama's Oven!</h1>
                            <img src='" . BASE_URL . "/assets/images/logo.jpeg' alt='Mama\\'s Oven Logo' style='height: 80px;'>
                        </div>
                        
                        <p>Dear {$inputs['full_name']},</p>
                        
                        <p>Welcome to the Mama's Oven family! We're thrilled to have you join our community of baking enthusiasts.</p>
                        
                        <div style='background: #f9f6f0; padding: 20px; border-radius: 8px; margin: 25px 0; border-left: 4px solid #8B4513;'>
                            <h3 style='margin-top: 0; color: #8B4513;'>Your Account Details</h3>
                            <p><strong>Full Name:</strong> {$inputs['full_name']}</p>
                            <p><strong>Username:</strong> {$inputs['username']}</p>
                            <p><strong>Email:</strong> {$inputs['email']}</p>
                        </div>

                        <div style='background: #fff8e1; padding: 20px; border-radius: 8px; margin: 25px 0;'>
                            <h3 style='margin-top: 0; color: #8B4513;'>What can you do now?</h3>
                            <ul style='margin: 0; padding-left: 20px;'>
                                <li style='margin-bottom: 8px;'>Browse our delicious range of <a href='" . BASE_URL . "/products.php' style='color: #8B4513;'>baked goods</a></li>
                                <li style='margin-bottom: 8px;'>Add items to your cart and place orders</li>
                                <li style='margin-bottom: 8px;'>Track your <a href='" . BASE_URL . "/orders.php' style='color: #8B4513;'>order history</a></li>
                                <li style='margin-bottom: 8px;'>Enjoy convenient delivery to your doorstep</li>
                                <li style='margin-bottom: 8px;'>Get updates on new products and special offers</li>
                            </ul>
                        </div>

                        <div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 25px 0;'>
                            <h3 style='margin-top: 0; color: #2d5a2d;'>Why Choose Mama's Oven?</h3>
                            <ul style='margin: 0; padding-left: 20px;'>
                                <li style='margin-bottom: 8px;'><strong>Fresh Daily:</strong> All our products are baked fresh every day</li>
                                <li style='margin-bottom: 8px;'><strong>Quality Ingredients:</strong> We use only the finest, locally-sourced ingredients</li>
                                <li style='margin-bottom: 8px;'><strong>Fast Delivery:</strong> Quick and reliable delivery across Kampala</li>
                                <li style='margin-bottom: 8px;'><strong>Cash on Delivery:</strong> Pay conveniently when your order arrives</li>
                            </ul>
                        </div>

                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='" . BASE_URL . "/products.php' style='background: #8B4513; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;'>Start Shopping Now</a>
                        </div>

                        <p>If you have any questions or need assistance, don't hesitate to <a href='" . BASE_URL . "/contact.php' style='color: #8B4513;'>contact our friendly team</a>.</p>
                        
                        <p style='margin-top: 30px;'>
                            Once again, welcome to Mama's Oven!<br>
                            <strong>The Mama's Oven Team</strong>
                        </p>
                        
                        <hr style='border: none; border-top: 1px solid #ddd; margin: 30px 0;'>
                        <p style='font-size: 12px; color: #666; text-align: center;'>
                            You're receiving this email because you created an account with Mama's Oven.<br>
                            Visit us at <a href='" . BASE_URL . "' style='color: #8B4513;'>" . BASE_URL . "</a>
                        </p>
                    </div>
                </body>
                </html>";

                $mail->send();
            } catch (Exception $e) {
                // Log email error but don't fail the registration
                error_log('Welcome email failed: ' . $e->getMessage());
            }
            
            // Auto login after registration
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $inputs['username'];
            $_SESSION['full_name'] = $inputs['full_name'];
            $_SESSION['role'] = 'customer';
            $_SESSION['email'] = $inputs['email'];
            
            // Redirect to a welcome page or homepage
            header('Location: ' . '../index.php?registered=1');
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

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="form-text">Minimum 8 characters, 1 uppercase letter, 1 special character</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
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
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>