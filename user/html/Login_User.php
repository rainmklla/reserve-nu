<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Log In</title>
    <!-- Link to external CSS for page styling -->
    <link rel="stylesheet" href="../css/login_user.css">
</head>

<body>
    <div class="main-container"> 
        <div class="login-container">
            
            <!-- Green box section containing an image -->
            <div class="green-box">
                <img src="../css/images/cover.png" alt="Sign In Illustration" class="bully">
            </div>
        
            <!-- Login form section -->
            <div class="login-form">

                <!-- Top link for admin login -->
                <div class="login-link">
                    <p>Are you an admin? <a href="../html/Login_Admin.php">Click here.</a></p> 
                    <h2>Log In</h2>
                </div>
            
                <h6>For Nationalian use only</h6>

                <?php
                // Start the session to access session variables
                session_start();

                // Display error message if login fails
                if (isset($_SESSION['error'])) {
                    echo "<p style='color:red;'>" . $_SESSION['error'] . "</p>";
                    unset($_SESSION['error']); // Clear error message after displaying
                }

                // Display success message and start redirect countdown if login succeeds
                if (isset($_SESSION['success'])) {
                    echo "<p style='color:green;'>" . $_SESSION['success'] . "</p>";
                    echo "<p id='countdown' style='color:green;'>Redirecting in 2 seconds...</p>";
                    echo "<script>
                let seconds = 2;
                let countdownElem = document.getElementById('countdown');
                let interval = setInterval(function() {
                    seconds--;
                    countdownElem.textContent = 'Redirecting in ' + seconds + ' seconds...';
                    if (seconds <= 0) {
                        clearInterval(interval);
                        // Redirect to user dashboard after countdown
                        window.location.href = 'dashboard.php';
                    }
                }, 1000);
              </script>";
                    unset($_SESSION['success']); // Clear success message after displaying
                }
                ?>

                <!-- User login form -->
                <form action="../login.php" method="POST">
                    <!-- University ID input field (retains old input value if form fails) -->
                    <div class="input-group">
                        <input type="text" placeholder="University ID" value="<?php echo isset($_SESSION['old_university_id']) ? htmlspecialchars($_SESSION['old_university_id']) : ''; ?>" name="university_id">
                    </div>

                    <!-- Password input field -->
                    <div class="input-group">
                        <input type="password" placeholder="Password" name="password">
                    </div>

                    <!-- Options section for stay-signed-in checkbox and forgot password link -->
                    <div class="options-group">
                        <div class="stay-signed-in">
                            <input type="checkbox" id="stay-signed-checkbox" name="stay_signed_in">
                            <label for="stay-signed-checkbox">Stay signed in</label>
                        </div>
                        <a href="Forgot_Pass_User.php" class="forgot-password-link">Forgot Password?</a>
                    </div>

                    <!-- Submit button for login -->
                    <button type="submit" class="login-verify-btn" name="login">Log In</button>
                </form>

                <!-- Link for users who are not yet registered -->
                <div class="signup-link">
                    <p>Not Registered? <a href="../html/Signup_User.php">Sign up here.</a></p>
                </div> 
            </div>
        </div> 
    </div>
</body>
</html>

<?php
    // Clear previously stored university ID after page load
    unset($_SESSION['old_university_id']);
?>
