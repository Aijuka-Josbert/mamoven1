<?php
$page_title = 'Login';
// The header file starts the session and includes necessary configs.
include_once __DIR__ . '/../includes/header.php';

// If user is already logged in, redirect them away from the login page.
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: '. BASE_URL . '/admin/dashboard.php');
    } else {
        header('Location: ' . BASE_URL . '/index.php');
    }
    exit;
}

$error = '';
$timeout_message = '';
$redirect_target = trim($_GET['redirect'] ?? ($_POST['redirect'] ?? ''));

if (isset($_GET['timeout'])) {
    $timeout_message = 'You were logged out due to inactivity. Please sign in again.';
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
  
    if (empty($username) || empty($password)) {
        $error = 'Please fill in both username/email and password.';
    } else {
        try {
            // Check for user by username OR email
            $stmt = $pdo->prepare("SELECT id, username, email, password, full_name, role, is_verified FROM users WHERE username = :username OR email = :email");
            $stmt->execute(['username' => $username, 'email' => $username]);
            $user = $stmt->fetch();
            
            // Verify user exists and password is correct
            if ($user && password_verify($password, $user['password'])) {
                
                // Block unverified users from logging in
                if ($user['role'] === 'customer' && isset($user['is_verified']) && $user['is_verified'] == 0) {
                    $_SESSION['verify_email'] = $user['email'];
                    $_SESSION['pending_remember_me'] = $remember_me ? 1 : 0;

                    if (!empty($redirect_target) && strpos($redirect_target, 'http') !== 0 && strpos($redirect_target, '//') !== 0) {
                        $_SESSION['redirect_after_login'] = BASE_URL . '/' . ltrim($redirect_target, '/');
                    }

                    header('Location: ' . BASE_URL . '/auth/verify.php');
                    exit;
                }

                // Regenerate session ID for security
                session_regenerate_id(true);

                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                apply_auth_session_preferences($remember_me);

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: ' . BASE_URL . '/admin/dashboard.php');
                } else {
                    // Respect optional redirect param if provided (must be a safe local path)
                    if (!empty($redirect_target) && strpos($redirect_target, 'http') !== 0 && strpos($redirect_target, '//') !== 0) {
                        // normalize leading slash
                        $redirect_target = ltrim($redirect_target, '/');
                        header('Location: ' . BASE_URL . '/' . $redirect_target);
                    } else {
                        header('Location: ' . BASE_URL . '/index.php');
                    }
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
            <div class="card shadow-sm border-0 login-card login-variant-warm">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <img src="../assets/images/logo.png" alt="Logo" style="height: 80px;" class="mb-3">
                        <h2 class="card-title h3">Welcome Back!</h2>
                        <p class="text-muted">Sign in to continue to <?php echo SITE_NAME; ?>.</p>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($timeout_message)): ?>
                        <div class="alert alert-warning" role="alert">
                            <?php echo htmlspecialchars($timeout_message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect_target); ?>">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username or Email</label>
                            <input type="text" class="form-control form-control-lg" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required autofocus>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                                <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me" value="1" <?php echo isset($_POST['remember_me']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="remember_me">Remember me</label>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i> Sign In
                            </button>
                        </div>

                        <div class="text-center">
                            <p class="mb-0 text-muted">Don't have an account? 
                                <a href="<?php echo BASE_URL; ?>/auth/register.php">Sign up here</a>
                            </p>
                            <p class="mb-0 text-muted mt-2">
                                <a href="forgot_password.php">Forgot your password?</a>
                            </p>
                        </div>
                    </form>

                    <!-- Demo Accounts Info
                    <div class="mt-4 p-3 bg-light rounded border text-center">
                        <h6 class="mb-2 fw-bold">Demo Account</h6>
                        <small class="text-muted d-block">
                            <strong>Admin:</strong> admin / Mama2023!
                        </small>
                    </div> -->

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