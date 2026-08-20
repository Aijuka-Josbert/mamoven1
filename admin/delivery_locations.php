<?php
$page_title = 'Delivery Locations';
require_once __DIR__ . '/includes/header.php';
require_admin();
$success_msg = '';
$error_msg = '';

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Your session security token is missing or expired. Please refresh the page and try again.';
    } elseif (isset($_POST['add_location'])) {
        $name = trim($_POST['name']);
        $fee = (float)$_POST['fee'];
        try {
            $stmt = $pdo->prepare("INSERT INTO delivery_locations (name, fee) VALUES (?, ?)");
            $stmt->execute([$name, $fee]);
            log_audit_event('delivery_location_created', 'delivery_location', $pdo->lastInsertId(), $name);
            $success_msg = "Delivery location added successfully!";
        } catch (PDOException $e) {
            $error_msg = "Error adding location. It might already exist.";
        }
    } elseif (isset($_POST['delete_location'])) {
        $id = (int)$_POST['location_id'];
        $stmt = $pdo->prepare("DELETE FROM delivery_locations WHERE id = ?");
        $stmt->execute([$id]);
        log_audit_event('delivery_location_deleted', 'delivery_location', $id);
        $success_msg = "Location removed.";
    } elseif (isset($_POST['update_location'])) {
        $id = (int)$_POST['location_id'];
        $fee = (float)($_POST['fee'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $stmt = $pdo->prepare("UPDATE delivery_locations SET fee = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$fee, $is_active, $id]);
        log_audit_event('delivery_location_updated', 'delivery_location', $id, 'Fee: ' . $fee . ', Active: ' . ($is_active ? 'yes' : 'no'));
        $success_msg = "Location updated.";
    }
}

// Fetch Locations
$locations = $pdo->query("SELECT * FROM delivery_locations ORDER BY name ASC")->fetchAll();
?>

<div class="container-fluid px-4 py-4">
    <h2>Manage Delivery Zones</h2>
    
    <?php if ($success_msg) echo "<div class='alert alert-success'>$success_msg</div>"; ?>
    <?php if ($error_msg) echo "<div class='alert alert-danger'>$error_msg</div>"; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white"><h5>Add New Zone</h5></div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <div class="mb-3">
                            <label>Location Name (e.g., Ntinda, Entebbe)</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Delivery Fee (UGX)</label>
                            <input type="number" name="fee" class="form-control" required>
                        </div>
                        <button type="submit" name="add_location" class="btn btn-primary w-100">Add Location</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr><th>Location</th><th style="width:160px;">Fee (UGX)</th><th style="width:90px;">Active</th><th style="width:170px;">Action</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($locations as $loc): ?>
                            <?php $formId = 'loc-form-' . $loc['id']; ?>
                            <tr class="<?php echo (!$loc['is_active'] || $loc['fee'] == 0) ? 'table-warning' : ''; ?>">
                                <td>
                                    <form id="<?php echo $formId; ?>" method="POST" class="d-none">
                                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                        <input type="hidden" name="location_id" value="<?php echo $loc['id']; ?>">
                                    </form>
                                    <?php echo htmlspecialchars($loc['name']); ?>
                                    <?php if ($loc['fee'] == 0): ?><span class="badge bg-secondary ms-1">Not priced</span><?php endif; ?>
                                </td>
                                <td><input type="number" step="0.01" min="0" name="fee" form="<?php echo $formId; ?>" value="<?php echo $loc['fee']; ?>" class="form-control form-control-sm"></td>
                                <td class="text-center"><input type="checkbox" name="is_active" form="<?php echo $formId; ?>" class="form-check-input" <?php echo $loc['is_active'] ? 'checked' : ''; ?>></td>
                                <td class="d-flex gap-1">
                                    <button type="submit" name="update_location" form="<?php echo $formId; ?>" class="btn btn-sm btn-outline-primary">Save</button>
                                    <button type="submit" name="delete_location" form="<?php echo $formId; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this location?');">Del</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>