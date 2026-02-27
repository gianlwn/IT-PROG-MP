<?php
$host     = "localhost";
$username = "root"; // CHANGE TO YOUR CREDENTIALS 
$password = "1234"; // CHANGE TO YOUR CREDENTIALS 
$database = "task_master_db";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>