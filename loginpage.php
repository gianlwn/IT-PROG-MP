<?php
session_start();
include 'db.php';

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $conn->real_escape_string(trim($_POST["email"]));
  $password = $conn->real_escape_string($_POST["password"]);

  $sql = "SELECT user_id, password_hash, first_name, last_name, role, dlsu_email, profile_picture
          FROM users
          WHERE dlsu_email = '$email'";

  $result = $conn->query($sql);

  if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user["password_hash"])) {
      $_SESSION["dlsu_email"] = $user["dlsu_email"];
      $_SESSION["user_id"] = $user["user_id"];
      $_SESSION["profile_picture"] = $user["profile_picture"];
      $_SESSION["first_name"] = $user["first_name"];
      $_SESSION["last_name"] = $user["last_name"];
      $_SESSION["role"] = $user["role"];
      header("Location: home.php");
      exit();
    } else {
      $error_message = "Invalid email or password.";
    }
  } else {
    $error_message = "Invalid email or password.";
  }
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="stylesheets/loginpage.css" />
  <title>DLSU Marketplace | Login</title>
</head>

<body>
  <form action="loginpage.php" method="post" onsubmit="disableButton()">
    <div class="container">
      <img src="images/login-icon.png" alt="login-icon" class="login-icon">

      <?php if (!empty($error_message)): ?>
        <div class="error-msg"><?php echo $error_message; ?></div>
      <?php endif; ?>

      <div class="input-container">
        <label for="email">Email:</label>
        <input
          type="email"
          name="email"
          class="input-field"
          pattern="^[a-z._]+@dlsu\.edu\.ph$"
          placeholder="email@dlsu.edu.ph"
          title="Use your DLSU email (name_name@dlsu.edu.ph)"
          required />
        <label for="password">Password:</label>
        <input
          type="password"
          name="password"
          class="input-field"
          placeholder="Enter your password"
          title="Enter your password"
          required />
        <input type="submit" value="Login" class="submit-btn" />
      </div>
      <hr />
      <div class="text-options"><a href="verifypage.php">First time user?</a></div>
      <div class="text-options"><a href="forgotpassword.php">Forgot password</a></div>
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

<?php $conn->close(); ?>