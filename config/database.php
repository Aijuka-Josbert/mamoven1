<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<?php
$host = 'localhost';
$dbname = 'mamaove';
$username = 'root';
$password = '!Log19tan88';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Define constants
define('SITE_URL', 'http://mamasovenug');
define('SITE_NAME', "Mama's Oven Uganda");
define('ADMIN_EMAIL', 'admin@mamasovenug.com');
?>
