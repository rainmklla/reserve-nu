<?php
session_start();
require 'config.php';

// Remove remember me token if set
if (isset($_COOKIE['remember_admin'])) {
    list($admin_id, $token) = explode(":", $_COOKIE['remember_admin'], 2);

    $stmt = $pdo->prepare("UPDATE admins SET remember_token = NULL WHERE id = ?");
    $stmt->execute([$admin_id]);

    setcookie("remember_admin", "", time() - 3600, "/");
}

// Destroy session
session_unset();
session_destroy();

// Redirect back to admin login
header("Location: html/Login_Admin.php");
exit();
?>