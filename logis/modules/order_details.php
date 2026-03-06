<?php
// modules/order_details.php
require_once '../includes/header.php';
require_once '../config/database.php';

if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$query = "SELECT o.*, u.full_name as created_by_name FROM orders o 
          LEFT JOIN users u ON o.created_by = u.id 
          WHERE o.id = :id";
$stmt = $db->prepare($query);
$stmt->execute([':id' => $_GET['id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "Order not found.";
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'update_status') {
        $query = "UPDATE orders SET status = :status WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':status' => $_POST['status'],
            ':id' => $_GET['id']
        ]);
        
        // Refresh order data
        $stmt = $db->prepare("SELECT o.*, u.full_name as created_by_name FROM orders o 
                             LEFT JOIN users u ON o.created_by = u.id 
                             WHERE o.id = :id");
        $stmt->execute([':id' => $_GET['id']]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<div class="container-fluid mt-5">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Order Details - <?php echo htmlspecialchars($order['order_number']); ?></h5>
                    <div>
                        <a href="orders.php" class="btn btn-sm btn-secondary me-2">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </a>
                        <button onclick="printOrder(<?php echo $order['id']; ?>)" class="btn btn-sm btn-primary">
                            <i class="fas fa-print me-2"></i>Print
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-muted">Order Information</h6>
                                    <p class="mb-2"><strong>Order Number:</strong> <?php echo htmlspecialchars($order['order_number']); ?></p>
                                    <p class="mb-2"><strong>Order Date:</strong> <?php echo date('M d, Y', strtotime($order['order_date'])); ?></p>
                                    <p class="mb-2"><strong>Customer Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                                    <p class="mb-2"><strong>Created By:</strong> <?php echo htmlspecialchars($order['created_by_name'] ?? 'System'); ?></p>
                                    <p class="mb-0"><strong>Created At:</strong> <?php echo $order['created_at']; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-muted">Financial Information</h6>
                                    <p class="mb-2"><strong>Total Amount:</strong> <span class="text-primary" style="font-size: 20px; font-weight: bold;">$<?php echo number_format($order['total_amount'], 2); ?></span></p>
                                    <p class="mb-2"><strong>Current Status:</strong></p>
                                    <div>
                                        <span class="badge bg-<?php 
                                            echo match($order['status']) {
                                                'Pending' => 'warning',
                                                'Approved' => 'success',
                                                'Processing' => 'info',
                                                'Shipped' => 'primary',
                                                'Delivered' => 'success',
                                                default => 'secondary'
                                            };
                                        ?>" style="font-size: 14px; padding: 8px 12px;">
                                            <?php echo $order['status']; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-muted">Order Items</h6>
                                    <p><?php echo nl2br(htmlspecialchars($order['items'] ?? 'No items specified')); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (in_array($_SESSION['role'], ['Admin', 'Procurement Officer'])): ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-muted mb-3">Update Order Status</h6>
                                    <form method="POST" class="d-flex gap-2 align-items-end">
                                        <input type="hidden" name="action" value="update_status">
                                        <div class="flex-grow-1">
                                            <label class="form-label">New Status</label>
                                            <select name="status" class="form-select">
                                                <option value="Pending" <?php echo $order['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="Approved" <?php echo $order['status'] == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                                <option value="Processing" <?php echo $order['status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                                <option value="Shipped" <?php echo $order['status'] == 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                <option value="Delivered" <?php echo $order['status'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-check me-2"></i>Update
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function printOrder(orderId) {
    window.open(`print_order.php?id=${orderId}`, '_blank');
}
</script>

<?php require_once '../includes/footer.php'; ?>
