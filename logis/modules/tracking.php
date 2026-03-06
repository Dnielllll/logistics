<?php
// modules/tracking.php
require_once '../includes/header.php';
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Handle tracking updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'update_tracking') {
        $query = "UPDATE shipments SET tracking_updates = :updates WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':updates' => $_POST['update_text'],
            ':id' => $_POST['shipment_id']
        ]);
    }
}

// Get all shipments with tracking info
$query = "SELECT s.*, o.order_number, o.customer_name, v.vehicle_number, d.name as driver_name 
          FROM shipments s 
          LEFT JOIN orders o ON s.order_id = o.id 
          LEFT JOIN vehicles v ON s.vehicle_id = v.id 
          LEFT JOIN drivers d ON s.driver_id = d.id 
          ORDER BY s.id DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid mt-5">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Shipment Tracking</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Shipment #</th>
                                    <th>Order</th>
                                    <th>Customer</th>
                                    <th>Vehicle</th>
                                    <th>Driver</th>
                                    <th>Status</th>
                                    <th>Departure</th>
                                    <th>Est. Arrival</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($shipments as $shipment): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($shipment['shipment_number']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($shipment['order_number'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($shipment['customer_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($shipment['vehicle_number'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($shipment['driver_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($shipment['status']) {
                                                'Preparing' => 'secondary',
                                                'Loaded' => 'info',
                                                'In Transit' => 'primary',
                                                'Delivered' => 'success',
                                                'Delayed' => 'danger',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo $shipment['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $shipment['departure_date']; ?></td>
                                    <td><?php echo $shipment['estimated_arrival']; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#trackingModal<?php echo $shipment['id']; ?>">
                                            <i class="fas fa-tracking"></i> Track
                                        </button>
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
</div>

<!-- Tracking Update Modals -->
<?php foreach ($shipments as $shipment): ?>
<div class="modal fade" id="trackingModal<?php echo $shipment['id']; ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Track: <?php echo htmlspecialchars($shipment['shipment_number']); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Order:</strong> <?php echo htmlspecialchars($shipment['order_number']); ?></p>
                        <p><strong>Customer:</strong> <?php echo htmlspecialchars($shipment['customer_name']); ?></p>
                        <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($shipment['vehicle_number']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Driver:</strong> <?php echo htmlspecialchars($shipment['driver_name']); ?></p>
                        <p><strong>Status:</strong> 
                            <span class="badge bg-<?php 
                                echo match($shipment['status']) {
                                    'Preparing' => 'secondary',
                                    'Loaded' => 'info',
                                    'In Transit' => 'primary',
                                    'Delivered' => 'success',
                                    'Delayed' => 'danger',
                                    default => 'secondary'
                                };
                            ?>">
                                <?php echo $shipment['status']; ?>
                            </span>
                        </p>
                        <p><strong>Route:</strong> <?php echo htmlspecialchars($shipment['route_from']) . ' → ' . htmlspecialchars($shipment['route_to']); ?></p>
                    </div>
                </div>

                <hr>

                <h6>Route Information</h6>
                <div class="alert alert-light border">
                    <p><strong>From:</strong> <?php echo htmlspecialchars($shipment['route_from']); ?></p>
                    <p><strong>To:</strong> <?php echo htmlspecialchars($shipment['route_to']); ?></p>
                    <p><strong>Departure:</strong> <?php echo $shipment['departure_date']; ?></p>
                    <p><strong>Est. Arrival:</strong> <?php echo $shipment['estimated_arrival']; ?></p>
                    <?php if ($shipment['actual_arrival']): ?>
                    <p><strong>Actual Arrival:</strong> <?php echo $shipment['actual_arrival']; ?></p>
                    <?php endif; ?>
                </div>

                <h6>Tracking Updates</h6>
                <div class="alert alert-light border" style="max-height: 200px; overflow-y: auto;">
                    <?php echo nl2br(htmlspecialchars($shipment['tracking_updates'] ?? 'No updates yet')); ?>
                </div>

                <form method="POST">
                    <input type="hidden" name="action" value="update_tracking">
                    <input type="hidden" name="shipment_id" value="<?php echo $shipment['id']; ?>">
                    <div class="mb-3">
                        <label class="form-label">Add Update</label>
                        <textarea name="update_text" class="form-control" rows="3" placeholder="Add tracking update..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Add Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php require_once '../includes/footer.php'; ?>
