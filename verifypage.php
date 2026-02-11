<!--verifypage.php-->

<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if (isset($_POST["scode"])) {
    if (!isset($_SESSION["verification_code"])) {
        echo "<p>Redirecting you back to login page...</p>";
        echo "<script>
        setTimeout(() => {
            window.location.href = 'loginpage.html';
        }, 5000);
      </script>";
        exit;
    }

    if (time() - $_SESSION["verification_time"] > 120) {
        unset($_SESSION["verification_code"], $_SESSION["verification_time"]);
        $error = "Verification code expired. Please refresh to login again";
    } else if ($_POST["scode"] != $_SESSION["verification_code"]) {
        $error = "Incorrect verification code.";
    } else {
        unset($_SESSION["verification_code"]);
        header("Location: home.php");
        exit;
    }
}

if (isset($_POST["semail"])) {
    $email = trim($_POST["semail"]);

    if (!preg_match('/^[a-z._]+@dlsu\.edu\.ph$/', $email)) {
        die("Invalid DLSU email");
    }

    if (!isset($_SESSION["verification_code"])) {
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
        } catch (Exception $e) {
            die("Email failed: {$mail->ErrorInfo}");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="design/verifypage-style.css">
    <title>Login</title>
</head>

<body>
    <form action="verifypage.php" method="post" onsubmit="disableButton()">
        <div id="container">
            <div id="verify">Verify</div>
            <div id="input">
                <label for="code">Code:</label>
                <input type="password" name="scode" id="code" minlength="6" maxlength="6" placeholder="Enter the code sent to your email." required />
                <input type="submit" value="Verify" id="submitbtn" />
                <?php if (!empty($error)): ?>
                    <p id="error-msg">
                        <?php echo $error; ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <script>
        function disableButton() {
            const btn = document.getElementById("submitbtn");
            btn.disabled = true;
            btn.value = "Please wait...";
        }
    </script>
</body>

</html>