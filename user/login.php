<?php
session_start();
require 'config.php'; // DB connection (PDO)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $university_id = trim($_POST['university_id'] ?? '');
    $password   = $_POST['password'] ?? '';
    $remember   = isset($_POST['stay_signed_in']);

    // Before each error redirect
    $_SESSION['old_university_id'] = $university_id;

    // Validate inputs
    if ($university_id === '' || $password === '') {
        $_SESSION['error'] = "All fields are required.";
        $_SESSION['old_university_id'] = $university_id;
        header("Location: html/Login_User.php");
        exit();
    }

    try {
        // Find user by ID
        $stmt = $pdo->prepare("SELECT * FROM users WHERE university_id = ? LIMIT 1");
        $stmt->execute([$university_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // User not found
            $_SESSION['error'] = "Invalid Student ID or Password.";
            $_SESSION['old_university_id'] = $university_id;
            header("Location: html/Login_User.php");
            exit();
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            $_SESSION['error'] = "Invalid Student ID or Password.";
            $_SESSION['old_university_id'] = $university_id;
            header("Location: html/Login_User.php");
            exit();
        }

        // Success → set session
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role']      = $user['role'];

        // Stay signed in
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $hashedToken = password_hash($token, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
            $stmt->execute([$hashedToken, $user['id']]);

            // Cookie lasts 30 days, HttpOnly for security
            setcookie("remember_me", $user['id'] . ":" . $token, time() + (86400 * 30), "/", "", false, true);
        }

        // Clear old inputs after success
        unset($_SESSION['old_university_id']);

        // Success message
        $_SESSION['success'] = "Login successful!";
        header("Location: html/Login_User.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error. Please try again later.";
        // In production, log $e->getMessage() instead of showing it
        header("Location: user/html/Login_User.php");
        exit();
    }
}
?>
