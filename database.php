<?php
$db_server = "localhost";
$db_user = "root";
$db_password = "1234";
$db_name = "itprogmp_db";

$conn = mysqli_connect($db_server, $db_user, $db_password, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>