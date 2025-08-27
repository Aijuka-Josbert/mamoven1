<?php
// Admin Customers Management Page
// List, add, edit, and delete customers
require_once '../config/database.php'; // Ensure PDO is included
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Fetch customers
$stmt = $pdo->query("SELECT id, full_name, email, created_at FROM users WHERE role='customer'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Customers - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Manage Customers</h2>
    <table border="1">
        <tr><th>ID</th><th>Name</th><th>Email</th><th>Joined</th></tr>
        <?php while($row = $stmt->fetch()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['full_name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= $row['created_at'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
<!-- 
@workspace give it a nice user intefabce design 
showing name, username,email,username,phone,address,role,created atm and can deleted client edit client etc  select * from users;
+----+----------+-------------------------------+--------------------------------------------------------------+-------------------+-------------------+------------------+-------+---------+----------+---------------------+---------------------+
| id | username | email                         | password                                                     | verification_code | email_verified_at | full_name        | phone | address | role     | created_at          | updated_at          |
+----+----------+-------------------------------+--------------------------------------------------------------+-------------------+-------------------+------------------+-------+---------+----------+---------------------+---------------------+ -->
