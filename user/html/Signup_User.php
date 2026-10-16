<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up as User</title>
    <link rel="stylesheet" href="../css/signup_user.css">

    <style>
        /* === Modal Styling === */
        #termsModal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.4);
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        #termsModal .modal-content {
            background: #fff;
            width: 80%;
            max-width: 850px;
            height: 80%;
            border-radius: 10px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        #termsModal iframe {
            flex: 1;
            width: 100%;
            border: none;
        }
    </style>
</head>

<body>
    <div class="main-container">
        <div class="signup-container">
            <div class="green-box">
                <img src="../css/images/cover.png" alt="Sign In Illustration" class="bully">
            </div>

            <div class="signup-form">
                <div class="login-link">
                    <p>Already Registered? <a href="../html/Login_User.php">Log in here.</a></p>
                    <h2>Sign Up</h2>
                </div>

                <?php
                session_start();
                if (isset($_SESSION['error'])) {
                    echo "<p style='color:red;'>".$_SESSION['error']."</p>";
                    unset($_SESSION['error']);
                }
                if (isset($_SESSION['success'])) {
                    echo "<p style='color:green;'>".$_SESSION['success']."</p>";
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

                <!-- === Signup Form === -->
                <form action="../signup.php" method="POST">
                    <div class="input-group">
                        <input type="text" 
                               name="full_name" 
                               placeholder="Full Name"
                               value="<?php echo isset($_SESSION['old_full_name']) ? htmlspecialchars($_SESSION['old_full_name']) : ''; ?>">
                    </div>

                    <div class="input-group">
                        <input type="text" 
                               name="university_id" 
                               placeholder="University ID"
                               value="<?php echo isset($_SESSION['old_university_id']) ? htmlspecialchars($_SESSION['old_university_id']) : ''; ?>">
                    </div>

                    <div class="password-group">
                        <input type="password" name="password" placeholder="Password">
                        <input type="password" name="confirm_password" placeholder="Confirm Password">
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="terms-and-conditions">
                        <input type="checkbox" name="terms" id="terms-checkbox"
                            <?php echo isset($_SESSION['old_terms']) && $_SESSION['old_terms'] ? 'checked' : ''; ?>>
                        <label for="terms-checkbox">
                            I Agree to the 
                            <a href="#" id="openTermsLink"><b>Terms and Conditions</b></a>
                        </label>
                    </div>

                    <!-- === Modal === -->
                    <div id="termsModal">
                        <div class="modal-content">
                            <iframe src="Terms_Conditions_User.php"></iframe>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="btn-group">
                        <button type="submit" class="signup-verify-btn" name="signup">Sign Up</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('termsModal');
        const openLink = document.getElementById('openTermsLink');

        // Open modal
        openLink.addEventListener('click', function(e) {
            e.preventDefault();
            modal.style.display = 'flex';
            modal.scrollTop = 0;
        });

        // Listen for close message from iframe
        window.addEventListener('message', (event) => {
            if (event.data === 'closeTermsModal') {
                modal.style.display = 'none';
            }
        });
    </script>
</body>
</html>

<?php
unset($_SESSION['old_full_name'], $_SESSION['old_university_id'], $_SESSION['old_terms']);
?>
