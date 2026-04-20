<?php

// Load simple .env file (if present) so getenv() works without external libs.
function load_dotenv($path)
{
    if (!is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0 || strpos($line, '//') === 0) {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
            (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
            $value = substr($value, 1, -1);
        }

        if (function_exists('putenv')) {
            @putenv($name . '=' . $value);
        }
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

load_dotenv(__DIR__ . '/../.env');

function env_value($key, $default = '')
{
    $value = getenv($key);
    if ($value !== false) {
        return $value;
    }

    if (array_key_exists($key, $_ENV)) {
        return $_ENV[$key];
    }

    if (array_key_exists($key, $_SERVER)) {
        return $_SERVER[$key];
    }

    return $default;
}

$httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isLocalhost = in_array($httpHost, ['localhost', '127.0.0.1', 'localhost:8000', '127.0.0.1:80'], true);
$isInfinityHosting =
    stripos($httpHost, 'infinityfreeapp.com') !== false ||
    stripos($httpHost, '.epizy.com') !== false ||
    stripos($httpHost, '.rf.gd') !== false;

$appEnv = strtolower((string)env_value('APP_ENV', $isLocalhost ? 'development' : 'production'));
$isProduction = $appEnv === 'production';

// Prevent PCRE JIT warnings on restricted hosts (common on shared hosting).
ini_set('pcre.jit', '0');

error_reporting(E_ALL);
ini_set('display_errors', $isProduction ? '0' : '1');
ini_set('log_errors', '1');

$host = (string)env_value('DB_HOST', $isLocalhost ? 'localhost' : 'sql303.infinityfree.com');
$dbname = (string)env_value('DB_NAME', $isLocalhost ? 'mamaove' : 'if0_40616210_mamaove');
$username = (string)env_value('DB_USER', $isLocalhost ? 'root' : 'if0_40616210');
$password = (string)env_value('DB_PASS', '');
$charset = (string)env_value('DB_CHARSET', 'utf8mb4');

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

    $errorText = strtolower($e->getMessage());
    $driverCode = 'unknown';
    if (preg_match('/\[(\d{3,5})\]/', $e->getMessage(), $matches)) {
        $driverCode = $matches[1];
    }

    $likelyCause = 'Connection failed for an unknown reason.';
    if (strpos($errorText, 'access denied') !== false || $driverCode === '1045') {
        $likelyCause = 'Access denied. DB_USER or DB_PASS is incorrect.';
    } elseif (strpos($errorText, 'unknown database') !== false || $driverCode === '1049') {
        $likelyCause = 'Database name not found. DB_NAME is incorrect or database was not created.';
    } elseif (strpos($errorText, 'php_network_getaddresses') !== false || strpos($errorText, 'getaddrinfo') !== false || strpos($errorText, 'name or service not known') !== false) {
        $likelyCause = 'Database host cannot be resolved. DB_HOST is incorrect.';
    } elseif (strpos($errorText, "can't connect") !== false || strpos($errorText, 'connection refused') !== false || strpos($errorText, 'timed out') !== false || $driverCode === '2002') {
        $likelyCause = 'Database server is unreachable. Check DB_HOST or temporary host availability.';
    }

    $envPath = __DIR__ . '/../.env';
    $envReadable = is_readable($envPath) ? 'yes' : 'no';

    if (!$isProduction) {
        throw new PDOException($e->getMessage(), (int)$e->getCode());
    }

    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Configuration Issue</title>';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<style>body{font-family:Arial,sans-serif;background:#fff7f2;color:#4a2a1d;padding:32px;line-height:1.5}';
    echo '.box{max-width:760px;margin:40px auto;background:#fff;border:1px solid #efd9cb;border-radius:10px;padding:24px}';
    echo 'h1{margin-top:0}code{background:#f8eee8;padding:2px 6px;border-radius:4px}</style></head><body>';
    echo '<div class="box"><h1>Configuration Error</h1>';
    echo '<p>The application cannot connect to the database right now.</p>';
    echo '<p><strong>Detected issue:</strong> ' . htmlspecialchars($likelyCause, ENT_QUOTES, 'UTF-8') . '</p>';
    echo '<p><strong>Driver code:</strong> <code>' . htmlspecialchars($driverCode, ENT_QUOTES, 'UTF-8') . '</code></p>';
    echo '<p><strong>Runtime values:</strong></p>';
    echo '<ul>';
    echo '<li>DB_HOST: <code>' . htmlspecialchars((string)$host, ENT_QUOTES, 'UTF-8') . '</code></li>';
    echo '<li>DB_NAME: <code>' . htmlspecialchars((string)$dbname, ENT_QUOTES, 'UTF-8') . '</code></li>';
    echo '<li>DB_USER: <code>' . htmlspecialchars((string)$username, ENT_QUOTES, 'UTF-8') . '</code></li>';
    echo '<li>DB_PASS length: <code>' . strlen((string)$password) . '</code></li>';
    echo '<li>.env readable by PHP: <code>' . htmlspecialchars($envReadable, ENT_QUOTES, 'UTF-8') . '</code></li>';
    echo '</ul>';
    echo '<p>Check these values in <code>.env</code> on your hosting account:</p>';
    echo '<ul><li>DB_HOST</li><li>DB_NAME</li><li>DB_USER</li><li>DB_PASS</li></ul>';
    echo '<p>Also ensure the <code>.env</code> file exists in your project root (same level as <code>index.php</code>).</p>';
    echo '<p>If this just happened after deployment, re-upload <code>.env</code> and try again.</p></div></body></html>';
    exit;
}

if (env_value('BASE_URL', '') !== '') {
    define('BASE_URL', rtrim((string)env_value('BASE_URL', ''), '/'));
} else {
    $https = $_SERVER['HTTPS'] ?? '';
    $forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
    $requestScheme = strtolower($_SERVER['REQUEST_SCHEME'] ?? '');
    $serverPort = (int)($_SERVER['SERVER_PORT'] ?? 0);
    $cfVisitor = strtolower($_SERVER['HTTP_CF_VISITOR'] ?? '');
    $isHttps =
        $https === 'on' ||
        $https === '1' ||
        strtolower($forwardedProto) === 'https' ||
        $requestScheme === 'https' ||
        $serverPort === 443 ||
        strpos($cfVisitor, '"scheme":"https"') !== false;
    $protocol = $isHttps ? 'https' : 'http';

    if ($isLocalhost) {
        define('BASE_URL', $protocol . '://' . $httpHost . '/mamoven1');
    } else {
        define('BASE_URL', $protocol . '://mamoven.infinityfreeapp.com/mamoven1');
    }
}

define('IS_PRODUCTION', $isProduction);
define('IS_INFINITY_HOSTING', $isInfinityHosting);
define('SITE_NAME', (string)env_value('SITE_NAME', "Mama's Oven"));
define('EMAIL_TRANSPORT', strtolower((string)env_value('EMAIL_TRANSPORT', 'auto')));

define('SMTP_HOST', (string)env_value('SMTP_HOST', 'smtp.gmail.com'));
define('SMTP_PORT', (int)env_value('SMTP_PORT', 587));
define('SMTP_USER', (string)env_value('SMTP_USER', ''));
define('SMTP_PASS', (string)env_value('SMTP_PASS', ''));
define('SMTP_SECURE', (string)env_value('SMTP_SECURE', 'tls'));

define('ADMIN_EMAIL', 'mamasovenug@gmail.com');

$defaultMailFrom = 'mamasovenug@gmail.com';
define('MAIL_FROM', (string)env_value('MAIL_FROM', $defaultMailFrom));
define('PASSWORD_RESET_EXPIRY', 7200); // 2 hours in seconds
define('SESSION_INACTIVITY_TIMEOUT', (int)env_value('SESSION_INACTIVITY_TIMEOUT', 900));
define('REMEMBER_ME_LIFETIME', (int)env_value('REMEMBER_ME_LIFETIME', 2592000));
define('REMEMBER_ME_COOKIE', 'mamoven_remember');

define('UPLOAD_PATH', __DIR__ . '/../assets/images/');
define('UPLOAD_URL', BASE_URL . '/assets/images/');

function request_expects_json_response()
{
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    if (strpos($scriptName, '/api/') !== false) {
        return true;
    }

    $accept = strtolower($_SERVER['HTTP_ACCEPT'] ?? '');
    if (strpos($accept, 'application/json') !== false) {
        return true;
    }

    $requestedWith = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
    return $requestedWith === 'xmlhttprequest';
}

function client_ip_address()
{
    $cfIp = trim((string)($_SERVER['HTTP_CF_CONNECTING_IP'] ?? ''));
    if (filter_var($cfIp, FILTER_VALIDATE_IP)) {
        return $cfIp;
    }

    $forwardedFor = trim((string)($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''));
    if ($forwardedFor !== '') {
        $parts = explode(',', $forwardedFor);
        $firstIp = trim($parts[0]);
        if (filter_var($firstIp, FILTER_VALIDATE_IP)) {
            return $firstIp;
        }
    }

    $remoteAddr = trim((string)($_SERVER['REMOTE_ADDR'] ?? ''));
    if (filter_var($remoteAddr, FILTER_VALIDATE_IP)) {
        return $remoteAddr;
    }

    return 'unknown';
}

function ensure_db_rate_limit_table()
{
    static $checked = false;
    static $available = false;

    if ($checked) {
        return $available;
    }

    $checked = true;

    try {
        global $pdo;
        if (!($pdo instanceof PDO)) {
            return false;
        }

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS request_rate_limits (
                bucket_hash CHAR(40) NOT NULL PRIMARY KEY,
                window_start INT NOT NULL,
                request_count INT NOT NULL DEFAULT 0,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_updated_at (updated_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        $available = true;
    } catch (Throwable $e) {
        error_log('Rate limit table setup failed: ' . $e->getMessage());
        $available = false;
    }

    return $available;
}

function register_rate_limit_attempt_db($bucketKey, $maxRequests, $windowSeconds)
{
    if (!ensure_db_rate_limit_table()) {
        return null;
    }

    global $pdo;
    if (!($pdo instanceof PDO)) {
        return null;
    }

    $now = time();
    $bucketHash = sha1($bucketKey);
    $ownsTransaction = false;

    try {
        if (!$pdo->inTransaction()) {
            $pdo->beginTransaction();
            $ownsTransaction = true;
        } else {
            $ownsTransaction = false;
        }

        $stmt = $pdo->prepare('SELECT window_start, request_count FROM request_rate_limits WHERE bucket_hash = ? FOR UPDATE');
        $stmt->execute([$bucketHash]);
        $row = $stmt->fetch();

        $allowed = true;
        $retryAfter = 0;

        if (!$row) {
            $insertStmt = $pdo->prepare('INSERT INTO request_rate_limits (bucket_hash, window_start, request_count) VALUES (?, ?, 1)');
            $insertStmt->execute([$bucketHash, $now]);
        } else {
            $windowStart = (int)$row['window_start'];
            $count = (int)$row['request_count'];

            if (($now - $windowStart) >= $windowSeconds) {
                $windowStart = $now;
                $count = 0;
            }

            if ($count >= $maxRequests) {
                $allowed = false;
                $retryAfter = max(1, $windowSeconds - ($now - $windowStart));
            } else {
                $count++;
            }

            $updateStmt = $pdo->prepare('UPDATE request_rate_limits SET window_start = ?, request_count = ? WHERE bucket_hash = ?');
            $updateStmt->execute([$windowStart, $count, $bucketHash]);
        }

        // Lightweight table hygiene.
        if (($now % 37) === 0) {
            $cleanupStmt = $pdo->prepare('DELETE FROM request_rate_limits WHERE updated_at < (NOW() - INTERVAL 1 DAY)');
            $cleanupStmt->execute();
        }

        if ($ownsTransaction) {
            $pdo->commit();
        }

        return ['allowed' => $allowed, 'retry_after' => $retryAfter];
    } catch (Throwable $e) {
        if ($ownsTransaction && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Rate limit DB check failed: ' . $e->getMessage());
        return null;
    }
}

function register_rate_limit_attempt_session($bucketKey, $maxRequests, $windowSeconds)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return ['allowed' => true, 'retry_after' => 0];
    }

    if (!isset($_SESSION['__rate_limits']) || !is_array($_SESSION['__rate_limits'])) {
        $_SESSION['__rate_limits'] = [];
    }

    $key = sha1($bucketKey);
    $now = time();

    if (!isset($_SESSION['__rate_limits'][$key]) || !is_array($_SESSION['__rate_limits'][$key])) {
        $_SESSION['__rate_limits'][$key] = [
            'window_start' => $now,
            'count' => 0,
        ];
    }

    $windowStart = (int)($_SESSION['__rate_limits'][$key]['window_start'] ?? $now);
    $count = (int)($_SESSION['__rate_limits'][$key]['count'] ?? 0);

    if (($now - $windowStart) >= $windowSeconds) {
        $windowStart = $now;
        $count = 0;
    }

    $allowed = true;
    $retryAfter = 0;

    if ($count >= $maxRequests) {
        $allowed = false;
        $retryAfter = max(1, $windowSeconds - ($now - $windowStart));
    } else {
        $count++;
    }

    $_SESSION['__rate_limits'][$key] = [
        'window_start' => $windowStart,
        'count' => $count,
    ];

    return ['allowed' => $allowed, 'retry_after' => $retryAfter];
}

function rate_limit_storage_file($bucketKey)
{
    static $storageDir = null;

    if ($storageDir === null) {
        $projectDir = __DIR__ . '/../db/.ratelimits';
        if (!is_dir($projectDir)) {
            @mkdir($projectDir, 0775, true);
        }

        if (is_dir($projectDir) && is_writable($projectDir)) {
            $storageDir = $projectDir;
        } else {
            $tmpDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR);
            if (is_dir($tmpDir) && is_writable($tmpDir)) {
                $storageDir = $tmpDir;
            } else {
                $storageDir = '';
            }
        }
    }

    if ($storageDir === '') {
        return '';
    }

    $safeKey = sha1($bucketKey);
    return rtrim($storageDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'mamoven_rl_' . $safeKey . '.json';
}

function register_rate_limit_attempt($bucketKey, $maxRequests, $windowSeconds)
{
    $maxRequests = max(1, (int)$maxRequests);
    $windowSeconds = max(1, (int)$windowSeconds);

    $dbCheck = register_rate_limit_attempt_db($bucketKey, $maxRequests, $windowSeconds);
    if (is_array($dbCheck)) {
        return $dbCheck;
    }

    $filePath = rate_limit_storage_file($bucketKey);
    if ($filePath === '') {
        return register_rate_limit_attempt_session($bucketKey, $maxRequests, $windowSeconds);
    }

    $now = time();

    $handle = @fopen($filePath, 'c+');
    if ($handle === false) {
        return ['allowed' => true, 'retry_after' => 0];
    }

    if (!@flock($handle, LOCK_EX)) {
        fclose($handle);
        return ['allowed' => true, 'retry_after' => 0];
    }

    $existing = stream_get_contents($handle);
    $data = json_decode((string)$existing, true);
    if (!is_array($data) || !isset($data['window_start'], $data['count'])) {
        $data = [
            'window_start' => $now,
            'count' => 0,
        ];
    }

    $windowStart = (int)$data['window_start'];
    $count = (int)$data['count'];

    if (($now - $windowStart) >= $windowSeconds) {
        $windowStart = $now;
        $count = 0;
    }

    $allowed = true;
    $retryAfter = 0;

    if ($count >= $maxRequests) {
        $allowed = false;
        $retryAfter = max(1, $windowSeconds - ($now - $windowStart));
    } else {
        $count++;
    }

    $newData = [
        'window_start' => $windowStart,
        'count' => $count,
    ];

    rewind($handle);
    ftruncate($handle, 0);
    fwrite($handle, json_encode($newData));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);

    return ['allowed' => $allowed, 'retry_after' => $retryAfter];
}

function enforce_rate_limit($bucketKey, $maxRequests, $windowSeconds, $message = 'Too many requests. Please try again shortly.')
{
    $check = register_rate_limit_attempt($bucketKey, $maxRequests, $windowSeconds);
    if (!empty($check['allowed'])) {
        return;
    }

    $retryAfter = (int)($check['retry_after'] ?? 30);
    header('Retry-After: ' . $retryAfter);
    http_response_code(429);

    if (request_expects_json_response()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => $message,
            'retry_after' => $retryAfter,
        ]);
        exit;
    }

    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Rate Limited</title></head><body>';
    echo '<h2>Please slow down</h2>';
    echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
    echo '<p>Try again in about ' . $retryAfter . ' seconds.</p>';
    echo '</body></html>';
    exit;
}

function auto_enforce_request_limits()
{
    if (PHP_SAPI === 'cli') {
        return;
    }

    $scriptName = strtolower((string)($_SERVER['SCRIPT_NAME'] ?? ''));
    if ($scriptName === '') {
        return;
    }

    $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    $clientIp = client_ip_address();
    $endpoint = basename($scriptName);

    if (strpos($scriptName, '/api/') !== false) {
        enforce_rate_limit('api-global:' . $clientIp, 180, 60, 'Too many API requests from your network. Please wait a moment.');
        enforce_rate_limit('api-endpoint:' . $clientIp . ':' . $endpoint, 20, 60, 'Rate limit reached for this endpoint. Please retry shortly.');
    }

    if ($method === 'POST' && preg_match('#/auth/(login|register|forgot_password|reset_password|verify)\\.php$#', $scriptName, $matches)) {
        enforce_rate_limit('auth-post:' . $clientIp . ':' . $matches[1], 10, 300, 'Too many authentication attempts. Please wait a few minutes and try again.');
    }

    if ($method === 'POST' && preg_match('#/contact\\.php$#', $scriptName)) {
        enforce_rate_limit('contact-post:' . $clientIp, 8, 300, 'Too many contact requests. Please wait a few minutes before sending another message.');
    }
}

function clear_auth_session_data()
{
    $cookieParams = session_get_cookie_params();
    $cookiePath = $cookieParams['path'] ?? '/';
    $cookieDomain = $cookieParams['domain'] ?? '';
    $cookieSecure = !empty($cookieParams['secure']);
    $cookieHttpOnly = !empty($cookieParams['httponly']);
    $cookieSameSite = $cookieParams['samesite'] ?? 'Lax';

    setcookie(REMEMBER_ME_COOKIE, '', [
        'expires' => time() - 3600,
        'path' => $cookiePath,
        'domain' => $cookieDomain,
        'secure' => $cookieSecure,
        'httponly' => true,
        'samesite' => $cookieSameSite,
    ]);

    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }

    $_SESSION = [];

    setcookie(session_name(), '', [
        'expires' => time() - 3600,
        'path' => $cookiePath,
        'domain' => $cookieDomain,
        'secure' => $cookieSecure,
        'httponly' => $cookieHttpOnly,
        'samesite' => $cookieSameSite,
    ]);

    session_destroy();
}

function apply_auth_session_preferences($rememberMe)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }

    $rememberMe = (bool)$rememberMe;
    $_SESSION['remember_me'] = $rememberMe ? 1 : 0;
    $_SESSION['last_activity'] = time();

    $cookieParams = session_get_cookie_params();
    $cookiePath = $cookieParams['path'] ?? '/';
    $cookieDomain = $cookieParams['domain'] ?? '';
    $cookieSecure = !empty($cookieParams['secure']);
    $cookieHttpOnly = !empty($cookieParams['httponly']);
    $cookieSameSite = $cookieParams['samesite'] ?? 'Lax';
    $sessionExpiry = $rememberMe ? (time() + REMEMBER_ME_LIFETIME) : 0;

    setcookie(session_name(), session_id(), [
        'expires' => $sessionExpiry,
        'path' => $cookiePath,
        'domain' => $cookieDomain,
        'secure' => $cookieSecure,
        'httponly' => $cookieHttpOnly,
        'samesite' => $cookieSameSite,
    ]);

    if ($rememberMe) {
        setcookie(REMEMBER_ME_COOKIE, '1', [
            'expires' => time() + REMEMBER_ME_LIFETIME,
            'path' => $cookiePath,
            'domain' => $cookieDomain,
            'secure' => $cookieSecure,
            'httponly' => true,
            'samesite' => $cookieSameSite,
        ]);
    } else {
        setcookie(REMEMBER_ME_COOKIE, '', [
            'expires' => time() - 3600,
            'path' => $cookiePath,
            'domain' => $cookieDomain,
            'secure' => $cookieSecure,
            'httponly' => true,
            'samesite' => $cookieSameSite,
        ]);
    }
}

function enforce_auth_session_policy()
{
    if (session_status() !== PHP_SESSION_ACTIVE || !isset($_SESSION['user_id'])) {
        return;
    }

    $scriptName = basename($_SERVER['SCRIPT_NAME'] ?? '');
    if ($scriptName === 'logout.php') {
        return;
    }

    $now = time();
    $lastActivity = (int)($_SESSION['last_activity'] ?? $now);
    $isRemembered = !empty($_SESSION['remember_me']);

    if (!$isRemembered && ($now - $lastActivity) > SESSION_INACTIVITY_TIMEOUT) {
        clear_auth_session_data();

        if (request_expects_json_response()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Session expired. Please log in again.',
                'session_expired' => true,
            ]);
            exit;
        }

        header('Location: ' . BASE_URL . '/auth/login.php?timeout=1');
        exit;
    }

    $_SESSION['last_activity'] = $now;
}

enforce_auth_session_policy();
auto_enforce_request_limits();

function should_use_smtp_transport()
{
    if (EMAIL_TRANSPORT === 'smtp') {
        return SMTP_USER !== '' && SMTP_PASS !== '';
    }

    if (EMAIL_TRANSPORT === 'mail') {
        return false;
    }

    // auto mode: prefer mail on Infinity hosting, otherwise use SMTP if configured
    if (IS_INFINITY_HOSTING) {
        return false;
    }

    return SMTP_USER !== '' && SMTP_PASS !== '';
}

function configure_mailer_transport($mail)
{
    if (should_use_smtp_transport()) {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
    } else {
        // InfinityFree blocks outbound SMTP ports; PHP mail() is the default safe transport.
        $mail->isMail();
    }
}

function default_mail_from_address()
{
    $configuredFrom = trim((string)MAIL_FROM);
    if ($configuredFrom !== '') {
        return $configuredFrom;
    }

    if (should_use_smtp_transport()) {
        return SMTP_USER;
    }

    return MAIL_FROM;
}

function send_mail_with_fallback($mail)
{
    if (trim((string)MAIL_FROM) !== '') {
        try {
            $hasReplyTo = method_exists($mail, 'getReplyToAddresses')
                ? !empty($mail->getReplyToAddresses())
                : false;

            if (!$hasReplyTo) {
                $mail->addReplyTo(MAIL_FROM, SITE_NAME);
            }
        } catch (Throwable $replyToError) {
            error_log('Could not set default reply-to: ' . $replyToError->getMessage());
        }
    }

    try {
        configure_mailer_transport($mail);
        return $mail->send();
    } catch (Throwable $primaryError) {
        error_log('Primary mail transport failed: ' . $primaryError->getMessage());

        // If SMTP failed, try local mail transport as fallback.
        if (should_use_smtp_transport()) {
            try {
                $mail->isMail();
                return $mail->send();
            } catch (Throwable $fallbackError) {
                error_log('Fallback mail() transport failed: ' . $fallbackError->getMessage());
            }
        }

        return false;
    }
}

function email_logo_path()
{
    static $logoPath = null;
    static $resolved = false;

    if ($resolved) {
        return $logoPath;
    }

    $resolved = true;

    $candidates = [
        __DIR__ . '/../assets/images/logo.png',
        __DIR__ . '/../assets/images/logo.jpg',
        __DIR__ . '/../assets/images/logo.jpeg',
        __DIR__ . '/../assets/image2/logo.png',
        __DIR__ . '/../assets/image2/logo.jpg',
        __DIR__ . '/../assets/image2/logo.jpeg',
    ];

    foreach ($candidates as $candidate) {
        if (is_readable($candidate)) {
            $logoPath = $candidate;
            break;
        }
    }

    return $logoPath;
}

function email_logo_html($mail, $height = 80)
{
    // The $mail argument is intentionally retained for backward compatibility.
    unset($mail);

    $height = max(24, (int)$height);
    $style = "height: {$height}px; margin-bottom: 15px; display: inline-block;";
    $alt = htmlspecialchars(SITE_NAME . ' Logo', ENT_QUOTES, 'UTF-8');

    $candidates = [
        [
            'path' => __DIR__ . '/../assets/images/logo.png',
            'url' => BASE_URL . '/assets/images/logo.png',
        ],
        [
            'path' => __DIR__ . '/../assets/images/logo.jpg',
            'url' => BASE_URL . '/assets/images/logo.jpg',
        ],
        [
            'path' => __DIR__ . '/../assets/images/logo.jpeg',
            'url' => BASE_URL . '/assets/images/logo.jpeg',
        ],
    ];

    foreach ($candidates as $candidate) {
        if (is_readable($candidate['path'])) {
            $logoUrl = htmlspecialchars($candidate['url'], ENT_QUOTES, 'UTF-8');
            return "<img src='{$logoUrl}' alt='{$alt}' style='{$style}'>";
        }
    }

    return "<strong style='color:#8B4513;font-size:22px;display:inline-block;margin-bottom:15px;'>" . htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') . "</strong>";
}