<?php
session_start();
require_once '../config.php';
/*
// Example: Assuming the logged-in user ID is stored in session
if (!isset($_SESSION['user_id'])) {
    header("Location: User_Login.php");
    exit;
}
*/
$user_id = $_SESSION['user_id'];


// Fetch user requests from the database
try {
    $stmt = $pdo->prepare("SELECT id, room, event_name, start_datetime, end_datetime, status 
                            FROM events 
                            WHERE user_id = :user_id 
                            ORDER BY start_datetime DESC");
    $stmt->execute([':user_id' => $user_id]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Request Status</title>
    <link rel="stylesheet" href="../css/User_Request_Status.css">
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
            <a href="User_Request_Status.php" class="active">
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

    <div class="request-status-container">
        <div class="request-status-card">
            <h2>Request Status</h2>
            <div class="status-tabs">
                <button class="tab active">All</button>
                <button class="tab">Pending</button>
                <button class="tab">Approved</button>
                <button class="tab">Declined</button>
            </div>

            <div class="request-list">
                <?php if (empty($requests)): ?>
                    <p style="text-align:center; color: gray; margin-top: 20px;">No requests found.</p>
                <?php else: ?>
                    <?php foreach ($requests as $req): ?>
                        <?php
                            $status = strtolower($req['status']);
                            $status_label = ucfirst($status);
                            $start = date('M d, Y h:i A', strtotime($req['start_datetime']));
                            $end = date('M d, Y h:i A', strtotime($req['end_datetime']));
                        ?>
                        <div class="request-row" data-status="<?= htmlspecialchars($status) ?>">
                            <span>
                                <strong><?= htmlspecialchars($req['event_name']) ?></strong><br>
                                <?= htmlspecialchars($req['room']) ?><br>
                                <small><?= $start ?> - <?= $end ?></small>
                            </span>
                            <span class="status-badge <?= htmlspecialchars($status) ?>">
                                <?= htmlspecialchars($status_label) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.status-tabs .tab');
        const rows = document.querySelectorAll('.request-list .request-row');

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                const filter = this.textContent.trim().toLowerCase();

                rows.forEach(row => {
                    const status = row.getAttribute('data-status');
                    if (filter === 'all' || status === filter) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    });
    </script>
</body>
</html>
