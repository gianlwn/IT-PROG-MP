<?php
session_start();
include 'db.php';

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $conn->real_escape_string(trim($_POST["email"]));
  $password = $_POST["password"];

  $sql = "SELECT user_id, password_hash, first_name, role, dlsu_email
          FROM users
          WHERE dlsu_email = '$email'";

  $result = $conn->query($sql);

  if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user["password_hash"])) {
      $_SESSION["dlsu_email"] = $user["dlsu_email"];
      $_SESSION["user_id"] = $user["user_id"];
      $_SESSION["first_name"] = $user["first_name"];
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
  <title>Login</title>
</head>

<body>
  <form action="loginpage.php" method="post" onsubmit="disableButton()">
    <div id="container">
      <div id="login">Login</div>

      <?php if (!empty($error_message)): ?>
        <div id="error-msg"><?php echo $error_message; ?></div>
      <?php endif; ?>

      <div id="input-container">
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
        <input type="submit" value="Login" id="submitbtn" />
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