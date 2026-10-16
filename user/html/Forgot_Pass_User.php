<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password?</title>
    <link rel="stylesheet" href="../css/forgot_pass_user.css">
</head>
<body>
    <div class="main-container">
        <div class="forgot-container">
            <div class="forgot-form">
                <h1>Forgot Password?</h1>

                <?php
                // Show error or success messages
                if (isset($_SESSION['error'])) {
                    echo "<p style='color:red;'>" . $_SESSION['error'] . "</p>";
                    unset($_SESSION['error']);
                }
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
                            window.location.href = 'dashboard.php';
                            }
                        }, 1000);
                        </script>";
                unset($_SESSION['success']);
                }
                ?>

                <form action="../reset_password.php" method="POST">
                    <div class="input-group" style="width: 100%;"> 
                        <input type="text" id="student_id" name="student_id" 
                            placeholder="Student ID" 
                            value="<?php echo isset($_SESSION['old_student_id']) ? htmlspecialchars($_SESSION['old_student_id']) : ''; ?>">
                    </div>

                    <div class="input-group" style="width: 100%;">
                        <input type="password" id="new_password" name="password" placeholder="New Password" >
                    </div>

                    <div class="input-group" style="width: 100%;">
                        <input type="password" id="confirm_new_password" name="confirm_password" placeholder="Confirm New Password" >
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="back-to-login-btn">Submit</button>
                        <button type="button" class="back-to-login-btn" onclick="window.location.href='Login_User.php'">Back</button>

                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

<?php
// Clear saved input after displaying it
unset($_SESSION['old_student_id']);
?>