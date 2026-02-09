<?php
// verifypage.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if (!isset($_POST["semail"])) {
    die("Email required");
}

$email = trim($_POST["semail"]);

if (!preg_match('/^[a-z._]+@dlsu\.edu\.ph$/', $email)) {
    die("Invalid DLSU email");
}

$verificationCode = rand(100000, 999999);
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
} catch (Exception $e) {
    die("Email failed: {$mail->ErrorInfo}");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="design/verifypage-style.css">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono&display=swap" rel="stylesheet">
    <title>Login</title>
</head>

<body>
    <form action="home.php" method="post" onsubmit="disableButton()">
        <div id="container">
            <div id="verify">Verify</div>
            <div id="input">
                <label for="code">Code:</label>
                <input type="password" name="scode" id="code" placeholder="Enter the code sent to your email." required>
                <input type="submit" value="Verify" id="submitbtn">
            </div>
        </div>
    </form>

    <script>
        function disableButton() {
            const btn = document.getElementById("submitbtn");
            btn.disabled = true;
            btn.value = "Loading...";
        }
    </script>
</body>

</html>