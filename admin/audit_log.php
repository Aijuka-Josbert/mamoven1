<?php
$page_title = 'Audit Log';
require_once __DIR__ . '/includes/header.php';
require_admin();

$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 40;
$offset = ($page - 1) * $per_page;
$action_filter = trim($_GET['action_filter'] ?? '');

$where = '';
$params = [];
if ($action_filter !== '') {
    $where = 'WHERE action LIKE ?';
    $params[] = '%' . $action_filter . '%';
}

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM audit_log $where");
$count_stmt->execute($params);
$total = (int)$count_stmt->fetchColumn();
$total_pages = max(1, (int)ceil($total / $per_page));

$stmt = $pdo->prepare("SELECT * FROM audit_log $where ORDER BY created_at DESC LIMIT $per_page OFFSET $offset");
$stmt->execute($params);
$logs = $stmt->fetchAll();
?>

<div class="card shadow">
    <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="m-0 font-weight-bold text-primary">Audit Log <span class="text-muted fw-normal">(<?php echo $total; ?> events)</span></h6>
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="action_filter" class="form-control form-control-sm" placeholder="Filter by action..." value="<?php echo htmlspecialchars($action_filter); ?>">
            <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover admin-table">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Admin</th>
                        <th>Action</th>
                        <th>On</th>
                        <th>Details</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="6" class="text-center text-muted">No audit events found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="text-nowrap"><?php echo date('d M Y, H:i', strtotime($log['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($log['username'] ?? '—'); ?></td>
                            <td><span class="order-status-pill status-<?php echo str_contains($log['action'], 'failed') || str_contains($log['action'], 'mismatch') ? 'cancelled' : 'confirmed'; ?>"><?php echo htmlspecialchars($log['action']); ?></span></td>
                            <td><?php echo htmlspecialchars(trim(($log['entity_type'] ?? '') . ' #' . ($log['entity_id'] ?? ''), ' #')); ?></td>
                            <td class="small text-muted"><?php echo htmlspecialchars($log['details'] ?? ''); ?></td>
                            <td class="small text-muted"><?php echo htmlspecialchars($log['ip_address'] ?? ''); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
        <nav>
            <ul class="pagination pagination-sm justify-content-center mb-0">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&action_filter=<?php echo urlencode($action_filter); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
