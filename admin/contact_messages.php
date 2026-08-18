<?php
$page_title = 'Contact Messages';
require_once __DIR__ . '/includes/header.php';
require_admin();

$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_message') {
    $message_id = (int)($_POST['message_id'] ?? 0);

    if ($message_id > 0) {
        try {
            $delete_stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
            $delete_stmt->execute([$message_id]);
            $success_message = 'Contact message deleted successfully.';
        } catch (PDOException $e) {
            $error_message = 'Failed to delete message: ' . $e->getMessage();
        }
    }
}

try {
    $messages_stmt = $pdo->query("\n        SELECT id, name, email, message, created_at\n        FROM contact_messages\n        ORDER BY created_at DESC\n    ");
    $messages = $messages_stmt->fetchAll();
} catch (PDOException $e) {
    $messages = [];
    $error_message = 'Failed to fetch contact messages: ' . $e->getMessage();
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Contact Messages</h1>
    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<?php if (!empty($success_message)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Message</th>
                        <th>Sent At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($messages)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No contact messages found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                            <tr>
                                <td><?php echo (int)$msg['id']; ?></td>
                                <td><?php echo htmlspecialchars($msg['name']); ?></td>
                                <td>
                                    <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>">
                                        <?php echo htmlspecialchars($msg['email']); ?>
                                    </a>
                                </td>
                                <td style="min-width: 280px; white-space: pre-wrap;"><?php echo htmlspecialchars($msg['message']); ?></td>
                                <td><?php echo date('d M Y, g:ia', strtotime($msg['created_at'])); ?></td>
                                <td class="text-end">
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this contact message?');">
                                        <input type="hidden" name="action" value="delete_message">
                                        <input type="hidden" name="message_id" value="<?php echo (int)$msg['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
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
