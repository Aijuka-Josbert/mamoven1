<?php
$page_title = 'Manage Customers';
require_once __DIR__ . '/includes/header.php';

$errors = [];
$success_message = '';

// --- HANDLE DELETE ACTION ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $user_id_to_delete = (int)$_GET['id'];

    // Security check: Prevent an admin from deleting their own account
    if ($user_id_to_delete === $_SESSION['user_id']) {
        header('Location: customers.php?error=selfdelete');
        exit();
    }

    try {
        // We can add more checks here, e.g., prevent deleting the last admin account
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id_to_delete]);
        
        // Redirect to avoid re-deleting on refresh
        header('Location: customers.php?status=deleted');
        exit();

    } catch(PDOException $e) {
        $errors[] = "Failed to delete user: " . $e->getMessage();
    }
}

// Check for status messages from redirects
if (isset($_GET['status']) && $_GET['status'] == 'deleted') {
    $success_message = "User has been successfully deleted.";
}
if (isset($_GET['error']) && $_GET['error'] == 'selfdelete') {
    $errors[] = "You cannot delete your own account.";
}


// Fetch all users for display
try {
    $stmt = $pdo->query("SELECT id, username, email, full_name, phone, address, role, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = "Could not fetch users: " . $e->getMessage();
    $users = [];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Manage Users</h1>
</div>

<!-- Display success or error messages -->
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php if ($success_message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">User List</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="customersTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Contact Info</th>
                        <th>Address</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No users found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($user['full_name']); ?></strong></td>
                            <td>
                                <?php echo htmlspecialchars($user['email']); ?><br>
                                <small class="text-muted">Username: <?php echo htmlspecialchars($user['username']); ?></small><br>
                                <small class="text-muted">Phone: <?php echo htmlspecialchars($user['phone'] ?: 'N/A'); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($user['address'] ?: 'N/A'); ?></td>
                            <td>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="badge bg-primary">Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Customer</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td class="text-end">
                                <a href="edit_customer.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php // Prevent deleting the current logged-in user ?>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <a href="javascript:void(0);" onclick="confirmDelete('customers.php?action=delete&id=<?php echo $user['id']; ?>')" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>