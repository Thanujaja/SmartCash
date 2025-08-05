<?php
/**
 * inventory_products.php
 *
 * This file displays a list of all products in the SmartCash inventory.
 * It allows the Inventory Manager to view product details and serves as
 * the base for adding, editing, and deleting products.
 *
 * For your HND Final Project at ATI Dehiwala SLIATE, SLIATE Sri Lanka.
 * Current location: Colombo, Western Province, Sri Lanka.
 */

require_once __DIR__ . '/db_connect.php'; // Ensure db_connect.php is correctly linked
session_start();

// --- Security Check: Ensure only logged-in inventory manager can access ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'inventory_manager') {
    header("Location: common_user_login.php");
    exit();
}

$message = '';

// Check for and display flash messages from previous operations (add, edit, delete)
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']); // Clear the message after displaying it
}

$products = []; // Initialize products array

try {
    $stmt = $pdo->query("SELECT product_id, product_name, category, buying_price, selling_price, discount, current_stock, min_stock_level, supplier FROM products ORDER BY product_name ASC");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching products for inventory_products.php: " . $e->getMessage());
    $message .= '<div class="error-message">Error loading products. Please try again later.</div>';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCash - Product List</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Specific styles for this page, extending dashboard-container */
        .product-list-container {
            background-color: var(--light-color);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 1000px; /* Wider for product table */
            margin: 20px auto;
            flex-grow: 1;
            text-align: center; /* Center content within container */
        }

        .product-list-container h1 {
            color: var(--primary-blue);
            margin-bottom: 25px;
            font-size: 2.2em;
        }

        .product-actions {
            margin-bottom: 25px;
            text-align: left; /* Align buttons to the left */
        }

        .product-table-wrapper {
            overflow-x: auto; /* Enable horizontal scrolling for narrow screens */
        }

        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .product-table th,
        .product-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            white-space: nowrap; /* Prevent wrapping in cells */
        }

        .product-table th {
            background-color: var(--primary-blue);
            color: var(--light-color);
            font-weight: bold;
        }

        .product-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .product-table .actions {
            text-align: center;
        }

        .product-table .actions a.edit-btn,
        .product-table .actions button.delete-btn {
            display: inline-block;
            padding: 5px 10px;
            margin: 2px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9em;
            transition: background-color 0.3s ease;
        }

        .product-table .actions a.edit-btn {
            background-color: #007bff; /* Blue for edit */
            color: white;
        }
        .product-table .actions a.edit-btn:hover {
            background-color: #0056b3;
        }

        .product-table .actions button.delete-btn {
            background-color: #dc3545; /* Red for delete */
            color: white;
        }
        .product-table .actions button.delete-btn:hover {
            background-color: #c82333;
        }

        .stock-low {
            color: var(--error-red);
            font-weight: bold;
        }
        .success-message, .error-message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            font-weight: bold;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>

    <div class="product-list-container">
        <h1>Product Inventory</h1>
        <?php echo $message; // This will now display flash messages too ?>

        <div class="product-actions">
            <a href="inventory_add_product.php" class="form-button">Add New Product</a>
            <a href="inventory_dashboard.php" class="form-button" style="background-color: #6c757d;">Back to Dashboard</a>
        </div>

        <?php if (empty($products)): ?>
            <p>No products found in the inventory. Add some products to get started!</p>
        <?php else: ?>
            <div class="product-table-wrapper">
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Buying Price (LKR)</th>
                            <th>Selling Price (LKR)</th>
                            <th>Discount (LKR)</th>
                            <th>Current Stock</th>
                            <th>Min Stock Level</th>
                            <th>Supplier</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['product_id'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($product['product_name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($product['category'] ?? ''); ?></td>
                                <td><?php echo number_format($product['buying_price'], 2); ?></td>
                                <td><?php echo number_format($product['selling_price'], 2); ?></td>
                                <td><?php echo number_format($product['discount'], 2); ?></td>
                                <td class="<?php echo ($product['current_stock'] <= $product['min_stock_level'] ? 'stock-low' : ''); ?>">
                                    <?php echo htmlspecialchars($product['current_stock'] ?? ''); ?>
                                </td>
                                <td><?php echo htmlspecialchars($product['min_stock_level'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($product['supplier'] ?? ''); ?></td>
                                <td class="actions">
                                    <a href="inventory_edit_product.php?id=<?php echo htmlspecialchars($product['product_id'] ?? ''); ?>" class="edit-btn">Edit</a>
                                    <form action="inventory_delete_product.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete <?php echo htmlspecialchars($product['product_name'] ?? ''); ?>? This action cannot be undone.');">
                                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['product_id'] ?? ''); ?>">
                                        <button type="submit" class="delete-btn">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <p>
            <a href="inventory_dashboard.php" class="back-link">Back to Inventory Dashboard</a>
        </p>
    </div>

    <div class="footer">
        &copy; <?php echo date("Y"); ?> SmartCash
    </div>

</body>
</html>