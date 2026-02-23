<!--loginpage.php-->

<?php
session_start();
require 'database.php';


?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="stylesheets/loginpage.css" />
  <title>Login</title>
</head>

<body>
  <form action="loginpage.php" method="post" onsubmit="disableButton()">
    <div id="container">
      <div id="login">Login</div>
      <div id="input">
        <label for="email">Email:</label>
        <input
          type="email"
          name="email"
          class="input-field"
          pattern="^[a-z._]+@dlsu\.edu\.ph$"
          placeholder="email@dlsu.edu.ph"
          title="Use lowercase letters and underscores only (name_name@dlsu.edu.ph)"
          required />
        <label for="password">Password:</label>
        <input
          type="password"
          name="password"
          class="input-field"
          placeholder="Enter your password"
          title="Enter your password"
          required />
        <input type="submit" value="Login" id="submitbtn" />
      </div>
      <hr /> <!-- lines 44-46 need css -->
      <div class="text-options"><a href="#">First time user?</a></div>
      <div class="text-options"><a href="#">Forgot password</a></div>
    </div>
  </form>

  <script>
    function disableButton() {
      const btn = document.getElementById("submitbtn");
      btn.disabled = true;
      btn.value = "Please wait...";

      // re-enable the button after 60 seconds
      setTimeout(() => {
        btn.disabled = false;
        btn.value = "Login";
      }, 60000);
    }
  </script>
</body>

</html>