<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: Login_Admin.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin | Event Calendar</title>
  <link rel="stylesheet" href="../css/Admin_Event_Calendar.css">

  
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
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

      <!-- Reminders Section -->
      <div class="content-container">
        <h3>Reminders</h3>
        <ul>
          <li>You are permitted to update and make changes in the calendar.</li>
          <li>Changes that you make public will appear on the users' interface, as well.</li>
        </ul>
      </div>

   
    <!-- FULLCALENDAR RENDER CONTAINER -->
<div class="calendar-box">
  <iframe src="https://calendar.google.com/calendar/embed?height=600&wkst=1&ctz=Asia%2FManila&showTz=0&showTitle=0&src=bWlrYWVsbGFnb21lejEwMjAwNEBnbWFpbC5jb20&src=MmIwODlhMzkyNDk1NGI1NGU1ZmEyZGYxYmE1MTE2NGU5Y2RiZDMwM2ZiOTg3Mjc4NmY3ZTlmYWRmMTc4Njc4NUBncm91cC5jYWxlbmRhci5nb29nbGUuY29t&color=%23039be5&color=%23d81b60"  style="border: 0;" 
    class="calendar-iframe"
    frameborder="0" 
    scrolling="no"></iframe>
</div>


    </div>
  </div>
</body>
</html>

