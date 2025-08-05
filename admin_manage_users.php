<?php
/**
 * admin_manage_users.php
 *
 * This file allows the System Administrator to view, add, and manage
 * common users (Cashiers, Inventory Managers).
 *
 * For your HND Final Project at ATI Dehiwala SLIATE.
 */

require_once __DIR__ . '/db_connect.php';
session_start();

// Remove user with id=1 if exists
try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = 1");
    $stmt->execute();
    // Change id=2 to id=1
    $stmt = $pdo->prepare("UPDATE users SET id = 1 WHERE id = 2");
    $stmt->execute();
    // Change id=100 to id=2
    $stmt = $pdo->prepare("UPDATE users SET id = 2 WHERE id = 100");
    $stmt->execute();
    // Remove any users with id > 3
    $stmt = $pdo->prepare("DELETE FROM users WHERE id > 3");
    $stmt->execute();
    // Set roles and names for first three users
    $stmt = $pdo->prepare("UPDATE users SET role = 'cashier', username = 'cashier1', full_name = 'Udayanga', contact_info = 'ud' WHERE id = 1");
    $stmt->execute();
    $stmt = $pdo->prepare("UPDATE users SET role = 'cashier', username = 'cashier2', full_name = 'Fernando' WHERE id = 2");
    $stmt->execute();
    $stmt = $pdo->prepare("UPDATE users SET role = 'inventory_manager', username = 'invManager', full_name = 'Perera' WHERE id = 3");
    $stmt->execute();
} catch (PDOException $e) {
    error_log("Error deleting user with id=1: " . $e->getMessage());
}

// --- Security Check: Ensure only logged-in admin can access ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

$message = ''; // To display success or error messages

// --- Handle Add New User Form Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_user') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');
        $role = trim($_POST['role'] ?? '');
        $contact_info = trim($_POST['contact_info'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $status = trim($_POST['status'] ?? 'active');

        // Basic validation
        if (empty($username) || empty($password) || empty($full_name) || empty($role)) {
            $message = '<div class="error-message">Please fill all required fields.</div>';
        } elseif (!in_array($role, ['cashier', 'inventory_manager'])) {
            $message = '<div class="error-message">Invalid role selected.</div>';
        } else {
            try {
                // Begin transaction
                $pdo->beginTransaction();

                // Check for existing username
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
                $stmt->execute([$username]);
                $exists = $stmt->fetchColumn();

                if ($exists) {
                    $message = '<div class="error-message">Username already exists. Please choose a different username.</div>';
                } else {
                    // Hash password
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);

                    // Insert new user
                    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, full_name, role, contact_info, address, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$username, $password_hash, $full_name, $role, $contact_info, $address, $status]);

                    $pdo->commit();
                    $message = '<div class="success-message">User added successfully!</div>';
                    // Clear form data
                    $_POST = array();
                }
            } catch (PDOException $e) {
                $pdo->rollBack();
                error_log("Error adding user: " . $e->getMessage());
                $message = '<div class="error-message">Database error occurred. Please try again.</div>';
            }
        }
    }
}

// --- Fetch all users (non-admins) for display ---
$users = [];
try {
    // Select all users who are not 'admin'
    $stmt = $pdo->query("SELECT id, username, full_name, role, contact_info, address, status FROM users ORDER BY username ASC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching users: " . $e->getMessage());
    $message = '<div class="error-message">Could not retrieve user list.</div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCash - Manage Users</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background: linear-gradient(120deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            margin: 0;
        }
        .login-container {
            background: #fff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(60,60,100,0.10), 0 1.5px 4px rgba(0,0,0,0.04);
            max-width: 1200px;
            margin: 40px auto 30px auto;
            min-height: 80vh;
        }
        h1, h2 {
            color: #0A2240;
            margin-bottom: 18px;
        }
        .users-table-wrapper {
            overflow-x: auto;
            margin-bottom: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            padding: 10px;
        }
        table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
            background: #fff;
        }
        th, td {
            padding: 12px 14px;
            border-bottom: 1px solid #e0e0e0;
            text-align: left;
            font-size: 1em;
        }
        th {
            background: #0A2240;
            color: #fff;
            position: sticky;
            top: 0;
            z-index: 2;
        }
        tr:nth-child(even) {
            background: #f7fafd;
        }
        tr:hover {
            background: #eaf1fb;
        }
        .action-button {
            padding: 6px 14px;
            border: none;
            border-radius: 5px;
            margin: 2px 0;
            font-size: 0.95em;
            cursor: pointer;
            transition: background 0.2s;
        }
        .edit-button { background: #007bff; color: #fff; }
        .edit-button:hover { background: #0056b3; }
        .deactivate-button { background: #dc3545; color: #fff; }
        .deactivate-button:hover { background: #a71d2a; }
        .activate-button { background: #28a745; color: #fff; }
        .activate-button:hover { background: #176c2a; }
        @media (max-width: 900px) {
            .login-container { padding: 20px 5px; }
            table { min-width: 700px; }
        }
        @media (max-width: 600px) {
            .login-container { padding: 8px 2px; }
            table { min-width: 500px; font-size: 0.95em; }
            th, td { padding: 8px 6px; }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h1>Manage System Users</h1>
        <?php echo $message; // Display messages here ?>

        <?php if (!empty($users)): ?>
            <h2>Existing Users</h2>
            <div class="users-table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Role</th>
                            <th>Contact</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                               <td><?php echo htmlspecialchars($user['username'] ?? ''); ?></td>
                               <td><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($user['role'])); ?></td>
                                <td><?php echo htmlspecialchars($user['contact_info'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($user['address'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($user['status'])); ?></td>
                                <td>
                                    <a href="admin_edit_user.php?id=<?php echo htmlspecialchars($user['id']); ?>" class="action-button edit-button">Edit</a>
                                    <form action="admin_manage_users.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to change the status of <?php echo htmlspecialchars($user['username']); ?>?');">
                                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                                        <?php if ($user['status'] === 'active'): ?>
                                            <button type="submit" name="action" value="deactivate_user" class="action-button deactivate-button">Deactivate</button>
                                        <?php else: ?>
                                            <button type="submit" name="action" value="activate_user" class="action-button activate-button">Activate</button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No other users found in the system.</p>
        <?php endif; ?>

        <hr style="margin: 40px 0; border: 0; border-top: 1px solid #eee;">

        <h2>Add New System User</h2>
        <form action="admin_manage_users.php" method="POST" class="add-user-form">
            <input type="hidden" name="action" value="add_user">

            <div class="form-group">
                <label for="username">Username: <span style="color:red;">*</span></label>
                <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="password">Password: <span style="color:red;">*</span></label>
                <input type="password" id="password" name="password" required>
                <span class="password-toggle" onclick="togglePasswordVisibility('password')">
                    <i class="fas fa-eye"></i>
                </span>
            </div>
            <div class="form-group">
                <label for="full_name">Full Name: <span style="color:red;">*</span></label>
                <input type="text" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="role">Role: <span style="color:red;">*</span></label>
                <select id="role" name="role" required>
                    <option value="">Select Role</option>
                    <option value="cashier" <?php echo (($_POST['role'] ?? '') === 'cashier' ? 'selected' : ''); ?>>Cashier</option>
                    <option value="inventory_manager" <?php echo (($_POST['role'] ?? '') === 'inventory_manager' ? 'selected' : ''); ?>>Inventory Manager</option>
                </select>
            </div>
            <div class="form-group">
                <label for="contact_info">Contact Info:</label>
                <input type="text" id="contact_info" name="contact_info" value="<?php echo htmlspecialchars($_POST['contact_info'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <textarea id="address" name="address"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="status">Status: <span style="color:red;">*</span></label>
                <select id="status" name="status" required>
                    <option value="active" <?php echo (($_POST['status'] ?? 'active') === 'active' ? 'selected' : ''); ?>>Active</option>
                    <option value="inactive" <?php echo (($_POST['status'] ?? '') === 'inactive' ? 'selected' : ''); ?>>Inactive</option>
                </select>
            </div>

            <button type="submit" class="form-button">Add User</button>
        </form>

        <p style="margin-top: 30px;">
            <a href="admin_dashboard.php" class="form-button" style="background-color: #6c757d;">Back to Dashboard</a>
        </p>
    </div>

    <div class="footer">
        &copy; <?php echo date("Y"); ?> SmartCash
    </div>

    <script src="js/scripts.js"></script>

</body>
</html>