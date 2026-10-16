<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: Login_Admin.php");
    exit;
}

include('../config.php');

// Fetch only pending requests with full name of the user
$stmt = $pdo->prepare("
    SELECT e.*, u.full_name 
    FROM events e
    JOIN users u ON e.user_id = u.id
    WHERE e.status = 'pending'
    ORDER BY e.start_datetime ASC
");
$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin | Requests</title>
  <link rel="stylesheet" href="../css/Admin_Request.css">
  <style>
    .doc-container {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }
    .doc-preview-box {
      border: 2px solid black;
      width: 150px;
      height: 120px;
      overflow: hidden;
      border-radius: 8px;
      cursor: pointer;
    }
    .doc-preview-box iframe,
    .doc-preview-box img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border: none;
    }

    /* Modal styling */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      padding-top: 60px;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.8);
    }
    .modal-content {
      margin: auto;
      display: block;
      max-width: 90%;
      max-height: 80%;
      background-color: #fff;
      border-radius: 10px;
    }
    .modal iframe,
    .modal img {
      width: 100%;
      height: 100%;
      border: none;
      border-radius: 10px;
    }
    .close-modal {
      position: absolute;
      top: 15px;
      right: 35px;
      color: white;
      font-size: 35px;
      font-weight: bold;
      cursor: pointer;
    }
    .close-modal:hover {
      color: #ccc;
    }
  </style>
</head>
<body>
     
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
        <a href="Admin_History.php">
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
      <h2>Pending Requests</h2>
    </div>

    <div class="separator">
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Requester</th>
              <th>ID</th>
              <th>Room</th>
            </tr>
          </thead>
          <tbody id="requests-body">
            <?php if (empty($requests)): ?>
              <tr>
                <td colspan="3" style="text-align:center; padding:1rem; font-style:italic; color:gray;">
                  No pending requests.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($requests as $req): ?>
                <tr id="row-<?= $req['id'] ?>"
                    onclick='showDetails(<?= json_encode($req) ?>)'>
                  <td><img src="../images/pfp.png"> <?= htmlspecialchars($req['full_name']) ?></td>
                  <td><?= $req['id'] ?></td>
                  <td><?= htmlspecialchars($req['room']) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Detail card -->
      <div class="detail-card" id="detail-card" style="display: none;">
        <img src="../images/pfp.png" id="detail-avatar">

        <h3 id="detail-event">Event Name:</h3>
        <h3 id="detail-room">Room:</h3>
        <h3 id="detail-date">Date:</h3>
        <h3 id="detail-time">Time:</h3>
        <h3 id="detail-attendees">No. of Attendees:</h3>

        <h3>Purpose of Reservation:</h3>
        <textarea readonly id="detail-purpose"></textarea>

        <h3>Supporting Documents:</h3>
        <div id="detail-docs" class="doc-container"></div>

        <h3>Note (optional):</h3>
        <textarea readonly id="detail-note"></textarea>

        <div class="button-group">
          <button class="approve" onclick="handleAction(selectedId, 'approve')">Approve</button>
          <button class="reject" onclick="handleAction(selectedId, 'reject')">Reject</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Document Modal -->
<div id="docModal" class="modal">
  <span class="close-modal" onclick="closeModal()">&times;</span>
  <div class="modal-content" id="modal-content"></div>
</div>

<script>
let selectedId = null;

function showDetails(event) {
  selectedId = event.id;
  document.getElementById("detail-card").style.display = "block";

  // Basic info
  document.getElementById("detail-event").innerText = "Event Name: " + event.event_name;
  document.getElementById("detail-room").innerText = "Room: " + event.room;
  document.getElementById("detail-date").innerText = "Date: " + formatDate(event.start_datetime);
  document.getElementById("detail-time").innerText =
    "Time: " + formatTime(event.start_datetime) + " - " + formatTime(event.end_datetime);
  document.getElementById("detail-attendees").innerText = "No. of Attendees: " + event.attendees;
  document.getElementById("detail-purpose").value = event.event_purpose;
  document.getElementById("detail-note").value = event.note || "None";

  // Document previews
  const docContainer = document.getElementById("detail-docs");
  docContainer.innerHTML = "";
  if (event.file_path) {
    const paths = event.file_path.split(',').map(p => p.trim());
    paths.forEach(path => {
      const ext = path.split('.').pop().toLowerCase();
      const box = document.createElement("div");
      box.classList.add("doc-preview-box");

      let previewHTML = "";
      if (["jpg", "jpeg", "png", "gif", "webp"].includes(ext)) {
        previewHTML = `<img src="/reservenu/${path}" alt="Preview" onclick="openModal('/reservenu/${path}', 'image')">`;
      } else if (ext === "pdf") {
        previewHTML = `<iframe src="/reservenu/${path}" onclick="openModal('/reservenu/${path}', 'pdf')"></iframe>`;
      } else {
        previewHTML = `<a href="/reservenu/${path}" target="_blank" style="display:block;text-align:center;padding:10px;">📄 ${path.split('/').pop()}</a>`;
      }

      box.innerHTML = previewHTML;
      docContainer.appendChild(box);
    });
  } else {
    docContainer.innerHTML = "<p>None</p>";
  }
}

function openModal(filePath, type) {
  const modal = document.getElementById("docModal");
  const modalContent = document.getElementById("modal-content");

  modalContent.innerHTML =
    type === "pdf"
      ? `<iframe src="${filePath}"></iframe>`
      : `<img src="${filePath}" alt="Document">`;

  modal.style.display = "block";
}

function closeModal() {
  document.getElementById("docModal").style.display = "none";
  document.getElementById("modal-content").innerHTML = "";
}

window.onclick = function(e) {
  const modal = document.getElementById("docModal");
  if (e.target === modal) closeModal();
}

function formatDate(datetime) {
  const date = new Date(datetime);
  return date.toLocaleDateString("en-US", { year: "numeric", month: "long", day: "numeric" });
}

function formatTime(datetime) {
  const date = new Date(datetime);
  return date.toLocaleTimeString("en-US", { hour: "numeric", minute: "2-digit", hour12: true });
}

function handleAction(id, action) {
  if (!id) {
    alert("Please select a request first.");
    return;
  }

  fetch("../php/process_request.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "id=" + id + "&action=" + action
  })
    .then(res => res.text())
    .then(data => {
      console.log("Server response:", data);
      document.getElementById("row-" + id)?.remove();

      const tbody = document.getElementById("requests-body");
      const rowsLeft = tbody.querySelectorAll("tr").length;

      if (rowsLeft === 0) {
        document.getElementById("detail-card").style.display = "none";
        const noRow = document.createElement("tr");
        noRow.innerHTML = `<td colspan="3" style="text-align:center; padding:1rem; font-style:italic; color:gray;">No pending requests.</td>`;
        tbody.appendChild(noRow);
      } else {
        document.getElementById("detail-card").style.display = "none";
        selectedId = null;
      }
    })
    .catch(err => console.error("Error:", err));
}
</script>
</body>
</html>
