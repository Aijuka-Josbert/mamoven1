<?php
// Load .env file (if present)
function load_dotenv($path) {
    if (!is_readable($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0 || strpos($line, '//') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
            (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
            $value = substr($value, 1, -1);
        }
        putenv($name . '=' . $value);
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}
load_dotenv(__DIR__ . '/../.env');

function env_value($key, $default = '') {
    $value = getenv($key);
    if ($value !== false) return $value;
    return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
}

// Session cookie settings (only if session not started)
if (!headers_sent() && session_status() === PHP_SESSION_NONE) {
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

$httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isLocalhost = in_array($httpHost, ['localhost', '127.0.0.1', 'localhost:8000', '127.0.0.1:80'], true);
$isInfinityHosting = stripos($httpHost, 'infinityfreeapp.com') !== false ||
                    stripos($httpHost, '.epizy.com') !== false ||
                    stripos($httpHost, '.rf.gd') !== false;

$appEnv = strtolower(env_value('APP_ENV', $isLocalhost ? 'development' : 'production'));
$isProduction = $appEnv === 'production';

ini_set('pcre.jit', '0');
error_reporting(E_ALL);
ini_set('display_errors', $isProduction ? '0' : '1');
ini_set('log_errors', '1');

$host = env_value('DB_HOST', $isLocalhost ? 'localhost' : 'sql303.infinityfree.com');
$dbname = env_value('DB_NAME', $isLocalhost ? 'mamaove' : 'if0_40616210_mamaove');
$username = env_value('DB_USER', $isLocalhost ? 'root' : 'if0_40616210');
$password = env_value('DB_PASS', '');
$charset = env_value('DB_CHARSET', 'utf8mb4');

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    error_log('Database bootstrap failed: ' . $e->getMessage());
    if (!$isProduction) {
        throw $e;
    }
    // Friendly error page (simplified)
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html><head><title>Configuration Issue</title></head><body>';
    echo '<h1>Database Connection Error</h1>';
    echo '<p>Please check your .env file settings.</p>';
    echo '</body></html>';
    exit;
}

// Constants
define('BASE_URL', rtrim(env_value('BASE_URL', $isLocalhost ? 'http://localhost/mamoven1' : 'https://mamoven.infinityfreeapp.com/mamoven1'), '/'));
define('IS_PRODUCTION', $isProduction);
define('IS_INFINITY_HOSTING', $isInfinityHosting);
define('SITE_NAME', env_value('SITE_NAME', "Mama's Oven"));
define('EMAIL_TRANSPORT', strtolower(env_value('EMAIL_TRANSPORT', 'auto')));
define('SMTP_HOST', env_value('SMTP_HOST', 'smtp.gmail.com'));
define('SMTP_PORT', (int)env_value('SMTP_PORT', 587));
define('SMTP_USER', env_value('SMTP_USER', ''));
define('SMTP_PASS', env_value('SMTP_PASS', ''));
define('SMTP_SECURE', env_value('SMTP_SECURE', 'tls'));
define('ADMIN_EMAIL', 'mamasovenug@gmail.com');
define('MAIL_FROM', env_value('MAIL_FROM', 'mamasovenug@gmail.com'));
define('PASSWORD_RESET_EXPIRY', 7200); // 2 hours
define('SESSION_INACTIVITY_TIMEOUT', (int)env_value('SESSION_INACTIVITY_TIMEOUT', 900));
define('REMEMBER_ME_LIFETIME', (int)env_value('REMEMBER_ME_LIFETIME', 2592000));
define('REMEMBER_ME_COOKIE', 'mamoven_remember');

function request_expects_json_response() {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    if (strpos($scriptName, '/api/') !== false) return true;
    $accept = strtolower($_SERVER['HTTP_ACCEPT'] ?? '');
    if (strpos($accept, 'application/json') !== false) return true;
    return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
}

function client_ip_address() {
    $cfIp = trim($_SERVER['HTTP_CF_CONNECTING_IP'] ?? '');
    if (filter_var($cfIp, FILTER_VALIDATE_IP)) return $cfIp;
    $forwardedFor = trim($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '');
    if ($forwardedFor !== '') {
        $parts = explode(',', $forwardedFor);
        $firstIp = trim($parts[0]);
        if (filter_var($firstIp, FILTER_VALIDATE_IP)) return $firstIp;
    }
    $remoteAddr = trim($_SERVER['REMOTE_ADDR'] ?? '');
    if (filter_var($remoteAddr, FILTER_VALIDATE_IP)) return $remoteAddr;
    return 'unknown';
}

// --------------------- RATE LIMITING (simplified, robust) ---------------------
function rate_limit_check($bucketKey, $maxRequests, $windowSeconds) {
    // Use session as primary storage (always available)
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return ['allowed' => true, 'retry_after' => 0];
    }
    if (!isset($_SESSION['__rate_limits'])) {
        $_SESSION['__rate_limits'] = [];
    }
    $key = sha1($bucketKey);
    $now = time();
    if (!isset($_SESSION['__rate_limits'][$key])) {
        $_SESSION['__rate_limits'][$key] = ['window_start' => $now, 'count' => 0];
    }
    $windowStart = (int)$_SESSION['__rate_limits'][$key]['window_start'];
    $count = (int)$_SESSION['__rate_limits'][$key]['count'];
    if (($now - $windowStart) >= $windowSeconds) {
        $windowStart = $now;
        $count = 0;
    }
    if ($count >= $maxRequests) {
        $retryAfter = max(1, $windowSeconds - ($now - $windowStart));
        return ['allowed' => false, 'retry_after' => $retryAfter];
    }
    $count++;
    $_SESSION['__rate_limits'][$key] = ['window_start' => $windowStart, 'count' => $count];
    return ['allowed' => true, 'retry_after' => 0];
}

function enforce_rate_limit($bucketKey, $maxRequests, $windowSeconds, $message = 'Too many requests. Please try again shortly.', $redirectOnLimit = null) {
    $check = rate_limit_check($bucketKey, $maxRequests, $windowSeconds);
    if ($check['allowed']) return;
    $retryAfter = $check['retry_after'];
    header('Retry-After: ' . $retryAfter);
    http_response_code(429);
    if ($redirectOnLimit !== null) {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $_SESSION['rate_limit_error'] = $message;
        $_SESSION['rate_limit_retry_after'] = $retryAfter;
        header('Location: ' . $redirectOnLimit);
        exit;
    }
    if (request_expects_json_response()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => $message, 'retry_after' => $retryAfter]);
        exit;
    }
    header('Content-Type: text/html; charset=utf-8');
    echo "<!doctype html><html><head><title>Rate Limited</title></head><body>";
    echo "<h2>Please slow down</h2><p>" . htmlspecialchars($message) . "</p>";
    echo "<p>Try again in about $retryAfter seconds.</p></body></html>";
    exit;
}

function auto_enforce_request_limits() {
    if (PHP_SAPI === 'cli') return;
    $scriptName = strtolower($_SERVER['SCRIPT_NAME'] ?? '');
    if ($scriptName === '') return;
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    $clientIp = client_ip_address();
    $endpoint = basename($scriptName);

    // Auth endpoints – less strict (10 per 15 min)
    if ($method === 'POST' && preg_match('#/auth/(login|register|forgot_password|reset_password|verify)\\.php$#', $scriptName, $matches)) {
        $action = $matches[1];
        $redirectUrl = BASE_URL . '/auth/' . $action . '.php';
        enforce_rate_limit(
            'auth-post:' . $clientIp . ':' . $action,
            10,  // 10 attempts
            900, // 15 minutes
            'Too many authentication attempts. Please wait 15 minutes and try again.',
            $redirectUrl
        );
    }
}
auto_enforce_request_limits();

// --------------------- EMAIL FUNCTIONS ---------------------
function should_use_smtp_transport() {
    if (EMAIL_TRANSPORT === 'smtp') return SMTP_USER !== '' && SMTP_PASS !== '';
    if (EMAIL_TRANSPORT === 'mail') return false;
    if (IS_INFINITY_HOSTING) return false;
    return SMTP_USER !== '' && SMTP_PASS !== '';
}

function configure_mailer_transport($mail) {
    if (should_use_smtp_transport()) {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
    } else {
        $mail->isMail();
    }
}

function default_mail_from_address() {
    $from = trim(MAIL_FROM);
    if ($from !== '') return $from;
    if (should_use_smtp_transport()) return SMTP_USER;
    return 'mamasovenug@gmail.com';
}

function send_mail_with_fallback($mail) {
    try {
        configure_mailer_transport($mail);
        return $mail->send();
    } catch (Throwable $e) {
        error_log('Mail send failed: ' . $e->getMessage());
        // Fallback to mail()
        try {
            $mail->isMail();
            return $mail->send();
        } catch (Throwable $e2) {
            error_log('Fallback mail() failed: ' . $e2->getMessage());
            return false;
        }
    }
}

function email_logo_html($mail, $height = 80) {
    $height = max(24, (int)$height);
    $style = "height: {$height}px; margin-bottom: 15px; display: inline-block;";
    $alt = htmlspecialchars(SITE_NAME . ' Logo', ENT_QUOTES, 'UTF-8');
    $logoPath = __DIR__ . '/../assets/images/logo.png';
    if (is_readable($logoPath)) {
        $logoUrl = BASE_URL . '/assets/images/logo.png';
        return "<img src='{$logoUrl}' alt='{$alt}' style='{$style}'>";
    }
    return "<strong style='color:#8B4513;font-size:22px;display:inline-block;margin-bottom:15px;'>" . htmlspecialchars(SITE_NAME) . "</strong>";
}

// --------------------- SECURITY HELPERS ---------------------
function is_password_compromised($password) {
    $hash = strtoupper(sha1($password));
    $prefix = substr($hash, 0, 5);
    $suffix = substr($hash, 5);
    $url = "https://api.pwnedpasswords.com/range/" . $prefix;
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 3,
        CURLOPT_USERAGENT => 'MamaOven/1.0',
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode !== 200 || $response === false) {
        error_log('Pwned Passwords API unavailable, skipping breach check.');
        return false;
    }
    foreach (explode("\r\n", $response) as $line) {
        list($hashSuffix, $count) = explode(':', $line);
        if (strcasecmp($hashSuffix, $suffix) === 0) {
            return true;
        }
    }
    return false;
}

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        if (request_expects_json_response()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

function require_admin() {
    require_login();
    if ($_SESSION['role'] !== 'admin') {
        if (request_expects_json_response()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Forbidden']);
            exit;
        }
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

// --------------------- CSRF ---------------------
function generate_csrf_token() {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['csrf_token'])) return false;
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Enforce CSRF for state-changing API endpoints. Accepts the token either as
// a POST field (csrf_token) or as the X-CSRF-Token header (used by main.js's
// global AJAX setup), so existing form-based callers keep working unchanged.
function require_csrf_or_fail() {
    $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!validate_csrf_token($token)) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Your session security token is missing or expired. Please refresh the page and try again.'
        ]);
        exit;
    }
}

// --------------------- SESSION MANAGEMENT ---------------------
function clear_auth_session_data() {
    $cookieParams = session_get_cookie_params();
    setcookie(REMEMBER_ME_COOKIE, '', [
        'expires' => time() - 3600,
        'path' => $cookieParams['path'] ?? '/',
        'domain' => $cookieParams['domain'] ?? '',
        'secure' => !empty($cookieParams['secure']),
        'httponly' => true,
        'samesite' => $cookieParams['samesite'] ?? 'Lax',
    ]);
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];
        setcookie(session_name(), '', [
            'expires' => time() - 3600,
            'path' => $cookieParams['path'] ?? '/',
            'domain' => $cookieParams['domain'] ?? '',
            'secure' => !empty($cookieParams['secure']),
            'httponly' => true,
            'samesite' => $cookieParams['samesite'] ?? 'Lax',
        ]);
        session_destroy();
    }
}

function apply_auth_session_preferences($rememberMe) {
    if (session_status() !== PHP_SESSION_ACTIVE) return;
    $rememberMe = (bool)$rememberMe;
    $_SESSION['remember_me'] = $rememberMe ? 1 : 0;
    $_SESSION['last_activity'] = time();
    $cookieParams = session_get_cookie_params();
    $sessionExpiry = $rememberMe ? (time() + REMEMBER_ME_LIFETIME) : 0;
    setcookie(session_name(), session_id(), [
        'expires' => $sessionExpiry,
        'path' => $cookieParams['path'] ?? '/',
        'domain' => $cookieParams['domain'] ?? '',
        'secure' => !empty($cookieParams['secure']),
        'httponly' => true,
        'samesite' => $cookieParams['samesite'] ?? 'Lax',
    ]);
    if ($rememberMe) {
        setcookie(REMEMBER_ME_COOKIE, '1', [
            'expires' => time() + REMEMBER_ME_LIFETIME,
            'path' => $cookieParams['path'] ?? '/',
            'domain' => $cookieParams['domain'] ?? '',
            'secure' => !empty($cookieParams['secure']),
            'httponly' => true,
            'samesite' => $cookieParams['samesite'] ?? 'Lax',
        ]);
    } else {
        setcookie(REMEMBER_ME_COOKIE, '', [
            'expires' => time() - 3600,
            'path' => $cookieParams['path'] ?? '/',
            'domain' => $cookieParams['domain'] ?? '',
            'secure' => !empty($cookieParams['secure']),
            'httponly' => true,
            'samesite' => $cookieParams['samesite'] ?? 'Lax',
        ]);
    }
}

function enforce_auth_session_policy() {
    if (session_status() !== PHP_SESSION_ACTIVE || !isset($_SESSION['user_id'])) return;
    $scriptName = basename($_SERVER['SCRIPT_NAME'] ?? '');
    if ($scriptName === 'logout.php') return;
    $now = time();
    $lastActivity = (int)($_SESSION['last_activity'] ?? $now);
    $isRemembered = !empty($_SESSION['remember_me']);
    if (!$isRemembered && ($now - $lastActivity) > SESSION_INACTIVITY_TIMEOUT) {
        clear_auth_session_data();
        if (request_expects_json_response()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Session expired. Please log in again.', 'session_expired' => true]);
            exit;
        }
        header('Location: ' . BASE_URL . '/auth/login.php?timeout=1');
        exit;
    }
    $_SESSION['last_activity'] = $now;
}
enforce_auth_session_policy();

// Enforce HTTPS in production
if (IS_PRODUCTION && !isset($_SERVER['HTTPS']) && !isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}
?>