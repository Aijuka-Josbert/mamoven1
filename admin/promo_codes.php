<?php
$page_title = 'Promo Codes';
require_once __DIR__ . '/includes/header.php';
require_admin();

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_promo') {
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $description = trim($_POST['description'] ?? '');
        $discount_type = $_POST['discount_type'] ?? 'percentage';
        $discount_value = (float)($_POST['discount_value'] ?? 0);
        $min_order_amount = (float)($_POST['min_order_amount'] ?? 0);
        $max_uses_input = trim($_POST['max_uses'] ?? '');
        $max_uses = $max_uses_input === '' ? null : (int)$max_uses_input;
        $valid_from_input = trim($_POST['valid_from'] ?? '');
        $valid_until_input = trim($_POST['valid_until'] ?? '');
        $status = $_POST['status'] ?? 'active';

        if ($code === '' || !preg_match('/^[A-Z0-9_-]{3,50}$/', $code)) {
            $error_message = 'Promo code must be 3-50 chars and use only letters, numbers, underscore, or dash.';
        } elseif (!in_array($discount_type, ['percentage', 'fixed'], true)) {
            $error_message = 'Invalid discount type.';
        } elseif ($discount_value <= 0) {
            $error_message = 'Discount value must be greater than zero.';
        } elseif ($discount_type === 'percentage' && $discount_value > 100) {
            $error_message = 'Percentage discount cannot exceed 100%.';
        } elseif ($min_order_amount < 0) {
            $error_message = 'Minimum order amount cannot be negative.';
        } elseif ($max_uses !== null && $max_uses < 1) {
            $error_message = 'Max uses must be at least 1 when provided.';
        } elseif (!in_array($status, ['active', 'inactive'], true)) {
            $error_message = 'Invalid status.';
        } else {
            try {
                $valid_from = $valid_from_input !== '' ? date('Y-m-d H:i:s', strtotime($valid_from_input)) : null;
                $valid_until = $valid_until_input !== '' ? date('Y-m-d H:i:s', strtotime($valid_until_input)) : null;

                if ($valid_from && $valid_until && strtotime($valid_until) <= strtotime($valid_from)) {
                    throw new Exception('Valid until date must be later than valid from date.');
                }

                $insert_stmt = $pdo->prepare(
                    'INSERT INTO promo_codes (code, description, discount_type, discount_value, min_order_amount, max_uses, valid_from, valid_until, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $insert_stmt->execute([
                    $code,
                    $description !== '' ? $description : null,
                    $discount_type,
                    $discount_value,
                    $min_order_amount,
                    $max_uses,
                    $valid_from,
                    $valid_until,
                    $status,
                ]);

                $success_message = 'Promo code created successfully.';
            } catch (Throwable $e) {
                if ($e instanceof PDOException && (int)$e->getCode() === 23000) {
                    $error_message = 'Promo code already exists. Use a different code.';
                } else {
                    $error_message = 'Failed to create promo code: ' . $e->getMessage();
                }
            }
        }
    }

    if ($action === 'toggle_status') {
        $promo_id = (int)($_POST['promo_id'] ?? 0);
        $next_status = $_POST['next_status'] ?? '';

        if ($promo_id > 0 && in_array($next_status, ['active', 'inactive'], true)) {
            try {
                $update_stmt = $pdo->prepare('UPDATE promo_codes SET status = ? WHERE id = ?');
                $update_stmt->execute([$next_status, $promo_id]);
                $success_message = 'Promo status updated successfully.';
            } catch (PDOException $e) {
                $error_message = 'Failed to update promo status: ' . $e->getMessage();
            }
        }
    }

    if ($action === 'delete_promo') {
        $promo_id = (int)($_POST['promo_id'] ?? 0);
        if ($promo_id > 0) {
            try {
                $delete_stmt = $pdo->prepare('DELETE FROM promo_codes WHERE id = ?');
                $delete_stmt->execute([$promo_id]);
                $success_message = 'Promo code deleted successfully.';
            } catch (PDOException $e) {
                $error_message = 'Failed to delete promo code: ' . $e->getMessage();
            }
        }
    }
}

try {
    $promo_stmt = $pdo->query(
        'SELECT p.*, COUNT(u.id) AS usage_count
         FROM promo_codes p
         LEFT JOIN promo_usage u ON u.promo_id = p.id
         GROUP BY p.id
         ORDER BY p.created_at DESC'
    );
    $promo_codes = $promo_stmt->fetchAll();
} catch (PDOException $e) {
    $promo_codes = [];
    if ($error_message === '') {
        $error_message = 'Could not fetch promo codes: ' . $e->getMessage();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Manage Promo Codes</h1>
    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>

<?php if (!empty($success_message)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<?php if (!empty($error_message)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header">
        <h2 class="h5 mb-0">How Promo Codes Work</h2>
    </div>
    <div class="card-body">
        <ol class="mb-0">
            <li>Create a promo code below and keep status as Active.</li>
            <li>Share the exact code with customers (WhatsApp, social media, or in-store).</li>
            <li>Customers enter the code at checkout in the Promo Code box.</li>
            <li>The discount applies automatically if order amount and date rules are met.</li>
            <li>Monitor usage in the table and deactivate or delete codes when needed.</li>
        </ol>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header">
        <h2 class="h5 mb-0">Create Promo Code</h2>
    </div>
    <div class="card-body">
        <form method="POST" class="row g-3">
            <input type="hidden" name="action" value="create_promo">

            <div class="col-md-4">
                <label for="code" class="form-label">Code</label>
                <input type="text" id="code" name="code" class="form-control" placeholder="EASTER20" required>
            </div>

            <div class="col-md-4">
                <label for="discount_type" class="form-label">Discount Type</label>
                <select id="discount_type" name="discount_type" class="form-select" required>
                    <option value="percentage">Percentage</option>
                    <option value="fixed">Fixed Amount</option>
                </select>
            </div>

            <div class="col-md-4">
                <label for="discount_value" class="form-label">Discount Value</label>
                <input type="number" step="0.01" min="0.01" id="discount_value" name="discount_value" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label for="description" class="form-label">Description</label>
                <input type="text" id="description" name="description" class="form-control" placeholder="20% off birthday cakes">
            </div>

            <div class="col-md-3">
                <label for="min_order_amount" class="form-label">Minimum Order (UGX)</label>
                <input type="number" step="0.01" min="0" id="min_order_amount" name="min_order_amount" class="form-control" value="0">
            </div>

            <div class="col-md-3">
                <label for="max_uses" class="form-label">Max Uses</label>
                <input type="number" min="1" id="max_uses" name="max_uses" class="form-control" placeholder="Leave blank = unlimited">
            </div>

            <div class="col-md-4">
                <label for="valid_from" class="form-label">Valid From</label>
                <input type="datetime-local" id="valid_from" name="valid_from" class="form-control">
            </div>

            <div class="col-md-4">
                <label for="valid_until" class="form-label">Valid Until</label>
                <input type="datetime-local" id="valid_until" name="valid_until" class="form-control">
            </div>

            <div class="col-md-4">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Create Promo Code
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow">
    <div class="card-header">
        <h2 class="h5 mb-0">Existing Promo Codes</h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Discount</th>
                        <th>Minimum</th>
                        <th>Validity</th>
                        <th>Usage</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($promo_codes)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No promo codes yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($promo_codes as $promo): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($promo['code']); ?></strong>
                                    <?php if (!empty($promo['description'])): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($promo['description']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($promo['discount_type'] === 'percentage'): ?>
                                        <?php echo rtrim(rtrim(number_format((float)$promo['discount_value'], 2, '.', ''), '0'), '.'); ?>%
                                    <?php else: ?>
                                        UGX <?php echo number_format((float)$promo['discount_value']); ?>
                                    <?php endif; ?>
                                </td>
                                <td>UGX <?php echo number_format((float)$promo['min_order_amount']); ?></td>
                                <td>
                                    <?php echo $promo['valid_from'] ? date('d M Y H:i', strtotime($promo['valid_from'])) : 'Any time'; ?>
                                    <br>
                                    <?php echo $promo['valid_until'] ? date('d M Y H:i', strtotime($promo['valid_until'])) : 'No expiry'; ?>
                                </td>
                                <td>
                                    <?php
                                        $usage_count = (int)$promo['usage_count'];
                                        $max_uses = $promo['max_uses'] !== null ? (int)$promo['max_uses'] : null;
                                    ?>
                                    <?php if ($max_uses !== null): ?>
                                        <?php echo $usage_count . ' / ' . $max_uses; ?>
                                    <?php else: ?>
                                        <?php echo $usage_count; ?> (unlimited)
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($promo['status'] === 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="promo_id" value="<?php echo (int)$promo['id']; ?>">
                                        <input type="hidden" name="next_status" value="<?php echo $promo['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-warning">
                                            <?php echo $promo['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                        </button>
                                    </form>

                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this promo code permanently?');">
                                        <input type="hidden" name="action" value="delete_promo">
                                        <input type="hidden" name="promo_id" value="<?php echo (int)$promo['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
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
