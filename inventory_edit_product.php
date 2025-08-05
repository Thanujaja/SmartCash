<?php
/**
 * inventory_edit_product.php
 *
 * This file allows the Inventory Manager to edit existing product details.
 * It requires a 'product_id' in the URL query string.
 *
 * For your HND Final Project at ATI Dehiwala SLIATE.
 */

require_once __DIR__ . '/db_connect.php';
session_start();

// --- Security Check: Ensure only logged-in inventory manager can access ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'inventory_manager') {
    header("Location: common_user_login.php");
    exit();
}

$message = '';
$product_id = $_GET['id'] ?? null;
$product_data = null;

// Fetch existing product data if ID is provided
if ($product_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = :product_id");
        $stmt->execute(['product_id' => $product_id]);
        $product_data = $stmt->fetch();

        if (!$product_data) {
            $message = '<div class="error-message">Product not found.</div>';
            $product_id = null; // Invalidate ID if product not found
        }
    } catch (PDOException $e) {
        error_log("Error fetching product for edit: " . $e->getMessage());
        $message = '<div class="error-message">A database error occurred while loading product details.</div>';
        $product_id = null;
    }
} else {
    $message = '<div class="error-message">No product ID provided for editing.</div>';
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $product_id) {
    // Collect and trim updated input data
    $product_name = trim($_POST['product_name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $buying_price = trim($_POST['buying_price'] ?? '');
    $selling_price = trim($_POST['selling_price'] ?? '');
    $discount = trim($_POST['discount'] ?? '');
    $current_stock = trim($_POST['current_stock'] ?? '');
    $min_stock_level = trim($_POST['min_stock_level'] ?? '');
    $supplier = trim($_POST['supplier'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Basic validation
    $errors = [];
    if (empty($product_name)) { $errors[] = "Product Name is required."; }
    if (empty($buying_price) || !is_numeric($buying_price) || $buying_price < 0) { $errors[] = "Valid Buying Price is required."; }
    if (empty($selling_price) || !is_numeric($selling_price) || $selling_price < 0) { $errors[] = "Valid Selling Price is required."; }
    if (!is_numeric($discount) || $discount < 0) { $errors[] = "Valid Discount is required."; }
    if (!is_numeric($current_stock) || $current_stock < 0) { $errors[] = "Valid Current Stock is required."; }
    if (!is_numeric($min_stock_level) || $min_stock_level < 0) { $errors[] = "Valid Minimum Stock Level is required."; }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE products SET
                                   product_name = :product_name,
                                   category = :category,
                                   buying_price = :buying_price,
                                   selling_price = :selling_price,
                                   discount = :discount,
                                   current_stock = :current_stock,
                                   min_stock_level = :min_stock_level,
                                   supplier = :supplier,
                                   description = :description
                                   WHERE product_id = :product_id");

            $stmt->execute([
                'product_name' => $product_name,
                'category' => !empty($category) ? $category : null,
                'buying_price' => $buying_price,
                'selling_price' => $selling_price,
                'discount' => $discount,
                'current_stock' => $current_stock,
                'min_stock_level' => $min_stock_level,
                'supplier' => !empty($supplier) ? $supplier : null,
                'description' => !empty($description) ? $description : null,
                'product_id' => $product_id
            ]);

            // Re-fetch updated data to show on the form if successful
            $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = :product_id");
            $stmt->execute(['product_id' => $product_id]);
            $product_data = $stmt->fetch();

            $_SESSION['flash_message'] = '<div class="success-message">Product "' . htmlspecialchars($product_name) . '" updated successfully!</div>';
            header("Location: inventory_products.php"); // Redirect to product list
            exit();

        } catch (PDOException $e) {
            // Check for duplicate entry error if product name changed to existing one
            if ($e->getCode() == '23000') {
                $message = '<div class="error-message">Error: A product with this name already exists.</div>';
            } else {
                error_log("Error updating product: " . $e->getMessage());
                $message = '<div class="error-message">A database error occurred while updating the product. Please try again.</div>';
            }
        }
    } else {
        $message = '<div class="error-message">' . implode('<br>', $errors) . '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCash - Edit Product</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .form-container { /* Reusing styles from add_product for consistency */
            background-color: var(--light-color);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            flex-grow: 1;
            text-align: left;
        }
        .form-container h1 {
            color: var(--primary-blue);
            margin-bottom: 25px;
            text-align: center;
        }
        .form-group label {
            margin-bottom: 5px;
        }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea {
            width: calc(100% - 24px);
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box;
        }
        .form-group textarea {
            min-height: 80px;
            resize: vertical;
        }
        .form-buttons {
            text-align: center;
            margin-top: 30px;
        }
        .form-buttons .form-button {
            margin: 0 10px;
        }
    </style>
</head>
<body>

    <div class="form-container">
        <h1>Edit Product</h1>
        <?php echo $message; ?>

        <?php if ($product_data): ?>
        <form action="inventory_edit_product.php?id=<?php echo htmlspecialchars($product_id); ?>" method="POST">
            <div class="form-group">
                <label for="product_name">Product Name: <span style="color:red;">*</span></label>
                <input type="text" id="product_name" name="product_name" value="<?php echo htmlspecialchars($product_data['product_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="category">Category:</label>
                <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($product_data['category']); ?>">
            </div>
            <div class="form-group">
                <label for="buying_price">Buying Price (LKR): <span style="color:red;">*</span></label>
                <input type="number" step="0.01" id="buying_price" name="buying_price" value="<?php echo htmlspecialchars($product_data['buying_price']); ?>" required>
            </div>
            <div class="form-group">
                <label for="selling_price">Selling Price (LKR): <span style="color:red;">*</span></label>
                <input type="number" step="0.01" id="selling_price" name="selling_price" value="<?php echo htmlspecialchars($product_data['selling_price']); ?>" required>
            </div>
            <div class="form-group">
                <label for="discount">Discount per Item (LKR):</label>
                <input type="number" step="0.01" id="discount" name="discount" value="<?php echo htmlspecialchars($product_data['discount'] ?? ''); ?>" min="0">
            </div>
            <div class="form-group">
                <label for="current_stock">Current Stock: <span style="color:red;">*</span></label>
                <input type="number" id="current_stock" name="current_stock" value="<?php echo htmlspecialchars($product_data['current_stock'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="min_stock_level">Minimum Stock Level: <span style="color:red;">*</span></label>
                <input type="number" id="min_stock_level" name="min_stock_level" value="<?php echo htmlspecialchars($product_data['min_stock_level'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="supplier">Supplier:</label>
                <?php
                $suppliers = $pdo->query("SELECT name FROM suppliers ORDER BY name ASC")->fetchAll();
                ?>
                <select id="supplier" name="supplier">
                    <option value="">-- Select Supplier --</option>
                    <?php foreach ($suppliers as $sup): ?>
                        <option value="<?php echo htmlspecialchars($sup['name']); ?>" <?php echo (isset($product_data['supplier']) && $product_data['supplier'] === $sup['name']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($sup['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description"><?php echo htmlspecialchars($product_data['description'] ?? ''); ?></textarea>
            </div>
            <div class="form-buttons">
                <button type="submit" class="form-button">Update Product</button>
                <a href="inventory_products.php" class="form-button" style="background-color: #6c757d; text-decoration: none;">Cancel</a>
            </div>
        </form>
        <?php endif; ?>
    </div>

    <div class="footer">
        &copy; <?php echo date("Y"); ?> SmartCash
    </div>

</body>
</html>