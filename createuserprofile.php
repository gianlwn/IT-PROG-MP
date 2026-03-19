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
    $id_number = intval(trim($_POST["id_number"]));
    $dlsu_email = $_SESSION["verification_email"];
    $password = $conn->real_escape_string($_POST["password"]);
    $confirm_password = $_POST["confirm_password"];
    $first_name = $conn->real_escape_string(trim($_POST["first_name"]));
    $last_name = $conn->real_escape_string(trim($_POST["last_name"]));
    $course_code = $conn->real_escape_string(trim($_POST["course_code"]));
    $role = $conn->real_escape_string($_POST["role"]);
    $phone_number = $conn->real_escape_string(trim($_POST["phone_number"]));


    if (empty($id_number) || empty($first_name) || empty($last_name) || empty($role) || empty($password)) {
        $error_message = "Please fill in all required fields.";
    } else if ($password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } else {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $create_query = "INSERT INTO users (user_id, dlsu_email, password_hash, first_name, last_name, course_code, role, phone_number)
                         VALUES ('$id_number', '$dlsu_email', '$password_hash', '$first_name', '$last_name', '$course_code', '$role', '$phone_number')";

        try {
            if ($conn->query($create_query) === TRUE) {
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
    <title>DLSU Marketplace | Create User Profile</title>
</head>

<body>
    <form action="createuserprofile.php" method="post" enctype="multipart/form-data">
        <div class="container">
            <div class="header">
                <h1>Create Profile</h1>
                <hr>
                <div class="profile-top">
                    <img src="images/login-icon.png" class="profile-image">
                    <div class="profile-info">
                        <div class="id-container">
                            <label for="id_number">ID Number</label>
                            <input type="text" name="id_number" class="input-field" minlength="8" maxlength="8" pattern="[0-9]{8}" placeholder="e.g. 12345678" required>
                        </div>
                        <div class="email-display">
                            Email: <?php echo $_SESSION["verification_email"]; ?>
                        </div>
                    </div>
                </div>
                <?php if (!empty($error_message)): ?>
                    <div class="error-msg"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <?php if (!empty($success_message)): ?>
                    <div class="success-msg"><?php echo $success_message; ?></div>
                    <script>
                        setTimeout(() => {
                            window.location.href = 'loginpage.php?create=success';
                        }, 3000);
                    </script>
                <?php endif; ?>
                <div class="input-container">
                    <div class="form-column">
                        <div class="section-header">Personal Information</div>
                        <label for="first_name">First Name</label>
                        <input type="text" name="first_name" class="input-field" placeholder="e.g. Juan" required>
                        <label for="last_name">Last Name</label>
                        <input type="text" name="last_name" class="input-field" placeholder="e.g. Dela Cruz" required>
                        <label for="course_code">Course Code</label>
                        <input type="text" name="course_code" class="input-field" placeholder="e.g. BS-IT" required>
                        <?php if ($_SESSION["email_type"] === "student/staff"): ?>
                            <div class="role-group">
                                <div class="section-header">Role</div>
                                <label><input type="radio" name="role" value="Student"> Student</label>
                                <label><input type="radio" name="role" value="Staff"> Staff</label>
                            </div
                                <?php elseif ($_SESSION["email_type"] === "faculty"): ?>
                                <div class="role-group">
                            <div class="section-header">Role</div>
                            <label><input type="radio" name="role" value="Faculty"> Faculty</label>
                    </div>
                <?php endif; ?>
                </div>
                <div class="form-column">
                    <div class="section-header">Contact Information</div>
                    <label for="phone_number">Phone Number</label>
                    <input type="text"
                        name="phone_number"
                        class="input-field"
                        pattern="09[0-9]{9}"
                        minlength="11"
                        maxlength="11"
                        placeholder="e.g. 09685706073"
                        required>
                    <div class="password-group">
                        <div class="section-header">Create Password</div>
                        <label for="password">New Password:</label>
                        <input type="password" name="password" class="input-field" placeholder="Create a password" required>
                        <br>
                        <label for="confirm_password">Confirm Password:</label>
                        <input type="password" name="confirm_password" class="input-field" placeholder="Confirm your password" required>
                        <input type="submit" value="Complete Profile" class="submit-btn">
                        <a href="logout.php">Cancel</a>
                    </div>
                </div>
            </div>
    </form>
</body>

</html>