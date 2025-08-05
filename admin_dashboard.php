<?php
/**
 * admin_dashboard.php
 *
 * Basic placeholder for the System Admin dashboard.
 * Includes security checks and navigation links.
 *
 * For your HND Final Project at ATI Dehiwala SLIATE.
 */

session_start();

// --- Security Check: Ensure only logged-in admin can access ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCash - Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
    // Show a warning when the user tries to leave or refresh the page
    window.onbeforeunload = function(event) {
        // Most browsers ignore custom messages for security, but the prompt will still appear.
        const message = "You will be logged out if you leave or refresh this page. Are you sure?";
        event.returnValue = message; // Standard for most browsers
        return message; // For older browsers
    };
</script>
</head>
<body style="background: linear-gradient(135deg, #e0e0e0 0%, #f7f7f7 100%); min-height: 100vh;">

    <div class="login-container"> <h1>Welcome, Admin <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p style="color:#fff;">This is your main administration panel.</p>
        <p style="color:#fff;">From here you can manage various aspects of the SmartCash system.</p>

        <div class="dashboard-buttons" style="margin-top: 30px;">
            <a href="admin_password_requests.php" class="form-button" style="color:#fff;">Manage Password Reset Requests</a>
            <a href="admin_manage_users.php" class="form-button" style="color:#fff;">Manage System Users</a>
            <a href="admin_sales_summary.php" class="form-button" style="color:#fff;">Sales & Summary</a>
            <a href="logout.php" class="form-button" style="background-color: #6c757d;color:#fff;">Logout</a>
        </div> 
    </div>

    <div class="footer">
        &copy; <?php echo date("Y"); ?> SmartCash
    </div>

</body>
</html>