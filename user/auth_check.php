<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    $_SESSION['error'] = "Access denied.";
    header("Location: Login_User.php");
    exit();
}

// If session already set, nothing else needed
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    list($user_id, $token) = explode(":", $_COOKIE['remember_me'], 2);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['remember_token'] && password_verify($token, $user['remember_token'])) {
        // Restore session
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role']      = $user['role'];
    } else {
        // Invalid cookie → remove it
        setcookie("remember_me", "", time() - 3600, "/");
    }
}
