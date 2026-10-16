<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        // 1. Update DB
        $stmt = $conn->prepare("UPDATE events SET status = 'approved' WHERE id = ?");
        $stmt->execute([$id]);

        // 2. Fetch event details
        $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$id]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        // 3. Insert into Google Calendar
        require __DIR__ . '/../../vendor/autoload.php';

        $client = new Google_Client();
        $client->setAuthConfig(__DIR__ . '/credentials.json');
        $client->setAccessType('offline');
        $client->addScope(Google_Service_Calendar::CALENDAR);

        $tokenPath = __DIR__ . '/token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);

            // Refresh token if expired
            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                file_put_contents($tokenPath, json_encode($client->getAccessToken()));
            }
        }

        $service = new Google_Service_Calendar($client);

 $gEvent = new Google_Service_Calendar_Event([
    'summary' => $event['event_name'],
    'description' => $event['event_purpose'],
    'location' => $event['room'],
    'start' => [
        'dateTime' => date('c', strtotime($event['start_datetime'])),
        'timeZone' => 'Asia/Manila',
    ],
    'end' => [
        'dateTime' => date('c', strtotime($event['end_datetime'])),
        'timeZone' => 'Asia/Manila',
    ],
]);


       $calendarId = 'primary'; // change if you want a shared calendar

try {
    $createdEvent = $service->events->insert($calendarId, $gEvent);
    echo "✅ Event created in Google Calendar: <a href='" . $createdEvent->htmlLink . "' target='_blank'>View Event</a><br>";
} catch (Exception $e) {
    echo "❌ Google Calendar insert error: " . $e->getMessage();
    exit;
}

    }

    if ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE events SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$id]);
    }
}

header("Location: admin_requests.php");
exit;
