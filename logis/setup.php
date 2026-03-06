<?php
// setup.php
require_once 'config/database.php';

// Initialize database
initializeDatabase();

$database = new Database();
$db = $database->getConnection();

// Insert sample suppliers
$suppliers = [
    ['ABC Supplies', 'John Doe', 'john@abcsupplies.com', '123-456-7890', '123 Main St, City', 'Active'],
    ['XYZ Logistics', 'Jane Smith', 'jane@xyzlogistics.com', '098-765-4321', '456 Oak Ave, Town', 'Active'],
    ['Global Traders', 'Bob Johnson', 'bob@globaltraders.com', '555-123-4567', '789 Pine Rd, Village', 'Active']
];

$supplierStmt = $db->prepare("INSERT INTO suppliers (name, contact_person, email, phone, address, status) 
                              VALUES (?, ?, ?, ?, ?, ?)");

foreach ($suppliers as $supplier) {
    $supplierStmt->execute($supplier);
}

// Insert sample vehicles
$vehicles = [
    ['TRK-001', 'Truck', '10000', 'Available', '2024-01-15'],
    ['TRK-002', 'Truck', '8000', 'Available', '2024-02-20'],
    ['VAN-001', 'Van', '3000', 'In Transit', '2024-01-10'],
    ['VAN-002', 'Van', '3500', 'Available', '2024-03-01']
];

$vehicleStmt = $db->prepare("INSERT INTO vehicles (vehicle_number, type, capacity, status, last_maintenance) 
                            VALUES (?, ?, ?, ?, ?)");

foreach ($vehicles as $vehicle) {
    $vehicleStmt->execute($vehicle);
}

// Insert sample drivers
$drivers = [
    ['Mike Wilson', 'DL-001-2024', '111-222-3333', 'Available'],
    ['Sarah Brown', 'DL-002-2024', '444-555-6666', 'On Duty'],
    ['Tom Davis', 'DL-003-2024', '777-888-9999', 'Available']
];

$driverStmt = $db->prepare("INSERT INTO drivers (name, license_number, phone, status) VALUES (?, ?, ?, ?)");

foreach ($drivers as $driver) {
    $driverStmt->execute($driver);
}

// Insert sample orders
$orders = [
    ['ORD-20240101-001', 'ABC Corp', '2024-01-15', 'Approved', 1500.00, 1],
    ['ORD-20240102-002', 'XYZ Ltd', '2024-01-16', 'Processing', 2300.00, 1],
    ['ORD-20240103-003', 'Global Inc', '2024-01-17', 'Shipped', 3200.00, 1],
    ['ORD-20240104-004', 'Local Store', '2024-01-18', 'Delivered', 850.00, 1],
    ['ORD-20240105-005', 'Online Shop', '2024-01-19', 'Pending', 1750.00, 1]
];

$orderStmt = $db->prepare("INSERT INTO orders (order_number, customer_name, order_date, status, total_amount, created_by) 
                          VALUES (?, ?, ?, ?, ?, ?)");

foreach ($orders as $order) {
    $orderStmt->execute($order);
}

echo "Database setup completed successfully!";
echo "<br><br>";
echo "<a href='login.php'>Go to Login Page</a>";
?>