<?php
// modules/transport.php
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../config/api.php';

// Check if API key is configured
if (GEOAPIFY_API_KEY === 'your_geoapify_api_key_here') {
    echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>Location API Not Configured:</strong> Please set your Geoapify API key in <code>config/api.php</code> for full location functionality.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';
}

$database = new Database();
$db = $database->getConnection();

// Handle shipment creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'create_shipment') {
        $shipment_number = 'SHP-' . date('Ymd') . '-' . rand(1000, 9999);
        $query = "INSERT INTO shipments (shipment_number, order_id, vehicle_id, driver_id, route_from, route_to, departure_date, estimated_arrival, status) 
                  VALUES (:shipment_number, :order_id, :vehicle_id, :driver_id, :route_from, :route_to, :departure_date, :estimated_arrival, :status)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':shipment_number' => $shipment_number,
            ':order_id' => $_POST['order_id'],
            ':vehicle_id' => $_POST['vehicle_id'],
            ':driver_id' => $_POST['driver_id'],
            ':route_from' => $_POST['route_from'],
            ':route_to' => $_POST['route_to'],
            ':departure_date' => $_POST['departure_date'],
            ':estimated_arrival' => $_POST['estimated_arrival'],
            ':status' => 'Preparing'
        ]);
    }
}

// Get all shipments
$query = "SELECT s.*, o.order_number, v.vehicle_number, d.name as driver_name 
          FROM shipments s 
          LEFT JOIN orders o ON s.order_id = o.id 
          LEFT JOIN vehicles v ON s.vehicle_id = v.id 
          LEFT JOIN drivers d ON s.driver_id = d.id 
          ORDER BY s.id DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get orders, vehicles, and drivers for dropdowns
$orders = $db->query("SELECT id, order_number FROM orders WHERE status != 'Delivered' ORDER BY order_number")->fetchAll(PDO::FETCH_ASSOC);
$vehicles = $db->query("SELECT id, vehicle_number FROM vehicles WHERE status = 'Available' ORDER BY vehicle_number")->fetchAll(PDO::FETCH_ASSOC);
$drivers = $db->query("SELECT id, name FROM drivers WHERE status = 'Available' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid mt-5">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-route me-2"></i>Transportation & Shipment Management</h5>
                </div>
                <div class="card-body">
                    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#shipmentModal">
                        <i class="fas fa-plus me-2"></i>Create New Shipment
                    </button>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Shipment #</th>
                                    <th>Order</th>
                                    <th>Vehicle</th>
                                    <th>Driver</th>
                                    <th>Route</th>
                                    <th>Status</th>
                                    <th>Estimated Arrival</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($shipments as $shipment): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($shipment['shipment_number']); ?></td>
                                    <td><?php echo htmlspecialchars($shipment['order_number'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($shipment['vehicle_number'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($shipment['driver_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($shipment['route_from'] ?? '') . ' → ' . htmlspecialchars($shipment['route_to'] ?? ''); ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo $shipment['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $shipment['estimated_arrival']; ?></td>
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

<!-- Create Shipment Modal -->
<div class="modal fade" id="shipmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Shipment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_shipment">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Order *</label>
                            <select name="order_id" class="form-select" required>
                                <option value="">Select Order</option>
                                <?php foreach ($orders as $order): ?>
                                <option value="<?php echo $order['id']; ?>"><?php echo htmlspecialchars($order['order_number']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Vehicle *</label>
                            <select name="vehicle_id" class="form-select" required>
                                <option value="">Select Vehicle</option>
                                <?php foreach ($vehicles as $vehicle): ?>
                                <option value="<?php echo $vehicle['id']; ?>"><?php echo htmlspecialchars($vehicle['vehicle_number']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Driver *</label>
                            <select name="driver_id" class="form-select" required>
                                <option value="">Select Driver</option>
                                <?php foreach ($drivers as $driver): ?>
                                <option value="<?php echo $driver['id']; ?>"><?php echo htmlspecialchars($driver['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Departure Date *</label>
                            <input type="datetime-local" name="departure_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">From Location *</label>
                            <div class="input-group">
                                <input type="text" name="route_from" id="route_from" class="form-control location-input" placeholder="Click map button or enter location" required>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#locationPickerFromModal">
                                    <i class="fas fa-map-marker-alt"></i> Map
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">To Location *</label>
                            <div class="input-group">
                                <input type="text" name="route_to" id="route_to" class="form-control location-input" placeholder="Click map button or enter location" required>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#locationPickerToModal">
                                    <i class="fas fa-map-marker-alt"></i> Map
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Estimated Arrival *</label>
                        <input type="datetime-local" name="estimated_arrival" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Shipment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Location Picker Modal - From Location -->
<div class="modal fade" id="locationPickerFromModal" tabindex="-1" style="z-index: 2000;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select From Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" id="fromLocationSearch" class="form-control" placeholder="Search location or click on map...">
                    <div id="fromSearchLoading" class="mt-1" style="display: none; color: #666; font-size: 12px;">Searching...</div>
                </div>
                <div id="mapFromLocation" style="height: 400px; border-radius: 5px; border: 1px solid #ddd;"></div>
                <div class="mt-2" id="fromLocationDisplay" style="padding: 10px; background: #f9f9f9; border-radius: 5px; display: none;">
                    <strong>Selected Location:</strong> <span id="fromLocationText"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmFromLocation" data-bs-dismiss="modal">Confirm Location</button>
            </div>
        </div>
    </div>
</div>

<!-- Location Picker Modal - To Location -->
<div class="modal fade" id="locationPickerToModal" tabindex="-1" style="z-index: 2000;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select To Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" id="toLocationSearch" class="form-control" placeholder="Search location or click on map...">
                    <div id="toSearchLoading" class="mt-1" style="display: none; color: #666; font-size: 12px;">Searching...</div>
                </div>
                <div id="mapToLocation" style="height: 400px; border-radius: 5px; border: 1px solid #ddd;"></div>
                <div class="mt-2" id="toLocationDisplay" style="padding: 10px; background: #f9f9f9; border-radius: 5px; display: none;">
                    <strong>Selected Location:</strong> <span id="toLocationText"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmToLocation" data-bs-dismiss="modal">Confirm Location</button>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet CSS and JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

<style>
    .leaflet-container {
        border-radius: 5px;
    }
    .location-input {
        font-size: 14px;
    }
</style>

<script>
    let fromMap = null;
    let toMap = null;
    let fromMarker = null;
    let toMarker = null;
    let selectedFromLocation = '';
    let selectedToLocation = '';

    // Initialize From Location Map
    document.getElementById('locationPickerFromModal').addEventListener('show.bs.modal', function () {
        setTimeout(() => {
            if (!fromMap) {
                fromMap = L.map('mapFromLocation').setView([20, 0], 2);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 19
                }).addTo(fromMap);

                fromMap.on('click', function(e) {
                    selectFromLocation(e.latlng);
                });
            }
            fromMap.invalidateSize();
        }, 200);
    });

    // Initialize To Location Map
    document.getElementById('locationPickerToModal').addEventListener('show.bs.modal', function () {
        setTimeout(() => {
            if (!toMap) {
                toMap = L.map('mapToLocation').setView([20, 0], 2);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 19
                }).addTo(toMap);

                toMap.on('click', function(e) {
                    selectToLocation(e.latlng);
                });
            }
            toMap.invalidateSize();
        }, 200);
    });

    function selectFromLocation(latlng) {
        if (fromMarker) fromMap.removeLayer(fromMarker);
        
        fromMarker = L.marker(latlng).addTo(fromMap);
        selectedFromLocation = latlng.lat.toFixed(6) + ', ' + latlng.lng.toFixed(6);
        
        // Get location name from coordinates using Geoapify reverse geocoding
        fetch(`https://api.geoapify.com/v1/geocode/reverse?lat=${latlng.lat}&lon=${latlng.lng}&apiKey=<?php echo GEOAPIFY_API_KEY; ?>`)
            .then(response => response.json())
            .then(data => {
                if (data.features && data.features.length > 0) {
                    const properties = data.features[0].properties;
                    selectedFromLocation = properties.formatted || properties.city || properties.town || properties.village || selectedFromLocation;
                }
                document.getElementById('fromLocationText').textContent = selectedFromLocation;
                document.getElementById('fromLocationDisplay').style.display = 'block';
            })
            .catch(error => {
                console.error('Reverse geocoding error:', error);
                document.getElementById('fromLocationText').textContent = selectedFromLocation;
                document.getElementById('fromLocationDisplay').style.display = 'block';
            });
    }

    function selectToLocation(latlng) {
        if (toMarker) toMap.removeLayer(toMarker);
        
        toMarker = L.marker(latlng).addTo(toMap);
        selectedToLocation = latlng.lat.toFixed(6) + ', ' + latlng.lng.toFixed(6);
        
        // Get location name from coordinates using Geoapify reverse geocoding
        fetch(`https://api.geoapify.com/v1/geocode/reverse?lat=${latlng.lat}&lon=${latlng.lng}&apiKey=<?php echo GEOAPIFY_API_KEY; ?>`)
            .then(response => response.json())
            .then(data => {
                if (data.features && data.features.length > 0) {
                    const properties = data.features[0].properties;
                    selectedToLocation = properties.formatted || properties.city || properties.town || properties.village || selectedToLocation;
                }
                document.getElementById('toLocationText').textContent = selectedToLocation;
                document.getElementById('toLocationDisplay').style.display = 'block';
            })
            .catch(error => {
                console.error('Reverse geocoding error:', error);
                document.getElementById('toLocationText').textContent = selectedToLocation;
                document.getElementById('toLocationDisplay').style.display = 'block';
            });
    }

    // Search From Location
    document.getElementById('fromLocationSearch').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const query = this.value.trim();
            if (!query) return;
            
            const loadingEl = document.getElementById('fromSearchLoading');
            loadingEl.style.display = 'block';
            loadingEl.textContent = 'Searching...';
            
            fetch(`https://api.geoapify.com/v1/geocode/search?text=${encodeURIComponent(query)}&apiKey=<?php echo GEOAPIFY_API_KEY; ?>`)
                .then(response => response.json())
                .then(data => {
                    loadingEl.style.display = 'none';
                    if (data.features && data.features.length > 0) {
                        const result = data.features[0];
                        const coords = result.geometry.coordinates;
                        fromMap.setView([coords[1], coords[0]], 15);
                        selectFromLocation(L.latLng(coords[1], coords[0]));
                    } else {
                        loadingEl.textContent = 'No results found';
                        loadingEl.style.display = 'block';
                        setTimeout(() => loadingEl.style.display = 'none', 3000);
                    }
                })
                .catch(error => {
                    console.error('Geocoding error:', error);
                    loadingEl.textContent = 'Search failed. Please try again.';
                    loadingEl.style.display = 'block';
                    setTimeout(() => loadingEl.style.display = 'none', 3000);
                });
        }
    });

    // Search To Location
    document.getElementById('toLocationSearch').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const query = this.value.trim();
            if (!query) return;
            
            const loadingEl = document.getElementById('toSearchLoading');
            loadingEl.style.display = 'block';
            loadingEl.textContent = 'Searching...';
            
            fetch(`https://api.geoapify.com/v1/geocode/search?text=${encodeURIComponent(query)}&apiKey=<?php echo GEOAPIFY_API_KEY; ?>`)
                .then(response => response.json())
                .then(data => {
                    loadingEl.style.display = 'none';
                    if (data.features && data.features.length > 0) {
                        const result = data.features[0];
                        const coords = result.geometry.coordinates;
                        toMap.setView([coords[1], coords[0]], 15);
                        selectToLocation(L.latLng(coords[1], coords[0]));
                    } else {
                        loadingEl.textContent = 'No results found';
                        loadingEl.style.display = 'block';
                        setTimeout(() => loadingEl.style.display = 'none', 3000);
                    }
                })
                .catch(error => {
                    console.error('Geocoding error:', error);
                    loadingEl.textContent = 'Search failed. Please try again.';
                    loadingEl.style.display = 'block';
                    setTimeout(() => loadingEl.style.display = 'none', 3000);
                });
        }
    });

    // Confirm From Location
    document.getElementById('confirmFromLocation').addEventListener('click', function() {
        if (selectedFromLocation) {
            document.getElementById('route_from').value = selectedFromLocation;
        }
    });

    // Confirm To Location
    document.getElementById('confirmToLocation').addEventListener('click', function() {
        if (selectedToLocation) {
            document.getElementById('route_to').value = selectedToLocation;
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>
