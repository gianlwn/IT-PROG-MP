<?php
session_start();
require 'db.php';

// check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: loginpage.php");
    exit();
}

$error_message = "";
$success_message = "";
$email_type = "";

$dlsu_id_number = intval(trim($_SESSION['dlsu_id_number']));
$dlsu_email = $_SESSION['dlsu_email'];
$first_name = trim($_SESSION['first_name']);
$last_name = trim($_SESSION['last_name']);
$course_code = trim($_SESSION['course_code']);
$role = $_SESSION['role'];
$phone_number = trim($_SESSION['phone_number']);

if (preg_match('/^[a-z]+(_[a-z]+)*@dlsu\.edu\.ph$/', $dlsu_email)) {
    $email_type = "student/staff";

    // check if the code matches and is a faculty account
} else if (preg_match('/^[a-z]+(\.[a-z]+)*@dlsu\.edu\.ph$/', $dlsu_email)) {
    $email_type = "faculty";
}
/*
if (empty($id_number) || empty($first_name) || empty($last_name) || empty($role) || empty($password)) {
    $error_message = "Please fill in all required fields.";
} else if ($password !== $confirm_password) {
    $error_message = "Passwords do not match!";
} else {
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    $create_query = "INSERT INTO users (dlsu_id_number, dlsu_email, password_hash, first_name, last_name, course_code, role, phone_number)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    try {
        $stmt = $conn->prepare($create_query);

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("isssssss", $dlsu_id_number, $dlsu_email, $password_hash, $first_name, $last_name, $course_code, $role, $phone_number);

        if ($stmt->execute()) {
            // ask user to login again
            $success_message = "Profile created successfully! Redirecting to login...";

            // unset email_verified
            unset($_SESSION['email_verified']);
        }
    } catch (mysqli_sql_exception $e) {
        if ($conn->errno == 1062) {
            $error_message = "This ID Number or Email is already registered.";
        } else {
            $error_message = "Database Error: " . $conn->error;
        }
    }
}
    */
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
    <form action="editprofile.php" method="post" enctype="multipart/form-data">
        <div class="container">
            <div class="header">
                <h1>Create Profile</h1>
                <hr>
                <div class="profile-top">
                    <img src="images/login-icon.png" class="profile-image">
                    <div class="profile-info">
                        <div class="id-container">
                            <label for="id_number">ID Number</label>
                            <input type="text" name="id_number" value="<?php echo htmlspecialchars($dlsu_id_number) ?>" class="input-field" minlength="8" maxlength="8" pattern="[0-9]{8}" placeholder="e.g. 12345678" required>
                        </div>
                        <div class="email-display">
                            Email: <?php echo $_SESSION['dlsu_email']; ?>
                        </div>
                    </div>
                </div>
                <?php if (!empty($error_message)): ?>
                    <div class="error-msg"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>
                <?php if (!empty($success_message)): ?>
                    <div class="success-msg"><?php echo htmlspecialchars($success_message); ?></div>
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
                        <input type="text" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" class="input-field" placeholder="e.g. Juan" required>
                        <label for="last_name">Last Name</label>
                        <input type="text" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" class="input-field" placeholder="e.g. Dela Cruz" required>
                        <label for="course_code">Course Code</label>
                        <input type="text" name="course_code" value="<?php echo htmlspecialchars($course_code); ?>" class="input-field" placeholder="e.g. BS-IT" required>
                        <?php if ($email_type === "student/staff"): ?>
                            <div class="role-group">
                                <div class="section-header">Role</div>
                                <label><input type="radio" name="role" value="Student" <?php echo $role === "Student" ? "checked" : ""; ?> > Student</label>
                                <label><input type="radio" name="role" value="Staff" <?php echo $role === "Staff" ? "checked" : ""; ?> > Staff</label>
                            </div>
                        <?php elseif ($email_type === "faculty"): ?>
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
                            value="<?php echo htmlspecialchars($phone_number); ?>"
                            class="input-field"
                            pattern="09[0-9]{9}"
                            minlength="11"
                            maxlength="11"
                            placeholder="e.g. 09685706073"
                            required>
                        <div class="password-group">
                            <div class="section-header">Edit Password</div>
                            <label for="password">New Password:</label>
                            <input type="password" name="password" class="input-field" placeholder="Create a password">
                            <br>
                            <label for="confirm_password">Confirm Password:</label>
                            <input type="password" name="confirm_password" class="input-field" placeholder="Confirm your password">
                            <input type="submit" value="Complete Profile" class="submit-btn">
                            <a href="home.php">Cancel</a>
                        </div>
                    </div>
                </div>
    </form>
</body>

</html>