<?php

// Include database connection
include_once __DIR__ . '/config/database.php';

// Create a new strong password
$new_password = 'Mama2023!';
$new_hash = password_hash($new_password, PASSWORD_DEFAULT);

try {
    // First check if admin user exists
    $check = $pdo->prepare("SELECT * FROM users WHERE username = 'admin'");
    $check->execute();
    $admin = $check->fetch();
    
    if ($admin) {
        // Update the admin password
        $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE username = 'admin'");
        $stmt->execute(['password' => $new_hash]);
        echo "<h2>Admin password changed successfully!</h2>";
        echo "<p>New login details:</p>";
        echo "<ul>";
        echo "<li><strong>Username:</strong> admin</li>";
        echo "<li><strong>Password:</strong> $new_password</li>";
        echo "</ul>";
        echo "<p><a href='auth/login.php'>Go to login page</a></p>";
    } else {
        echo "<h2>Admin user not found in database.</h2>";
        echo "<p>Creating new admin user...</p>";
        
        // Create new admin user
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES ('admin', 'admin@mamasovenug.com', :password, 'Administrator', 'admin')");
        $stmt->execute(['password' => $new_hash]);
        
        echo "<h2>New admin user created successfully!</h2>";
        echo "<p>Login details:</p>";
        echo "<ul>";
        echo "<li><strong>Username:</strong> admin</li>";
        echo "<li><strong>Password:</strong> $new_password</li>";
        echo "</ul>";
        echo "<p><a href='auth/login.php'>Go to login page</a></p>";
    }
} catch (PDOException $e) {
    echo "<h2>Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>