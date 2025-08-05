<?php
/**
 * cashier_dashboard.php
 *
 * This file serves as the main dashboard for the Cashier.
 * It provides an overview and navigation to the Point of Sale interface.
 *
 * For your HND Final Project at ATI Dehiwala SLIATE, Sri Lanka.
 */

require_once __DIR__ . '/db_connect.php'; // Ensure db_connect.php is correctly linked
session_start();

// --- Security Check: Ensure only logged-in cashier can access ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'cashier') {
    // Redirect to common user login if not logged in or not a cashier
    header("Location: common_user_login.php");
    exit();
}

$username = htmlspecialchars($_SESSION['username']);

// Fetch some quick stats for the dashboard (optional)
$total_transactions_today = 0;
$total_sales_today = 0.00;

try {
    // Get today's date in 'YYYY-MM-DD' format
    $today_date = date('Y-m-d');

    $stmt_transactions = $pdo->prepare("SELECT COUNT(transaction_id) AS total, SUM(total_amount) AS sales_sum
                                        FROM transactions
                                        WHERE DATE(transaction_date) = :today_date AND user_id = :user_id");
    $stmt_transactions->execute([
        'today_date' => $today_date,
        'user_id' => $_SESSION['user_id']
    ]);
    $stats = $stmt_transactions->fetch(PDO::FETCH_ASSOC);

    $total_transactions_today = $stats['total'] ?? 0;
    $total_sales_today = $stats['sales_sum'] ?? 0.00;

} catch (PDOException $e) {
    error_log("Error fetching cashier dashboard stats: " . $e->getMessage());
    // Display a user-friendly error message or handle gracefully
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCash - Cashier Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dashboard-main-container {
            background-color: var(--light-color);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
            text-align: center;
            margin: 20px auto;
            flex-grow: 1;
        }

        .dashboard-main-container h1 {
            color: var(--primary-blue);
            margin-bottom: 20px;
            font-size: 2.2em;
        }

        .dashboard-main-container p {
            font-size: 1.1em;
            color: #333;
            margin-bottom: 30px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background-color: var(--background-grey);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .stat-card h3 {
            color: var(--accent-maroon);
            margin-bottom: 10px;
            font-size: 1.5em;
        }

        .stat-card p {
            font-size: 2em;
            font-weight: bold;
            color: var(--primary-blue);
            margin: 0;
        }
    </style>
</head>
<body style="background: linear-gradient(135deg, #e0e0e0 0%, #f7f7f7 100%); min-height: 100vh;">

    <div class="dashboard-main-container">
        <h1>Welcome, Cashier <?php echo $username; ?>!</h1>
        <p>This is your cashier dashboard. Get ready to process sales efficiently!</p>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Today's Transactions</h3>
                <p><?php echo $total_transactions_today; ?></p>
            </div>
            <div class="stat-card">
                <h3>Today's Sales (LKR)</h3>
                <p><?php echo number_format($total_sales_today, 2); ?></p>
            </div>
            </div>

        <div style="margin-top: 30px;">
            <a href="cashier_pos.php" class="form-button">Start New Sale</a>
            <a href="logout.php" class="form-button" style="background-color: #6c757d;">Logout</a>
        </div>
    </div>

    <div class="footer">
        &copy; <?php echo date("Y"); ?> SmartCash
    </div>

</body>
</html>