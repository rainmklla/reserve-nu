<?php
// Database Connection Configuration

$host = "localhost";      // Database host
$dbname = "reservenu_db";   // Database name
$username = "root";       // Database username (default in XAMPP)
$password = "";           // Database password (default in XAMPP is empty)

try {
    // Create a PDO instance (DB connection)
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

    // Set error reporting
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    
    die("Database connection failed: " . $e->getMessage());
}
?>
