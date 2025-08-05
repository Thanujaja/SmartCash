<?php
/**
 * forgot_password.php
 *
 * This file allows Cashiers and Inventory Managers to initiate a password reset request.
 * The request is recorded in the database for the System Admin's approval.
 *
 * For your HND Final Project at ATI Dehiwala SLIATE.
 */

require_once __DIR__ . '/db_connect.php'; // Ensure db_connect.php is correctly linked

$message = ''; // To display success or error messages

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');

    if (empty($username) || empty($full_name)) {
        $message = '<div class="error-message">Please enter both your username and full name.</div>';
    } else {
        try {
            // 1. Verify if the username and full name exist in the users table
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username AND full_name = :full_name AND role IN ('cashier', 'inventory_manager') AND status = 'active'");
            $stmt->execute(['username' => $username, 'full_name' => $full_name]);
            $user = $stmt->fetch();

            if ($user) {
                // User found, now check for existing pending requests
                $stmt_check_pending = $pdo->prepare("SELECT id FROM password_resets WHERE user_id = :user_id AND request_status = 'pending'");
                $stmt_check_pending->execute(['user_id' => $user['id']]);

                if ($stmt_check_pending->fetch()) {
                    $message = '<div class="error-message">You already have a pending password reset request. Please wait for admin approval.</div>';
                } else {
                    // No pending request, insert a new one
                    $stmt_insert = $pdo->prepare("INSERT INTO password_resets (user_id, username, full_name_on_request, request_status) VALUES (:user_id, :username, :full_name, 'pending')");
                    $stmt_insert->execute([
                        'user_id' => $user['id'],
                        'username' => $username,
                        'full_name' => $full_name
                    ]);
                    $message = '<div class="success-message">Your password reset request has been sent for admin approval. You will be able to set a new password once it\'s approved.</div>';
                }
            } else {
                $message = '<div class="error-message">Invalid username or full name. Please ensure details match your registration.</div>';
            }
        } catch (PDOException $e) {
            error_log("Forgot password request error: " . $e->getMessage());
            $message = '<div class="error-message">A system error occurred. Please try again later.</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCash - Forgot Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Specific styling for success messages */
        .success-message {
            color: #28a745; /* Green for success */
            margin-top: 15px;
            padding: 10px;
            background-color: #d4edda; /* Light green background */
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h1>Forgot Password?</h1>
        <p>Please enter your registered username and full name to request a password reset.</p>
        <?php echo $message; // Display messages here ?>
        <form action="forgot_password.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="full_name">Full Name:</label>
                <input type="text" id="full_name" name="full_name" required autocomplete="name">
            </div>
            <button type="submit" class="form-button">Request Reset</button>
        </form>
        <p style="margin-top: 20px;">
            <a href="common_user_login.php" style="color: var(--primary-blue); text-decoration: none; font-weight: bold;">Back to Login</a>
        </p>
    </div>

    <div class="footer">
        &copy; <?php echo date("Y"); ?> SmartCash
    </div>

    <script src="js/scripts.js"></script>

</body>
</html>