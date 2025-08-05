<?php
/**
 * admin_sales_summary.php
 *
 * Displays sales summary with a creative bar chart/histogram and PDF invoice summary generation option.
 * Accessible only to admin users.
 */

session_start();
require_once __DIR__ . '/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Fetch sales data for chart (grouped by day)
$sales_data = [];
$stmt = $pdo->query("SELECT DATE(transaction_date) as sale_date, SUM(total_amount) as total_sales, COUNT(*) as num_bills FROM transactions GROUP BY sale_date ORDER BY sale_date DESC LIMIT 14");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $sales_data[] = $row;
}

// Fetch recent invoices for PDF summary option
$recent_bills = [];
$stmt = $pdo->query("SELECT t.transaction_id, t.transaction_date, t.total_amount, u.full_name FROM transactions t JOIN users u ON t.user_id = u.id ORDER BY t.transaction_date DESC LIMIT 10");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $recent_bills[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales & Summary - SmartCash Admin</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .summary-container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 24px rgba(10,34,64,0.10);
            padding: 32px 22px 18px 22px;
        }
        .summary-title {
            text-align: center;
            font-size: 2em;
            color: #0A2240;
            margin-bottom: 18px;
        }
        .chart-wrapper {
            width: 100%;
            max-width: 700px;
            margin: 0 auto 30px auto;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .invoice-table th, .invoice-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e0e0e0;
            text-align: left;
        }
        .invoice-table th {
            background: #f0f2f5;
            color: #0A2240;
        }
        .pdf-btn {
            background: linear-gradient(90deg,#0A2240 60%,#007bff 100%);
            color: #fff;
            border: none;
            border-radius: 20px;
            padding: 7px 18px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            margin: 0 2px;
        }
        .pdf-btn:hover {
            background: linear-gradient(90deg,#007bff 60%,#0A2240 100%);
        }
    </style>
</head>
<body>
    <div class="summary-container">
        <div class="summary-title">Sales & Invoice Summary</div>
        <div class="chart-wrapper">
            <canvas id="salesChart"></canvas>
        </div>
        <h2 style="color:#0A2240; font-size:1.3em;">Recent Bills (Invoice PDF Option)</h2>
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Bill #</th>
                    <th>Date</th>
                    <th>Cashier</th>
                    <th>Total (LKR)</th>
                    <th>PDF</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_bills as $bill): ?>
                <tr>
                    <td><?php echo $bill['transaction_id']; ?></td>
                    <td><?php echo date('Y-m-d H:i', strtotime($bill['transaction_date'])); ?></td>
                    <td><?php echo htmlspecialchars($bill['full_name']); ?></td>
                    <td><?php echo number_format($bill['total_amount'], 2); ?></td>
                    <td><a href="generate_invoice_pdf.php?transaction_id=<?php echo $bill['transaction_id']; ?>" class="pdf-btn" target="_blank">Download PDF</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script>
        const salesLabels = <?php echo json_encode(array_reverse(array_column($sales_data, 'sale_date'))); ?>;
        const salesTotals = <?php echo json_encode(array_reverse(array_map('floatval', array_column($sales_data, 'total_sales')))); ?>;
        const numBills = <?php echo json_encode(array_reverse(array_map('intval', array_column($sales_data, 'num_bills')))); ?>;
        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: salesLabels,
                datasets: [
                    {
                        label: 'Total Sales (LKR)',
                        data: salesTotals,
                        backgroundColor: 'rgba(10,34,64,0.7)',
                        borderRadius: 8,
                    },
                    {
                        label: 'Number of Bills',
                        data: numBills,
                        type: 'line',
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0,123,255,0.12)',
                        fill: false,
                        tension: 0.3,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: {
                        display: true,
                        text: 'Sales Overview (Last 14 Days)',
                        font: { size: 18 }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Sales (LKR)' }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        title: { display: true, text: 'Number of Bills' }
                    }
                }
            }
        });
    </script>
</body>
</html>
