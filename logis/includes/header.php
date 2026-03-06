<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$full_name = $_SESSION['full_name'];

// Role-based color scheme
$roleColors = [
    'Admin' => 'primary',
    'Procurement Officer' => 'success',
    'Transport Coordinator' => 'info',
    'Fleet Manager' => 'warning',
    'Tracking Officer' => 'purple'
];

$roleColor = $roleColors[$role] ?? 'secondary';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logistics Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/logis/css/style.css">
    <link rel="stylesheet" href="/logis/css/loader.css">
</head>
<body>
    <div class="loader-container" id="loader">
        <div class="bouncing-dots">
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>

    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-truck-fast me-2"></i>LogiTrack</h3>
            </div>

            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="user-details">
                    <span class="user-name"><?php echo htmlspecialchars($full_name); ?></span>
                    <span class="user-role badge bg-<?php echo $roleColor; ?>"><?php echo $role; ?></span>
                </div>
            </div>

            <ul class="nav-links">
                <li class="nav-item">
                    <a href="/logis/project/dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-pie"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <?php if (in_array($role, ['Admin', 'Procurement Officer'])): ?>
                <li class="nav-item procurement-section">
                    <a href="/logis/modules/orders.php" class="nav-link">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Order Management</span>
                    </a>
                    <a href="/logis/modules/suppliers.php" class="nav-link">
                        <i class="fas fa-truck"></i>
                        <span>Supplier & Vendor</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (in_array($role, ['Admin', 'Transport Coordinator'])): ?>
                <li class="nav-item transport-section">
                    <a href="/logis/modules/transport.php" class="nav-link">
                        <i class="fas fa-route"></i>
                        <span>Transportation</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (in_array($role, ['Admin', 'Fleet Manager'])): ?>
                <li class="nav-item fleet-section">
                    <a href="/logis/modules/fleet.php" class="nav-link">
                        <i class="fas fa-truck-pickup"></i>
                        <span>Fleet Management</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (in_array($role, ['Admin', 'Tracking Officer'])): ?>
                <li class="nav-item tracking-section">
                    <a href="/logis/modules/tracking.php" class="nav-link">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Shipment Tracking</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="nav-item mt-auto">
                    <a href="/logis/project/logout.php" class="nav-link text-danger">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Sticky Header -->
            <header class="sticky-header">
                <div class="header-left">
                    <button type="button" id="sidebarToggle" class="btn btn-link">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h4 class="page-title">
                        <?php
                        $page = basename($_SERVER['PHP_SELF'], '.php');
                        echo ucwords(str_replace('_', ' ', $page));
                        ?>
                    </h4>
                </div>
                <div class="header-right">
                    <div class="dropdown">
                        <button class="btn btn-link dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">3</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">New order received</a></li>
                            <li><a class="dropdown-item" href="#">Shipment delayed</a></li>
                            <li><a class="dropdown-item" href="#">Vehicle maintenance due</a></li>
                        </ul>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-link dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-cog"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">Profile</a></li>
                            <li><a class="dropdown-item" href="#">Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="content-area">