<?php
$page_title = 'Two-Factor Verification';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/totp.php';

if (!isset($_SESSION['pending_2fa_user_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
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

$user_id = (int)$_SESSION['pending_2fa_user_id'];
$error = '';
$resent = false;

$pendingUserStmt = $pdo->prepare("SELECT email, two_factor_method FROM users WHERE id = ? AND role = 'admin'");
$pendingUserStmt->execute([$user_id]);
$pendingUser = $pendingUserStmt->fetch();
if (!$pendingUser) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}
$method = $pendingUser['two_factor_method'] ?? 'totp';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['resend'] ?? '') === '1') {
    if (validate_csrf_token($_POST['csrf_token'] ?? '') && $method === 'email') {
        $otpCode = sprintf('%06d', random_int(100000, 999999));
        $pdo->prepare("UPDATE users SET email_otp_code = ?, email_otp_expires = DATE_ADD(NOW(), INTERVAL 10 MINUTE) WHERE id = ?")->execute([$otpCode, $user_id]);
        send_login_2fa_email($pendingUser['email'], $otpCode);
        $resent = true;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please refresh and try again.';
    } else {
        $code = trim($_POST['code'] ?? '');
        if (empty($code) || !preg_match('/^\d{6}$/', $code)) {
            $error = 'Please enter a valid 6-digit code.';
        } else {
            $stmt = $pdo->prepare("SELECT id, username, full_name, role, email, two_factor_method, two_factor_secret, email_otp_code, email_otp_expires FROM users WHERE id = ? AND role = 'admin' AND two_factor_enabled = 1");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            $verified = false;
            if ($user) {
                if ($user['two_factor_method'] === 'email') {
                    $notExpired = $user['email_otp_expires'] && strtotime($user['email_otp_expires']) > time();
                    $verified = $notExpired && !empty($user['email_otp_code']) && hash_equals($user['email_otp_code'], $code);
                } else {
                    $verified = !empty($user['two_factor_secret']) && TOTP::verify($user['two_factor_secret'], $code);
                }
            }

            if ($verified) {
                $pdo->prepare("UPDATE users SET email_otp_code = NULL, email_otp_expires = NULL WHERE id = ?")->execute([$user['id']]);
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['admin_ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
                $_SESSION['admin_ua_hash'] = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
                $remember_me = !empty($_SESSION['pending_2fa_remember_me']);
                apply_auth_session_preferences($remember_me);
                record_user_session($user['id'], $user['role']);
                unset($_SESSION['pending_2fa_user_id']);
                unset($_SESSION['pending_2fa_remember_me']);
                log_audit_event('admin_login', 'user', $user['id'], 'Admin logged in (2FA verified).');
                header('Location: ' . BASE_URL . '/admin/dashboard.php');
                exit;
            } else {
                log_audit_event('admin_2fa_failed', 'user', $user_id, 'Invalid 2FA code entered.');
                $error = 'Invalid or expired code. Please try again.';
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
                        <i class="fas fa-shield-alt fa-3x mb-3" style="color: #F39C6A;"></i>
                        <h2 class="card-title h3">Two-Factor Verification</h2>
                        <p class="text-muted">
                            <?php echo $method === 'email'
                                ? 'Enter the 6-digit code we emailed to you.'
                                : 'Enter the 6-digit code from your authenticator app.'; ?>
                        </p>
                    </div>

                    <?php if ($resent): ?>
                        <div class="alert alert-success">A new code has been sent to your email.</div>
                    <?php endif; ?>

                    <?php if (!empty($rate_limit_error)): ?>
                        <div class="alert alert-warning"><?php echo htmlspecialchars($rate_limit_error); ?></div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <div class="mb-3">
                            <label for="code" class="form-label">Authentication Code</label>
                            <input type="text" class="form-control form-control-lg text-center" id="code" name="code"
                                   maxlength="6" pattern="\d{6}" inputmode="numeric" autocomplete="one-time-code"
                                   placeholder="000000" autofocus required style="letter-spacing: 8px; font-size: 1.5rem;">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-check me-2"></i> Verify
                        </button>
                    </form>

                    <?php if ($method === 'email'): ?>
                    <form method="POST" class="mt-2 text-center">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <input type="hidden" name="resend" value="1">
                        <button type="submit" class="btn btn-link btn-sm">Resend code</button>
                    </form>
                    <?php endif; ?>

                    <div class="text-center mt-3">
                        <a href="login.php" class="text-muted small">Cancel and sign in as someone else</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
