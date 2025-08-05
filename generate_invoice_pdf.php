<?php
// generate_invoice_pdf.php
// Generates a PDF invoice summary for a given transaction_id
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
// Use dompdf for PDF generation
require_once __DIR__ . '/vendor/autoload.php';
use Dompdf\Dompdf;
$dompdf = new Dompdf();
$html = '<html><head><style>body{font-family:Segoe UI,Arial,sans-serif;}table{width:100%;border-collapse:collapse;}th,td{padding:8px;border-bottom:1px solid #e0e0e0;}th{background:#f0f2f5;}tfoot td{font-weight:bold;background:#f7fafd;}.money-saved{color:#388e3c;}</style></head><body>';
$html .= '<h2 style="color:#0A2240;">SmartCash POS Bill #'.$transaction_id.'</h2>';
$html .= '<p>Date: '.date('Y-m-d H:i:s', strtotime($transaction['transaction_date'])).'<br>Cashier: '.htmlspecialchars($transaction['full_name']).' ('.htmlspecialchars($transaction['username']).')</p>';
$html .= '<table><thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Discount</th><th>Total</th></tr></thead><tbody>';
foreach ($items as $item) {
    $html .= '<tr><td>'.htmlspecialchars($item['product_name']).'</td><td>'.$item['quantity'].'</td><td>'.number_format($item['selling_price_at_sale'],2).'</td><td>'.number_format($item['discount'],2).'</td><td>'.number_format(($item['selling_price_at_sale']-$item['discount'])*$item['quantity'],2).'</td></tr>';
}
$html .= '</tbody><tfoot>';
$html .= '<tr><td colspan="4" style="text-align:right;">Total:</td><td>'.number_format($transaction['total_amount'],2).'</td></tr>';
$html .= '<tr><td colspan="4" style="text-align:right;" class="money-saved">Money Saved This Bill:</td><td class="money-saved">'.number_format($total_discount,2).'</td></tr>';
$html .= '<tr><td colspan="4" style="text-align:right;">Amount Paid:</td><td>'.number_format($transaction['amount_paid'],2).'</td></tr>';
$html .= '<tr><td colspan="4" style="text-align:right;">Change Given:</td><td>'.number_format($transaction['change_given'],2).'</td></tr>';
$html .= '</tfoot></table>';
$html .= '<p style="text-align:center;font-size:1.1em;color:#0A2240;margin-top:22px;font-weight:500;">Thank you for your purchase! Please come again!</p>';
$html .= '<div style="text-align:center;margin-top:30px;">
    <button onclick="window.close();" style="background:#007bff;color:#fff;border:none;border-radius:20px;padding:10px 28px;font-size:1.1em;font-weight:600;cursor:pointer;">Close Bill</button>
</div>';
$html .= '</body></html>';
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('SmartCash_Bill_'.$transaction_id.'.pdf', ['Attachment' => false]);
exit;
