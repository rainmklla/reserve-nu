<?php
require '../auth_check.php'; // session and auth check

$full_name = $_SESSION['full_name'];
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7f9;
            margin: 0;
            padding: 0;
        }
        .dashboard-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        h1 {
            color: #2e7d32;
        }
        p {
            font-size: 18px;
            color: #333;
        }
        a.logout-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 18px;
            background: #d32f2f;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        a.logout-btn:hover {
            background: #b71c1c;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>Welcome, <?php echo "{$full_name}"; ?></h1>
        <p>You are logged in as <b><?php echo ucfirst($role); ?></b>.</p>

        <p>This is your dashboard. From here, you’ll be able to:</p>
        <ul style="text-align:left; display:inline-block;">
            <li>View available rooms</li>
            <li>Request reservations</li>
            <li>See your booking history</li>
        </ul>
        <br>
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>
</body>
</html>
