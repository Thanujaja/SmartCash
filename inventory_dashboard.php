<?php
/**
 * inventory_dashboard.php
 *
 * This file serves as the main dashboard for the Inventory Manager.
 * It provides an overview and navigation to inventory management features.
 *
 * For your HND Final Project at ATI Dehiwala SLIATE.
 */

require_once __DIR__ . '/db_connect.php'; // Ensure db_connect.php is correctly linked
session_start();

// --- Security Check: Ensure only logged-in inventory manager can access ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'inventory_manager') {
    // Redirect to common user login if not logged in or not an inventory manager
    header("Location: common_user_login.php");
    exit();
}

$username = htmlspecialchars($_SESSION['username']);

// Fetch some quick stats for the dashboard (optional, but good for a dashboard)
$total_products = 0;
$low_stock_products = 0;

try {
    $stmt_total = $pdo->query("SELECT COUNT(product_id) AS total FROM products");
    $total_products = $stmt_total->fetchColumn();

    $stmt_low_stock = $pdo->query("SELECT COUNT(product_id) AS low_stock FROM products WHERE current_stock <= min_stock_level");
    $low_stock_products = $stmt_low_stock->fetchColumn();

} catch (PDOException $e) {
    error_log("Error fetching inventory stats: " . $e->getMessage());
    // Display a user-friendly error message or handle gracefully
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCash - Inventory Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dashboard-main-container {
            background: linear-gradient(135deg, #0A2240 0%, #007bff 100%);
            color: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
            text-align: center;
            margin: 20px auto;
            flex-grow: 1;
        }

        .dashboard-main-container h1 {
            color: #fff;
            margin-bottom: 20px;
            font-size: 2.2em;
        }

        .dashboard-main-container p {
            font-size: 1.1em;
            color: #fff;
            margin-bottom: 30px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background-color: var(--background-grey);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .stat-card h3 {
            color: #111;
            margin-bottom: 10px;
            font-size: 1.5em;
        }

        .stat-card p {
            font-size: 2em;
            font-weight: bold;
            color: #111;
            margin: 0;
        }
        .stat-card.low-stock {
            background-color: #ffcccc;
            border: 1px solid #a00;
        }
        .stat-card.low-stock h3, .stat-card.low-stock p {
            color: #111 !important;
        }
    </style>
</head>
<body style="background: linear-gradient(135deg, #e0e0e0 0%, #f7f7f7 100%); min-height: 100vh;">

    <div class="dashboard-main-container">
        <h1>Welcome, Inventory Manager <?php echo $username; ?>!</h1>
        <p>This is your inventory management dashboard. Here you can oversee stock levels, manage products, and more.</p>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Products</h3>
                <p><?php echo $total_products; ?></p>
            </div>
            <div class="stat-card <?php echo ($low_stock_products > 0 ? 'low-stock' : ''); ?>">
                <h3>Low Stock Alerts</h3>
                <p><?php echo $low_stock_products; ?></p>
            </div>
            </div>

        <div style="margin-top: 30px;">
            <a href="inventory_products.php" class="form-button">Manage Products</a>
            <a href="inventory_suppliers.php" class="form-button" style="background-color: #17a2b8;">Manage Suppliers</a>
            <a href="logout.php" class="form-button" style="background-color: #6c757d;">Logout</a>
        </div>
    </div>

    <div class="footer">
        &copy; <?php echo date("Y"); ?> SmartCash
    </div>

</body>
</html>