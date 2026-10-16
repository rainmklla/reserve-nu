<?php
session_start();
require 'config.php'; // DB connection (PDO)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = trim($_POST['admin_id'] ?? '');
    $password = $_POST['password'] ?? '';

    // Preserve input
    // $_SESSION['old_admin_id'] = $admin_id;

    // Validate
    if ($admin_id === '' || $password === '') {
        $_SESSION['error'] = "All fields are required.";
        header("Location: html/Login_Admin.php");
        exit();
    }

    try {
        // Fetch admin
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE admin_id = ? LIMIT 1");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin || !password_verify($password, $admin['password'])) {
            $_SESSION['error'] = "Invalid Admin ID or Password.";
            header("Location: html/Login_Admin.php");
            exit();
        }

        // Success → set session
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['full_name']  = $admin['full_name'];
        $_SESSION['role']       = 'admin';

        // Clear old input
        unset($_SESSION['old_admin_id']);

        $_SESSION['success'] = "Login successful!";
        header("Location: html/Login_Admin.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error. Please try again later.";
        header("Location: html/Login_Admin.php");
        exit();
    }
}
?>
