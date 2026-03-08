<?php
session_start();
include 'db.php';

// check if user is logged in
if (!isset($_SESSION["dlsu_email"])) {
    header("Location: loginpage.php");
    exit();
}

// user data for display
$first_name = $_SESSION["first_name"] ?? "User";
$last_name = $_SESSION["last_name"] ?? "";
$full_name = trim($first_name . " " . $last_name);
$user_id = $_SESSION["user_id"] ?? "00000000";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stylesheets/home.css" />
    <title>DLSU Marketplace | Home</title>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="user-profile-section">
                <img src="images/login-icon.png" alt="Profile" class="nav-logo">
                <div class="user-info-display">
                    <h2 class="user-name"><?php echo $full_name; ?></h2>
                    <p class="user-id">ID: <?php echo $user_id; ?></p>
                </div>
            </div>
            <nav class="nav-menu">
                <a href="home.php" class="active">Home</a>
                <a href="#">My Listings</a>
                <a href="#">My Claims</a>
                <a href="#">Profile</a>
                <hr class="nav-divider">
                <a href="logout.php" class="logout-link">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <div class="search-wrapper">
                    <input type="text" placeholder="Search for items...">
                </div>
                <div class="header-actions">
                    <button class="cart-btn">Cart (0)</button>
                    <button class="create-listing-btn">+ Create Listing</button>
                </div>
            </header>

            <section class="content-body">
                <div class="section-header">
                    <h3>Recent Listings</h3>
                </div>

                <div class="product-grid">