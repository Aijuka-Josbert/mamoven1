<?php
// No need to include database here, header.php does it.
$page_title = 'Login';
// The header file starts the session and includes necessary configs.
include_once __DIR__ . '/../includes/header.php';

// If user is already logged in, redirect them away from the login page.
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: ' . asset_url('admin/dashboard.php'));
    } else {
        header('Location: ' . asset_url('index.php'));
    }
    exit;
}

$error = '';
// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
  
    if (empty($username) || empty($password)) {
        $error = 'Please fill in both username/email and password.';
    } else {
        try {
            // Check for user by username OR email
            $stmt = $pdo->prepare("SELECT id, username, email, password, full_name, role FROM users WHERE username = :username OR email = :email");
            $stmt->execute(['username' => $username, 'email' => $username]);
            $user = $stmt->fetch();
            
            // Verify user exists and password is correct
            if ($user && password_verify($password, $user['password'])) {
                // Regenerate session ID for security
                session_regenerate_id(true);

                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: ' . asset_url('admin/dashboard.php'));
                } else {
                    // Redirect to the page they were trying to access, or homepage
                    $redirect_url = $_GET['redirect'] ?? asset_url('index.php');
                    header('Location: ' . $redirect_url);
                }
                exit;
            } else {
                $error = 'Invalid username or password. Please try again.';
            }
        } catch (PDOException $e) {
            // In production, you would log this error.
            $error = 'A database error occurred. Please try again later.';
        }
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <img src="<?php echo asset_url('assets/images/logo.png'); ?>" alt="Logo" style="height: 60px;" class="mb-3">
                        <h2 class="card-title h3">Welcome Back!</h2>
                        <p class="text-muted">Sign in to continue to Mama's Oven.</p>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username or Email</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required autofocus>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i> Sign In
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-0 text-muted">Don't have an account? 
                            <a href="<?php echo asset_url('auth/register.php'); ?>">Sign up here</a>
                        </p>
                    </div>

                    <!-- Demo Accounts Info -->
                    <div class="mt-4 p-3 bg-light rounded border">
                        <h6 class="text-center mb-2 fw-bold">Demo Account</h6>
                        <small class="text-muted d-block text-center">
                            <strong>Admin:</strong> admin / admin123
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>