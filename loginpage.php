<?php
// loginpage.php
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Login</title>
</head>

<body>
    <form action="" method="post">
        <div class="container">
            <div id="login">
                <p>Login</p>
            </div>
            <div class="input">
                <label for="email">Email:</label>
                <input type="email" name="semail" class="input-text" id="email" placeholder="email@dlsu.edu.ph" required><br><br>
                <label for="2FA">2FA Code:</label>
                <input type="text" name="s2fa" class="input-text" id="2FA" placeholder="Enter the 2FA code"><br><br>
                <input type="submit" value="Verify" id="submitbtn">
            </div>
        </div>
    </form>
</body>

</html>