<?php
// bill_popup.php
// Shows the bill in a popup window and closes after pressing Enter or clicking a button
require_once __DIR__ . '/db_connect.php';

$transaction_id = isset($_GET['transaction_id']) ? intval($_GET['transaction_id']) : 0;
if ($transaction_id <= 0) {
    die('Invalid Bill');
}
// Fetch transaction
$stmt = $pdo->prepare("SELECT t.*, u.full_name, u.username FROM transactions t JOIN users u ON t.user_id = u.id WHERE t.transaction_id = ?");
$stmt->execute([$transaction_id]);
$transaction = $stmt->fetch();
if (!$transaction) {
    die('Bill not found');
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
        body { font-family: Segoe UI, Arial, sans-serif; background: #fff; }
        .bill-container { max-width: 500px; margin: 30px auto; background: #f9f9f9; border-radius: 12px; box-shadow: 0 4px 18px rgba(10,34,64,0.10); padding: 28px 18px; }
        h2 { color: #0A2240; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { padding: 8px; border-bottom: 1px solid #e0e0e0; }
        th { background: #f0f2f5; }
        tfoot td { font-weight: bold; background: #f7fafd; }
        .money-saved { color: #388e3c; }
        .close-btn { background: #007bff; color: #fff; border: none; border-radius: 20px; padding: 10px 28px; font-size: 1.1em; font-weight: 600; cursor: pointer; margin-top: 22px; display: block; margin-left: auto; margin-right: auto; }
    </style>
    <script>
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                window.close();
            }
        });
    </script>
</head>
<body>
    <div class="bill-container">
        <h2>SmartCash POS Bill #<?php echo $transaction_id; ?></h2>
        <p>Date: <?php echo date('Y-m-d H:i:s', strtotime($transaction['transaction_date'])); ?><br>Cashier: <?php echo htmlspecialchars($transaction['full_name']); ?> (<?php echo htmlspecialchars($transaction['username']); ?>)</p>
        <table>
            <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Discount</th><th>Total</th></tr></thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo number_format($item['selling_price_at_sale'],2); ?></td>
                    <td><?php echo number_format($item['discount'],2); ?></td>
                    <td><?php echo number_format(($item['selling_price_at_sale']-$item['discount'])*$item['quantity'],2); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr><td colspan="4" style="text-align:right;">Total:</td><td><?php echo number_format($transaction['total_amount'],2); ?></td></tr>
                <tr><td colspan="4" style="text-align:right;" class="money-saved">Money Saved This Bill:</td><td class="money-saved"><?php echo number_format($total_discount,2); ?></td></tr>
                <tr><td colspan="4" style="text-align:right;">Amount Paid:</td><td><?php echo number_format($transaction['amount_paid'],2); ?></td></tr>
                <tr><td colspan="4" style="text-align:right;">Change Given:</td><td><?php echo number_format($transaction['change_given'],2); ?></td></tr>
            </tfoot>
        </table>
        <p style="text-align:center;font-size:1.1em;color:#0A2240;margin-top:22px;font-weight:500;">Thank you for your purchase! Please come again!</p>
        <div class="bill-actions">
            <button class="close-btn" onclick="window.close();">Close Bill</button>
            <button class="close-btn" style="background:#28a745;margin-top:10px;" onclick="window.print();">Print Bill</button>
            <p style="text-align:center;color:#888;font-size:0.95em;">(You can also press Enter to close this bill)</p>
        </div>
        <style>
            @media print {
                .bill-actions { display: none !important; }
            }
        </style>
    </div>
</body>
</html>
