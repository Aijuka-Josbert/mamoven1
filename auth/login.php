<?php
$page_title = 'Login';
include_once __DIR__ . '/../includes/header.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: ' . BASE_URL . '/admin/dashboard.php');
    } else {
        header('Location: ' . BASE_URL . '/index.php');
    }
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

$error = '';
$timeout_message = '';
$redirect_target = trim($_GET['redirect'] ?? ($_POST['redirect'] ?? ''));

if (isset($_GET['timeout'])) {
    $timeout_message = 'You were logged out due to inactivity. Please sign in again.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $remember_me = isset($_POST['remember_me']);

        if (empty($username) || empty($password)) {
            $error = 'Please fill in both username/email and password.';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id, username, email, password, full_name, role, is_verified FROM users WHERE username = :username OR email = :email");
                $stmt->execute(['username' => $username, 'email' => $username]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    // Block unverified customers
                    if ($user['role'] === 'customer' && isset($user['is_verified']) && $user['is_verified'] == 0) {
                        $_SESSION['verify_email'] = $user['email'];
                        $_SESSION['pending_remember_me'] = $remember_me ? 1 : 0;
                        if (!empty($redirect_target) && strpos($redirect_target, 'http') !== 0 && strpos($redirect_target, '//') !== 0) {
                            $_SESSION['redirect_after_login'] = BASE_URL . '/' . ltrim($redirect_target, '/');
                        }
                        header('Location: ' . BASE_URL . '/auth/verify.php');
                        exit;
                    }

                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['email'] = $user['email'];
                    apply_auth_session_preferences($remember_me);

                    if ($user['role'] === 'admin') {
                        header('Location: ' . BASE_URL . '/admin/dashboard.php');
                    } else {
                        if (!empty($redirect_target) && strpos($redirect_target, 'http') !== 0 && strpos($redirect_target, '//') !== 0) {
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
                error_log('Login error: ' . $e->getMessage());
                $error = 'A database error occurred. Please try again later.';
            }
        }
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0 auth-card">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <img src="../assets/images/logo.png" alt="Logo" style="height: 80px;" class="mb-3">
                        <h2 class="card-title h3">Welcome Back!</h2>
                        <p class="text-muted">Sign in to continue to <?php echo SITE_NAME; ?>.</p>
                    </div>

                    <?php if (!empty($rate_limit_error)): ?>
                        <div class="alert alert-danger" id="rate-limit-alert" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($rate_limit_error); ?>
                            <?php if ($rate_limit_retry_after > 0): ?>
                                <br>
                                <span id="countdown-timer">Wait <strong><?php echo $rate_limit_retry_after; ?></strong> seconds before trying again.</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

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

                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="login-form">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
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
                            <button type="submit" class="btn btn-primary btn-lg" id="login-submit-btn">
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
                </div>
            </div>
        </div>
    </div>
</div>

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
<?php if ($rate_limit_retry_after > 0): ?>
(function() {
    let secondsLeft = <?php echo $rate_limit_retry_after; ?>;
    const timerSpan = document.getElementById('countdown-timer');
    const submitBtn = document.getElementById('login-submit-btn');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-clock me-2"></i> Please wait...';
    }
    const interval = setInterval(function() {
        secondsLeft--;
        if (timerSpan) timerSpan.innerHTML = 'Wait <strong>' + secondsLeft + '</strong> seconds before trying again.';
        if (secondsLeft <= 0) {
            clearInterval(interval);
            if (timerSpan) timerSpan.innerHTML = 'You can now try again.';
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i> Sign In';
            }
        }
    }, 1000);
})();
<?php endif; ?>
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>