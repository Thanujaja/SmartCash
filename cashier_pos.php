<?php
/**
 * cashier_pos.php
 *
 * This file implements the Point of Sale (POS) interface for cashiers.
 * It allows adding products to a cart, calculating totals, and completing sales.
 *
 * For your HND Final Project at ATI Dehiwala SLIATE, Sri Lanka.
 */

require_once __DIR__ . '/db_connect.php';
session_start();

// --- Security Check: Ensure only logged-in cashier can access ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'cashier') {
    header("Location: common_user_login.php");
    exit();
}

$message = '';

// Initialize cart in session if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = []; // cart will store product_id => ['product_details' => [...], 'quantity' => X]
}

// Handle adding product to cart
if (isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    $product_identifier = trim($_POST['product_identifier'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 1);

    if (empty($product_identifier) || $quantity <= 0) {
        $message = '<div class="error-message">Please enter a valid Product ID/Name and quantity.</div>';
    } else {
        try {
            // Try to find product by ID or Name
            $stmt = $pdo->prepare("SELECT product_id, product_name, selling_price, discount, current_stock FROM products WHERE product_id = :id OR product_name LIKE :name");
            $stmt->bindValue(':id', $product_identifier, PDO::PARAM_INT);
            $stmt->bindValue(':name', '%' . $product_identifier . '%', PDO::PARAM_STR);
            $stmt->execute();
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                $product_id = $product['product_id'];
                $available_stock = $product['current_stock'];

                // Check if product is already in cart, update quantity
                $current_cart_qty = $_SESSION['cart'][$product_id]['quantity'] ?? 0;
                $new_total_qty = $current_cart_qty + $quantity;

                if ($new_total_qty > $available_stock) {
                    $message = '<div class="error-message">Not enough stock for ' . htmlspecialchars($product['product_name']) . '. Available: ' . $available_stock . '. Current in cart: ' . $current_cart_qty . '.</div>';
                } else {
                    $_SESSION['cart'][$product_id] = [
                        'product_id' => $product['product_id'],
                        'product_name' => $product['product_name'],
                        'selling_price' => $product['selling_price'],
                        'discount' => $product['discount'],
                        'quantity' => $new_total_qty
                    ];
                    $message = '<div class="success-message">' . htmlspecialchars($quantity) . ' x ' . htmlspecialchars($product['product_name']) . ' added to cart.</div>';
                }
            } else {
                $message = '<div class="error-message">Product not found.</div>';
            }
        } catch (PDOException $e) {
            error_log("POS add to cart error: " . $e->getMessage());
            $message = '<div class="error-message">A system error occurred. Please try again.</div>';
        }
    }
}

// Handle updating quantity in cart (for individual items already added)
if (isset($_POST['action']) && $_POST['action'] === 'update_cart_quantity') {
    $product_id_to_update = (int)($_POST['product_id_to_update'] ?? 0);
    $new_quantity = (int)($_POST['new_quantity'] ?? 0);

    if ($product_id_to_update > 0 && isset($_SESSION['cart'][$product_id_to_update])) {
        if ($new_quantity <= 0) {
            unset($_SESSION['cart'][$product_id_to_update]); // Remove if quantity is zero or less
            $message = '<div class="success-message">Product removed from cart.</div>';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT current_stock FROM products WHERE product_id = :product_id");
                $stmt->execute(['product_id' => $product_id_to_update]);
                $product_stock_info = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($product_stock_info && $new_quantity <= $product_stock_info['current_stock']) {
                    $_SESSION['cart'][$product_id_to_update]['quantity'] = $new_quantity;
                    $message = '<div class="success-message">Quantity updated successfully.</div>';
                } elseif ($product_stock_info) {
                    $message = '<div class="error-message">Not enough stock. Available: ' . $product_stock_info['current_stock'] . '. Cannot set quantity to ' . $new_quantity . '.</div>';
                } else {
                     $message = '<div class="error-message">Product stock information not found.</div>';
                }
            } catch (PDOException $e) {
                error_log("POS update quantity error: " . $e->getMessage());
                $message = '<div class="error-message">A system error occurred while updating quantity.</div>';
            }
        }
    } else {
        $message = '<div class="error-message">Invalid product or cart item to update.</div>';
    }
}


// Handle removing product from cart
if (isset($_POST['action']) && $_POST['action'] === 'remove_from_cart') {
    $product_id_to_remove = (int)($_POST['product_id_to_remove'] ?? 0);
    if ($product_id_to_remove > 0 && isset($_SESSION['cart'][$product_id_to_remove])) {
        unset($_SESSION['cart'][$product_id_to_remove]);
        $message = '<div class="success-message">Product removed from cart.</div>';
    } else {
        $message = '<div class="error-message">Product not found in cart.</div>';
    }
}

// Handle clearing the entire cart
if (isset($_POST['action']) && $_POST['action'] === 'clear_cart') {
    $_SESSION['cart'] = [];
    $message = '<div class="success-message">Cart cleared successfully.</div>';
}

// Calculate cart total (with discount)
$cart_total = 0;
foreach ($_SESSION['cart'] as $item) {
    $item_total = ($item['selling_price'] - $item['discount']) * $item['quantity'];
    $cart_total += $item_total;
}


// Handle completing the sale
if (isset($_POST['action']) && $_POST['action'] === 'complete_sale') {
    $payment_method = trim($_POST['payment_method'] ?? 'Cash');
    $amount_paid = isset($_POST['amount_paid']) ? floatval($_POST['amount_paid']) : 0;
    $change_given = 0;

    // Recalculate cart total (with discount) in case session/cart changed
    $cart_total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $item_total = ($item['selling_price'] - $item['discount']) * $item['quantity'];
        $cart_total += $item_total;
    }

    if (empty($_SESSION['cart'])) {
        $message = '<div class="error-message">Cannot complete sale: Cart is empty.</div>';
    } elseif ($amount_paid < $cart_total && $payment_method === 'Cash') {
         $message = '<div class="error-message">Amount paid is less than total. Please collect LKR ' . number_format($cart_total - $amount_paid, 2) . ' more.</div>';
    }
    else {
        try {
            $pdo->beginTransaction();

            $stmt_transaction = $pdo->prepare("INSERT INTO transactions (user_id, total_amount, payment_method, amount_paid, change_given)
                                               VALUES (:user_id, :total_amount, :payment_method, :amount_paid, :change_given)");

            if ($payment_method === 'Cash') {
                $change_given = max(0, $amount_paid - $cart_total);
            } else {
                $amount_paid = $cart_total;
                $change_given = 0;
            }

            $stmt_transaction->execute([
                'user_id' => $_SESSION['user_id'],
                'total_amount' => $cart_total,
                'payment_method' => $payment_method,
                'amount_paid' => $amount_paid,
                'change_given' => $change_given
            ]);
            $transaction_id = $pdo->lastInsertId();

            // Save each cart item to transaction_items
            foreach ($_SESSION['cart'] as $item) {
                $stmt_item = $pdo->prepare("INSERT INTO transaction_items (transaction_id, product_id, quantity, selling_price_at_sale)
                                           VALUES (:transaction_id, :product_id, :quantity, :selling_price_at_sale)");
                $stmt_item->execute([
                    'transaction_id' => $transaction_id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'selling_price_at_sale' => $item['selling_price']
                ]);
                $stmt_update_stock = $pdo->prepare("UPDATE products SET current_stock = current_stock - :quantity WHERE product_id = :product_id");
                $stmt_update_stock->execute([
                    'quantity' => $item['quantity'],
                    'product_id' => $item['product_id']
                ]);
            }

            $pdo->commit();
            // Open bill page in a new tab using JavaScript BEFORE clearing the cart
            echo "<script>
                var billWindow = window.open('bill_popup.php?transaction_id=$transaction_id', '_blank');
                if (!billWindow) {
                    setTimeout(function() {
                        document.write('<div style=\"text-align:center;margin-top:40px;font-size:1.3em;color:#0A2240;\">Bill could not be opened automatically. <br><a href=\"bill.php?transaction_id=$transaction_id\" target=\"_blank\" style=\"color:#007bff;font-weight:bold;\">Click here to view the bill</a></div>');
                    }, 5000);
                } else {
                    setTimeout(function() {
                        window.location.href = \"cashier_pos.php\";
                    }, 500);
                }
            </script>";
            $_SESSION['cart'] = [];
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("POS complete sale error: " . $e->getMessage());
            $message = '<div class="error-message">A system error occurred while completing the sale. Please try again.</div>';
        }
    }
}

// Check for and display flash messages (e.g., from successful sale completion)
if (isset($_SESSION['flash_message_pos'])) {
    $message = $_SESSION['flash_message_pos'];
    unset($_SESSION['flash_message_pos']); // Clear the message after displaying it
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCash - Point of Sale</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .pos-container {
            display: flex;
            flex-direction: column;
            background: linear-gradient(135deg, #0A2240 0%, #007bff 100%);
            color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 1000px;
            margin: 20px auto;
            min-height: 70vh; /* Make sure it takes up some height */
        }

        .pos-header {
            text-align: center;
            color: #fff;
            margin-bottom: 20px;
        }

        .pos-main-content {
            display: flex;
            flex-grow: 1; /* Allow main content to take available space */
            gap: 20px;
            flex-wrap: wrap; /* Allow wrapping on smaller screens */
        }

        .product-input-section, .cart-section {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.06);
            color: #111;
        }

        .product-input-section {
            flex: 1; /* Takes equal width as cart section */
            min-width: 300px; /* Minimum width before wrapping */
        }

        .cart-section {
            flex: 2; /* Takes more width for cart items */
            min-width: 400px; /* Minimum width before wrapping */
        }

        .cart-items {
            max-height: 350px; /* Fixed height for scrollable cart */
            overflow-y: auto;
            border: 1px solid #eee;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-details {
            flex-grow: 1;
            text-align: left;
        }
        .cart-item-details strong {
            display: block;
            font-size: 1.1em;
            color: #111;
        }
        .cart-item-details span {
            font-size: 0.9em;
            color: #555;
        }

        .cart-item-qty input {
            width: 50px;
            padding: 5px;
            text-align: center;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .cart-item-actions {
            margin-left: 10px;
        }
        .cart-item-actions button {
            background: none;
            border: none;
            color: var(--error-red);
            cursor: pointer;
            font-size: 1.1em;
            padding: 5px;
        }
        .cart-item-actions button:hover {
            color: #a00;
        }

        .cart-summary {
            margin-top: 15px;
            border-top: 2px solid var(--primary-blue);
            padding-top: 15px;
            text-align: right;
        }
        .cart-summary h3 {
            font-size: 1.6em;
            color: #111;
        }
        .cart-summary p {
            font-size: 1.2em;
            font-weight: bold;
            color: #111;
        }

        .payment-section {
            margin-top: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            color: #111;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.06);
            text-align: left;
        }
        .payment-section .form-group {
            margin-bottom: 15px;
        }
        .payment-section label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .payment-section input[type="number"],
        .payment-section select {
            width: calc(100% - 24px);
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box;
        }

        .pos-buttons {
            margin-top: 20px;
            text-align: center;
        }
        .pos-buttons .form-button {
            margin: 0 10px;
            padding: 12px 25px;
            font-size: 1.1em;
        }
    </style>
</head>
<body>

    <div class="pos-container">
        <h1 class="pos-header">SmartCash - Point of Sale</h1>
        <?php echo $message; ?>

        <div class="pos-main-content">
            <div class="product-input-section">
                <h2>Add Product to Cart</h2>
                <form action="cashier_pos.php" method="POST">
                    <input type="hidden" name="action" value="add_to_cart">
                    <div class="form-group">
                        <label for="product_identifier">Product ID or Name:</label>
                        <input type="text" id="product_identifier" name="product_identifier" placeholder="Enter ID or Name" required autofocus>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" required>
                    </div>
                    <button type="submit" class="form-button" style="width: 100%;">Add to Cart</button>
                </form>
            </div>

            <div class="cart-section">
                <h2>Current Sale</h2>
                <div class="cart-items">
                    <?php if (empty($_SESSION['cart'])): ?>
                        <p style="text-align: center; padding: 20px; color: #777;">Cart is empty.</p>
                    <?php else: ?>
                        <?php foreach ($_SESSION['cart'] as $productId => $item): ?>
                            <div class="cart-item">
                                <div class="cart-item-details">
                                    <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                    <span>LKR <?php echo number_format($item['selling_price'], 2); ?> each</span>
                                </div>
                                <div class="cart-item-qty">
                                    <form action="cashier_pos.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="update_cart_quantity">
                                        <input type="hidden" name="product_id_to_update" value="<?php echo htmlspecialchars($productId); ?>">
                                        <input type="number" name="new_quantity" value="<?php echo htmlspecialchars($item['quantity']); ?>" min="1" onchange="this.form.submit()">
                                    </form>
                                </div>
                                <div class="cart-item-actions">
                                    <form action="cashier_pos.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="remove_from_cart">
                                        <input type="hidden" name="product_id_to_remove" value="<?php echo htmlspecialchars($productId); ?>">
                                        <button type="submit"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="cart-summary">
                    <h3>Total: LKR <?php echo number_format($cart_total, 2); ?></h3>
                </div>

                <div class="pos-buttons">
                    <form action="cashier_pos.php" method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="clear_cart">
                        <button type="submit" class="form-button" style="background-color: #f0ad4e;">Clear Cart</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="payment-section">
            <h2>Payment</h2>
            <form action="cashier_pos.php" method="POST" style="max-width:350px;margin:auto;">
                <input type="hidden" name="action" value="complete_sale">
                <div class="form-group">
                    <label for="payment_method">Payment Method:</label>
                    <select id="payment_method" name="payment_method" required>
                        <option value="Cash">Cash</option>
                        <option value="Card">Card</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="amount_paid">Amount Paid (LKR):</label>
                    <input type="number" step="0.01" id="amount_paid" name="amount_paid" value="<?php echo number_format($cart_total, 2, '.', ''); ?>" min="0" required>
                </div>
                <button type="submit" class="form-button" style="width:auto; min-width:120px; max-width:220px; border-radius:24px; font-size:1.08em;">
                    &#128179; Pay
                </button>
            </form>
        </div>

        <div class="pos-buttons" style="margin-top: 30px;">
            <a href="cashier_dashboard.php" class="form-button" style="background-color: #6c757d;">Back to Dashboard</a>
        </div>
    </div>

    <div class="footer">
        &copy; <?php echo date("Y"); ?> SmartCash
    </div>

    <script src="js/scripts.js"></script>
</body>
</html>