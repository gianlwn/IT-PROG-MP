<?php
session_start();
include 'db.php';

// check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: loginpage.php");
    exit();
}


// user data for display
$first_name = $_SESSION["first_name"];
$last_name = $_SESSION["last_name"];
$full_name = trim($first_name . " " . $last_name);
$user_id = $_SESSION["user_id"];
$profile_pic = $_SESSION["profile_picture"] ?? "login-icon.png";
$pic_path = "images/" . $profile_pic;

// get all listings
$listings = [];
$listing_query = "SELECT l.listing_id, c1.category_name AS cat1, c2.category_name AS cat2, c3.category_name AS cat3,
                         l.product_name, CONCAT(u.first_name, ' ', u.last_name) AS full_name, IFNULL(AVG(r.rating_value), 0) AS avg_rating,
                         l.price, l.quantity, (SELECT image_path FROM listing_images WHERE listing_id = l.listing_id LIMIT 1) AS image_path
                  FROM listings l
                  LEFT JOIN users u ON u.user_id = l.seller_id
                  LEFT JOIN ratings r ON r.rated_user_id = u.user_id
                  LEFT JOIN categories c1 ON c1.category_id = l.category1_id
                  LEFT JOIN categories c2 ON c2.category_id = l.category2_id
                  LEFT JOIN categories c3 ON c3.category_id = l.category3_id
                  WHERE l.status = 'Available'
                  GROUP BY l.listing_id";

$listing_result = $conn->query($listing_query);

if ($listing_result == TRUE && $listing_result->num_rows > 0) {
    while ($row = $listing_result->fetch_assoc()) {
        $listings[] = $row;
    }
}

// handle action for createlisting.php, viewcart.php, and viewitem.php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["action"]) && $_POST["action"] === "createlisting") {
        header("Location: createlisting.php");
        exit();
    } else if (isset($_POST["action"]) && $_POST["action"] === "viewcart") {
        header("Location: viewcart.php");
        exit();
    } else if (isset($_POST["viewitem"])) {
        header("Location: viewitem.php?listing_id=" . $_POST["viewitem"]);
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
                        <button class="cart-btn" type="submit" name="action" value="viewcart">Cart (0)</button>
                        <button class="create-listing-btn" type="submit" name="action" value="createlisting">+ Create Listing</button>
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
                        <h3 class="section-title">All Items</h3>
                        <div class="product-grid">
                            <?php foreach ($listings as $l): ?>
                                <div class="product-card">
                                    <?php if (!empty($l["image_path"])): ?>
                                        <img src="<?php echo $l["image_path"]; ?>" alt="Product Image" class="product-image">
                                    <?php else: ?>
                                        <div class="product-image-placeholder">No Image</div>
                                    <?php endif; ?>
                                    <div class="product-info">
                                        <?php
                                        $categories = [];
                                        if (!empty($l["cat1"])) $categories[] = $l["cat1"];
                                        if (!empty($l["cat2"])) $categories[] = $l["cat2"];
                                        if (!empty($l["cat3"])) $categories[] = $l["cat3"];
                                        $category_display = implode(', ', $categories);
                                        ?>
                                        <span class="category-tag"><?php echo $category_display; ?></span>
                                        <h4 class="item-name"><?php echo $l["product_name"]; ?></h4>
                                        <div class="seller-row">
                                            <span class="seller-name"><?php echo $l["full_name"]; ?></span>
                                            <span class="seller-rating">★ <?php echo number_format($l["avg_rating"], 1); ?></span>
                                        </div>
                                        <div class="price-qty-row">
                                            <p class="item-price">₱<?php echo number_format($l["price"], 2); ?></p>
                                            <span class="item-quantity">Qty: <?php echo $l["quantity"]; ?></span>
                                        </div>
                                        <button class="view-item-btn" name="viewitem" value="<?php echo $l["listing_id"]; ?>">View Details</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </form>
</body>

</html>