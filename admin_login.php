<?php
/**
 * admin_login.php
 *
 * This file handles the login for the Main System Administrator of the SmartCash application.
 * It includes the database connection and processes login form submissions.
 *
 * For your HND Final Project at ATI Dehiwala SLIATE.
 */

// Include the database connection file using __DIR__ for reliable pathing
require_once __DIR__ . '/db_connect.php'; // Database connection is needed for SQL-based admin login

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
        // Direct login for main admin (for recovery/testing)
        if ($username === 'administrator' && $password === '1111') {
            $_SESSION['user_id'] = 0;
            $_SESSION['username'] = 'administrator';
            $_SESSION['role'] = 'admin';
            header("Location: admin_dashboard.php");
            exit();
        }
        try {
            // Prepare a SQL query to fetch the admin user from the database
            $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = :username AND role = 'admin' AND status = 'active'");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Credentials are correct!
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = 'admin';
                // Redirect to admin dashboard
                header("Location: admin_dashboard.php");
                exit();
            } else {
                $error_message = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            error_log("Admin login error: " . $e->getMessage());
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
    <title>SmartCash - Admin Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="login-container">
        <img src="images/smartcash_logo.png" alt="SmartCash Logo" style="width:120px;height:120px;object-fit:contain;display:block;margin:0 auto 20px auto;">
        <h1>Main Admin Login</h1>
        <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <form action="admin_login.php" method="POST">
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
    </div>

    <div class="footer">
        &copy; <?php echo date("Y"); ?> SmartCash
    </div>

    <script src="js/scripts.js"></script>

</body>
</html>