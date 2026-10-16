<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
   
    header("Location: Login_Admin.php");
    exit;
}

// Database 
require_once '../config.php'; 

// --- FUNCTIONS ---

// Get upcoming approved reservations
function getUpcomingReservations($pdo, $limit = 3) {
    $sql = "SELECT event_name, start_datetime, end_datetime, room
            FROM events
            WHERE status = 'approved' AND start_datetime >= NOW()
            ORDER BY start_datetime ASC
            LIMIT :limit";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get recently created events (updates)
function getRecentCalendarUpdatesFromDB($pdo, $limit = 3) {
    $sql = "SELECT event_name, created_at, status
            FROM events
            ORDER BY created_at DESC
            LIMIT :limit";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get pending requests count
function getPendingRequestsCount($pdo) {
    $sql = "SELECT COUNT(*) AS total FROM events WHERE status = 'pending'";
    $stmt = $pdo->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['total'];
}

// --- DATA FETCHING ---
$upcomingReservations = getUpcomingReservations($pdo, 3);
$calendarUpdates = getRecentCalendarUpdatesFromDB($pdo, 3);
$pendingRequests = getPendingRequestsCount($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Dashboard</title>
    <link rel="stylesheet" href="../css/Admin_Dashboard.css">
</head>
<body>
<div class="navbar">
    <div class="trademark">
        <h3><img src="../images/reserveNU_icon.png" alt="ReserveNU">ReserveNU</h3>
    </div>
    <div class="navigationButtons">
        <a href="admin_dashboard.php">
            <img src="../images/dashboard.png" alt="Dashboard Icon">Dashboard
        </a>
        <a href="Admin_Event_Calendar.php">
            <img src="../images/event-calendar.png" alt="Calendar Icon">Event Calendar
        </a>
        <a href="Admin_Request.php">
            <img src="../images/requests.png" alt="Request Icon">Requests
        </a>
        <a href="Admin_History.php">
            <img src="../images/history.png" alt="History Icon">History
        </a>
    </div>
    <div class="logOutButton">
        <a href="../logout_ad.php">
            <img src="../images/logout.png" alt="Logout Icon">Log Out
        </a>
    </div>
</div>

    <div class="main-content">
        <div class="main-box">
            <div class="header">
                <h1>Welcome, <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?>!</h1>


            </div>

            <div class="content-container">
                <!-- Upcoming Reservations -->
                <h3>Upcoming Reservation/s:</h3>
                <ul>
                    <?php if (empty($upcomingReservations)): ?>
                        <li>No upcoming reservations.</li>
                    <?php else: ?>
                        <?php foreach ($upcomingReservations as $event): ?>
                            <li>
                                <?= date('d/m/Y, h:i A', strtotime($event['start_datetime'])) ?> 
                                to <?= date('h:i A', strtotime($event['end_datetime'])) ?>, 
                                Room <?= htmlspecialchars($event['room']) ?> — 
                                <?= htmlspecialchars($event['event_name']) ?>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>

               
                <h3>Calendar Updates:</h3>
                <ul>
                    <?php if (empty($calendarUpdates)): ?>
                        <li>No recent updates.</li>
                    <?php else: ?>
                        <?php foreach ($calendarUpdates as $update): ?>
                            <li>
                                <?= date('M d', strtotime($update['created_at'])) ?> — 
                                "<?= htmlspecialchars($update['event_name']) ?>" 
                                was <?= htmlspecialchars($update['status']) ?>.
                                <a href="Admin_Event_Calendar.php">See details.</a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>

              <h3>Request/s:</h3>
            <ul>
                 <li>
                    There is/are <?= $pendingRequests ?> pending request/s. 
                    <a href="Admin_Request.php">See details.</a>
                </li>
            </ul>

            </div>
        </div>
    </div>
</body>
</html>
