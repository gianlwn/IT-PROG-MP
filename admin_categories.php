<?php
session_start();
require 'db.php';

// check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: loginpage.php');
    exit();
}

// kick out anyone who is not an admin
$admin_role_id = isset($_SESSION['admin_role_id']) ? intval($_SESSION['admin_role_id']) : 0;
if ($admin_role_id !== 1 && $admin_role_id !== 2) {
    header('Location: home.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stylesheets/home.css">
    <link rel="stylesheet" href="stylesheets/admin.css">
    <title>DLSU Marketplace | Manage Categories</title>
</head>

<body>

</body>

</html>