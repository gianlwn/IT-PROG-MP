<?php
session_start();
require 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$error_message = "";
$success_message = "";
$success_message_reset = "";
$flag = true;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // send the code
    if (isset($_POST["send_code"])) {
        $email = trim($_POST["email"]);

        if (!preg_match('/^[a-z._]+@dlsu\.edu\.ph$/', $email)) {
            $error_message = "Invalid DLSU email format.";
        } else {
            $forgot_query = "SELECT dlsu_email
                             FROM users
                             WHERE dlsu_email = ?";

            $stmt = $conn->prepare($forgot_query);

            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }

            $stmt->bind_param("s", $email);
            $stmt->execute();
            $forgot_result = $stmt->get_result();

            // check if the email is already registered
            if ($forgot_result->num_rows === 0) {
                $error_message = "This email is not registered. Please create an account.";
            } else {
                $forgot_code = rand(100000, 999999);
                $_SESSION["forgot_code"] = $forgot_code;
                $_SESSION["forgot_email"] = $email;
                $_SESSION["forgot_time"] = time();
                $_SESSION["forgot_verified"] = false;
                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'dlsu.marketplace@gmail.com';
                    $mail->Password = 'bddq lwzp gxyf xdno';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('dlsu.marketplace@gmail.com', 'DLSU Marketplace');
                    $mail->addAddress($email);

                    $mail->Subject = 'DLSU Marketplace Password Reset Code';
                    $mail->Body = "Your password reset code is: $forgot_code\n\nIf you did not request this, please ignore this email.";

                    $mail->send();
                    $success_message = "Reset code sent! Please check your email.";
                } catch (Exception $e) {
                    die("Email failed to send: {$mail->ErrorInfo}");
                }
            }
        }
        // verify the code
    } else if (isset($_POST["verify_code"])) {
        $code = trim($_POST["code"]);

        // check if the code is inputted
        if (!isset($_POST["code"]) || !isset($_SESSION["forgot_time"])) {
            $error_message = "Please request a reset code first.";
            // check if the code expired
        } else if (time() - $_SESSION["forgot_time"] > 120) {
            unset($_SESSION["forgot_code"], $_SESSION["forgot_time"]);
            $error_message = "Reset code expired. Please send a new code.";
            // check if the code doesn't match
        } else if ($code != $_SESSION["forgot_code"]) {
            $error_message = "Incorrect reset code.";
            // code is correct
        } else {
            unset($_SESSION["forgot_code"], $_SESSION["forgot_time"], $_POST["send_code"]);
            $_SESSION["forgot_verified"] = true;
            $flag = false;
            $success_message = "Code verified! You may now reset your password.";
        }
        // reset the password
    } else if (isset($_POST["reset_password"])) {
        if (!isset($_SESSION["forgot_verified"]) || $_SESSION["forgot_verified"] !== true) {
            $error_message = "Please verify your email first.";
        } else {
            $new_password = $conn->real_escape_string($_POST["new_password"]);
            $confirm_password = $conn->real_escape_string($_POST["confirm_password"]);

            if (empty($new_password) || empty($confirm_password)) {
                $error_message = "Please fill in all fields.";
            } else if ($new_password !== $confirm_password) {
                $error_message = "Passwords do not match!";
            } else {
                $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
                $forgot_email = $conn->real_escape_string($_SESSION["forgot_email"]);

                $reset_query = "UPDATE users
                                SET password_hash = ?
                                WHERE dlsu_email = ?";

                $stmt = $conn->prepare($reset_query);

                if (!$stmt) {
                    die("Prepare failed: " . $conn->error);
                }

                $stmt->bind_param("ss", $password_hash, $forgot_email);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    $success_message_reset = "Password resetted successfully! Redirecting to login...";
                    unset($_SESSION["forgot_email"], $_SESSION["forgot_verified"], $_POST["verify_code"]);
                } else {
                    $error_message = "Database Error: " . $conn->error;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stylesheets/verifypage.css">
    <?php if (empty($success_message_reset) && (!isset($_SESSION["forgot_verified"]) || $_SESSION["forgot_verified"] !== true) && $flag): ?>
        <title>DLSU Marketplace | Forgot Password</title>
    <?php else: ?>
        <title>DLSU Marketplace | Reset Password</title>
    <?php endif; ?>
</head>

<body>
    <form action="forgotpassword.php" method="post">
        <div class="container">
            <img src="images/login-icon.png" alt="login-icon" class="login-icon">

            <?php if (!empty($error_message)): ?>
                <div class="error-msg"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="success-msg"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <?php if (!empty($success_message_reset)): ?>
                <div class="success-msg"><?php echo $success_message_reset; ?></div>
                <script>
                    setTimeout(() => {
                        window.location.href = 'loginpage.php?create=success';
                    }, 3000);
                </script>
            <?php endif; ?>

            <div class="input-container">
                <!-- SHOW VERIFICATION UI FIRST -->
                <?php if (empty($success_message_reset) && (!isset($_SESSION["forgot_verified"]) || $_SESSION["forgot_verified"] !== true) && $flag): ?>
                    <label for="email">Registered Email:</label>
                    <div class="email-group">
                        <input type="email" name="email" id="email-input-field" class="input-field" pattern="^[a-z._]+@dlsu\.edu\.ph$" title="Use your DLSU email (name_name@dlsu.edu.ph)" placeholder="email@dlsu.edu.ph" value="
                        <?php echo isset($_SESSION["forgot_email"]) ? $_SESSION["forgot_email"] : (isset($_POST["email"]) ? $_POST["email"] : ""); ?>" required />
                        <input type="submit" value="Send Code" name="send_code" id="send-code-btn" formnovalidate onclick="disableSend()" />
                    </div>

                    <label for="code">6-Digit Code:</label>
                    <input type="password" name="code" id="code-input-field" class="input-field" minlength="6" maxlength="6" pattern="[0-9]{6}" title="Enter the code sent to your email" placeholder="Enter the reset code" />
                    <input type="submit" name="verify_code" value="Verify Code" class="submit-btn" />
                    <!-- THEN SHOW PASSWORD RESET UI -->
                <?php else: ?>
                    <label for="new_password">New Password:</label>
                    <input type="password" name="new_password" class="input-field" placeholder="Create a new password" required />
                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" name="confirm_password" class="input-field" placeholder="Confirm your new password" required />
                    <input type="submit" name="reset_password" value="Reset Password" class="submit-btn" />
                <?php endif; ?>
            </div>
            <hr>
            <div class="text-options">
                <a href="logout.php">Back to Login</a>
            </div>
        </div>
    </form>
    <script>
        function disableSend() {
            const btn = document.getElementById("send-code-btn");
            setTimeout(() => {
                btn.disabled = true;
                btn.value = "Sending...";
                btn.style.cursor = "not-allowed";
            }, 10);
        }
    </script>
</body>

</html>