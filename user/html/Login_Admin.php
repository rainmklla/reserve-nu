<?php
// Start a new session or resume an existing one
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Log In</title>
    <!-- Link to external CSS file for styling -->
    <link rel="stylesheet" href="../css/login_admin.css">
</head>
<body>
    <div class="main-container">
        <div class="login-container"> 
            
            <!-- === Login Form Section === -->
            <div class="login-form"> 
                <div class="login-link">
                    <h2>Log In</h2>
                </div>
            
                <h6>For Admin use only</h6>

                <?php
                // Display error message if login fails
                if (isset($_SESSION['error'])) {
                    echo "<p style='color:red;'>" . $_SESSION['error'] . "</p>";
                    unset($_SESSION['error']); // Clear the error message after displaying it
                }

                // Display success message and redirect if login succeeds
                if (isset($_SESSION['success'])) {
                    echo "<p style='color:green;'>" . $_SESSION['success'] . "</p>";
                    echo "<p id='countdown' style='color:green;'>Redirecting in 2 seconds...</p>";

                    // JavaScript countdown before redirecting to the admin dashboard
                    echo "<script>
                        let seconds = 2;
                        let countdownElem = document.getElementById('countdown');
                        let interval = setInterval(function() {
                            seconds--;
                            countdownElem.textContent = 'Redirecting in ' + seconds + ' seconds...';
                            if (seconds <= 0) {
                                clearInterval(interval);
                                // Redirect to admin dashboard
                                window.location.href = '../../admin/html/admin_dashboard.php';
                            }
                        }, 1000);
                    </script>";

                    unset($_SESSION['success']); // Clear success message after showing it
                }
                ?> 
                
                <!-- === Admin Login Form === -->
                <form action="../login_ad.php" method="POST">
                    <!-- Admin ID Input -->
                    <div class="input-group">
                        <input type="text" name="admin_id" placeholder="Admin ID" 
                            value="<?php echo isset($_SESSION['old_admin_id']) ? htmlspecialchars($_SESSION['old_admin_id']) : ''; ?>">
                    </div>

                    <!-- Password Input -->
                    <div class="password-group">
                        <input type="password" name="password" placeholder="Password">
                    </div>

                    <!-- (Optional) Additional Options Group -->
                    <div class="options-group">
                        <!-- Currently empty, can be used for “Remember Me” or other features -->
                    </div>

                    <!-- Submit Button -->
                    <div class="btn-group">
                        <button type="submit" class="login-verify-btn">Log In</button>
                    </div>
                </form>

                <!-- Redirect Link for Non-Admins -->
                <div class="signup-link">
                    <p>Not an Admin? <a href="../html/Signup_User.php">Click here.</a></p>
                </div> 
            </div>

            <!-- === Right-Side Blue Box with Illustration === -->
            <div class="blue-box">
                <img src="../css/images/cover.png" alt="Sign In Illustration" class="bully">
            </div>
        </div>
    </div>
</body>
</html>

<?php
// Clear old input data after the page loads
unset($_SESSION['old_admin_id']);
?>
