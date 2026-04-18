<?php
error_reporting(0);
ini_set('display_errors', 0);

// Load simple .env file (if present) so getenv() works in PHP without external libs
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

        // Remove surrounding quotes if present
        if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
            (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
            $value = substr($value, 1, -1);
        }

        if (getenv($name) === false) {
            putenv("$name=$value");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Load .env from project root (one level up from config/)
load_dotenv(__DIR__ . '/../.env');

// Turn on error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- ENVIRONMENT DETECTION ---
// Detect if we're on localhost or production
$isLocalhost = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', 'localhost:8000', '127.0.0.1:80']);

// --- DATABASE CREDENTIALS ---
if ($isLocalhost) {
    // Local development
    $host    = getenv('DB_HOST') ?: 'localhost';
    $dbname  = getenv('DB_NAME') ?: 'mamaove';
    $username= getenv('DB_USER') ?: 'root';
    $password= getenv('DB_PASS') ?: '';
} else {
    // Production (InfinityFree)
    $host    = getenv('DB_HOST') ?: 'sql303.infinityfree.com';
    $dbname  = getenv('DB_NAME') ?: 'if0_40616210_mamaove';
    $username= getenv('DB_USER') ?: 'if0_40616210';
    $password= getenv('DB_PASS') ?: 'josbert003'; // Use .env for security
}
$charset = getenv('DB_CHARSET') ?: 'utf8mb4';

// --- PDO CONNECTION ---
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// --- BASE URL WITH AUTOMATIC PROTOCOL DETECTION ---
if (getenv('BASE_URL')) {
    // Use .env if set
    define('BASE_URL', getenv('BASE_URL'));
} else {
    // Auto-detect protocol and domain
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    
    if ($isLocalhost) {
        define('BASE_URL', $protocol . '://' . $_SERVER['HTTP_HOST'] . '/mamoven1');
    } else {
        define('BASE_URL', $protocol . '://mamoven.infinityfreeapp.com/mamoven1');
    }
}

// --- ENVIRONMENT & EMAIL CONFIGURATION ---
define('IS_PRODUCTION', false); // Change to true for live server

if (IS_PRODUCTION) {
    define('SMTP_USER', getenv('SMTP_USER') ?: 'mamasovenug@gmail.com');
    // You might also need a production SMTP_PASS here
    define('SMTP_PASS', getenv('SMTP_PASS') ?: 'YOUR_PRODUCTION_APP_PASSWORD');
} else {
    define('SMTP_USER', getenv('SMTP_USER') ?: 'joszialvin@gmail.com');
    define('SMTP_PASS', getenv('SMTP_PASS') ?: 'fsbc ktft yacu lhwe');
}

// --- SITE CONSTANTS ---
define('SITE_NAME', getenv('SITE_NAME') ?: "Mama's Oven");
define('ADMIN_EMAIL', getenv('ADMIN_EMAIL') ?: 'joszialvin@gmail.com');

// --- SMTP / Email Settings ---
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_SECURE', getenv('SMTP_SECURE') ?: 'tls');

// --- UPLOAD DIRECTORY ---
define('UPLOAD_PATH', __DIR__ . '/../assets/images/');
define('UPLOAD_URL', BASE_URL . '/assets/image2/');
?>


<!-- sent for online hosting eg on freehostings -->
<!-- hosting online we use this <?php
// --- CONFIGURATION FOR INFINITYFREE ---
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// // 1. DATABASE CREDENTIALS
// // IMPORTANT: 'josbert003' is likely WRONG for the DB password. 
// // It is usually a random string (e.g., 'Xy7z8abC') found in your Client Area -> MySQL Details.
// // If 'josbert003' really is your VPanel password, keep it. If not, swap it.
// $host     = 'sql303.infinityfree.com'; 
// $dbname   = 'if0_40616210_mamaove';
// $username = 'if0_40616210';            
// $password = 'josbert003'; // CHECK THIS: Is this the random string from the panel?

// 2. BASE URL (Automatic Protocol Detection)
// This fixes the issue where CSS gets blocked because of http vs https
// $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
// define('BASE_URL', $protocol . '://mamoven.infinityfreeapp.com/mamoven1');

// define('SITE_NAME', "Mama's Oven");
// define('ADMIN_EMAIL', 'joszialvin@gmail.com');

// // 3. DATABASE CONNECTION
// $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
// $options = [
//     PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
//     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
//     PDO::ATTR_EMULATE_PREPARES   => false,
// ];

// try {
//     $pdo = new PDO($dsn, $username, $password, $options);
// } catch (\PDOException $e) {
//     die("Connection Failed: " . $e->getMessage());
// }

// 4. PATHS
// define('UPLOAD_PATH', __DIR__ . '/../assets/images/');
// define('UPLOAD_URL', BASE_URL . '/assets/image2/');

// 5. EMAIL
// define('SMTP_HOST', 'smtp.gmail.com');
// define('SMTP_PORT', 587);
// define('SMTP_USER', 'joszialvin@gmail.com'); 
// define('SMTP_PASS', 'fsbc ktft yacu lhwe'); 
// define('SMTP_SECURE', 'tls');
?> -->