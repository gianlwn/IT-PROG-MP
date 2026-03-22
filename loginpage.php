<?php
session_start();
require 'db.php';

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = trim($_POST["email"]);
  $password = $_POST["password"];

  $login_query = "SELECT u.user_id, u.dlsu_id_number, u.password_hash, u.first_name, u.last_name, u.role, u.dlsu_email, u.profile_picture, a.admin_role_id, ar.role_name
                  FROM users u
                  LEFT JOIN admin_accounts a ON a.user_id = u.user_id
                  LEFT JOIN admin_roles ar ON ar.admin_role_id = a.admin_role_id
                  WHERE dlsu_email = ?";

  $stmt = $conn->prepare($login_query);

  if (!$stmt) {
    die("Prepare failed: " . $conn->error);
  }

  $stmt->bind_param("s", $email);
  $stmt->execute();
  $login_result = $stmt->get_result();

  if ($login_result->num_rows === 1) {
    $user = $login_result->fetch_assoc();

    if (password_verify($password, $user["password_hash"])) {
      $_SESSION["user_id"] = intval($user["user_id"]);
      $_SESSION["dlsu_id_number"] = intval($user["dlsu_id_number"]);
      $_SESSION["first_name"] = $user["first_name"];
      $_SESSION["last_name"] = $user["last_name"];

      if (!empty($user["admin_role_id"])) {
        $_SESSION["role"] = $user["role_name"];
      } else {
        $_SESSION["role"] = $user["role"];
      }

      $_SESSION["dlsu_email"] = $user["dlsu_email"];
      $_SESSION["profile_picture"] = $user["profile_picture"];
      $_SESSION["admin_role_id"] = !empty($user["admin_role_id"]) ? $user["admin_role_id"] : "";
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
        <div class="error-msg"><?php echo htmlspecialchars($error_message); ?></div>
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