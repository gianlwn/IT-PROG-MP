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
    // send the code
    if (isset($_POST["send_code"])) {
        $email = trim($_POST["send_code"]);

        if (!preg_match('/^[a-z._]+@dlsu\.edu\.ph$/', $email)) {
            $error_message = "Invalid DLSU email format.";
        } else {
            $email_clean = $conn->real_escape_string($email);
            $sql = "SELECT dlsu_email FROM users WHERE dlsu_email = '$email_clean'";
            $result = $conn->query($sql);

            // check if the email is already registered
            if ($result && $result->num_rows === 0) {
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
                    $mail->Body = "Your password reset code is: $verificationCode.\n\nIf you did not request this, please ignore this email.";

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
        if (!isset($_POST["forgot_code"])) {
            $error_message = "Please request a reset code first.";
            // check if the code expired
        } else if (time() - $_SESSION["forgot_verified"] > 120) {
            unset($_SESSION["forgot_code"], $_SESSION["forgot_time"]);
            $error_message = "Reset code expired. Please send a new code.";
            // check if the code doesn't match
        } else if ($code != $_SESSION["forgot_code"]) {
            $error_message = "Incorrect reset code.";
            // code is correct
        } else {
            unset($_SESSION["forgot_code"], $_SESSION["forgot_time"]);
            $_SESSION["forgot_verified"] = true;
            $success_message = "Code verified! You may now reset your password.";
        }
        // reset the password
    } else if (isset($_POST["reset_password"])) {
        if (!isset($_SESSION["forgot_verified"]) || $_SESSION["forgot_verified"] !== true) {
            $error_message = "Please verify your email first.";
        } else {
            $new_password = $_POST["new_password"];
            $confirm_password = $_POST["confirm_password"];

            if (empty($new_password) || empty($confirm_password)) {
                $error_message = "Please fill in all fields.";
            } else if ($new_password !== $confirm_password) {
                $error_message = "Passwords do not match!";
            } else {
                $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
                $email_clean = $conn->real_escape_string($_SESSION["forgot_email"]);
                $sql = "UPDATE users SET password_hash = '$password_hash' WHERE dlsu_email = '$email_clean'";

                if ($conn->query($sql) === TRUE) {
                    unset($_SESSION["forgot_email"], $_SESSION["forgot_verified"]);
                    header("Location: loginpage.php");
                    exit();
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
    <title>Forgot Password</title>
</head>

<body>

</body>

</html>