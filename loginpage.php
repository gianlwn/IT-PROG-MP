<?php
// loginpage.php
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono&display=swap" rel="stylesheet">
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
                <input type="email" name="semail" id="email" placeholder="email@dlsu.edu.ph" required>
                <input type="submit" value="Verify" id="submitbtn">
            </div>
        </div>
    </form>
</body>

</html>