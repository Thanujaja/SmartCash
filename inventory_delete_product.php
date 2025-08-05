<?php
/**
 * inventory_delete_product.php
 *
 * This file handles the deletion of a product from the inventory.
 * It's a backend script and redirects after processing.
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'] ?? null;

    if ($product_id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = :product_id");
            $stmt->execute(['product_id' => $product_id]);

            if ($stmt->rowCount()) {
                $_SESSION['flash_message'] = '<div class="success-message">Product deleted successfully!</div>';
            } else {
                $_SESSION['flash_message'] = '<div class="error-message">Product not found or could not be deleted.</div>';
            }
        } catch (PDOException $e) {
            error_log("Error deleting product: " . $e->getMessage());
            $_SESSION['flash_message'] = '<div class="error-message">A database error occurred while deleting the product.</div>';
        }
    } else {
        $_SESSION['flash_message'] = '<div class="error-message">No product ID provided for deletion.</div>';
    }
} else {
    $_SESSION['flash_message'] = '<div class="error-message">Invalid request method.</div>';
}

header("Location: inventory_products.php"); // Redirect back to the product list
exit();
?>