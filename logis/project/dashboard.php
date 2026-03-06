<?php
// dashboard.php
require_once '../includes/header.php';
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get statistics based on role
$stats = [];

// Common stats
$ordersQuery = $db->query("SELECT COUNT(*) as total, 
                           SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                           SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
                           SUM(CASE WHEN status = 'Shipped' THEN 1 ELSE 0 END) as shipped,
                           SUM(CASE WHEN status = 'Delivered' THEN 1 ELSE 0 END) as delivered
                           FROM orders")->fetch(PDO::FETCH_ASSOC);
$stats['orders'] = $ordersQuery;

// Shipments stats
$shipmentsQuery = $db->query("SELECT COUNT(*) as total,
                              SUM(CASE WHEN status = 'In Transit' THEN 1 ELSE 0 END) as in_transit,
                              SUM(CASE WHEN status = 'Delivered' THEN 1 ELSE 0 END) as delivered
                              FROM shipments")->fetch(PDO::FETCH_ASSOC);
$stats['shipments'] = $shipmentsQuery;

// Vehicles stats
$vehiclesQuery = $db->query("SELECT COUNT(*) as total,
                             SUM(CASE WHEN status = 'Available' THEN 1 ELSE 0 END) as available
                             FROM vehicles")->fetch(PDO::FETCH_ASSOC);
$stats['vehicles'] = $vehiclesQuery;

// Recent orders
$recentOrders = $db->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <!-- Welcome Banner -->
    <div class="welcome-banner animate__animated animate__fadeIn">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2>Welcome back, <?php echo $_SESSION['full_name']; ?>!</h2>
                <p class="mb-0">Here's what's happening with your logistics today.</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="date-display">
                    <i class="fas fa-calendar-alt me-2"></i><?php echo date('l, F j, Y'); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mt-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card hover-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary-soft">
                            <i class="fas fa-shopping-cart fa-2x"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="stat-label">Total Orders</h6>
                            <h3 class="stat-value"><?php echo $stats['orders']['total'] ?? 0; ?></h3>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 5px;">
                        <div class="progress-bar bg-primary" style="width: <?php echo ($stats['orders']['shipped']/$stats['orders']['total'])*100 ?? 0; ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card hover-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-success-soft">
                            <i class="fas fa-truck fa-2x"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="stat-label">Active Shipments</h6>
                            <h3 class="stat-value"><?php echo $stats['shipments']['in_transit'] ?? 0; ?></h3>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 5px;">
                        <div class="progress-bar bg-success" style="width: <?php echo ($stats['shipments']['delivered']/$stats['shipments']['total'])*100 ?? 0; ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card hover-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-warning-soft">
                            <i class="fas fa-truck-pickup fa-2x"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="stat-label">Available Vehicles</h6>
                            <h3 class="stat-value"><?php echo $stats['vehicles']['available'] ?? 0; ?></h3>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 5px;">
                        <div class="progress-bar bg-warning" style="width: <?php echo ($stats['vehicles']['available']/$stats['vehicles']['total'])*100 ?? 0; ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card hover-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-info-soft">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="stat-label">Delivered Today</h6>
                            <h3 class="stat-value">12</h3>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 5px;">
                        <div class="progress-bar bg-info" style="width: 75%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Module Cards -->
    <div class="row mt-4">
        <?php if (in_array($role, ['Admin', 'Procurement Officer'])): ?>
        <div class="col-lg-4 mb-4">
            <div class="module-card procurement-module hover-card" onclick="location.href='/logis/modules/orders.php'">
                <div class="card-body text-center">
                    <div class="module-icon procurement-icon">
                        <i class="fas fa-shopping-cart fa-3x"></i>
                    </div>
                    <h5 class="mt-3">Order Management</h5>
                    <p class="text-muted">Create and approve orders</p>
                    <div class="module-stats">
                        <span class="badge bg-light text-dark">Pending: <?php echo $stats['orders']['pending'] ?? 0; ?></span>
                        <span class="badge bg-success">Approved: <?php echo $stats['orders']['approved'] ?? 0; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="module-card procurement-module hover-card" onclick="location.href='/logis/modules/suppliers.php'">
                <div class="card-body text-center">
                    <div class="module-icon procurement-icon">
                        <i class="fas fa-truck fa-3x"></i>
                    </div>
                    <h5 class="mt-3">Supplier & Vendor</h5>
                    <p class="text-muted">Manage suppliers and POs</p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (in_array($role, ['Admin', 'Transport Coordinator'])): ?>
        <div class="col-lg-4 mb-4">
            <div class="module-card transport-module hover-card" onclick="location.href='/logis/modules/transport.php'">
                <div class="card-body text-center">
                    <div class="module-icon transport-icon">
                        <i class="fas fa-route fa-3x"></i>
                    </div>
                    <h5 class="mt-3">Transportation</h5>
                    <p class="text-muted">Plan routes and dispatch</p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (in_array($role, ['Admin', 'Fleet Manager'])): ?>
        <div class="col-lg-4 mb-4">
            <div class="module-card fleet-module hover-card" onclick="location.href='/logis/modules/fleet.php'">
                <div class="card-body text-center">
                    <div class="module-icon fleet-icon">
                        <i class="fas fa-truck-pickup fa-3x"></i>
                    </div>
                    <h5 class="mt-3">Fleet Management</h5>
                    <p class="text-muted">Manage vehicles and drivers</p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (in_array($role, ['Admin', 'Tracking Officer'])): ?>
        <div class="col-lg-4 mb-4">
            <div class="module-card tracking-module hover-card" onclick="location.href='/logis/modules/tracking.php'">
                <div class="card-body text-center">
                    <div class="module-icon tracking-icon">
                        <i class="fas fa-map-marker-alt fa-3x"></i>
                    </div>
                    <h5 class="mt-3">Shipment Tracking</h5>
                    <p class="text-muted">Track and update shipments</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Charts and Tables -->
    <div class="row mt-4">
        <div class="col-lg-8 mb-4">
            <div class="chart-card">
                <div class="card-header">
                    <h5>Order Progress Overview</h5>
                </div>
                <div class="card-body">
                    <canvas id="orderProgressChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="chart-card">
                <div class="card-header">
                    <h5>Shipment Status</h5>
                </div>
                <div class="card-body">
                    <canvas id="shipmentStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="row mt-4">
        <div class="col-12 mb-4">
            <div class="table-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Recent Orders</h5>
                    <a href="/logis/modules/orders.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td><strong><?php echo $order['order_number']; ?></strong></td>
                                    <td><?php echo $order['customer_name']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                    <td>
                                        <span class="badge status-<?php echo strtolower($order['status']); ?>">
                                            <?php echo $order['status']; ?>
                                        </span>
                                    </td>
                                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts using ChartManager
    if (typeof chartManager !== 'undefined') {
        // Order Progress Chart
        if (document.getElementById('orderProgressChart')) {
            chartManager.createOrderProgressChart('orderProgressChart', {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                values: [12, 19, 15, 17, 14, 13]
            });
        }

        // Shipment Status Chart
        if (document.getElementById('shipmentStatusChart')) {
            chartManager.createShipmentStatusChart('shipmentStatusChart', {
                labels: ['In Transit', 'Delivered', 'Delayed', 'Preparing'],
                values: [45, 30, 10, 15]
            });
        }
    } else {
        // Fallback to direct Chart.js if ChartManager not available
        // Order Progress Chart
        const orderCtx = document.getElementById('orderProgressChart');
        if (orderCtx) {
            const orderChart = orderCtx.getContext('2d');
            new Chart(orderChart, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Orders',
                        data: [12, 19, 15, 17, 14, 13],
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78, 115, 223, 0.05)',
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        // Shipment Status Chart
        const shipmentCtx = document.getElementById('shipmentStatusChart');
        if (shipmentCtx) {
            const shipmentChart = shipmentCtx.getContext('2d');
            new Chart(shipmentChart, {
                type: 'doughnut',
                data: {
                    labels: ['In Transit', 'Delivered', 'Delayed', 'Preparing'],
                    datasets: [{
                        data: [45, 30, 10, 15],
                        backgroundColor: ['#36b9cc', '#1cc88a', '#e74a3b', '#f6c23e'],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>