<?php
include('../config.php'); // adjust path

// Get ID from query string
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: Admin_History.php");
    exit;
}

// Fetch reservation details
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    echo "<p>Reservation not found.</p>";
    exit;
}

// Multiple file path setup
$filePaths = [];
if (!empty($event['file_path'])) {
    $filePaths = explode(',', $event['file_path']);
    $filePaths = array_map('trim', $filePaths); // remove extra spaces
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin | History Details</title>
  <link rel="stylesheet" href="../css/History_Details.css">
  <style>
/* Modal overlay (background) */
#docModal {
  display: none;
  position: fixed;
  z-index: 9999;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.85);
  justify-content: center;
  align-items: center;
}

/* Modal content box */
#docModalContent {
  position: relative;
  background: white;
  border-radius: 10px;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
  box-shadow: 0 0 25px rgba(0,0,0,0.4);
  width: 90vw;
  height: 90vh;
}

/* Embedded document / image inside modal */
#docModalContent iframe,
#docModalContent img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  border: none;
}

/* Close button */
#closeModalBtn {
  position: absolute;
  top: 10px;
  right: 15px;
  color: white;
  font-size: 36px;
  cursor: pointer;
  font-weight: bold;
  z-index: 10000;
  text-shadow: 0 0 8px black;
}

/* Preview box (small thumbnails) */
.doc-preview-box {
  border: 2px solid black;
  width: 280px;
  height: 200px;
  overflow: hidden;
  border-radius: 8px;
  cursor: pointer;
  display: inline-block;
  margin: 10px;
  vertical-align: top;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.doc-preview-box:hover {
  transform: scale(1.03);
  box-shadow: 0 0 10px rgba(0,0,0,0.3);
}

/* Thumbnail preview for iframe */
.doc-preview-box iframe {
  width: 100%;
  height: 100%;
  border: none;
  pointer-events: none;
}

/* Container for all previews */
.doc-container {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}
</style>
</head>

<body>
  <div class="navbar">
    <div class="trademark">
      <h3><img src="../images/reserveNU_icon.png" alt="ReserveNU">ReserveNU</h3>
    </div>
    <div class="navigationButtons">
      <a href="Admin_Dashboard.php"><img src="../images/dashboard.png" alt="Dashboard Icon">Dashboard</a>
      <a href="Admin_Event_Calendar.php"><img src="../images/event-calendar.png" alt="Calendar Icon">Event Calendar</a>
      <a href="Admin_Request.php"><img src="../images/requests.png" alt="Request Icon">Requests</a>
      <a href="Admin_History.php"><img src="../images/history.png" alt="History Icon">History</a>
    </div>
    <div class="logOutButton">
      <a href="../logout_ad.php"><img src="../images/logout.png" alt="Logout Icon">Log Out</a>
    </div>
  </div>

  <div class="main-content">
    <div class="main-box">
      <div class="header">
        <h2>Reservation Details</h2>
        <a href="Admin_History.php" class="back">Go Back</a>
      </div>

      <div class="details-content">
        <!-- Reservation Info -->
        <div class="detail-group">
          <div class="detail-grid">
            <span class="detail-label">Room:</span>
            <span class="detail-value"><?= htmlspecialchars($event['room']) ?></span>
            
            <span class="detail-label">Date:</span>
            <span class="detail-value"><?= date("F j, Y", strtotime($event['start_datetime'])) ?></span>
            
            <span class="detail-label">Time:</span>
            <span class="detail-value">
              <?= date("g:i A", strtotime($event['start_datetime'])) ?> -
              <?= date("g:i A", strtotime($event['end_datetime'])) ?>
            </span>
            
            <span class="detail-label">No. of Attendees:</span>
            <span class="detail-value"><?= htmlspecialchars($event['attendees']) ?></span>
          </div>
        </div>

        <!-- Event Info -->
        <div class="detail-group">
          <h3>Event Information</h3>
          <div class="detail-grid">
            <span class="detail-label">Event Name:</span>
            <span class="detail-value"><?= htmlspecialchars($event['event_name']) ?></span>
            
            <div class="full-width-field">
              <span class="detail-label">Event Purpose:</span>
              <div class="detail-value"><?= nl2br(htmlspecialchars($event['event_purpose'])) ?></div>
            </div>
          </div>
        </div>

        <!-- Supporting Docs & Notes -->
        <div class="detail-group">
          <h3>Supporting Documents & Notes</h3>
          <div class="detail-grid">
            <span class="detail-label">Supporting Document(s):</span>
            <span class="detail-value">
              <?php if (!empty($filePaths)): ?>
                <div class="doc-container">
                  <?php foreach ($filePaths as $path): 
                    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                  ?>
                    <?php if (in_array($ext, ['jpg','jpeg','png','gif','webp'])): ?>
                      <div class="doc-preview-box" onclick="openModal('<?= htmlspecialchars($path) ?>', 'image')">
                        <img src="/reservenu/<?= htmlspecialchars($path) ?>" alt="Image Preview" style="width:100%;height:100%;object-fit:cover;">
                      </div>

                    <?php elseif ($ext === 'pdf'): ?>
                      <div class="doc-preview-box" onclick="openModal('<?= htmlspecialchars($path) ?>', 'pdf')">
                        <iframe src="/reservenu/<?= htmlspecialchars($path) ?>"></iframe>
                      </div>

                    <?php else: ?>
                      <div class="doc-preview-box">
                        <a href="/reservenu/<?= htmlspecialchars($path) ?>" target="_blank" style="display:block;text-align:center;padding:20px;">
                          📄 <?= basename($path) ?>
                        </a>
                      </div>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <p>None</p>
              <?php endif; ?>
            </span>

            <div class="full-width-field">
              <span class="detail-label">Note (optional):</span>
              <div class="detail-value"><?= !empty($event['note']) ? nl2br(htmlspecialchars($event['note'])) : "None" ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Popup -->
  <div id="docModal" onclick="closeModal()">
    <div id="docModalContent" onclick="event.stopPropagation()">
      <span id="closeModalBtn" onclick="closeModal()">&times;</span>
      <iframe id="docFrame" src=""></iframe>
      <img id="docImage" src="" alt="Preview" style="display:none;">
    </div>
  </div>

  <script>
  function openModal(path, type) {
    const modal = document.getElementById('docModal');
    const frame = document.getElementById('docFrame');
    const image = document.getElementById('docImage');

    if (type === 'pdf') {
      frame.src = "/reservenu/" + path;
      frame.style.display = 'block';
      image.style.display = 'none';
    } else {
      image.src = "/reservenu/" + path;
      image.style.display = 'block';
      frame.style.display = 'none';
    }

    modal.style.display = 'flex';
  }

  function closeModal() {
    const modal = document.getElementById('docModal');
    document.getElementById('docFrame').src = "";
    document.getElementById('docImage').src = "";
    modal.style.display = 'none';
  }
  </script>
</body>
</html>
