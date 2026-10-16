<!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Room Availability</title>
    <link rel="stylesheet" href="../css/User_Room_Availability.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php
require_once '../config.php'; 

function formatTimeDisplay($time) {
    return date('g:i A', strtotime($time));
}

function timeToMinutes($time) {
    $parts = explode(':', $time);
    return (int)$parts[0] * 60 + (int)$parts[1];
}

// Check room availability against events table
function isRoomAvailableForSlot($pdo, $room_id, $date, $start_time, $end_time) {
    $room_sql = "SELECT room_name, operating_hours_start, operating_hours_end, 
                        min_booking_duration, max_booking_duration 
                 FROM rooms WHERE room_id = ?";
    try {
        $stmt = $pdo->prepare($room_sql);
        $stmt->execute([$room_id]);
        $room_info = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Room Info Fetch Error: " . $e->getMessage());
        return false;
    }
    
    if (!$room_info) return false;

    $duration_minutes = timeToMinutes($end_time) - timeToMinutes($start_time);
    if ($start_time < $room_info['operating_hours_start'] || $end_time > $room_info['operating_hours_end']) {
        return 'OUTSIDE_HOURS';
    }
    if ($duration_minutes < $room_info['min_booking_duration'] || $duration_minutes > $room_info['max_booking_duration']) {
        return 'DURATION_FAILURE';
    }

    $start_dt = $date . ' ' . $start_time;
    $end_dt   = $date . ' ' . $end_time;

    $check_overlap_sql = "SELECT COUNT(*) FROM events 
                          WHERE room = ? 
                          AND status IN ('approved','pending')
                          AND (start_datetime < ? AND end_datetime > ?)";
    try {
        $stmt = $pdo->prepare($check_overlap_sql);
        $stmt->execute([$room_info['room_name'], $end_dt, $start_dt]);
        $count = $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Overlap Check Error: " . $e->getMessage());
        return false;
    }
    
    if ($count > 0) return 'BOOKED_OVERLAP';
    return true;
}

// Vars
$search_performed = false;
$available_rooms = [];
$error_message = '';
$success_message = '';
$room_search = '';

// Handle search
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_availability'])) {
    $room_search = trim($_POST['room'] ?? '');
    $date = $_POST['date'] ?? '';
    $start_time = $_POST['start-time'] ?? '';
    $end_time = $_POST['end-time'] ?? '';

    if (empty($date)) {
        $error_message = "Please select a date.";
    } elseif (empty($start_time) || empty($end_time)) {
        $error_message = "Please select both start and end times.";
    } elseif ($start_time >= $end_time) {
        $error_message = "End time must be after start time.";
    } else {
        $selected_datetime = new DateTime($date . ' ' . $start_time);
        $current_datetime = new DateTime();
        $hours_difference = ($selected_datetime->getTimestamp() - $current_datetime->getTimestamp()) / 3600;
        if ($hours_difference < 72) {
            $error_message = "Reservations must be made at least 72 hours in advance.";
        } else {
            $duration_minutes = timeToMinutes($end_time) - timeToMinutes($start_time);
            if ($duration_minutes < 120) {
                $error_message = "Minimum reservation duration is 2 hours.";
            } else {
                try {
                    $sql = "SELECT r.* FROM rooms r WHERE r.is_active = 1";
                    $params = [];
                    if (!empty($room_search)) {
                        $sql .= " AND (r.room_name LIKE ? OR r.building LIKE ? OR r.room_type LIKE ?)";
                        $search_param = "%$room_search%";
                        $params = [$search_param, $search_param, $search_param];
                    }
                    $sql .= " ORDER BY r.building, r.room_name";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($rooms as $room) {
                        $room['facilities_array'] = json_decode($room['facilities'] ?? '[]', true) ?? [];
                        $availability_status = isRoomAvailableForSlot($pdo, $room['room_id'], $date, $start_time, $end_time);
                        $room['is_available'] = $availability_status === true;
                        if ($availability_status === 'OUTSIDE_HOURS') {
                            $room['reason'] = "Outside operating hours (" . 
                                date('g:i A', strtotime($room['operating_hours_start'])) . " - " . 
                                date('g:i A', strtotime($room['operating_hours_end'])) . ")";
                        } elseif ($availability_status === 'DURATION_FAILURE') {
                            $room['reason'] = "Requested duration not allowed.";
                        } elseif ($availability_status === 'BOOKED_OVERLAP') {
                            $room['reason'] = "Booked during requested time.";
                        } else {
                            $room['reason'] = "Available.";
                        }
                        $available_rooms[] = $room;
                    }
                    $search_performed = true;
                } catch (PDOException $e) {
                    error_log("Search Database Error: " . $e->getMessage()); 
                    $error_message = "A system error occurred during the search.";
                    $search_performed = true; 
                }
            }
        }
    }
}

// Insert event request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_room'])) {
    $room_id = $_POST['room_id'];
    $date = $_POST['reservation_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    try {
        $stmt = $pdo->prepare("SELECT room_name FROM rooms WHERE room_id = ?");
        $stmt->execute([$room_id]);
        $room_name = $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Room Name Fetch Error: " . $e->getMessage());
        $room_name = null;
    }

    $start_dt = $date . ' ' . $start_time;
    $end_dt   = $date . ' ' . $end_time;

    $event_title = $_POST['event_title'] ?? "Room Reservation";
    $purpose = $_POST['purpose'] ?? "General Use";
    $user_id = 1; // TODO: replace with session
    $attendees = 1;

    try {
        $availability_status = isRoomAvailableForSlot($pdo, $room_id, $date, $start_time, $end_time);
        if ($availability_status === true) {
            $sql = "INSERT INTO events (user_id, room, event_name, event_purpose, attendees, start_datetime, end_datetime, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id, $room_name, $event_title, $purpose, $attendees, $start_dt, $end_dt]);
            
            header("Location: User_Room_Availability.php?success=" . urlencode("Event request submitted successfully!"));
            exit;
        } else {
            $error_message_redirect = "Room not available. Reason: " . $availability_status;
            header("Location: User_Room_Availability.php?error=" . urlencode($error_message_redirect)); 
            exit;
        }
    } catch (PDOException $e) {
        error_log("Event Submission Error: " . $e->getMessage()); 
        header("Location: User_Room_Availability.php?error=" . urlencode("Error submitting request.")); 
        exit;
    }
}

if (isset($_GET['success'])) $success_message = htmlspecialchars($_GET['success']);
elseif (isset($_GET['error'])) $error_message = htmlspecialchars($_GET['error']);

$all_rooms = [];
try {
    $sql = "SELECT room_name, building FROM rooms WHERE is_active = 1 ORDER BY room_name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $all_rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Autocomplete Fetch Error: " . $e->getMessage());
}
?>
    <div class="navbar">
        <div class="trademark">
            <h3>
                <img src="../css/images/reserveNU_icon.png" alt="ReserveNU">ReserveNU
            </h3>
        </div>
        <div class="navigationButtons">
            <a href="dashboard.php"><span class="icon-container"><img src="../css/images/dashboard.png" alt="Dashboard Icon"></span>Dashboard</a>
            <a href="User_Event_Calendar.php"><span class="icon-container"><img src="../css/images/event-calendar.png" alt="Calendar Icon"></span>Event Calendar</a>
            <a href="User_Room_Availability.php" id="avail"><span class="icon-container"><img src="../css/images/room-availability.png" alt="Request Icon"></span>Room Availability</a>
            <a href="User_Request_Status.php"><span class="icon-container"><img src="../css/images/your-requests.png" alt="Your Requests"></span>Your Requests</a>
        </div>
        <div class="logOutButton">
            <a href="../logout.php"><span class="icon-container"><img src="../css/images/logout.png" alt="Logout Icon"></span>Log Out</a>
        </div>
    </div>

    <div class="main-content">
        <?php if ($error_message && isset($_GET['error'])): ?>
            <div class="error-message"><i class="fa fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <?php if ($success_message && isset($_GET['success'])): ?>
            <div class="success-message"><i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <div class="main-box">
            <div class="availability-box">
                <div class="guidelines-box">
                    <div class="guidelines-title"><i class="fa fa-info-circle"></i> Booking Guidelines</div>
                    <div class="guideline-item"><i class="fa fa-clock" style="color: #17a2b8;"></i> Minimum booking duration: 2 hours</div>
                    <div class="guideline-item"><i class="fa fa-calendar-alt" style="color: #17a2b8;"></i> Reservations must be made at least 72 hours in advance</div>
                    <div class="guideline-item"><i class="fa fa-business-time" style="color: #17a2b8;"></i> Check room operating hours before booking</div>
                </div>

                <form method="POST" action="" id="availabilityForm">
                    <label for="room">Room</label>
                    <div class="form-row">
                        <div class="input-with-validation">
                            <div class="input-icon">
                                <input type="text" id="room" name="room" placeholder="Search by room name, building, or type..." 
                                       value="<?php echo htmlspecialchars($room_search); ?>" autocomplete="off">
                                <i class="fa fa-search" aria-hidden="true"></i>
                                <div id="room-suggestions" class="room-search-suggestions" style="display: none;"></div>
                            </div>
                        </div>
                    </div>
                    
                    <label for="date">Date</label>
                    <div class="form-row">
                        <div class="input-with-validation">
                            <div class="input-icon">
                                <input type="date" id="date" name="date" class="date-input" 
                                       value="<?php echo htmlspecialchars($_POST['date'] ?? date('Y-m-d', strtotime('+3 days'))); ?>" 
                                       min="<?php echo date('Y-m-d', strtotime('+3 days')); ?>">
                            </div>
                            <div class="validation-message" id="dateValidation"></div> 
                        </div>
                    </div>
                    
                    <label for="start-time">Time</label>
                    <div class="form-row">
                        <div class="time-group">
                            <div class="input-with-validation" style="flex: 1;">
                                <div class="input-icon">
                                    <input type="time" id="start-time" name="start-time" class="time-input"
                                           value="<?php echo htmlspecialchars($_POST['start-time'] ?? '08:00'); ?>">
                                </div>
                            </div>
                            <span style="font-size: 20px; font-weight: bold;">-</span>
                            <div class="input-with-validation" style="flex: 1;">
                                <div class="input-icon">
                                    <input type="time" id="end-time" name="end-time" class="time-input"
                                           value="<?php echo htmlspecialchars($_POST['end-time'] ?? '10:00'); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="validation-message" id="timeValidation"></div> 
                    </div>
                    
                    <div style="grid-column: 1 / -1; width: 100%;">
                        <button type="submit" name="check_availability" class="check-btn" id="submitBtn">
                             Check Availability
                        </button>
                    </div>
                </form>
            </div>

            <div class="results-section">
                <h3>Results:</h3>
                <?php if ($search_performed): ?>
                    <?php if (empty($available_rooms)): ?>
                        <div class="no-results"><i class="fa fa-search"></i><br><?php echo !empty($error_message) ? htmlspecialchars($error_message) : "No rooms found."; ?></div>
                    <?php else: ?>
                        <?php foreach ($available_rooms as $room): ?>
                            <div class="result-box">
                                <div class="room-info">
                                    <div class="room-name"><?php echo htmlspecialchars($room['room_name']); ?></div>
                                    <div class="room-details">
                                        <i class="fa fa-building"></i> <?php echo htmlspecialchars($room['building']); ?> - Floor <?php echo $room['floor_number']; ?> | 
                                        <i class="fa fa-users"></i> Capacity: <?php echo $room['capacity']; ?> | 
                                        <i class="fa fa-tag"></i> <?php echo htmlspecialchars($room['room_type']); ?>
                                    </div>
                                    <div class="room-facilities">
                                        <i class="fa fa-cogs"></i> Facilities: 
                                        <?php $facilities = $room['facilities_array'] ?? [];
                                        echo !empty($facilities) ? implode(', ', array_map('ucfirst', str_replace('_', ' ', $facilities))) : 'Basic facilities'; ?>
                                    </div> 
                                    <div class="room-facilities">
                                        <i class="fa fa-clock"></i> Operating Hours: 
                                        <?php echo date('g:i A', strtotime($room['operating_hours_start'])) . ' - ' . date('g:i A', strtotime($room['operating_hours_end'])); ?>
                                        | Max Duration: <?php echo $room['max_booking_duration']; ?> mins
                                    </div>
                                    <div style="margin-top: 10px; font-size: 13px; font-weight: bold; color: <?php echo $room['is_available'] ? '#28a745' : '#dc3545'; ?>;">
                                        <i class="fa fa-<?php echo $room['is_available'] ? 'check-circle' : 'times-circle'; ?>"></i>
                                        Status for <?php echo formatTimeDisplay($start_time) . ' - ' . formatTimeDisplay($end_time); ?>: 
                                        <?php echo $room['reason']; ?>
                                    </div>
                                </div>
                                 
                                    <?php if ($room['is_available']): ?> 
                                        <a href="User_Form.php?room_id=<?php echo urlencode($room['room_id']); ?>&room_name=<?php echo urlencode($room['room_name']); ?>&date=<?php echo urlencode($date); ?>&start_time=<?php echo urlencode($start_time); ?>&end_time=<?php echo urlencode($end_time); ?>" 
                                        class="submit-btn" 
                                        style="display: inline-block; text-decoration: none; text-align: center; padding: 10px 20px;">
                                            <i class="fa fa-calendar-plus"></i> Submit Request
                                        </a>
                                    <?php else: ?>
                                        <button class="submit-btn" disabled><i class="fa fa-times-circle"></i> Not Available</button>
                                    <?php endif; ?>
                            </div> 
                        <?php endforeach; ?> 
                    <?php endif; ?> 
                <?php else: ?> 
                    <div class="no-results"><i class="fa-solid fa-door-open"></i><br><br>Enter your search criteria above to check room availability.</div>
                <?php endif; ?> 
            </div> 
        </div> 
    </div> 

    <script>
        const dateInput = document.getElementById('date');
        const startTimeInput = document.getElementById('start-time');
        const endTimeInput = document.getElementById('end-time');
        const submitBtn = document.getElementById('submitBtn');

        // Helper function to render suggestions
        function renderSuggestions(matches) {
            const suggestionsDiv = document.getElementById('room-suggestions');
            if (matches.length === 0) {
                suggestionsDiv.innerHTML = '<div class="search-suggestion" style="color:#999;">No suggestions</div>';
            } else {
                suggestionsDiv.innerHTML = matches.map(r => 
                    `<div class="search-suggestion" data-room="${r.room_name}"><strong>${r.room_name}</strong><br><small>${r.building}</small></div>`
                ).join('');
            }
            suggestionsDiv.style.display = 'block';
        }

        function validateDate() {
          const selectedDate = new Date(dateInput.value);
          const today = new Date();
          const minDate = new Date(today.getTime() + (72 * 60 * 60 * 1000));
          const dateValidation = document.getElementById('dateValidation');
          if (!dateInput.value) { dateValidation.style.display='none'; return false; }
          if (selectedDate.getTime() < minDate.getTime()) {
            dateValidation.className='validation-message validation-error';
            dateValidation.innerHTML='<i class="fa fa-exclamation-circle"></i> Date must be at least 72 hours in advance.';
            dateValidation.style.display='block'; return false;
          }
          dateValidation.className='validation-message validation-success';
          dateValidation.innerHTML='<i class="fa fa-check-circle"></i> Date is valid.';
          dateValidation.style.display='block'; return true;
        }
        function validateTime() {
          const start=new Date('2000-01-01 '+startTimeInput.value);
          const end=new Date('2000-01-01 '+endTimeInput.value);
          const duration=(end-start)/60000;
          const timeValidation=document.getElementById('timeValidation');
          if (!startTimeInput.value||!endTimeInput.value) {timeValidation.style.display='none'; return false;}
          if (end<=start){timeValidation.className='validation-message validation-error';timeValidation.innerHTML='<i class="fa fa-exclamation-circle"></i> End time must be after start.';timeValidation.style.display='block';return false;}
          if (duration<120){timeValidation.className='validation-message validation-error';timeValidation.innerHTML='<i class="fa fa-exclamation-circle"></i> Minimum 2 hours.';timeValidation.style.display='block';return false;}
          timeValidation.className='validation-message validation-success';timeValidation.innerHTML='<i class="fa fa-check-circle"></i> Duration valid.';timeValidation.style.display='block';return true;
        }
        function updateSubmitButton(){ if(validateDate()&&validateTime()){submitBtn.disabled=false;}else{submitBtn.disabled=true;}}
        dateInput.addEventListener('change',updateSubmitButton);
        startTimeInput.addEventListener('change',updateSubmitButton);
        endTimeInput.addEventListener('change',updateSubmitButton);
        document.getElementById('availabilityForm').addEventListener('submit',function(e){if(!validateDate()||!validateTime()){e.preventDefault();}});

        const roomInput = document.getElementById('room');
        const suggestionsDiv = document.getElementById('room-suggestions');
        const allRooms = <?php echo json_encode($all_rooms); ?>;

        // *** MODIFICATION START ***

        // 1. Show all rooms when the input is focused (clicked/tapped)
        roomInput.addEventListener('focus', function() {
            // Show up to 10 rooms when focused
            const initialMatches = allRooms.slice(0, 10); 
            renderSuggestions(initialMatches);
        });

        // 2. Filter rooms when the user types
        roomInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            if (query.length === 0) {
                // If query is empty, show the initial list (up to 10 rooms)
                const initialMatches = allRooms.slice(0, 10); 
                renderSuggestions(initialMatches);
                return;
            }
            // Filter by query (name or building) and limit to 10 results
            const matches = allRooms.filter(r => 
                r.room_name.toLowerCase().includes(query) || 
                r.building.toLowerCase().includes(query)
            ).slice(0, 10); 
            renderSuggestions(matches);
        });
        
        // Use suggestion
        suggestionsDiv.addEventListener('click',function(e){
            const suggestion = e.target.closest('.search-suggestion');
            if (suggestion && suggestion.dataset.room) {
                roomInput.value = suggestion.dataset.room;
                suggestionsDiv.style.display = 'none';
            }
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click',function(e){
            if (!roomInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
                suggestionsDiv.style.display = 'none';
            }
        });
        
        // *** MODIFICATION END ***
        
        window.onload=function(){updateSubmitButton();};
    </script>
</body>
</html>