<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('db.php');

// Fetch all requests
$stmt = $conn->query("SELECT * FROM events ORDER BY start_datetime ASC");
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Requests</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f4f4f4; }
        .pending { color: orange; font-weight: bold; }
        .approved { color: green; font-weight: bold; }
        .rejected { color: red; font-weight: bold; }
        form { display: inline; }
    </style>
</head>
<body>
    <h1>📅 Admin Event Requests</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Room</th>
            <th>Event</th>
            <th>Purpose</th>
            <th>Attendees</th>
            <th>Start</th>
            <th>End</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php foreach ($requests as $req): ?>
        <tr>
            <td><?= $req['id'] ?></td>
            <td><?= $req['user_id'] ?></td>
            <td><?= htmlspecialchars($req['room']) ?></td>
            <td><?= htmlspecialchars($req['event_name']) ?></td>
            <td><?= htmlspecialchars($req['event_purpose']) ?></td>
            <td><?= $req['attendees'] ?></td>
            <td><?= $req['start_datetime'] ?></td>
            <td><?= $req['end_datetime'] ?></td>
            <td class="<?= $req['status'] ?>"><?= ucfirst($req['status']) ?></td>
            <td>
                <?php if ($req['status'] === 'pending'): ?>
                    <form method="POST" action="process_request.php">
                        <input type="hidden" name="id" value="<?= $req['id'] ?>">
                        <button type="submit" name="action" value="approve">✅ Approve</button>
                        <button type="submit" name="action" value="reject">❌ Reject</button>
                    </form>
                <?php else: ?>
                    <em>No action</em>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
