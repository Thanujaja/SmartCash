<?php
/**
 * admin_password_requests.php
 *
 * This file allows the System Administrator to view and manage (approve/deny)
 * password reset requests from common users (Cashiers, Inventory Managers).
 *
 * For your HND Final Project at ATI Dehiwala SLIATE.
 */

require_once __DIR__ . '/db_connect.php';
session_start();

// --- Security Check: Ensure only logged-in admin can access ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

$message = ''; // To display success or error messages for actions

// --- Handle Approve/Deny Actions ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_id = $_POST['request_id'] ?? null;
    $action = $_POST['action'] ?? null; // 'approve' or 'deny'

    if ($request_id && ($action === 'approve' || $action === 'deny')) {
        try {
            if ($action === 'approve') {
                // Generate a unique token
                $reset_token = bin2hex(random_bytes(32)); // Generates a 64-character hex string
                // Set token expiration (e.g., 1 hour from now)
                $token_expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $stmt = $pdo->prepare("UPDATE password_resets SET request_status = 'approved', reset_token = :reset_token, token_expires_at = :token_expires_at, approved_by_admin_at = CURRENT_TIMESTAMP WHERE id = :id AND request_status = 'pending'");
                $stmt->execute([
                    'reset_token' => $reset_token,
                    'token_expires_at' => $token_expires_at,
                    'id' => $request_id
                ]);
                if ($stmt->rowCount()) {
                    $message = '<div class="success-message">Password reset request approved successfully. User can now set new password.</div>';
                    // Display the reset link to the admin.
                    $reset_link = 'http://localhost/smartcash_project/reset_password.php?token=' . urlencode($reset_token);
                    $message .= '<div class="success-message">The reset link for the user is: <strong><a href="' . htmlspecialchars($reset_link) . '" target="_blank">' . htmlspecialchars($reset_link) . '</a></strong> (Valid for 1 hour). Please copy this link and provide it to the user.</div>';
                } else {
                    $message = '<div class="error-message">Could not approve request. It might no longer be pending or has already been processed.</div>';
                }
            } elseif ($action === 'deny') {
                $stmt = $pdo->prepare("UPDATE password_resets SET request_status = 'denied', denied_by_admin_at = CURRENT_TIMESTAMP WHERE id = :id");
                $stmt->execute(['id' => $request_id]);
                if ($stmt->rowCount()) {
                    $message = '<div class="success-message">Password reset request denied successfully.</div>';
                } else {
                    $message = '<div class="error-message">Could not deny request. It might no longer be pending or has already been processed.</div>';
                }
            }
        } catch (PDOException $e) {
            error_log("Error processing password reset request: " . $e->getMessage());
            $message = '<div class="error-message">A system error occurred while processing the request. Please try again.</div>';
        }
    }
}

// Fetch all password reset requests
$requests = [];
try {
    $stmt = $pdo->query("SELECT * FROM password_resets WHERE request_status IN ('pending', 'approved', 'denied')");
    $requests = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching password reset requests: " . $e->getMessage());
    $message .= '<div class="error-message">A system error occurred while fetching requests. Please try again.</div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Password Reset Requests</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <h1>Manage Password Reset Requests</h1>
        <?php echo $message; ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $request): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($request['id']); ?></td>
                        <td><?php echo htmlspecialchars($request['username']); ?></td>
                        <td><?php echo htmlspecialchars($request['request_status']); ?></td>
                        <td>
                            <form action="admin_password_requests.php" method="POST" style="display:inline;">
                                <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request['id']); ?>">
                                <button type="submit" name="action" value="approve">Approve</button>
                                <button type="submit" name="action" value="deny">Deny</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="footer">
        &copy; <?php echo date("Y"); ?> SmartCash
    </div>
</body>
</html>
