<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> User Event Calendar </title>
    <link rel="stylesheet" href="../css/User_Event_Calendar.css">

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
    <a href="User_Request_Status.php">
        <span class="icon-container">
            <img src="../css/images/your-requests.png" alt="Your Requests">
        </span>
        Your Requests
    </a>
</div>
<div class="logOutButton">
    <a href="../../logout.php">
        <span class="icon-container">
            <img src="../css/images/logout.png" alt="Logout Icon">
        </span>
        Log Out
    </a>
</div>
    </div>

    <div class="main-content">
    <div class="main-box">

      <div class="content-container">
        <h3>Reminders</h3>
        <ul>
          <li>You may use this feature for viewing only.</li>
          <li>Only admins are permitted to make changes in the calendar.</li>
        </ul>
      </div>
      
    <!-- FULLCALENDAR RENDER CONTAINER -->
<div class="calendar-box">
<iframe src="https://calendar.google.com/calendar/embed?height=600&wkst=1&ctz=Asia%2FManila&showTabs=0&showCalendars=0&showTitle=0&src=bWlrYWVsbGFnb21lejEwMjAwNEBnbWFpbC5jb20&src=MmIwODlhMzkyNDk1NGI1NGU1ZmEyZGYxYmE1MTE2NGU5Y2RiZDMwM2ZiOTg3Mjc4NmY3ZTlmYWRmMTc4Njc4NUBncm91cC5jYWxlbmRhci5nb29nbGUuY29t&color=%23039be5&color=%23d81b60" style="border: 0;" 
    class="calendar-iframe"
    frameborder="0" 
    scrolling="no"></iframe>
</div>

      
    </div>
  </div>
    
</body>
</html>