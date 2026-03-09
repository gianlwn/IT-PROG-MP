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
$profile_pic = $_SESSION["profile_picture"] ?? "login-icon.png";
$pic_path = "images/" . $profile_pic;
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
                <img src="<?php echo htmlspecialchars($pic_path); ?>" alt="Profile" class="nav-logo">
                <div class="user-info-display">
                    <h2 class="user-name"><?php echo htmlspecialchars($full_name); ?></h2>
                    <p class="user-id">ID: <?php echo htmlspecialchars($user_id); ?></p>
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
                <div class="section-container">
                    <h3 class="section-title">Quick Filter</h3>
                    <div class="category-scroll">
                        <button class="filter-chip active">All</button>
                        <button class="filter-chip">Books</button>
                        <button class="filter-chip">Uniforms</button>
                        <button class="filter-chip">Electronics</button>
                        <button class="filter-chip">School Supplies</button>
                        <button class="filter-chip">Laboratory Gear</button>
                        <button class="filter-chip">Others</button>
                    </div>
                </div>

                <div class="section-container">
                    <h3 class="section-title">Featured Items</h3>
                    <div class="product-grid">
                        <div class="product-card">
                            <div class="product-image-placeholder">No Image</div>
                            <div class="product-info">
                                <span class="category-tag">Books</span>
                                <h4 class="item-name">Engineering Math Textbook</h4>
                                <p class="item-price">₱450.00</p>
                                <button class="view-item-btn">View Details</button>
                            </div>
                        </div>
                        <div class="product-card">
                            <div class="product-image-placeholder">No Image</div>
                            <div class="product-info">
                                <span class="category-tag">Uniforms</span>
                                <h4 class="item-name">Men's Polo (Medium)</h4>
                                <p class="item-price">₱300.00</p>
                                <button class="view-item-btn">View Details</button>
                            </div>
                        </div>
                        <div class="product-card">
                            <div class="product-image-placeholder">No Image</div>
                            <div class="product-info">
                                <span class="category-tag">Electronics</span>
                                <h4 class="item-name">Scientific Calculator</h4>
                                <p class="item-price">₱1,200.00</p>
                                <button class="view-item-btn">View Details</button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>