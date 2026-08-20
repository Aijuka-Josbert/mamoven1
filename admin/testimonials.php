<?php
$page_title = 'Manage Testimonials';
require_once __DIR__ . '/includes/header.php';
require_admin();

// Admin check
if ($_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$error_message = '';
$success_message = '';

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['id'])) {
    $action = $_POST['action'];
    $testimonial_id = (int)$_POST['id'];

    if (in_array($action, ['approve', 'reject'])) {
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
            $error_message = 'Your session security token is missing or expired. Please refresh the page and try again.';
        } else {
        $status = ($action === 'approve') ? 'approved' : 'rejected';
        try {
            $stmt = $pdo->prepare("UPDATE testimonials SET status = ? WHERE id = ?");
            $stmt->execute([$status, $testimonial_id]);
            log_audit_event('testimonial_' . $status, 'testimonial', $testimonial_id);
            $success_message = "Testimonial has been " . $status . ".";
        } catch (PDOException $e) {
            $error_message = "Failed to update testimonial: " . $e->getMessage();
        }
        }
    }
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_message = 'Your session security token is missing or expired. Please refresh the page and try again.';
    } else {
    $testimonial_id = (int)$_POST['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
        $stmt->execute([$testimonial_id]);
        log_audit_event('testimonial_deleted', 'testimonial', $testimonial_id);
        $success_message = "Testimonial has been deleted.";
    } catch (PDOException $e) {
        $error_message = "Failed to delete testimonial: " . $e->getMessage();
    }
    }
}

// Fetch all testimonials
try {
    $stmt = $pdo->query("
        SELECT t.*, u.full_name as user_name, u.email as user_email
        FROM testimonials t
        LEFT JOIN users u ON t.user_id = u.id
        ORDER BY t.created_at DESC
    ");
    $testimonials = $stmt->fetchAll();
} catch (PDOException $e) {
    $testimonials = [];
    $error_message = "Failed to fetch testimonials: " . $e->getMessage();
}
?>

<div class="d-flex justify-content-end align-items-center mb-4">
    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>

<?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $success_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php echo $error_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-body">
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link active" href="#pending" data-bs-toggle="tab">Pending</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#approved" data-bs-toggle="tab">Approved</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#rejected" data-bs-toggle="tab">Rejected</a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Pending Tab -->
            <div class="tab-pane fade show active" id="pending">
                <?php 
                $pending = array_filter($testimonials, fn($t) => $t['status'] === 'pending');
                if (empty($pending)):
                ?>
                    <p class="text-muted">No pending testimonials.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Rating</th>
                                    <th>Review</th>
                                    <th>Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending as $testimonial): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($testimonial['user_name'] ?? 'Unknown User'); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($testimonial['user_email'] ?? ''); ?></small>
                                    </td>
                                    <td>
                                        <span class="stars" style="color: #FFD700;">
                                            <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>★<?php endfor; ?>
                                        </span>
                                    </td>
                                    <td><?php echo mb_substr(htmlspecialchars($testimonial['content'] ?? $testimonial['content'] ?? $testimonial['message'] ?? '' ?? ''), 0, 50) . '...'; ?></td>
                                    <td><?php echo date('d M Y', strtotime($testimonial['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" id="approve-testimonial-<?php echo $testimonial['id']; ?>" class="d-none">
                                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <input type="hidden" name="id" value="<?php echo $testimonial['id']; ?>">
                                        </form>
                                        <button type="button" onclick="document.getElementById('approve-testimonial-<?php echo $testimonial['id']; ?>').submit()" class="btn btn-sm btn-success" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <form method="POST" id="reject-testimonial-<?php echo $testimonial['id']; ?>" class="d-none">
                                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <input type="hidden" name="id" value="<?php echo $testimonial['id']; ?>">
                                        </form>
                                        <button type="button" onclick="document.getElementById('reject-testimonial-<?php echo $testimonial['id']; ?>').submit()" class="btn btn-sm btn-danger" title="Reject">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Approved Tab -->
            <div class="tab-pane fade" id="approved">
                <?php 
                $approved = array_filter($testimonials, fn($t) => $t['status'] === 'approved');
                if (empty($approved)):
                ?>
                    <p class="text-muted">No approved testimonials.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Rating</th>
                                    <th>Review</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($approved as $testimonial): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($testimonial['user_name'] ?? 'Unknown'); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($testimonial['user_email'] ?? ''); ?></small>
                                    </td>
                                    <td>
                                        <span class="stars" style="color: #FFD700;">
                                            <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>★<?php endfor; ?>
                                        </span>
                                    </td>
                                    <td><?php echo substr(htmlspecialchars($testimonial['content'] ?? $testimonial['message'] ?? ''), 0, 50) . '...'; ?></td>
                                    <td>
                                        <form method="POST" id="delete-testimonial-approved-<?php echo $testimonial['id']; ?>" class="d-none">
                                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $testimonial['id']; ?>">
                                        </form>
                                        <button type="button" onclick="confirmDelete('delete-testimonial-approved-<?php echo $testimonial['id']; ?>')" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Rejected Tab -->
            <div class="tab-pane fade" id="rejected">
                <?php 
                $rejected = array_filter($testimonials, fn($t) => $t['status'] === 'rejected');
                if (empty($rejected)):
                ?>
                    <p class="text-muted">No rejected testimonials.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Rating</th>
                                    <th>Review</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rejected as $testimonial): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($testimonial['user_name'] ?? 'Unknown'); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($testimonial['user_email'] ?? ''); ?></small>
                                    </td>
                                    <td>
                                        <span class="stars" style="color: #FFD700;">
                                            <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>★<?php endfor; ?>
                                        </span>
                                    </td>
                                    <td><?php echo substr(htmlspecialchars($testimonial['content'] ?? $testimonial['message'] ?? ''), 0, 50) . '...'; ?></td>
                                    <td>
                                        <form method="POST" id="delete-testimonial-rejected-<?php echo $testimonial['id']; ?>" class="d-none">
                                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $testimonial['id']; ?>">
                                        </form>
                                        <button type="button" onclick="confirmDelete('delete-testimonial-rejected-<?php echo $testimonial['id']; ?>')" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
