<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/google_calendar_sync.php';

// Get first and last day of current month
$startOfMonth = date('Y-m-01T00:00:00P');
$endOfMonth = date('Y-m-tT23:59:59P');

$events = getUpcomingEvents(null, $startOfMonth, $endOfMonth);

$formatted = [];
foreach ($events as $event) {
    $start = $event->start->dateTime ?? $event->start->date;
    $end = $event->end->dateTime ?? $event->end->date;

    $formatted[] = [
        'title' => $event->getSummary(),
        'start' => $start,
        'end'   => $end,
        'description' => $event->getDescription()
    ];
}

header('Content-Type: application/json');
echo json_encode($formatted);
