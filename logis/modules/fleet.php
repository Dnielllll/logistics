<?php
// modules/fleet.php
require_once '../includes/header.php';
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Handle vehicle creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'create_vehicle') {
        $query = "INSERT INTO vehicles (vehicle_number, type, capacity, status, last_maintenance) 
                  VALUES (:vehicle_number, :type, :capacity, :status, :last_maintenance)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':vehicle_number' => $_POST['vehicle_number'],
            ':type' => $_POST['type'],
            ':capacity' => $_POST['capacity'],
            ':status' => $_POST['status'] ?? 'Available',
            ':last_maintenance' => $_POST['last_maintenance'] ?? date('Y-m-d')
        ]);
    }
    
    if ($_POST['action'] == 'create_driver') {
        $query = "INSERT INTO drivers (name, license_number, phone, status) 
                  VALUES (:name, :license_number, :phone, :status)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':name' => $_POST['driver_name'],
            ':license_number' => $_POST['license_number'],
            ':phone' => $_POST['driver_phone'],
            ':status' => $_POST['driver_status'] ?? 'Available'
        ]);
    }
}

// Get all vehicles
$query = "SELECT * FROM vehicles ORDER BY vehicle_number";
$stmt = $db->prepare($query);
$stmt->execute();
$vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get drivers
$drivers = $db->query("SELECT id, name, status FROM drivers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid mt-5">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-truck-pickup me-2"></i>Fleet Management</h5>
                </div>
                <div class="card-body">
                    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#vehicleModal">
                        <i class="fas fa-plus me-2"></i>Add New Vehicle
                    </button>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Vehicle #</th>
                                    <th>Type</th>
                                    <th>Capacity</th>
                                    <th>Status</th>
                                    <th>Last Maintenance</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($vehicles as $vehicle): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($vehicle['vehicle_number']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($vehicle['type']); ?></td>
                                    <td><?php echo htmlspecialchars($vehicle['capacity']); ?> kg</td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($vehicle['status']) {
                                                'Available' => 'success',
                                                'In Transit' => 'info',
                                                'Maintenance' => 'warning',
                                                'Out of Service' => 'danger',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo $vehicle['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $vehicle['last_maintenance']; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
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

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-tie me-2"></i>Driver Management</h5>
                </div>
                <div class="card-body">
                    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#driverModal">
                        <i class="fas fa-plus me-2"></i>Add New Driver
                    </button>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Driver Name</th>
                                    <th>License Number</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($drivers as $driver): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($driver['name']); ?></td>
                                    <td><?php echo htmlspecialchars($driver['license_number'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($driver['phone'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($driver['phone'] ?? ''); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($driver['status']) {
                                                'Available' => 'success',
                                                'On Duty' => 'info',
                                                'Off Duty' => 'warning',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo $driver['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
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

<!-- Add Vehicle Modal -->
<div class="modal fade" id="vehicleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Vehicle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_vehicle">
                    <div class="mb-3">
                        <label class="form-label">Vehicle Number *</label>
                        <input type="text" name="vehicle_number" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type *</label>
                        <select name="type" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="Truck">Truck</option>
                            <option value="Van">Van</option>
                            <option value="Pickup">Pickup</option>
                            <option value="Trailer">Trailer</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Capacity (kg) *</label>
                        <input type="number" name="capacity" class="form-control" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="Available">Available</option>
                            <option value="In Transit">In Transit</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Out of Service">Out of Service</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Last Maintenance Date</label>
                        <input type="date" name="last_maintenance" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Vehicle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Driver Modal -->
<div class="modal fade" id="driverModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Driver</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_driver">
                    <div class="mb-3">
                        <label class="form-label">Driver Name *</label>
                        <input type="text" name="driver_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">License Number *</label>
                        <input type="text" name="license_number" class="form-control" placeholder="e.g. DL-001-2024" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number *</label>
                        <input type="tel" name="driver_phone" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="driver_status" class="form-select">
                            <option value="Available">Available</option>
                            <option value="On Duty">On Duty</option>
                            <option value="Off Duty">Off Duty</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Driver</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
