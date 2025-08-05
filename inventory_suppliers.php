<?php
// inventory_suppliers.php
// Manage suppliers: add, edit, delete
require_once __DIR__ . '/db_connect.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'inventory_manager') {
    header("Location: common_user_login.php");
    exit();
}
$message = '';
// Handle add supplier
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['supplier_name'] ?? '');
    if ($name) {
        $stmt = $pdo->prepare("INSERT INTO suppliers (name) VALUES (?)");
        $stmt->execute([$name]);
        $message = '<div class="success-message">Supplier added.</div>';
    }
}
// Handle edit supplier
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = intval($_POST['supplier_id'] ?? 0);
    $name = trim($_POST['supplier_name'] ?? '');
    if ($id && $name) {
        $stmt = $pdo->prepare("UPDATE suppliers SET name=? WHERE id=?");
        $stmt->execute([$name, $id]);
        $message = '<div class="success-message">Supplier updated.</div>';
    }
}
// Handle delete supplier
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = intval($_POST['supplier_id'] ?? 0);
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id=?");
        $stmt->execute([$id]);
        $message = '<div class="success-message">Supplier deleted.</div>';
    }
}
// Fetch suppliers
$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Suppliers</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 4px 18px rgba(10,34,64,0.10); padding: 32px 22px; }
        h2 { text-align: center; color: #0A2240; margin-bottom: 18px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        th, td { padding: 10px 8px; border-bottom: 1px solid #e0e0e0; text-align: left; }
        th { background: #f0f2f5; color: #0A2240; }
        .actions { text-align: right; }
        .form-inline { display: flex; gap: 8px; }
        .form-inline input[type=text] { flex: 1; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Manage Suppliers</h2>
        <?php echo $message; ?>
        <form method="POST" class="form-inline" style="margin-bottom:18px;">
            <input type="hidden" name="action" value="add">
            <input type="text" name="supplier_name" placeholder="New Supplier Name" required>
            <button type="submit" class="form-button">Add Supplier</button>
        </form>
        <table>
            <thead>
                <tr><th>Name</th><th class="actions">Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($suppliers as $sup): ?>
                <tr>
                    <form method="POST" class="form-inline">
                        <td>
                            <input type="hidden" name="supplier_id" value="<?php echo $sup['id']; ?>">
                            <input type="text" name="supplier_name" value="<?php echo htmlspecialchars($sup['name'] ?? ''); ?>" required>
                        </td>
                        <td class="actions">
                            <button type="submit" name="action" value="edit" class="form-button" style="background:#007bff;">Edit</button>
                            <button type="submit" name="action" value="delete" class="form-button" style="background:#dc3545;" onclick="return confirm('Delete supplier?');">Delete</button>
                        </td>
                    </form>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="inventory_dashboard.php" class="form-button" style="background:#6c757d;">Back to Dashboard</a>
    </div>
</body>
</html>
