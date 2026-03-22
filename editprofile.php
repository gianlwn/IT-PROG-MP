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

// get current user_data
$user_id = intval($_SESSION['user_id']);
$dlsu_id_number = intval(trim($_SESSION['dlsu_id_number']));
$dlsu_email = $_SESSION['dlsu_email'];
$first_name = trim($_SESSION['first_name']);
$last_name = trim($_SESSION['last_name']);
$course_code = trim($_SESSION['course_code']);
$role = $_SESSION['role'];
$phone_number = trim($_SESSION['phone_number']);
$current_pic = $_SESSION['profile_picture'];

// check if the email matches and determine email type (faculty/student/staff)
if (preg_match('/^[a-z]+(_[a-z]+)*@dlsu\.edu\.ph$/', $dlsu_email)) {
    $email_type = "student/staff";
} else if (preg_match('/^[a-z]+(\.[a-z]+)*@dlsu\.edu\.ph$/', $dlsu_email)) {
    $email_type = "faculty";
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $dlsu_id_number = intval(trim($_POST['dlsu_id_number']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $course_code = trim($_POST['course_code']);
    $role = $_POST['role'];
    $phone_number = trim($_POST['phone_number']);
    $profile_picture_name = $current_pic;

    // handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $upload_dir = "profile_pictures/";

        // automatically create the folder if it doesn't exist yet
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));

        // create a unique file name to prevent overwriting
        $new_file_name = "user_" . $user_id . "_" . time() . "." . $file_extension;
        $dest_path = $upload_dir . $new_file_name;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $dest_path)) {
            $profile_picture_name = $new_file_name;
        } else {
            $error_message = "Failed to move uploaded image.";
        }
    }

    // proceed with update if there were no upload errors
    if (empty($error_message)) {
        if ((empty($password) && !empty($confirm_password)) || (!empty($password) && empty($confirm_password))) {
            $error_message = "Please fill both password fields.";
        } else if ($password !== $confirm_password) {
            $error_message = "Passwords do not match!";
        } else {
            if (!empty($password) && !empty($confirm_password) && $password === $confirm_password) {
                $password_hash = password_hash($password, PASSWORD_BCRYPT);
                $edit_query = "UPDATE users
                               SET dlsu_id_number = ?, password_hash = ?, first_name = ?, last_name = ?, course_code = ?, role = ?, phone_number = ?, profile_picture = ?
                               WHERE user_id = ?";

                $stmt = $conn->prepare($edit_query);
                if (!$stmt) die("Prepare failed: " . $conn->error);
                $stmt->bind_param("isssssssi", $dlsu_id_number, $password_hash, $first_name, $last_name, $course_code, $role, $phone_number, $profile_picture_name, $user_id);
            } else {
                $edit_query = "UPDATE users
                               SET dlsu_id_number = ?, first_name = ?, last_name = ?, course_code = ?, role = ?, phone_number = ?, profile_picture = ?
                               WHERE user_id = ?";

                $stmt = $conn->prepare($edit_query);
                if (!$stmt) die("Prepare failed: " . $conn->error);
                $stmt->bind_param("issssssi", $dlsu_id_number, $first_name, $last_name, $course_code, $role, $phone_number, $profile_picture_name, $user_id);
            }

            if ($stmt->execute()) {
                $user_query = "SELECT *
                               FROM users
                               WHERE user_id = ?";

                $stmt = $conn->prepare($user_query);
                if (!$stmt) die("Prepare failed: " . $conn->error);

                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $user_result = $stmt->get_result();

                if ($user_result->num_rows === 1) {
                    $user = $user_result->fetch_assoc();
                    $_SESSION['dlsu_id_number'] = intval($user['dlsu_id_number']);
                    $_SESSION['dlsu_email'] = $user['dlsu_email'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name'] = $user['last_name'];
                    $_SESSION['course_code'] = $user['course_code'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['phone_number'] = $user['phone_number'];
                    $_SESSION['profile_picture'] = $user['profile_picture'];
                    $current_pic = $user['profile_picture'];
                }

                $success_message = "Edited profile successfully!";
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
    <link rel="stylesheet" href="stylesheets/profile_upload.css">
    <title>DLSU Marketplace | Edit User Profile</title>
</head>

<body>
    <form action="editprofile.php" method="post" enctype="multipart/form-data">
        <div class="container">
            <div class="header">
                <h1>Edit Profile</h1>
                <hr>
                <div class="profile-top">
                    <div class="profile-img-wrapper">
                        <img src="profile_pictures/<?php echo htmlspecialchars($current_pic); ?>" class="profile-image" id="profilePreview" alt="Profile Picture">
                        <label for="profile_picture" class="upload-btn">Change Picture</label>
                        <input type="file" name="profile_picture" id="profile_picture" accept="image/*" class="file-input" onchange="previewImage(event)">
                    </div>

                    <div class="profile-info">
                        <div class="id-container">
                            <label for="id_number">ID Number</label>
                            <input type="text" name="dlsu_id_number" value="<?php echo htmlspecialchars($dlsu_id_number) ?>" class="input-field" minlength="8" maxlength="8" pattern="[0-9]{8}" placeholder="e.g. 12345678">
                        </div>
                        <div class="email-display">
                            Email: <?php echo $dlsu_email; ?>
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
                            window.location.href = 'editprofile.php?test=success';
                        }, 3000);
                    </script>
                <?php endif; ?>

                <div class="input-container">
                    <div class="form-column">
                        <div class="section-header">Personal Information</div>
                        <label for="first_name">First Name</label>
                        <input type="text" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" class="input-field" placeholder="e.g. Juan">
                        <label for="last_name">Last Name</label>
                        <input type="text" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" class="input-field" placeholder="e.g. Dela Cruz">
                        <label for="course_code">Course Code</label>
                        <input type="text" name="course_code" value="<?php echo htmlspecialchars($course_code); ?>" class="input-field" placeholder="e.g. BS-IT">
                        <?php if ($email_type == "student/staff"): ?>
                            <div class="role-group">
                                <div class="section-header">Role</div>
                                <label><input type="radio" name="role" value="Student" <?php echo $role == "Student" ? "checked" : ""; ?>> Student</label>
                                <label><input type="radio" name="role" value="Staff" <?php echo $role == "Staff" ? "checked" : ""; ?>> Staff</label>
                            </div>
                        <?php elseif ($email_type == "faculty"): ?>
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
                            placeholder="e.g. 09685706073">
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
            </div>
    </form>

    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('profilePreview');
                output.src = reader.result;
            };
            if (event.target.files[0]) {
                reader.readAsDataURL(event.target.files[0]);
            }
        }
    </script>
</body>

</html>