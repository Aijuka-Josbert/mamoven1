<?php
// No need to include database here, header.php does it.
$page_title = 'Login';
// The header file starts the session and includes necessary configs.
include_once __DIR__ . '/../includes/header.php';

// If user is already logged in, redirect them away from the login page.
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: ' . '../admin/dashboard.php');
    } else {
        header('Location: ' . '../index.php');
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

            // // Safer debugging - only attempt to access array elements if $user is an array
            // var_dump($user);
            // var_dump($password);
            // if ($user) {
            //     var_dump(password_verify($password, $user['password']));
            // } else {
            //     echo "No user found with username/email: " . htmlspecialchars($username);
            // }
            
            // // Then comment out before going to production
            
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
                    // FIX: Add '../' prefix
                    header('Location: ' . '../admin/dashboard.php');
                } else {
                    // FIX: Add '../' prefix for index.php
                    $redirect_url = $_GET['redirect'] ?? '../index.php';
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
                        <img src="../assets/images/logo.jpeg" alt="Logo" style="height: 60px;" class="mb-3">
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
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required>
                                <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i> Sign In
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-0 text-muted">Don't have an account? 
                            <a href="register.php">Sign up here</a>
                        </p>
                    </div>

                    <!-- Demo Accounts Info -->
                    <div class="mt-4 p-3 bg-light rounded border">
                        <h6 class="text-center mb-2 fw-bold">Demo Account</h6>
                        <small class="text-muted d-block text-center">
                            <strong>Admin:</strong> admin / admin123
                        </small>
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
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>