<?php
// modules/print_order.php
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Print - <?php echo htmlspecialchars($order['order_number']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: white;
            font-family: Arial, sans-serif;
        }
        .print-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 40px;
            border: 1px solid #ddd;
            background: white;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h2 {
            color: #333;
            margin: 0;
        }
        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        .order-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .detail-box {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #f9f9f9;
        }
        .detail-label {
            font-weight: bold;
            color: #555;
            font-size: 12px;
            text-transform: uppercase;
        }
        .detail-value {
            font-size: 16px;
            color: #333;
            margin-top: 5px;
        }
        .items-section {
            margin: 30px 0;
        }
        .items-section h5 {
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .items-box {
            padding: 20px;
            border: 1px solid #ddd;
            background: #f9f9f9;
            min-height: 100px;
            border-radius: 5px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 20px;
            color: #666;
            font-size: 12px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
        }
        .status-pending { background: #ffc107; color: white; }
        .status-approved { background: #28a745; color: white; }
        .status-processing { background: #17a2b8; color: white; }
        .status-shipped { background: #007bff; color: white; }
        .status-delivered { background: #6f42c1; color: white; }
        
        @media print {
            body { margin: 0; padding: 0; }
            .print-container { border: none; padding: 0; margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="header">
            <div class="company-name">LogiTrack</div>
            <h2>Order Receipt</h2>
        </div>

        <div class="order-details">
            <div class="detail-box">
                <div class="detail-label">Order Number</div>
                <div class="detail-value"><?php echo htmlspecialchars($order['order_number']); ?></div>
            </div>
            <div class="detail-box">
                <div class="detail-label">Order Date</div>
                <div class="detail-value"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></div>
            </div>
            <div class="detail-box">
                <div class="detail-label">Customer Name</div>
                <div class="detail-value"><?php echo htmlspecialchars($order['customer_name']); ?></div>
            </div>
            <div class="detail-box">
                <div class="detail-label">Order Status</div>
                <div class="detail-value">
                    <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                        <?php echo $order['status']; ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="order-details">
            <div class="detail-box">
                <div class="detail-label">Total Amount</div>
                <div class="detail-value" style="font-size: 20px; color: #007bff; font-weight: bold;">
                    $<?php echo number_format($order['total_amount'], 2); ?>
                </div>
            </div>
            <div class="detail-box">
                <div class="detail-label">Created By</div>
                <div class="detail-value"><?php echo htmlspecialchars($order['created_by_name'] ?? 'System'); ?></div>
            </div>
        </div>

        <div class="items-section">
            <h5>Order Items</h5>
            <div class="items-box">
<?php echo !empty($order['items']) ? htmlspecialchars($order['items']) : 'No items specified'; ?>
            </div>
        </div>

        <div class="footer">
            <p>This is an official order receipt from LogiTrack Logistics Management System</p>
            <p>Printed on: <?php echo date('F d, Y \a\t h:i A'); ?></p>
            <button class="btn btn-sm btn-primary no-print" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
            <button class="btn btn-sm btn-secondary no-print ms-2" onclick="window.close()">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
