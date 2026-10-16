<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id       = trim($_POST['student_id'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Preserve student ID for repopulating form if something goes wrong
    $_SESSION['old_student_id'] = $student_id;

    // Validate inputs
    if ($student_id === '' || $password === '' || $confirm_password === '') {
        $_SESSION['error'] = "All fields are required.";
        header("Location: html/Forgot_Pass_User.php");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: html/Forgot_Pass_User.php");
        exit();
    }

    // Strong password validation
    $uppercase    = preg_match('@[A-Z]@', $password);
    $lowercase    = preg_match('@[a-z]@', $password);
    $number       = preg_match('@[0-9]@', $password);
    $specialChars = preg_match('@[^\w]@', $password);

    if (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.";
        header("Location: html/Forgot_Pass_User.php");
        exit();
    }

    try {
        // Check if Student ID exists
$stmt = $pdo->prepare("SELECT id, password FROM users WHERE university_id = ? LIMIT 1");
$stmt->execute([$student_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error'] = "No account found with that Student ID.";
    header("Location: html/Forgot_Pass_User.php");
    exit();
}

// Check if new password is the same as old
if (password_verify($password, $user['password'])) {
    $_SESSION['error'] = "New password cannot be the same as current.";
    header("Location: html/Forgot_Pass_User.php");
    exit();
}

// Update password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE university_id = ?");
$stmt->execute([$hashedPassword, $student_id]);

        // Clear old input after success
        unset($_SESSION['old_student_id']);

        $_SESSION['success'] = "Password has been reset successfully.";
        header("Location: html/Forgot_Pass_User.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error. Please try again later.";
        header("Location: html/Forgot_Pass_User.php");
        exit();
    }
}
?>
