-- database.sql
-- Logistics Management System Database

-- Create database
CREATE DATABASE IF NOT EXISTS logistics_db;
USE logistics_db;

-- =====================================================
-- Table: users
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE,
    role ENUM('Admin', 'Procurement Officer', 'Transport Coordinator', 'Fleet Manager', 'Tracking Officer') NOT NULL,
    full_name VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    profile_image VARCHAR(255),
    last_login DATETIME,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: orders
-- =====================================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100),
    customer_phone VARCHAR(20),
    shipping_address TEXT,
    order_date DATE NOT NULL,
    delivery_date DATE,
    status ENUM('Pending', 'Approved', 'Processing', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Pending',
    total_amount DECIMAL(10,2) NOT NULL,
    shipping_cost DECIMAL(10,2) DEFAULT 0.00,
    tax_amount DECIMAL(10,2) DEFAULT 0.00,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    payment_status ENUM('Pending', 'Paid', 'Failed', 'Refunded') DEFAULT 'Pending',
    payment_method VARCHAR(50),
    notes TEXT,
    created_by INT,
    approved_by INT,
    approved_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order_number (order_number),
    INDEX idx_status (status),
    INDEX idx_order_date (order_date),
    INDEX idx_customer (customer_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: order_items
-- =====================================================
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    product_sku VARCHAR(50),
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: suppliers
-- =====================================================
CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    alternate_phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(50),
    postal_code VARCHAR(20),
    country VARCHAR(50) DEFAULT 'USA',
    website VARCHAR(255),
    tax_id VARCHAR(50),
    payment_terms VARCHAR(100),
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    rating DECIMAL(2,1) DEFAULT 0.0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_supplier_code (supplier_code),
    INDEX idx_status (status),
    INDEX idx_city (city)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: purchase_orders
-- =====================================================
CREATE TABLE IF NOT EXISTS purchase_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_number VARCHAR(50) UNIQUE NOT NULL,
    supplier_id INT NOT NULL,
    order_date DATE NOT NULL,
    expected_delivery DATE,
    actual_delivery DATE,
    status ENUM('Draft', 'Sent', 'Received', 'Cancelled', 'Completed') DEFAULT 'Draft',
    subtotal DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) DEFAULT 0.00,
    shipping_cost DECIMAL(10,2) DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_terms VARCHAR(100),
    notes TEXT,
    created_by INT,
    approved_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_po_number (po_number),
    INDEX idx_status (status),
    INDEX idx_order_date (order_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: po_items
-- =====================================================
CREATE TABLE IF NOT EXISTS po_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    product_sku VARCHAR(50),
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    received_quantity INT DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (po_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    INDEX idx_po_id (po_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: vehicles
-- =====================================================
CREATE TABLE IF NOT EXISTS vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_number VARCHAR(50) UNIQUE NOT NULL,
    type VARCHAR(50) NOT NULL,
    model VARCHAR(100),
    make VARCHAR(50),
    year INT,
    license_plate VARCHAR(20) UNIQUE,
    capacity DECIMAL(10,2) NOT NULL,
    capacity_unit VARCHAR(20) DEFAULT 'kg',
    fuel_type ENUM('Diesel', 'Petrol', 'Electric', 'Hybrid') DEFAULT 'Diesel',
    status ENUM('Available', 'In Transit', 'Maintenance', 'Out of Service') DEFAULT 'Available',
    current_location TEXT,
    last_maintenance DATE,
    next_maintenance DATE,
    insurance_expiry DATE,
    registration_expiry DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_vehicle_number (vehicle_number),
    INDEX idx_status (status),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: drivers
-- =====================================================
CREATE TABLE IF NOT EXISTS drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    license_number VARCHAR(50) UNIQUE NOT NULL,
    license_expiry DATE,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    address TEXT,
    emergency_contact VARCHAR(100),
    emergency_phone VARCHAR(20),
    hire_date DATE,
    status ENUM('Available', 'On Duty', 'Off Duty', 'On Leave', 'Terminated') DEFAULT 'Available',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_driver_code (driver_code),
    INDEX idx_status (status),
    INDEX idx_license (license_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: shipments
-- =====================================================
CREATE TABLE IF NOT EXISTS shipments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shipment_number VARCHAR(50) UNIQUE NOT NULL,
    order_id INT,
    purchase_order_id INT,
    vehicle_id INT,
    driver_id INT,
    route_from VARCHAR(200) NOT NULL,
    route_to VARCHAR(200) NOT NULL,
    distance_km DECIMAL(10,2),
    estimated_duration_hours DECIMAL(5,2),
    departure_date DATETIME,
    estimated_arrival DATETIME,
    actual_arrival DATETIME,
    status ENUM('Preparing', 'Loaded', 'In Transit', 'Delivered', 'Delayed', 'Cancelled') DEFAULT 'Preparing',
    current_location TEXT,
    last_location_update DATETIME,
    fuel_consumption DECIMAL(10,2),
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE SET NULL,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL,
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_shipment_number (shipment_number),
    INDEX idx_status (status),
    INDEX idx_departure (departure_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: tracking_updates
-- =====================================================
CREATE TABLE IF NOT EXISTS tracking_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shipment_id INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    location VARCHAR(200),
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    description TEXT,
    updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_shipment_id (shipment_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: maintenance_records
-- =====================================================
CREATE TABLE IF NOT EXISTS maintenance_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT NOT NULL,
    maintenance_date DATE NOT NULL,
    maintenance_type VARCHAR(100),
    description TEXT,
    cost DECIMAL(10,2),
    odometer_reading INT,
    performed_by VARCHAR(100),
    next_maintenance_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_maintenance_date (maintenance_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: fuel_logs
-- =====================================================
CREATE TABLE IF NOT EXISTS fuel_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT NOT NULL,
    driver_id INT,
    fuel_date DATETIME NOT NULL,
    fuel_amount DECIMAL(10,2) NOT NULL,
    fuel_cost DECIMAL(10,2) NOT NULL,
    odometer_reading INT,
    location VARCHAR(200),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE SET NULL,
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_fuel_date (fuel_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: activity_logs
-- =====================================================
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    module VARCHAR(50),
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_module (module),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: notifications
-- =====================================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT,
    type ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info',
    link VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    read_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Insert Sample Data
-- =====================================================

-- Insert users with hashed passwords (password is 'password123' hashed)
INSERT INTO users (username, password, email, role, full_name, phone, address, status) VALUES
('admin', '$2y$10$YourHashedPasswordHere', 'admin@logistics.com', 'Admin', 'System Administrator', '+1-555-0001', '123 Admin St, New York, NY 10001', 'Active'),
('john_procurement', '$2y$10$YourHashedPasswordHere', 'john@logistics.com', 'Procurement Officer', 'John Smith', '+1-555-0002', '456 Procurement Ave, Los Angeles, CA 90001', 'Active'),
('sarah_transport', '$2y$10$YourHashedPasswordHere', 'sarah@logistics.com', 'Transport Coordinator', 'Sarah Johnson', '+1-555-0003', '789 Transport Rd, Chicago, IL 60601', 'Active'),
('mike_fleet', '$2y$10$YourHashedPasswordHere', 'mike@logistics.com', 'Fleet Manager', 'Mike Wilson', '+1-555-0004', '321 Fleet St, Houston, TX 77001', 'Active'),
('lisa_tracking', '$2y$10$YourHashedPasswordHere', 'lisa@logistics.com', 'Tracking Officer', 'Lisa Brown', '+1-555-0005', '654 Tracking Ln, Phoenix, AZ 85001', 'Active');

-- Insert suppliers
INSERT INTO suppliers (supplier_code, name, contact_person, email, phone, address, city, state, status) VALUES
('SUP001', 'ABC Supplies Inc.', 'Robert Green', 'robert@abcsupplies.com', '+1-555-1001', '123 Supply St', 'New York', 'NY', 'Active'),
('SUP002', 'XYZ Logistics Solutions', 'Emily White', 'emily@xyzlogistics.com', '+1-555-1002', '456 Logistics Ave', 'Los Angeles', 'CA', 'Active'),
('SUP003', 'Global Traders Ltd', 'David Black', 'david@globaltraders.com', '+1-555-1003', '789 Trade Rd', 'Chicago', 'IL', 'Active'),
('SUP004', 'Quality Parts Co', 'Lisa Gray', 'lisa@qualityparts.com', '+1-555-1004', '321 Quality Blvd', 'Houston', 'TX', 'Active'),
('SUP005', 'Fast Delivery Supplies', 'Tom Brown', 'tom@fastdelivery.com', '+1-555-1005', '654 Fast Ln', 'Phoenix', 'AZ', 'Active');

-- Insert vehicles
INSERT INTO vehicles (vehicle_number, type, model, make, year, license_plate, capacity, fuel_type, status, last_maintenance) VALUES
('VH001', 'Truck', 'F-650', 'Ford', 2022, 'ABC-1234', 10000.00, 'Diesel', 'Available', '2024-01-15'),
('VH002', 'Truck', 'T680', 'Kenworth', 2023, 'XYZ-5678', 12000.00, 'Diesel', 'Available', '2024-02-20'),
('VH003', 'Van', 'Transit', 'Ford', 2023, 'DEF-9012', 3500.00, 'Diesel', 'In Transit', '2024-01-10'),
('VH004', 'Van', 'Promaster', 'Ram', 2022, 'GHI-3456', 3800.00, 'Diesel', 'Available', '2024-03-01'),
('VH005', 'Truck', 'T270', 'Isuzu', 2023, 'JKL-7890', 8000.00, 'Diesel', 'Maintenance', '2024-02-28');

-- Insert drivers
INSERT INTO drivers (driver_code, name, license_number, license_expiry, phone, email, status) VALUES
('DRV001', 'Mike Johnson', 'DL123456', '2025-12-31', '+1-555-2001', 'mike.j@logistics.com', 'Available'),
('DRV002', 'Sarah Williams', 'DL789012', '2025-10-15', '+1-555-2002', 'sarah.w@logistics.com', 'On Duty'),
('DRV003', 'Tom Davis', 'DL345678', '2025-08-20', '+1-555-2003', 'tom.d@logistics.com', 'Available'),
('DRV004', 'Lisa Anderson', 'DL901234', '2025-11-30', '+1-555-2004', 'lisa.a@logistics.com', 'On Duty'),
('DRV005', 'James Wilson', 'DL567890', '2025-09-25', '+1-555-2005', 'james.w@logistics.com', 'Available');

-- Insert orders
INSERT INTO orders (order_number, customer_name, customer_email, customer_phone, shipping_address, order_date, status, total_amount, payment_status, created_by) VALUES
('ORD-2024-001', 'Tech Solutions Inc', 'orders@techsolutions.com', '+1-555-3001', '123 Tech Blvd, San Francisco, CA 94105', '2024-01-15', 'Delivered', 12500.00, 'Paid', 1),
('ORD-2024-002', 'City Restaurant', 'info@cityrestaurant.com', '+1-555-3002', '456 Main St, Chicago, IL 60601', '2024-01-16', 'Shipped', 3450.00, 'Paid', 2),
('ORD-2024-003', 'Medical Supplies Co', 'purchasing@medicalsupplies.com', '+1-555-3003', '789 Health Ave, Boston, MA 02108', '2024-01-17', 'Processing', 8900.00, 'Pending', 2),
('ORD-2024-004', 'Retail Store 123', 'store123@retail.com', '+1-555-3004', '321 Retail Ln, Miami, FL 33101', '2024-01-18', 'Approved', 5600.00, 'Paid', 1),
('ORD-2024-005', 'Construction Co', 'supply@construction.com', '+1-555-3005', '654 Build St, Denver, CO 80201', '2024-01-19', 'Pending', 15750.00, 'Pending', 2);

-- Insert order items
INSERT INTO order_items (order_id, product_name, product_sku, quantity, unit_price, total_price) VALUES
(1, 'Laptop Computers', 'TECH-LAP-001', 10, 1200.00, 12000.00),
(1, 'Wireless Mice', 'TECH-MOU-001', 20, 25.00, 500.00),
(2, 'Commercial Refrigerator', 'KIT-REF-001', 1, 3200.00, 3200.00),
(2, 'Kitchen Utensils Set', 'KIT-UTN-001', 5, 50.00, 250.00),
(3, 'Medical Masks Box', 'MED-MSK-001', 100, 80.00, 8000.00),
(3, 'Thermometers', 'MED-THM-001', 30, 30.00, 900.00),
(4, 'Clothing Racks', 'RET-RCK-001', 10, 350.00, 3500.00),
(4, 'Display Shelves', 'RET-SHL-001', 15, 140.00, 2100.00),
(5, 'Power Tools Set', 'CON-PWT-001', 5, 2500.00, 12500.00),
(5, 'Safety Equipment', 'CON-SAF-001', 25, 130.00, 3250.00);

-- Insert purchase orders
INSERT INTO purchase_orders (po_number, supplier_id, order_date, expected_delivery, status, subtotal, total_amount, created_by) VALUES
('PO-2024-001', 1, '2024-01-10', '2024-01-20', 'Received', 8500.00, 8925.00, 2),
('PO-2024-002', 2, '2024-01-12', '2024-01-22', 'Sent', 12300.00, 12915.00, 2),
('PO-2024-003', 3, '2024-01-15', '2024-01-25', 'Received', 5600.00, 5880.00, 2),
('PO-2024-004', 4, '2024-01-18', '2024-01-28', 'Draft', 4200.00, 4410.00, 2),
('PO-2024-005', 5, '2024-01-20', '2024-01-30', 'Sent', 9800.00, 10290.00, 2);

-- Insert shipments
INSERT INTO shipments (shipment_number, order_id, vehicle_id, driver_id, route_from, route_to, distance_km, departure_date, estimated_arrival, status) VALUES
('SHP-2024-001', 1, 1, 1, 'New York, NY', 'San Francisco, CA', 4500, '2024-01-16 08:00:00', '2024-01-19 14:00:00', 'Delivered'),
('SHP-2024-002', 2, 3, 2, 'Chicago, IL', 'Detroit, MI', 450, '2024-01-17 09:30:00', '2024-01-17 16:30:00', 'In Transit'),
('SHP-2024-003', 3, 2, 4, 'Boston, MA', 'New York, NY', 350, '2024-01-18 10:00:00', '2024-01-18 15:00:00', 'Loaded'),
('SHP-2024-004', 4, 4, 3, 'Miami, FL', 'Orlando, FL', 380, '2024-01-19 11:00:00', '2024-01-19 17:00:00', 'Preparing'),
('SHP-2024-005', 5, NULL, NULL, 'Denver, CO', 'Salt Lake City, UT', 800, NULL, NULL, 'Preparing');

-- Insert tracking updates
INSERT INTO tracking_updates (shipment_id, status, location, description) VALUES
(1, 'Picked Up', 'New York, NY', 'Shipment picked up from warehouse'),
(1, 'In Transit', 'Cleveland, OH', 'En route to destination'),
(1, 'In Transit', 'St. Louis, MO', 'Making good progress'),
(1, 'Delivered', 'San Francisco, CA', 'Successfully delivered to customer'),
(2, 'Picked Up', 'Chicago, IL', 'Shipment picked up'),
(2, 'In Transit', 'Gary, IN', 'Crossing state line');

-- Insert maintenance records
INSERT INTO maintenance_records (vehicle_id, maintenance_date, maintenance_type, description, cost, performed_by, next_maintenance_date) VALUES
(1, '2024-01-15', 'Regular Service', 'Oil change, tire rotation, brake inspection', 450.00, 'Fleet Maintenance Co', '2024-04-15'),
(2, '2024-02-20', 'Regular Service', 'Oil change, filter replacement', 380.00, 'Quick Service Center', '2024-05-20'),
(3, '2024-01-10', 'Emergency Repair', 'Engine check, belt replacement', 1200.00, 'Truck Repair Specialists', '2024-04-10'),
(5, '2024-02-28', 'Regular Service', 'Full inspection, brake pads replacement', 850.00, 'Fleet Maintenance Co', '2024-05-28');

-- Insert notifications
INSERT INTO notifications (user_id, title, message, type, link) VALUES
(1, 'New Order Received', 'A new order has been placed and requires approval', 'info', 'modules/orders.php'),
(1, 'Shipment Delayed', 'Shipment SHP-2024-002 is experiencing delays', 'warning', 'modules/tracking.php'),
(2, 'Purchase Order Approved', 'Your PO-2024-003 has been approved', 'success', 'modules/purchase_orders.php'),
(3, 'Vehicle Maintenance Due', 'Vehicle VH005 requires maintenance soon', 'danger', 'modules/fleet.php'),
(4, 'Driver Available', 'Driver James Wilson is now available for assignment', 'info', 'modules/fleet.php');

-- Insert activity logs
INSERT INTO activity_logs (user_id, action, module, description, ip_address) VALUES
(1, 'Login', 'Auth', 'User logged in successfully', '192.168.1.100'),
(1, 'Create', 'Orders', 'Created new order ORD-2024-005', '192.168.1.100'),
(2, 'Update', 'Purchase Orders', 'Updated PO status to Received', '192.168.1.101'),
(3, 'View', 'Shipments', 'Viewed shipment details', '192.168.1.102'),
(4, 'Assign', 'Fleet', 'Assigned driver to vehicle', '192.168.1.103');

-- =====================================================
-- Create Views
-- =====================================================

-- View: Active shipments with details
CREATE OR REPLACE VIEW view_active_shipments AS
SELECT 
    s.shipment_number,
    s.route_from,
    s.route_to,
    s.departure_date,
    s.estimated_arrival,
    s.status,
    v.vehicle_number,
    d.name AS driver_name,
    o.order_number,
    o.customer_name
FROM shipments s
LEFT JOIN vehicles v ON s.vehicle_id = v.id
LEFT JOIN drivers d ON s.driver_id = d.id
LEFT JOIN orders o ON s.order_id = o.id
WHERE s.status IN ('Loaded', 'In Transit');

-- View: Order summary
CREATE OR REPLACE VIEW view_order_summary AS
SELECT 
    DATE(order_date) as order_day,
    COUNT(*) as total_orders,
    SUM(total_amount) as total_revenue,
    SUM(CASE WHEN status = 'Delivered' THEN 1 ELSE 0 END) as delivered_orders,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_orders
FROM orders
GROUP BY DATE(order_date)
ORDER BY order_day DESC;

-- View: Vehicle utilization
CREATE OR REPLACE VIEW view_vehicle_utilization AS
SELECT 
    v.vehicle_number,
    v.type,
    v.status,
    COUNT(s.id) as total_shipments,
    SUM(s.distance_km) as total_distance,
    AVG(s.distance_km) as avg_distance
FROM vehicles v
LEFT JOIN shipments s ON v.id = s.vehicle_id AND s.status = 'Delivered'
GROUP BY v.id;

-- =====================================================
-- Create Stored Procedures
-- =====================================================

DELIMITER $$

-- Procedure: Get dashboard statistics
CREATE PROCEDURE sp_get_dashboard_stats()
BEGIN
    -- Total orders today
    SELECT COUNT(*) as today_orders 
    FROM orders 
    WHERE DATE(order_date) = CURDATE();
    
    -- Active shipments
    SELECT COUNT(*) as active_shipments 
    FROM shipments 
    WHERE status IN ('Loaded', 'In Transit');
    
    -- Available vehicles
    SELECT COUNT(*) as available_vehicles 
    FROM vehicles 
    WHERE status = 'Available';
    
    -- Pending approvals
    SELECT COUNT(*) as pending_approvals 
    FROM orders 
    WHERE status = 'Pending';
END$$

-- Procedure: Update shipment status with tracking
CREATE PROCEDURE sp_update_shipment_status(
    IN p_shipment_id INT,
    IN p_status VARCHAR(50),
    IN p_location VARCHAR(200),
    IN p_description TEXT,
    IN p_user_id INT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;
    
    -- Update shipment
    UPDATE shipments 
    SET status = p_status,
        current_location = p_location,
        last_location_update = NOW()
    WHERE id = p_shipment_id;
    
    -- Add tracking update
    INSERT INTO tracking_updates (shipment_id, status, location, description, updated_by)
    VALUES (p_shipment_id, p_status, p_location, p_description, p_user_id);
    
    -- If delivered, update actual arrival
    IF p_status = 'Delivered' THEN
        UPDATE shipments 
        SET actual_arrival = NOW() 
        WHERE id = p_shipment_id;
    END IF;
    
    COMMIT;
END$$

-- Procedure: Generate monthly report
CREATE PROCEDURE sp_generate_monthly_report(
    IN p_year INT,
    IN p_month INT
)
BEGIN
    -- Orders summary
    SELECT 
        'ORDERS' as section,
        COUNT(*) as total,
        SUM(total_amount) as revenue
    FROM orders 
    WHERE YEAR(order_date) = p_year AND MONTH(order_date) = p_month
    
    UNION ALL
    
    -- Shipments summary
    SELECT 
        'SHIPMENTS',
        COUNT(*),
        SUM(distance_km)
    FROM shipments 
    WHERE YEAR(departure_date) = p_year AND MONTH(departure_date) = p_month
    
    UNION ALL
    
    -- Fuel consumption
    SELECT 
        'FUEL',
        COUNT(*),
        SUM(fuel_cost)
    FROM fuel_logs 
    WHERE YEAR(fuel_date) = p_year AND MONTH(fuel_date) = p_month;
END$$

DELIMITER ;

-- =====================================================
-- Create Triggers
-- =====================================================

-- Trigger: Update order total when items change
DELIMITER $$
CREATE TRIGGER trg_update_order_total
AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
    UPDATE orders 
    SET total_amount = (
        SELECT SUM(total_price) 
        FROM order_items 
        WHERE order_id = NEW.order_id
    )
    WHERE id = NEW.order_id;
END$$

-- Trigger: Log order status changes
CREATE TRIGGER trg_log_order_status
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO activity_logs (user_id, action, module, description)
        VALUES (NEW.approved_by, 'Status Change', 'Orders', 
                CONCAT('Order ', NEW.order_number, ' status changed from ', 
                       OLD.status, ' to ', NEW.status));
    END IF;
END$$

-- Trigger: Check vehicle availability before assignment
CREATE TRIGGER trg_check_vehicle_before_assignment
BEFORE UPDATE ON shipments
FOR EACH ROW
BEGIN
    IF NEW.vehicle_id IS NOT NULL AND NEW.vehicle_id != OLD.vehicle_id THEN
        IF NOT EXISTS (
            SELECT 1 FROM vehicles 
            WHERE id = NEW.vehicle_id AND status = 'Available'
        ) THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Vehicle is not available for assignment';
        END IF;
    END IF;
END$$

DELIMITER ;

-- =====================================================
-- Create Indexes for Performance
-- =====================================================

-- Additional indexes for better query performance
CREATE INDEX idx_orders_customer_email ON orders(customer_email);
CREATE INDEX idx_orders_payment_status ON orders(payment_status);
CREATE INDEX idx_shipments_dates ON shipments(departure_date, estimated_arrival);
CREATE INDEX idx_tracking_updates_shipment_created ON tracking_updates(shipment_id, created_at);
CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read);
CREATE INDEX idx_activity_logs_user_date ON activity_logs(user_id, created_at);

-- =====================================================
-- Create User Roles and Permissions (Optional)
-- =====================================================

-- Create application users (if using MySQL users)
-- Note: These are MySQL database users, not application users
CREATE USER IF NOT EXISTS 'logistics_app'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT SELECT, INSERT, UPDATE, DELETE ON logistics_db.* TO 'logistics_app'@'localhost';

CREATE USER IF NOT EXISTS 'logistics_report'@'localhost' IDENTIFIED BY 'report_password_here';
GRANT SELECT ON logistics_db.* TO 'logistics_report'@'localhost';

FLUSH PRIVILEGES;

-- =====================================================
-- Database Status Information
-- =====================================================

-- Show table status
SELECT 
    table_name,
    table_rows,
    data_length / 1024 / 1024 as data_mb,
    index_length / 1024 / 1024 as index_mb,
    create_time,
    update_time
FROM information_schema.tables 
WHERE table_schema = 'logistics_db'
ORDER BY table_name;

-- Show database size
SELECT 
    table_schema as database_name,
    SUM(data_length + index_length) / 1024 / 1024 as size_mb
FROM information_schema.tables 
WHERE table_schema = 'logistics_db'
GROUP BY table_schema;

-- =====================================================
-- Notes for Production Deployment
-- =====================================================
/*
Production Deployment Checklist:
1. Change default passwords
2. Update database credentials in config files
3. Enable SSL for database connections
4. Set up regular backups
5. Configure database replication if needed
6. Set up monitoring and alerts
7. Optimize MySQL configuration
8. Enable query logging for debugging
9. Set up connection pooling
10. Configure proper firewall rules
*/

-- End of database.sql