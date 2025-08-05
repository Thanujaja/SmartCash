<?php
require_once __DIR__ . '/db_connect.php'; // Use your existing db_connect.php

$admin_username = 'admin';
$admin_password = '123456'; // The initial password you want to set for 'admin'
$admin_password_hash = password_hash($admin_password, PASSWORD_DEFAULT);

try {
    // Check if admin already exists to prevent duplicate entries
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = :username");
    $stmt->execute(['username' => $admin_username]);
    if ($stmt->fetchColumn() > 0) {
        echo "Admin user '{$admin_username}' already exists. No new user created.<br>";
    } else {
        // Insert the admin user into the admins table
        $stmt = $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (:username, :password_hash)");
        $stmt->execute([
            'username' => $admin_username,
            'password_hash' => $admin_password_hash
        ]);
        echo "Admin user '{$admin_username}' created successfully with initial password '{$admin_password}'.<br>";
        echo "<strong>IMPORTANT: DELETE THIS SCRIPT ('create_initial_admin.php') FROM YOUR SERVER IMMEDIATELY AFTER RUNNING IT!</strong>";
    }
} catch (PDOException $e) {
    die("Error creating admin user: " . $e->getMessage());
}
?>