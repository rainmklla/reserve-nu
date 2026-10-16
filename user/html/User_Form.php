<?php
include('../config.php'); 
session_start(); 

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: Login_User.php');
    exit();
}

// Get the logged-in user's ID from the session
$user_id = $_SESSION['user_id'];

$success = null; // To track if form submission succeeded
$error_message = null; // To store error messages

// Prefill form fields from URL parameters (if any)
$prefill_room = isset($_GET['room_name']) ? $_GET['room_name'] : '';
$prefill_date = isset($_GET['date']) ? $_GET['date'] : '';
$prefill_start_time = isset($_GET['start_time']) ? $_GET['start_time'] : '';
$prefill_end_time = isset($_GET['end_time']) ? $_GET['end_time'] : '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room = $_POST['room'];
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $event_name = $_POST['event_name'];
    $event_purpose = $_POST['event_purpose'];
    $attendees = $_POST['attendees'];

    // Validate attendees count
    if (!is_numeric($attendees) || $attendees <= 0) {
        $error_message = "Number of attendees must be greater than 0.";
        $success = false;
    } else {
        // Optional note field
        $note = $_POST['note'] ?? '';

        // Combine date and time inputs into datetime format
        $start_datetime = $date . ' ' . $start_time;
        $end_datetime = $date . ' ' . $end_time;

        // Handle multiple file uploads
        $file_paths = [];
        if (!empty($_FILES['files']['name'][0])) {
            $target_dir = dirname(__DIR__, 2) . '/uploads/'; // Directory for uploaded files
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true); // Create directory if it does not exist
            }

            // Loop through each uploaded file
            foreach ($_FILES['files']['name'] as $key => $name) {
                if ($_FILES['files']['error'][$key] === 0) {
                    $file_tmp = $_FILES['files']['tmp_name'][$key];
                    $safe_name = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $name); // Sanitize filename
                    $target_path = $target_dir . $safe_name;

                    // Move uploaded file to target directory
                    if (move_uploaded_file($file_tmp, $target_path)) {
                        $file_paths[] = 'uploads/' . $safe_name;
                    }
                }
            }
        }

        // Convert file paths into a single comma-separated string
        $file_path = !empty($file_paths) ? implode(',', $file_paths) : null;

        // Insert reservation details into the database
        try {
            $stmt = $pdo->prepare("INSERT INTO events 
                (user_id, room, event_name, event_purpose, attendees, file_path, note, start_datetime, end_datetime, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            
            // Execute query with parameter binding
            $success = $stmt->execute([
                $user_id,
                $room,
                $event_name,
                $event_purpose,
                $attendees,
                $file_path,
                $note,
                $start_datetime,
                $end_datetime
            ]);
        } catch (PDOException $e) {
            // Catch database errors
            $success = false;
            $error_message = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Request Form</title>
    <link rel="stylesheet" href="../css/User_Form.css"> <!-- Link to external CSS -->
</head>

<body>
    <!-- Navigation bar section -->
    <div class="navbar">
        <div class="trademark">
            <h3><img src="../css/images/reserveNU_icon.png" alt="ReserveNU">ReserveNU</h3>
        </div>
        <div class="navigationButtons">
            <!-- Navigation links -->
            <a href="dashboard.php"><span class="icon-container"><img src="../css/images/dashboard.png" alt="Dashboard Icon"></span>Dashboard</a>
            <a href="User_Event_Calendar.php"><span class="icon-container"><img src="../css/images/event-calendar.png" alt="Calendar Icon"></span>Event Calendar</a>
            <a href="User_Room_Availability.php" id="avail"><span class="icon-container"><img src="../css/images/room-availability.png" alt="Request Icon"></span>Room Availability</a>
            <a href="User_Request_Status.php"><span class="icon-container"><img src="../css/images/your-requests.png" alt="Your Requests"></span>Your Requests</a>
        </div>
        <div class="logOutButton">
            <a href="User_Logout.html"><span class="icon-container"><img src="../css/images/logout.png" alt="Logout Icon"></span>Log Out</a>
        </div>
    </div>

    <!-- Main content area -->
    <div class="main-content">
    <div class="main-box">
        <!-- Reservation request form -->
        <form class="request-form" method="POST" enctype="multipart/form-data">
            <div class="form-row top-row">
                <!-- Room details (read-only) -->
                <label class="room-label">Room
                    <input type="text" name="room" required class="room-input" 
                        value="<?php echo htmlspecialchars($prefill_room); ?>" readonly style="background-color: #f0f0f0;">
                </label>
                <label>Date
                    <input type="date" name="date" required class="date-input" 
                        value="<?php echo htmlspecialchars($prefill_date); ?>" readonly style="background-color: #f0f0f0;">
                </label>
                <label class="time-label">Time
                    <div class="time-row">
                        <input type="time" name="start_time" required class="time-input" 
                            value="<?php echo htmlspecialchars($prefill_start_time); ?>" readonly style="background-color: #f0f0f0;">
                        <span class="dash">-</span>
                        <input type="time" name="end_time" required class="time-input" 
                            value="<?php echo htmlspecialchars($prefill_end_time); ?>" readonly style="background-color: #f0f0f0;">
                    </div>
                </label>
            </div>
            <hr>

            <!-- Event details section -->
            <div class="form-row">
                <label class="form-label">Event Name:</label>
                <input type="text" name="event_name" required class="long-input" />
            </div>

            <div class="form-row">
                <label class="form-label">Event Purpose:</label>
                <textarea name="event_purpose" required class="long-textarea"></textarea>
            </div>

            <div class="form-row">
                <label class="form-label">No. of Attendees:</label>
                <input type="number" name="attendees" required class="short-input" min="1"/>
            </div>

            <!-- File upload section -->
            <div class="form-row">
                <label class="form-label">Upload supporting document/s:</label>
                <div class="file-upload">
                    <label for="files" class="file-label">Choose Files</label>
                    <span id="file-chosen">No files chosen</span>
                    <input type="file" id="files" name="files[]" class="file-input-hidden" multiple>
                </div>
            </div>

            <!-- Optional note field -->
            <div class="form-row">
                <label class="form-label">Note (optional):</label>
                <input type="text" name="note" class="long-input" />
            </div>

            <!-- Submit button -->
            <div class="form-row submit-row">
                <button type="submit" class="submit-btn">Submit Request</button>
            </div>

            <!-- Success or error messages -->
            <?php if ($success): ?>
                <p class="success-message" style="background-color: #D2EBC4; width: 45%; margin-left: auto; font-size: 18px;">
                    ✅ Request submitted successfully! Waiting for admin approval.
                </p>
            <?php elseif ($success === false): ?>
                <p class="error-message" style="background-color: #c00909ff; width: 45%; margin-left: auto; font-size: 18px;">
                    ❌ Error: <?= htmlspecialchars($error_message ?? 'Unknown error') ?>
                </p>
            <?php endif; ?>
        </form>

        <script>
            // Handle file upload display text
            const fileInput = document.getElementById("files");
            const fileChosen = document.getElementById("file-chosen");

            fileInput.addEventListener("change", function() {
                const count = this.files.length;
                fileChosen.textContent = count > 0 ? `${count} file${count > 1 ? 's' : ''} uploaded` : "No files chosen";
            });

            // Validate attendee input before form submission
            document.querySelector('.request-form').addEventListener('submit', function(e) {
                const attendeesInput = document.querySelector('input[name="attendees"]');
                const attendeesValue = parseInt(attendeesInput.value, 10);

                if (isNaN(attendeesValue) || attendeesValue <= 0) {
                    e.preventDefault(); // Prevent form submission
                    alert("⚠️ Number of attendees must be greater than 0.");
                    attendeesInput.focus();
                }
            });
        </script>

    </div>
    </div>
</body>
</html>
