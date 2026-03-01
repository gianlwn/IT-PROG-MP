<?php
session_start();
include 'db.php';

// prevent people from accessing this without getting verified
if (!isset($_SESSION["email_verified"]) || $_SESSION["email_verified"] !== true) {
    header("Location: verifypage.php");
    exit();
}

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_number = trim($_POST["id_number"] ?? "");
    $dlsu_email = $_SESSION["verification_email"];
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";
    $first_name = trim($_POST["first_name"] ?? "");
    $last_name = trim($_POST["last_name"] ?? "");
    $role = $_POST["role"] ?? "";

    if (empty($id_number) || empty($first_name) || empty($last_name) || empty($role) || empty($password)) {
        $error_message = "Please fill in all required fields.";
    } else if ($password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } else {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        $first_name_clean = $conn->real_escape_string($first_name);
        $last_name_clean = $conn->real_escape_string($last_name);

        $sql = "INSERT INTO users (user_id, dlsu_email, password_hash, first_name, last_name, role)
                VALUES ('$id_number', '$dlsu_email', '$password_hash', '$first_name_clean', '$last_name_clean', '$role')";

        try {
            if ($conn->query($sql) === TRUE) {
                // ask user to login again
                $success_message = "Profile created successfully! Redirecting to login...";

                // unset email_verified
                unset($_SESSION["email_verified"],);
            }
        } catch (mysqli_sql_exception $e) {
            if ($conn->errno == 1062) {
                $error_message = "This ID Number or Email is already registered.";
            } else {
                $error_message = "Database Error: " . $conn->error;
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
    <link rel="stylesheet" href="stylesheets/createuserprofile.css">
    <title>Create User Profile</title>
</head>

<body>
    <form action="createuserprofile.php" method="post" enctype="multipart/form-data">
        <div id="container">
            <div id="create-user-profile">Create Profile</div>

            <?php if (!empty($error_message)): ?>
                <div id="error-msg"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div id="success-msg"><?php echo $success_message; ?></div>
                <script>
                    setTimeout(() => {
                        window.location.href = 'loginpage.php?create=success';
                    }, 3000);
                </script>
            <?php endif; ?>

            <div id="input-container">
                <label for="id_number">ID Number:</label>
                <input type="text" name="id_number" id="id_number" class="input-field" minlength="8" maxlength="8" pattern="[0-9]{8}" placeholder="e.g. 12345678" required>

                <label for="first_name">First Name:</label>
                <input type="text" name="first_name" id="first_name" class="input-field" placeholder="e.g. Juan" required>

                <label for="last_name">Last Name:</label>
                <input type="text" name="last_name" id="last_name" class="input-field" placeholder="e.g. Dela Cruz" required>

                <?php
                if (strcmp($_SESSION["email_type"], "student/staff") == 0) {
                    echo "<label for=\"role\">Role:</label>
                          <select name=\"role\" id=\"role\" class=\"input-field\">
                            <option value=\"student\">Student</option>
                            <option value=\"staff\">Staff</option>
                          </select>";
                }
                ?>
                <?php
                if (strcmp($_SESSION["email_type"], "faculty") == 0) {
                    echo "<label for=\"role\">Role:</label>
                          <select name=\"role\" id=\"role\" class=\"input-field\">
                            <option value=\"faculty\">Faculty</option>
                          </select>";
                }
                ?>

                <label for="password">Password:</label>
                <input type="password" name="password" id="password" class="input-field" placeholder="Create a password" required>

                <label for="confirm_password">Confirm Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" class="input-field" placeholder="Confirm your password" required>

                <input type="submit" value="Complete Profile" id="submit-btn">
            </div>
        </div>
    </form>
</body>

</html>