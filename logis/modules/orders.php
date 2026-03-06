<?php
// modules/orders.php
require_once '../includes/header.php';
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Handle order creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'create_order') {
        $order_number = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
        $query = "INSERT INTO orders (order_number, customer_name, order_date, status, total_amount, items, created_by) 
                  VALUES (:order_number, :customer_name, :order_date, :status, :total_amount, :items, :created_by)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':order_number' => $order_number,
            ':customer_name' => $_POST['customer_name'],
            ':order_date' => $_POST['order_date'],
            ':status' => 'Pending',
            ':total_amount' => $_POST['total_amount'],
            ':items' => $_POST['items'] ?? '',
            ':created_by' => $_SESSION['user_id']
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Order created successfully']);
        exit();
    }
    
    if ($_POST['action'] == 'approve_order') {
        $query = "UPDATE orders SET status = 'Approved' WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $_POST['order_id']]);
        
        echo json_encode(['success' => true, 'message' => 'Order approved successfully']);
        exit();
    }
}

// Fetch orders
$orders = $db->query("SELECT * FROM orders ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="module-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4>Order Management</h4>
                <p class="text-muted mb-0">Manage and track all orders</p>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createOrderModal">
                    <i class="fas fa-plus me-2"></i>Create New Order
                </button>
                <button class="btn btn-outline-primary ms-2" id="refreshOrders">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="filter-bar">
        <div class="row">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="tableSearch" placeholder="Search orders...">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Processing">Processing</option>
                    <option value="Shipped">Shipped</option>
                    <option value="Delivered">Delivered</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" class="form-control" id="dateFilter">
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-secondary w-100" id="exportBtn">
                    <i class="fas fa-file-export me-2"></i>Export
                </button>
            </div>
        </div>
    </div>

    <div class="data-table">
        <table class="table table-hover" id="exportableTable">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><strong><?php echo $order['order_number']; ?></strong></td>
                    <td><?php echo $order['customer_name']; ?></td>
                    <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                    <td>
                        <span class="badge status-<?php echo strtolower($order['status']); ?>" id="status-<?php echo $order['id']; ?>">
                            <?php echo $order['status']; ?>
                        </span>
                    </td>
                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                    <td>
                        <?php if ($order['status'] == 'Pending' && in_array($_SESSION['role'], ['Admin', 'Procurement Officer'])): ?>
                        <button class="btn btn-sm btn-success status-update" 
                                data-id="<?php echo $order['id']; ?>" 
                                data-status="Approved"
                                onclick="approveOrder(<?php echo $order['id']; ?>)">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <?php endif; ?>
                        <button class="btn btn-sm btn-info" onclick="viewOrder(<?php echo $order['id']; ?>)">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-primary" onclick="printOrder(<?php echo $order['id']; ?>)">
                            <i class="fas fa-print"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create Order Modal -->
<div class="modal fade" id="createOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createOrderForm" class="needs-validation" novalidate>
                    <div class="form-group mb-3">
                        <label class="form-label">Customer Name</label>
                        <input type="text" class="form-control" name="customer_name" required>
                        <div class="invalid-feedback">Please enter customer name</div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Order Date</label>
                        <input type="date" class="form-control" name="order_date" required>
                        <div class="invalid-feedback">Please select order date</div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Total Amount</label>
                        <input type="number" step="0.01" class="form-control" name="total_amount" required>
                        <div class="invalid-feedback">Please enter total amount</div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Items</label>
                        <textarea class="form-control" name="items" rows="3" placeholder="Enter order items"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitOrder()">Create Order</button>
            </div>
        </div>
    </div>
</div>

<script>
function submitOrder() {
    const form = document.getElementById('createOrderForm');
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }
    
    const formData = new FormData(form);
    formData.append('action', 'create_order');
    
    showLoader('spinner');
    
    fetch('orders.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoader();
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        }
    })
    .catch(error => {
        hideLoader();
        showNotification('Error creating order', 'error');
    });
}

function approveOrder(orderId) {
    if (!confirm('Are you sure you want to approve this order?')) return;
    
    showLoader('spinner');
    
    const formData = new FormData();
    formData.append('action', 'approve_order');
    formData.append('order_id', orderId);
    
    fetch('orders.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoader();
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        }
    })
    .catch(error => {
        hideLoader();
        showNotification('Error approving order', 'error');
    });
}

function viewOrder(orderId) {
    // Implement view order details
    window.location.href = `order_details.php?id=${orderId}`;
}

function printOrder(orderId) {
    // Implement print order
    window.open(`print_order.php?id=${orderId}`, '_blank');
}

// Refresh orders
document.getElementById('refreshOrders')?.addEventListener('click', function() {
    showLoader('spinner');
    setTimeout(() => {
        location.reload();
    }, 1000);
});

// Status filter
document.getElementById('statusFilter')?.addEventListener('change', function() {
    const status = this.value.toLowerCase();
    const rows = document.querySelectorAll('#exportableTable tbody tr');
    
    rows.forEach(row => {
        const rowStatus = row.querySelector('td:nth-child(4) .badge').textContent.toLowerCase();
        if (!status || rowStatus === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>