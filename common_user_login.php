<?php
/**
 * common_user_login.php
 *
 * This file handles the login for Cashiers and Inventory Managers of the SmartCash application.
 * It includes the database connection and processes login form submissions.
 *
 * For your HND Final Project at ATI Dehiwala SLIATE.
 */

// Include the database connection file using __DIR__ for reliable pathing
require_once __DIR__ . '/db_connect.php';

// Start a session to manage user login state
session_start();

$error_message = ''; // Initialize error message variable

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Basic validation
    if (empty($username) || empty($password)) {
        $error_message = "Please enter both username and password.";
    } else {
        try {
            // Prepare a SQL query to fetch the user.
            // We're looking for users with 'cashier' or 'inventory_manager' roles.
            // Ensure the user is active.
            $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = :username AND role IN ('cashier', 'inventory_manager') AND status = 'active'");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch(); // Fetch the user row

            if ($user && password_verify($password, $user['password_hash'])) {
                // Password is correct!
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role']; // Store the user's role in the session

                // Redirect based on the user's role
                if ($user['role'] === 'cashier') {
                    header("Location: cashier_dashboard.php"); // Redirect to Cashier's dashboard
                } elseif ($user['role'] === 'inventory_manager') {
                    header("Location: inventory_dashboard.php"); // Redirect to Inventory Manager's dashboard
                }
                exit(); // Always exit after a header redirect
            } else {
                $error_message = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            // Log the error for debugging (e.g., to your server's error log)
            error_log("Common user login error: " . $e->getMessage());
            $error_message = "A system error occurred during login. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCash - User Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body style="background: linear-gradient(135deg, #e0e0e0 0%, #f7f7f7 100%); min-height: 100vh;">

    <div class="login-container">
        <h1>Other User Login</h1>
        <?php if ($error_message): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form action="common_user_login.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
                <span class="password-toggle" onclick="togglePasswordVisibility('password')">
                    <i class="fas fa-eye"></i>
                </span>
            </div>
            <button type="submit" class="form-button">Login</button>
        </form>
        <p style="margin-top: 20px;">
            <a href="forgot_password.php" style="color: #0A2240; text-decoration: none; font-weight: bold;">Forgot Password?</a>
        </p>
    </div>

    <div class="footer">
        &copy; <?php echo date("Y"); ?> SmartCash
    </div>

    <script src="js/scripts.js"></script>

</body>
</html>