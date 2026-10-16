<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions</title>
    <link rel="stylesheet" href="../css/terms_conditions_user.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: rgba(0,0,0,0.4);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .terms-container {
            background: #fff;
            width: 90%;
            max-width: 700px;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0px 4px 12px rgba(0,0,0,0.2);
            overflow-y: auto;
            max-height: 80vh;
        }
        h1 {
            margin-bottom: 10px;
            text-align: center;
        }
        p {
            line-height: 1.5;
            margin-bottom: 12px;
        }
        .back-btn {
            background: #000;
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
            display: block;
            margin: 15px auto 0;
            font-size: 15px;
        }
        .back-btn:hover {
            background: #333;
        }
    </style>
</head>
<body>

    <div class="terms-container">
        <div class="terms-content">
            <h1>Terms and Conditions</h1>

            <p>
                Welcome to <strong>ReserveNU</strong>, the official Online Room Reservation System. By accessing or using this system,
                you agree to comply with the following Terms and Conditions. Please read them carefully before proceeding.
            </p>

            <h3>1. User Responsibilities</h3>
            <p>
                - Users must provide accurate and up-to-date information during registration. <br>
                - Users are responsible for keeping their login credentials secure. <br>
                - Any misuse of the reservation system may lead to suspension or account termination.
            </p>

            <h3>2. Room Reservation Policy</h3>
            <p>
                - All reservations must be made through the official platform. <br>
                - Users must ensure that the rooms are used only for approved academic or organizational activities. <br>
                - Cancellations should be made at least 24 hours before the reserved time to allow other users to book the room.
            </p>

            <h3>3. System Usage</h3>
            <p>
                - Users shall not attempt to bypass, hack, or exploit the system in any way. <br>
                - Unauthorized access or sharing of accounts is strictly prohibited. <br>
                - The system administrators reserve the right to monitor all activity for security and compliance.
            </p>

            <h3>4. Data Privacy</h3>
            <p>
                - The system collects necessary personal information solely for reservation and verification purposes. <br>
                - All collected data will be stored securely and will not be shared with third parties without consent.
            </p>

            <h3>5. Agreement</h3>
            <p>
                By clicking "I Agree" on the sign-up page, you acknowledge that you have read, understood, and agreed to these Terms and Conditions.
            </p>

           <button class="back-btn" onclick="window.parent.postMessage('closeTermsModal', '*')">Back</button>


<script>
function closeTerms() {
    // Tell the parent page to close the modal
    window.parent.postMessage('closeTermsModal', '*');
}
</script>

        </div>
    </div>

    <script>
        function closeModal() {
            // This closes the modal instead of redirecting
            window.close(); 
        }
    </script>

</body>
</html>
