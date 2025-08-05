<?php
/**
 * reset_password.php
 *
 * This file allows a user (Cashier/Inventory Manager) to set a new password
 * after their reset request has been approved by the admin.
 * Requires a valid, unexpired reset token in the URL.
 *
 * For your HND Final Project at ATI Dehiwala SLIATE.
 */

require_once __DIR__ . '/db_connect.php';
session_start();

$message = '';
$token = $_GET['token'] ?? ''; // Get the token from the URL
$user_id_for_reset = null; // Will store the user_id if token is valid

if (empty($token)) {
    $message = '<div class="error-message">Invalid or missing password reset token.</div>';
} else {
    try {
        // 1. Validate the token and check its status and expiration
        $stmt = $pdo->prepare("SELECT pr.user_id, pr.request_status, pr.token_expires_at, u.username
                               FROM password_resets pr
                               JOIN users u ON pr.user_id = u.id
                               WHERE pr.reset_token = :token");
        $stmt->execute(['token' => $token]);
        $request = $stmt->fetch();

        if (!$request) {
            $message = '<div class="error-message">Invalid password reset token.</div>';
        } elseif ($request['request_status'] !== 'approved') {
            $message = '<div class="error-message">Your password reset request has not been approved or has already been used.</div>';
        } elseif (strtotime($request['token_expires_at']) < time()) {
            $message = '<div class="error-message">The password reset link has expired. Please request a new one.</div>';
            // Optionally, you might update the request status to 'expired' here
            // $pdo->prepare("UPDATE password_resets SET request_status = 'expired' WHERE reset_token = :token")->execute(['token' => $token]);
        } else {
            // Token is valid and approved, allow password reset
            $user_id_for_reset = $request['user_id'];
            $username_for_reset = $request['username'];

            // --- Handle Form Submission for New Password ---
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $new_password = $_POST['new_password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';

                if (empty($new_password) || empty($confirm_password)) {
                    $message = '<div class="error-message">Please enter and confirm your new password.</div>';
                } elseif ($new_password !== $confirm_password) {
                    $message = '<div class="error-message">Passwords do not match.</div>';
                } elseif (strlen($new_password) < 6) { // Example: Minimum 6 characters
                    $message = '<div class="error-message">Password must be at least 6 characters long.</div>';
                } else {
                    try {
                        // Hash the new password
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                        // Update the user's password in the 'users' table
                        $pdo->beginTransaction(); // Start transaction for atomicity

                        $stmt_update_user = $pdo->prepare("UPDATE users SET password_hash = :password_hash WHERE id = :user_id");
                        $stmt_update_user->execute(['password_hash' => $hashed_password, 'user_id' => $user_id_for_reset]);

                        // Mark the reset request as completed
                        $stmt_complete_request = $pdo->prepare("UPDATE password_resets SET request_status = 'completed', completed_at = CURRENT_TIMESTAMP WHERE reset_token = :token");
                        $stmt_complete_request->execute(['token' => $token]);

                        $pdo->commit(); // Commit transaction

                        $message = '<div class="success-message">Your password has been successfully reset! You can now <a href="common_user_login.php">log in</a> with your new password.</div>';
                        // Prevent the form from showing again after successful reset
                        $token = ''; // Clear token to hide form
                    } catch (PDOException $e) {
                        $pdo->rollBack(); // Rollback on error
                        error_log("Password reset update error: " . $e->getMessage());
                        $message = '<div class="error-message">A system error occurred while resetting password. Please try again.</div>';
                    }
                }
            }
        }
    } catch (PDOException $e) {
        error_log("Password reset token lookup error: " . $e->getMessage());
        $message = '<div class="error-message">A system error occurred. Please try again later.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCash - Reset Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .password-reset-container {
            background-color: var(--light-color);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            text-align: center;
            margin: auto;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .password-reset-container h1 {
            color: var(--primary-blue);
            margin-bottom: 20px;
            font-size: 2em;
        }
    </style>
</head>
<body>

    <div class="password-reset-container">
        <h1>Reset Your Password</h1>
        <?php echo $message; // Display messages here ?>

        <?php if ($user_id_for_reset && $token): // Only show form if token is valid and not yet consumed ?>
            <p>Hello, **<?php echo htmlspecialchars($username_for_reset); ?>**! Please set your new password below.</p>
            <form action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>" method="POST">
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" required autocomplete="new-password">
                    <span class="password-toggle" onclick="togglePasswordVisibility('new_password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
                    <span class="password-toggle" onclick="togglePasswordVisibility('confirm_password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <button type="submit" class="form-button">Set New Password</button>
            </form>
        <?php endif; ?>

        <?php if (!empty($message) && strpos($message, 'Your password has been successfully reset!') !== false): ?>
             <p style="margin-top: 20px;">
                <a href="common_user_login.php" class="form-button">Go to Login</a>
            </p>
        <?php endif; ?>

        <?php if (empty($token) && strpos($message, 'Invalid') !== false): // Only show back to login if initial token check failed ?>
            <p style="margin-top: 20px;">
                <a href="common_user_login.php" style="color: var(--primary-blue); text-decoration: none; font-weight: bold;">Back to Login</a>
            </p>
        <?php endif; ?>
    </div>

    <div class="footer">
        &copy; <?php echo date("Y"); ?> SmartCash
    </div>

    <script src="js/scripts.js"></script>

</body>
</html>