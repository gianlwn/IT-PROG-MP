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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["action"]) && $_POST["action"] == "create") {
        header("Location: createlisting.php");
        exit();
    }
}
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
    <form action="home.php" method="post">
        <div class="dashboard-container">
            <aside class="sidebar">
                <div class="user-profile-section">
                    <img src="<?php echo $pic_path; ?>" alt="Profile" class="nav-logo">
                    <div class="user-info-display">
                        <h2 class="user-name"><?php echo $full_name; ?></h2>
                        <p class="user-id">ID: <?php echo $user_id; ?></p>
                    </div>
                </div>
                <nav class="nav-menu">
                    <a href="home.php" class="active">Home</a>
                    <a href="mylistings.php">My Listings</a>
                    <a href="myclaims.php">My Claims</a>
                    <a href="editprofile.php">Edit Profile</a>
                    <hr class="nav-divider">
                    <a href="destroy.php" class="logout-link">Logout</a>
                </nav>
            </aside>

            <main class="main-content">
                <header class="top-bar">
                    <div class="search-wrapper">
                        <input type="text" placeholder="Search for items...">
                    </div>
                    <div class="header-actions">
                        <button class="cart-btn">Cart (0)</button>
                        <button class="create-listing-btn" type="submit" name="action" value="create">+ Create Listing</button>
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
                                    <div class="seller-row">
                                        <span class="seller-name">Jason Benedict Lee</span>
                                        <span class="seller-rating">★ 4.8</span>
                                    </div>
                                    <p class="item-price">₱450.00</p>
                                    <button class="view-item-btn">View Details</button>
                                </div>
                            </div>
                            <div class="product-card">
                                <div class="product-image-placeholder">No Image</div>
                                <div class="product-info">
                                    <span class="category-tag">Uniforms</span>
                                    <h4 class="item-name">Men's Polo (Medium)</h4>
                                    <div class="seller-row">
                                        <span class="seller-name">Camille Erika Sarabia</span>
                                        <span class="seller-rating">★ 1.7</span>
                                    </div>
                                    <p class="item-price">₱300.00</p>
                                    <button class="view-item-btn">View Details</button>
                                </div>
                            </div>
                            <div class="product-card">
                                <div class="product-image-placeholder">No Image</div>
                                <div class="product-info">
                                    <span class="category-tag">Electronics</span>
                                    <h4 class="item-name">Scientific Calculator</h4>
                                    <div class="seller-row">
                                        <span class="seller-name">Giancarlo Lawan</span>
                                        <span class="seller-rating">★ 5.0</span>
                                    </div>
                                    <p class="item-price">₱1,200.00</p>
                                    <button class="view-item-btn">View Details</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </form>
</body>

</html>