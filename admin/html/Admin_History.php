<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: Login_Admin.php");
    exit;
}

include('../config.php'); 

// Fetch all events, newest first
$stmt = $pdo->prepare("SELECT * FROM events ORDER BY start_datetime DESC");
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

$recentEvents = [];
$earlierEvents = [];
$now = new DateTime();
$lastApprovedEvent = null;

// Find the last approved event (by latest datetime)
foreach ($events as $index => $event) {
    if ($event['status'] === 'approved') {
        $lastApprovedEvent = $event;
        unset($events[$index]); // Remove it from the main list
        break; // We only want the latest one
    }
}

// Separate remaining events into recent/earlier
foreach ($events as $event) {
    $eventDate = new DateTime($event['start_datetime']);
    $daysDiff = $now->diff($eventDate)->days;

    if ($daysDiff <= 7) {
        $recentEvents[] = $event;
    } else {
        $earlierEvents[] = $event;
    }
}

// Sort recent and earlier (newest first)
usort($recentEvents, function($a, $b) {
    return strtotime($b['start_datetime']) - strtotime($a['start_datetime']);
});
usort($earlierEvents, function($a, $b) {
    return strtotime($b['start_datetime']) - strtotime($a['start_datetime']);
});

// Add last approved event to the top of recent
if ($lastApprovedEvent) {
    array_unshift($recentEvents, $lastApprovedEvent);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin | Event History</title>
  <link rel="stylesheet" href="../css/Admin_History.css">
</head>
<body>

<!-- NAVIGATION BAR -->
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
        <a href="Admin_History.php" class="active">
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
            <h2>Reservation History</h2>
        </div>
        <div class="filters">
            <button class="filter-btn" onclick="filterHistory('all')">All</button>
            <button class="filter-btn" onclick="filterHistory('approved')">Approved</button>
            <button class="filter-btn" onclick="filterHistory('rejected')">Rejected</button>
        </div>

        <!-- RECENT -->
        <div class="recent-history">
            <h3>Recent</h3>
            <ul>
                <?php if (empty($recentEvents)): ?>
                    <li>No recent reservations.</li>
                <?php else: ?>
                    <?php foreach ($recentEvents as $event): ?>
                        <li class="reservation-item <?= strtolower($event['status']) ?>">
                            <span>
                                <?= strtoupper($event['status']) ?>: Room Reservation for 
                                <?= htmlspecialchars($event['room']) ?> 
                                on <?= date("F j, Y", strtotime($event['start_datetime'])) ?>
                            </span>
                            <a href="History_Details.php?id=<?= $event['id'] ?>" class="details-btn">See Details</a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>

        <!-- EARLIER -->
        <div class="earlier-history">
            <h3>Earlier</h3>
            <ul>
                <?php if (empty($earlierEvents)): ?>
                    <li>No earlier reservations.</li>
                <?php else: ?>
                    <?php foreach ($earlierEvents as $event): ?>
                        <li class="reservation-item <?= strtolower($event['status']) ?>">
                            <span>
                                <?= strtoupper($event['status']) ?>: Room Reservation for 
                                <?= htmlspecialchars($event['room']) ?> 
                                on <?= date("F j, Y", strtotime($event['start_datetime'])) ?>
                            </span>
                            <a href="History_Details.php?id=<?= $event['id'] ?>" class="details-btn">See Details</a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<script>
// Filter Approved/Rejected
function filterHistory(filter) {
    const items = document.querySelectorAll('.reservation-item');
    items.forEach(item => {
        if (filter === 'all') {
            item.style.display = 'flex';
        } else {
            item.style.display = item.classList.contains(filter) ? 'flex' : 'none';
        }
    });
}
</script>

</body>
</html>
