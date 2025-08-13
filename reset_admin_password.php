<?php
// Include database connection
include_once __DIR__ . '/config/database.php';

// Generate a new password hash for "admin123"
$new_hash = password_hash('admin123', PASSWORD_DEFAULT);

// Update the admin user's password
try {
    $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE username = 'admin'");
    $stmt->execute(['password' => $new_hash]);
    echo "Admin password reset successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>