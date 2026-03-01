<?php
session_start();
include 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // send the code via email
    if (isset($_POST["send_code"])) {
        $email = trim($_POST["email"]);

        if (!preg_match('/^[a-z._]+@dlsu\.edu\.ph$/', $email)) {
            $error_message = "Invalid DLSU email format.";
        } else {
            $email_clean = $conn->real_escape_string($email);
            $sql = "SELECT dlsu_email
                    FROM users
                    WHERE dlsu_email = '$email_clean'";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                $error_message = "This email is already registered. Please log in.";
            } else {
                $verificationCode = rand(100000, 999999);
                $_SESSION["verification_code"] = $verificationCode;
                $_SESSION["verification_email"] = $email;
                $_SESSION["verification_time"] = time();
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

                    $mail->Subject = 'DLSU Marketplace Authentication Code';
                    $mail->Body = "Your verification code is: $verificationCode";

                    $mail->send();
                    $success_message = "Verification code sent! Please check your email.";
                } catch (Exception $e) {
                    die("Email failed: {$mail->ErrorInfo}");
                }
            }
        }
    }
    // verify the code
    elseif (isset($_POST["verify_code"])) {
        $email = trim($_POST["email"]);
        $code = trim($_POST["code"]);

        if (!isset($_SESSION["verification_code"])) {
            $error_message = "Please request a verification code first.";
        } else if (time() - $_SESSION["verification_time"] > 120) {
            unset($_SESSION["verification_code"], $_SESSION["verification_time"]);
            $error_message = "Verification code expired. Please send a new code.";
        } else if ($code != $_SESSION["verification_code"]) {
            $error_message = "Incorrect verification code.";
        } else if ($code == $_SESSION["verification_code"] && preg_match('/^[a-z]+(_[a-z]+)*@dlsu\.edu\.ph$/', $email)) {
            unset(
                $_SESSION["verification_code"],
                $_SESSION["verification_email"],
                $_SESSION["verification_time"]
            );
            $_SESSION["email_verified"] = true;
            $_SESSION["email_type"] = "student/staff";
            header("Location: createuserprofile.php");
            exit();
        } else if ($code == $_SESSION["verification_code"] && preg_match('/^[a-z]+(\.[a-z]+)*@dlsu\.edu\.ph$/', $email)) {
            unset(
                $_SESSION["verification_code"],
                $_SESSION["verification_email"],
                $_SESSION["verification_time"]
            );
            $_SESSION["email_verified"] = true;
            $_SESSION["email_type"] = "faculty";
            header("Location: createuserprofile.php");
            exit();
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
    <title>Verify</title>
</head>

<body>
    <form action="verifypage.php" method="post">
        <div id="container">
            <div id="verify">Verify</div>

            <?php if (!empty($error_message)): ?>
                <div id="error-msg"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div id="success-msg"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <div id="input-container">
                <label for="email">Email:</label>
                <div class="email-group">
                    <input
                        type="email"
                        name="email"
                        id="email-input-field"
                        class="input-field"
                        pattern="^[a-z._]+@dlsu\.edu\.ph$"
                        placeholder="email@dlsu.edu.ph"
                        title="Use your DLSU email (name_name@dlsu.edu.ph)"
                        value="<?php echo isset($_POST["email"]) ? $_POST["email"] : ""; ?>"
                        required />
                    <input
                        type="submit"
                        value="Send Code"
                        name="send_code"
                        id="send-code-btn"
                        formnovalidate
                        onclick="disableSend()" />
                </div>
                <label for="code">Code:</label>
                <input
                    type="password"
                    name="code"
                    id="code-input-field"
                    class="input-field"
                    minlength="6"
                    maxlength="6"
                    pattern="[0-9]{6}"
                    placeholder="Enter the code sent to your email"
                    title="Enter the code sent to your email"
                    required />
                <input type="submit" name="verify_code" value="Verify" id="submit-btn" />
            </div>
            <hr>
            <div class="text-options">
                <a href="loginpage.php">Back to Login</a>
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