<?php
session_start();
include 'db.php';

// check if user is logged in
if (!isset($_SESSION["dlsu_email"])) {
    header("Location: loginpage.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Listing</title>
</head>
<body>
    
</body>
</html>