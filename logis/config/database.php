<?php
// config/database.php
class Database {
    private $host = "localhost";
    private $db_name = "logistics_db";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            // First connect without database to create it if needed
            $tempConn = new PDO(
                "mysql:host=" . $this->host,
                $this->username,
                $this->password
            );
            $tempConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $tempConn->exec("CREATE DATABASE IF NOT EXISTS " . $this->db_name);
            $tempConn = null;

            // Now connect to the database
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Connection error: " . $e->getMessage();
        }
        return $this->conn;
    }
}

// Create tables if they don't exist
function initializeDatabase() {
    $database = new Database();
    $db = $database->getConnection();
    
    // Disable foreign key checks to allow dropping tables
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Drop tables if they exist to ensure correct schema
    $db->exec("DROP TABLE IF EXISTS shipments");
    $db->exec("DROP TABLE IF EXISTS purchase_orders");
    $db->exec("DROP TABLE IF EXISTS orders");
    $db->exec("DROP TABLE IF EXISTS drivers");
    $db->exec("DROP TABLE IF EXISTS vehicles");
    $db->exec("DROP TABLE IF EXISTS suppliers");
    $db->exec("DROP TABLE IF EXISTS users");
    
    // Re-enable foreign key checks
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // Users table
    $db->exec("CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('Admin', 'Procurement Officer', 'Transport Coordinator', 'Fleet Manager', 'Tracking Officer') NOT NULL,
        full_name VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Orders table
    $db->exec("CREATE TABLE orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_number VARCHAR(50) UNIQUE NOT NULL,
        customer_name VARCHAR(100),
        order_date DATE,
        status ENUM('Pending', 'Approved', 'Processing', 'Shipped', 'Delivered') DEFAULT 'Pending',
        total_amount DECIMAL(10,2),
        items TEXT,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id)
    )");
    
    // Suppliers table
    $db->exec("CREATE TABLE suppliers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        contact_person VARCHAR(100),
        email VARCHAR(100),
        phone VARCHAR(20),
        address TEXT,
        status ENUM('Active', 'Inactive') DEFAULT 'Active'
    )");
    
    // Purchase Orders table
    $db->exec("CREATE TABLE purchase_orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        po_number VARCHAR(50) UNIQUE NOT NULL,
        supplier_id INT,
        order_date DATE,
        expected_delivery DATE,
        status ENUM('Draft', 'Sent', 'Received', 'Cancelled') DEFAULT 'Draft',
        total_amount DECIMAL(10,2),
        created_by INT,
        FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
        FOREIGN KEY (created_by) REFERENCES users(id)
    )");
    
    // Vehicles table
    $db->exec("CREATE TABLE vehicles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        vehicle_number VARCHAR(50) UNIQUE NOT NULL,
        type VARCHAR(50),
        capacity DECIMAL(10,2),
        status ENUM('Available', 'In Transit', 'Maintenance', 'Out of Service') DEFAULT 'Available',
        last_maintenance DATE
    )");
    
    // Drivers table
    $db->exec("CREATE TABLE drivers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        license_number VARCHAR(50) UNIQUE,
        phone VARCHAR(20),
        status ENUM('Available', 'On Duty', 'Off Duty') DEFAULT 'Available'
    )");
    
    // Shipments table
    $db->exec("CREATE TABLE shipments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        shipment_number VARCHAR(50) UNIQUE NOT NULL,
        order_id INT,
        vehicle_id INT,
        driver_id INT,
        route_from VARCHAR(200),
        route_to VARCHAR(200),
        departure_date DATETIME,
        estimated_arrival DATETIME,
        actual_arrival DATETIME,
        status ENUM('Preparing', 'Loaded', 'In Transit', 'Delivered', 'Delayed') DEFAULT 'Preparing',
        tracking_updates TEXT,
        FOREIGN KEY (order_id) REFERENCES orders(id),
        FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
        FOREIGN KEY (driver_id) REFERENCES drivers(id)
    )");
    
    // Insert default admin user
    $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $db->exec("INSERT INTO users (username, password, role, full_name) 
               VALUES ('admin', '$hashedPassword', 'Admin', 'System Admin')");
}
?>