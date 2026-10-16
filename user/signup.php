<?php
// signup.php
session_start();
require 'config.php'; // include DB connection

if (isset($_POST['signup'])) {
    // Get form inputs
    $full_name = trim($_POST['full_name']);
    $university_id = trim($_POST['university_id']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $terms = isset($_POST['terms']); // checkbox

    // Preserve old input (except passwords for security)
    $_SESSION['old_full_name']     = $full_name;
    $_SESSION['old_university_id'] = $university_id;
    $_SESSION['old_pass'] = $password;
    $_SESSION['old_conf_pass'] = $confirm_password;
    $_SESSION['old_terms'] = $terms;

    // Validate inputs
    if (empty($full_name) || empty($university_id) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: html/Signup_User.php");
        exit();
    }

    if (!$terms) {
        $_SESSION['error'] = "You must agree to the Terms and Conditions.";
        header("Location: html/Signup_User.php");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: html/Signup_User.php");
        exit();
    }

    // Strong password validation
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number = preg_match('@[0-9]@', $password);
    $specialChars = preg_match('@[^\w]@', $password);

    if (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long and include an uppercase letter, lowercase letter, number, and special character.";
        header("Location: html/Signup_User.php");
        exit();
    }

    try {
        // Check if University ID already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE university_id = ?");
        $stmt->execute([$university_id]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "This University ID is already registered.";
            header("Location: html/Signup_User.php");
            exit();
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        $stmt = $pdo->prepare("INSERT INTO users (university_id, full_name, password, role) VALUES (?, ?, ?, 'user')");
        $stmt->execute([$university_id, $full_name, $hashed_password]);
        // Auto-login user after successful signup
        $newUserId = $pdo->lastInsertId();
        $_SESSION['user_id'] = $newUserId;
        $_SESSION['full_name'] = $full_name;
        $_SESSION['role'] = 'user';

        // Clear old inputs after success
        unset($_SESSION['old_full_name'], $_SESSION['old_university_id'], $_SESSION['old_pass'], $_SESSION['old_conf_pass'], $_SESSION['old_terms']);

        // Success block in signup.php
        $_SESSION['success'] = "Account created successfully!";
        header("Location: html/Signup_User.php");
        exit();


    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: html/Signup_User.php");
        exit();
    }
}
?>
