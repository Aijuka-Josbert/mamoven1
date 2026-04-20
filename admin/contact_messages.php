<?php
$page_title = 'Contact Messages';
require_once __DIR__ . '/includes/header.php';

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
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($messages)): ?>
                        <tr>
                            <td colspan="5" class="text-center">No contact messages found.</td>
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
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
