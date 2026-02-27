<?php
session_start();
include 'db.php';

$error_message = "";
$success_message = "";

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
            
            <div id="input-container">
                <label for="profile_pic">Profile Picture:</label>
                <input type="file" name="profile_pic" id="profile_pic" class="file-input" accept="image/*">
                
                <label for="first_name">First Name:</label>
                <input type="text" name="first_name" id="first_name" class="input-field" placeholder="e.g. Juan" required>
                
                <label for="last_name">Last Name:</label>
                <input type="text" name="last_name" id="last_name" class="input-field" placeholder="e.g. Dela Cruz" required>
                
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