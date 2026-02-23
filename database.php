<?php
$db_server = "localhost";
$db_user = "root"; // change details based on your credentials
$db_password = "1234"; // change details based on your credentials
$db_name = "itprogmp_db";
$conn = new mysqli($db_server, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// set charset
$conn->set_charset("utf8mb4");
?>