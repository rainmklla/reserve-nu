<?php
session_start();

// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: Login_User.php");
    exit;
}

// DB connection
require_once '../config.php';

/**
 * Get upcoming events for this user (1 to 2 day reminder).
 * Events must be between 1 day and 2 days from now, and be 'approved' or 'pending'.
 *
 * @param PDO $pdo The PDO database connection object.
 * @param int $userId The ID of the user.
 * @param int $limit The maximum number of events to fetch.
 * @return array An array of upcoming event records.
 */
function getUserUpcomingEvents($pdo, $userId, $limit = 3) {
    // Current time + 1 day (start of the reminder window)
    $tomorrow = date('Y-m-d H:i:s', strtotime('+1 day'));
    // Current time + 2 days (end of the reminder window)
    $dayAfterTomorrow = date('Y-m-d H:i:s', strtotime('+2 days'));

    $sql = "SELECT event_name, start_datetime, end_datetime, room
            FROM events
            WHERE status IN ('approved', 'pending')
              AND user_id = :user_id
              AND start_datetime >= :tomorrow_start
              AND start_datetime < :day_after_tomorrow_end
            ORDER BY start_datetime ASC
            LIMIT :limit";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':tomorrow_start', $tomorrow, PDO::PARAM_STR);
    $stmt->bindValue(':day_after_tomorrow_end', $dayAfterTomorrow, PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get user's recent status updates (approved or rejected).
 * Uses created_at as the updated_at timestamp for status changes.
 *
 * @param PDO $pdo The PDO database connection object.
 * @param int $userId The ID of the user.
 * @param int $limit The maximum number of updates to fetch.
 * @return array An array of recent status update records.
 */
function getRecentStatusUpdates($pdo, $userId, $limit = 3) {
    $sql = "SELECT event_name, status, created_at AS updated_at
            FROM events
            WHERE user_id = :user_id
              AND status IN ('approved', 'rejected')
            ORDER BY created_at DESC
            LIMIT :limit";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// --- DATA FETCHING ---
// Explicitly cast $_SESSION['user_id'] to (int) for security
$userId = (int)($_SESSION['user_id'] ?? 0);

// Fetch data
$upcomingEvents = getUserUpcomingEvents($pdo, $userId, 3);
$statusUpdates = getRecentStatusUpdates($pdo, $userId, 3);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="../css/User_Dashboard.css">
</head>
<body>
    <div class="navbar">
        <div class="trademark">
            <h3><img src="../css/images/reserveNU_icon.png" alt="ReserveNU">ReserveNU</h3>
        </div>
        <div class="navigationButtons">
            <a href="dashboard.php">
                <span class="icon-container">
                    <img src="../css/images/dashboard.png" alt="Dashboard Icon">
                </span>
                Dashboard
            </a>
            <a href="User_Event_Calendar.php">
                <span class="icon-container">
                    <img src="../css/images/event-calendar.png" alt="Calendar Icon">
                </span>
                Event Calendar
            </a>
            <a href="User_Room_Availability.php" id="avail">
                <span class="icon-container">
                    <img src="../css/images/room-availability.png" alt="Request Icon">
                </span>
                Room Availability
            </a>
            <a href="User_Request_Status.php">
                <span class="icon-container">
                    <img src="../css/images/your-requests.png" alt="Your Requests">
                </span>
                Your Requests
            </a>
        </div>
        <div class="logOutButton">
            <a href="../logout.php">
                <span class="icon-container">
                    <img src="../css/images/logout.png" alt="Logout Icon">
                </span>
                Log Out
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="main-box">
            <div class="header">
                <h1>Welcome, <?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'User'; ?>!</h1>
            </div>
            <div class="content-container">
                <h3>Upcoming Events:</h3>
                <ul>
                <?php if (empty($upcomingEvents)): ?>
                    <li>No upcoming events.</li>
                <?php else: ?>
                    <?php foreach ($upcomingEvents as $event): ?>
                        <li>
                            <?php echo date('d/m/Y, h:i A', strtotime($event['start_datetime'])); ?> 
                            to <?php echo date('h:i A', strtotime($event['end_datetime'])); ?>, 
                            Room <?php echo htmlspecialchars($event['room']); ?>: 
                            <?php echo htmlspecialchars($event['event_name']); ?> <a href="User_Event_Calendar.php">See on Calendar.</a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
                </ul>

                <h3>Status Updates:</h3>
                <ul>
                    <?php if (empty($statusUpdates)): ?>
                        <li>No recent status updates for your requests.</li>
                    <?php else: ?>
                        <?php foreach ($statusUpdates as $update): ?>
                            <li>
                                <?php echo date('M d', strtotime($update['updated_at'])); ?>: 
                                Your request for <strong><?php echo htmlspecialchars($update['event_name']); ?></strong> was <strong><?php echo htmlspecialchars(strtoupper($update['status'])); ?></strong>. 
                                <a href="User_Request_Status.php">View Your Requests.</a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    
</body>
</html>