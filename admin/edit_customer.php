<?php
$page_title = 'Edit User';
require_once __DIR__ . '/includes/header.php';
require_admin();

$errors = [];
$user_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$user = null;

// 1. --- VALIDATE ID AND FETCH USER DATA ---
if (!$user_id) {
    // If ID is missing or invalid, redirect
    header('Location: customers.php?error=invalid_id');
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // If no user is found with that ID, redirect
    if (!$user) {
        header('Location: customers.php?error=not_found');
        exit();
    }
} catch (PDOException $e) {
    // A database error is fatal on this page
    die("Database error: " . $e->getMessage());
}

// 2. --- HANDLE FORM SUBMISSION (UPDATE LOGIC) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session security token is missing or expired. Please refresh the page and try again.';
    }
    // Sanitize and retrieve form data
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $role = $_POST['role'] ?? 'customer';
    $password = $_POST['password'] ?? '';

    // --- Validation ---
    if (empty($full_name)) $errors[] = "Full name is required.";
    if (empty($username)) $errors[] = "Username is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "A valid email is required.";
    if (!in_array($role, ['admin', 'customer'])) $errors[] = "Invalid role selected.";

    // Check if email or username is already taken by another user
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?");
    $checkStmt->execute([$email, $username, $user_id]);
    if ($checkStmt->fetch()) {
        $errors[] = "The email or username is already in use by another account.";
    }
    
    // If there are no validation errors, proceed with the update
    if (empty($errors)) {
        try {
            $params = [
                $full_name,
                $username,
                $email,
                $phone,
                $address,
                $role
            ];
            
            // --- Securely Handle Optional Password Update ---
            if (!empty($password)) {
                // If a new password is provided, hash it and update the query
                $sql = "UPDATE users SET full_name = ?, username = ?, email = ?, phone = ?, address = ?, role = ?, password = ? WHERE id = ?";
                $params[] = password_hash($password, PASSWORD_DEFAULT);
            } else {
                // If password is not changed, don't update it in the database
                $sql = "UPDATE users SET full_name = ?, username = ?, email = ?, phone = ?, address = ?, role = ? WHERE id = ?";
            }
            
            $params[] = $user_id; // Add the user ID for the WHERE clause
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            if (($user['role'] ?? '') !== $role) {
                log_audit_event('user_role_changed', 'user', $user_id, 'Role changed from ' . ($user['role'] ?? 'unknown') . ' to ' . $role);
            } else {
                log_audit_event('customer_updated', 'user', $user_id);
            }

            // Redirect with a success message
            header('Location: customers.php?status=updated');
            exit();

        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
    // If there were validation errors, the form will re-display with the errors and user's input
    $user['full_name'] = $full_name;
    $user['username'] = $username;
    $user['email'] = $email;
    $user['phone'] = $phone;
    $user['address'] = $address;
    $user['role'] = $role;
}

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0">Editing: <strong><?php echo htmlspecialchars($user['full_name']); ?></strong></p>
    <a href="customers.php" class="btn btn-secondary">Back to User List</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <div class="row">
                <!-- Left Column -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username *</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                     <div class="mb-3">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password">
                        <div class="form-text">Provide a new password only if you want to change it.</div>
                    </div>
                </div>
                <!-- Right Column -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role *</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="customer" <?php echo ($user['role'] === 'customer') ? 'selected' : ''; ?>>Customer</option>
                            <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="customers.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>