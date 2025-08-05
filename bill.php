<?php
require_once __DIR__ . '/db_connect.php';
session_start();

$transaction_id = isset($_GET['transaction_id']) ? intval($_GET['transaction_id']) : 0;
if ($transaction_id <= 0) {
    die('<h2>Invalid Bill</h2>');
}

// Fetch transaction
$stmt = $pdo->prepare("SELECT t.*, u.full_name, u.username FROM transactions t JOIN users u ON t.user_id = u.id WHERE t.transaction_id = ?");
$stmt->execute([$transaction_id]);
$transaction = $stmt->fetch();

if (!$transaction) {
    die('<h2>Bill not found</h2>');
}

// Fetch items
$stmt = $pdo->prepare("SELECT ti.*, p.product_name, p.discount FROM transaction_items ti JOIN products p ON ti.product_id = p.product_id WHERE ti.transaction_id = ?");
$stmt->execute([$transaction_id]);
$items = $stmt->fetchAll();

$total_discount = 0;
foreach ($items as $item) {
    $total_discount += $item['discount'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SmartCash Bill #<?php echo $transaction_id; ?></title>
    <style>
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .bill-container {
            background: #fff;
            max-width: 480px;
            margin: 40px auto;
            border-radius: 14px;
            box-shadow: 0 4px 24px rgba(10,34,64,0.10);
            padding: 32px 22px 18px 22px;
        }
        .bill-logo {
            text-align: center;
            font-size: 2.5em;
            color: #0A2240;
            margin-bottom: 8px;
        }
        .bill-title {
            text-align: center;
            color: #0A2240;
            font-size: 1.5em;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }
        .bill-meta {
            text-align: center;
            color: #555;
            font-size: 1em;
            margin-bottom: 18px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 1.04em;
        }
        th, td {
            padding: 8px 6px;
            border-bottom: 1px solid #e0e0e0;
            text-align: left;
        }
        th {
            background: #f0f2f5;
            color: #0A2240;
            font-weight: 600;
        }
        tfoot td {
            font-weight: bold;
            background: #f7fafd;
        }
        .money-saved {
            color: #388e3c;
        }
        .bill-footer {
            text-align: center;
            font-size: 1.13em;
            color: #0A2240;
            margin-top: 22px;
            font-weight: 500;
        }
        .print-btn {
            background: linear-gradient(90deg,#0A2240 60%,#007bff 100%);
            color: #fff;
            border: none;
            border-radius: 20px;
            padding: 10px 28px;
            font-size: 1.08em;
            font-weight: 600;
            margin: 18px auto 0 auto;
            display: block;
            cursor: pointer;
            transition: background 0.18s, box-shadow 0.18s, transform 0.09s;
            box-shadow: 0 2px 8px rgba(0,123,255,0.10);
        }
        .print-btn:hover {
            background: linear-gradient(90deg,#007bff 60%,#0A2240 100%);
            transform: translateY(-2px) scale(1.05);
        }
    </style>
</head>
<body>
    <div class="bill-container">
        <div class="bill-logo">&#128179;</div>
        <div class="bill-title">SmartCash POS Bill</div>
        <div class="bill-meta">
            Bill #: <?php echo $transaction_id; ?><br>
            Date: <?php echo date('Y-m-d H:i:s', strtotime($transaction['transaction_date'])); ?><br>
            Cashier: <?php echo htmlspecialchars($transaction['full_name']); ?> (<?php echo htmlspecialchars($transaction['username']); ?>)
        </div>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Discount</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td style="text-align:center;"><?php echo $item['quantity']; ?></td>
                    <td style="text-align:right;"><?php echo number_format($item['selling_price_at_sale'], 2); ?></td>
                    <td style="text-align:right; color:#388e3c;"><?php echo number_format($item['discount'], 2); ?></td>
                    <td style="text-align:right; font-weight:500;"><?php echo number_format(($item['selling_price_at_sale'] - $item['discount']) * $item['quantity'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align:right;">Total:</td>
                    <td style="text-align:right;"><?php echo number_format($transaction['total_amount'], 2); ?></td>
                </tr>
                <tr>
                    <td colspan="4" style="text-align:right;" class="money-saved">Money Saved This Bill:</td>
                    <td style="text-align:right;" class="money-saved"><?php echo number_format($total_discount, 2); ?></td>
                </tr>
                <tr>
                    <td colspan="4" style="text-align:right;">Amount Paid:</td>
                    <td style="text-align:right;"><?php echo number_format($transaction['amount_paid'], 2); ?></td>
                </tr>
                <tr>
                    <td colspan="4" style="text-align:right;">Change Given:</td>
                    <td style="text-align:right;"><?php echo number_format($transaction['change_given'], 2); ?></td>
                </tr>
            </tfoot>
        </table>
        <div class="bill-footer">
            <span style="font-family: 'Segoe UI Symbol', 'Arial Unicode MS', Arial, sans-serif; font-size:1.3em; vertical-align:middle;">&#128522;</span>
            Thank you for your purchase! Please come again!
        </div>
        <button class="print-btn" onclick="window.print();">&#128424; Print Bill</button>
    </div>
</body>
</html>
