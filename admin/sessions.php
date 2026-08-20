<?php
$page_title = 'Active Sessions';
require_once __DIR__ . '/includes/header.php';
require_admin();

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'revoke') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $success = '';
    } else {
        $session_row_id = (int)($_POST['session_row_id'] ?? 0);
        $stmt = $pdo->prepare("UPDATE user_sessions SET revoked_at = NOW() WHERE id = ?");
        $stmt->execute([$session_row_id]);
        log_audit_event('session_revoked', 'session', $session_row_id);
        $success = 'Session revoked. That device will be signed out on its next action.';
    }
}

// Only show sessions active in the last 30 days, most recent first.
$sessions = $pdo->query("
    SELECT s.*, u.username, u.full_name
    FROM user_sessions s
    JOIN users u ON u.id = s.user_id
    WHERE s.last_activity_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
    ORDER BY s.last_activity_at DESC
    LIMIT 200
")->fetchAll();

$current_session_hash = hash('sha256', session_id());
?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Active Sessions <span class="text-muted fw-normal">(last 30 days)</span></h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover admin-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>IP Address</th>
                        <th>Device</th>
                        <th>Last Active</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sessions)): ?>
                        <tr><td colspan="7" class="text-center text-muted">No recent sessions found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($sessions as $s): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($s['full_name'] ?: $s['username']); ?></td>
                            <td><span class="order-status-pill status-<?php echo $s['role'] === 'admin' ? 'processing' : 'confirmed'; ?>"><?php echo htmlspecialchars($s['role']); ?></span></td>
                            <td><?php echo htmlspecialchars($s['ip_address'] ?? '—'); ?></td>
                            <td class="small text-muted" style="max-width:220px;"><?php echo htmlspecialchars(substr($s['user_agent'] ?? '—', 0, 60)); ?></td>
                            <td class="text-nowrap"><?php echo date('d M Y, H:i', strtotime($s['last_activity_at'])); ?></td>
                            <td>
                                <?php if ($s['session_id_hash'] === $current_session_hash): ?>
                                    <span class="badge bg-success">This device</span>
                                <?php elseif ($s['revoked_at']): ?>
                                    <span class="badge bg-secondary">Revoked</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark border">Active</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$s['revoked_at'] && $s['session_id_hash'] !== $current_session_hash): ?>
                                <form method="POST" onsubmit="return confirm('Revoke this session? That device will be signed out.');">
                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                    <input type="hidden" name="action" value="revoke">
                                    <input type="hidden" name="session_row_id" value="<?php echo $s['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Revoke</button>
                                </form>
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
