<?php
session_start();
require 'config.php';

// If logged in, remove remember token from DB
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
}

// Destroy session
session_unset();
session_destroy();

// Remove cookie
if (isset($_COOKIE['remember_me'])) {
    setcookie("remember_me", "", time() - 3600, "/");
}

header("Location: html/Login_User.php");
exit();
?>
