<?php
require_once '../../db.php'; // Adjust path if needed
require_once __DIR__ . '/google_calendar_sync.php'; // Keep if you still want Google events

header('Content-Type: application/json');

try {
    $formattedEvents = [];

    /** ✅ 1. Pull events from database **/
    $stmt = $conn->prepare("SELECT event_name, start_datetime, end_datetime FROM events WHERE status = 'approved'");
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $formattedEvents[] = [
            'title' => $row['event_name'],
            'start' => $row['start_datetime'],
            'end'   => $row['end_datetime']
        ];
    }

    /** ✅ 2. (Optional) Merge Google Calendar events **/
    try {
        $googleEvents = getUpcomingEvents(50);
        foreach ($googleEvents as $event) {
            $start = $event->start->dateTime ?? $event->start->date;
            $end   = $event->end->dateTime ?? $event->end->date;

            $formattedEvents[] = [
                'title' => $event->getSummary(),
                'start' => $start,
                'end'   => $end,
                'description' => $event->getDescription() ?? ''
            ];
        }
    } catch (Exception $ge) {
        // Optional: Log or ignore Google errors so your DB events still load
        error_log("Google Calendar fetch error: " . $ge->getMessage());
    }

    echo json_encode($formattedEvents);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
