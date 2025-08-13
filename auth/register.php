<?php
$page_title = 'Create Account';
include_once __DIR__ . '/../includes/header.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ' . '../index.php');
    exit;
}

$errors = [];
$inputs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and store inputs
    $inputs['full_name'] = trim($_POST['full_name'] ?? '');
    $inputs['username'] = trim($_POST['username'] ?? '');
    $inputs['email'] = trim($_POST['email'] ?? '');
    $inputs['password'] = $_POST['password'] ?? '';
    $inputs['confirm_password'] = $_POST['confirm_password'] ?? '';
    
    // --- Validation ---
    if (empty($inputs['full_name'])) $errors[] = 'Full name is required.';
    if (empty($inputs['username'])) $errors[] = 'Username is required.';
    if (empty($inputs['email'])) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($inputs['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }
    
    if (empty($inputs['password'])) {
        $errors[] = 'Password is required.';
    } elseif (strlen($inputs['password']) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }
    
    if ($inputs['password'] !== $inputs['confirm_password']) {
        $errors[] = 'Passwords do not match.';
    }
    
    // --- Check for existing user if basic validation passes ---
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
            $stmt->execute(['username' => $inputs['username'], 'email' => $inputs['email']]);
            if ($stmt->fetch()) {
                $errors[] = 'An account with this username or email already exists.';
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
                        <img src="assets/images/logo.png" alt="Logo" style="height: 60px;" class="mb-3">
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

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($inputs['full_name'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($inputs['username'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($inputs['email'] ?? ''); ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="form-text">Minimum 6 characters</div>
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
                            <a href="auth/login.php">Sign in</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>