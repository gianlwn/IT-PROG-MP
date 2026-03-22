<?php
session_start();
require 'db.php';

// check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: loginpage.php");
    exit();
}

// check if a listing ID was provided in the URL
if (!isset($_GET['listing_id']) || empty($_GET['listing_id'])) {
    header("Location: home.php");
    exit();
}

// user data for display
$dlsu_id_number = $_SESSION['dlsu_id_number'];
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
$full_name = trim($first_name . " " . $last_name);
$role = $_SESSION['role'];
$profile_pic = "images/" . $_SESSION['profile_picture'];
$admin_role_id = intval($_SESSION['admin_role_id']);

$listing_id = intval($_GET['listing_id']);

// handle top nav bar actions
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    if (isset($_POST['action']) && $_POST['action'] === "createlisting") {
        header("Location: createlisting.php");
        exit();
    } else if (isset($_POST['action']) && $_POST['action'] === "viewcart") {
        header("Location: viewcart.php");
        exit();
    }
}

// get cart count for the top nav bar
$cart_count_query = "SELECT COUNT(*) AS count
                     FROM cart
                     WHERE buyer_id = ?";

$stmt = $conn->prepare($cart_count_query);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_count_result = $stmt->get_result();

$cart_count = 0;

if ($cart_count_result->num_rows > 0) {
    $count_row = $cart_count_result->fetch_assoc();
    $cart_count = $count_row['count'];
}

$item_query = "SELECT c1.category_name AS cat1, c2.category_name AS cat2, c3.category_name AS cat3,
                      CONCAT(u.first_name, ' ', u.last_name) AS seller_name, IFNULL(ROUND(AVG(r.rating_value), 1), 0) AS avg_rating,
                      l.listing_id, l.seller_id, l.product_name, l.price, l.quantity, l.description, l.status
                FROM listings l
                LEFT JOIN users u ON u.user_id = l.seller_id
                LEFT JOIN ratings r ON r.rated_user_id = u.user_id
                LEFT JOIN categories c1 ON c1.category_id = l.category1_id
                LEFT JOIN categories c2 ON c2.category_id = l.category2_id
                LEFT JOIN categories c3 ON c3.category_id = l.category3_id
                WHERE l.listing_id = ?
                GROUP BY l.listing_id";

$stmt = $conn->prepare($item_query);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $listing_id);
$stmt->execute();
$item_result = $stmt->get_result();

// if the item doesnt exist, go back to home
if ($item_result->num_rows == 0) {
    header("Location: home.php");
    exit();
}

$item = $item_result->fetch_assoc();

// get all images for this listing
$images = [];

$image_query = "SELECT image_path
                FROM listing_images
                WHERE listing_id = ?";

$stmt = $conn->prepare($image_query);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $listing_id);
$stmt->execute();
$image_result = $stmt->get_result();

if ($image_result->num_rows > 0) {
    while ($img_row = $image_result->fetch_assoc()) {
        $images[] = $img_row['image_path'];
    }
}

// get the first image
$main_image = !empty($images) ? $images[0] : null;

// get all the categories for this listng
$categories = [];
if (!empty($item['cat1'])) $categories[] = $item['cat1'];
if (!empty($item['cat2'])) $categories[] = $item['cat2'];
if (!empty($item['cat3'])) $categories[] = $item['cat3'];
$category_display = implode(', ', $categories);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stylesheets/home.css" />
    <link rel="stylesheet" href="stylesheets/viewitem.css" />
    <title>DLSU Marketplace | <?php echo $item['product_name']; ?></title>
</head>

<body>
    <?php if (isset($_GET['success']) && $_GET['success'] == "added"): ?>
        <div id="toast-notification" class="toast success show">
            <span class="toast-icon">✓</span>
            Item successfully added to your cart!
        </div>
    <?php elseif (isset($_GET['error'])): ?>
        <div id="toast-notification" class="toast error show">
            <span class="toast-icon">✕</span>
            <?php
            if ($_GET['error'] == "exceeds") echo "Cannot add more than available stock!";
            else if ($_GET['error'] == "nostock") echo "Sorry, this item is currently out of stock.";
            else echo "Something went wrong. Please try again.";
            ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['success']) || isset($_GET['error'])): ?>
        <script>
            setTimeout(function() {
                var toast = document.getElementById("toast-notification");
                if (toast) {
                    toast.classList.remove("show");
                }
                window.history.replaceState(null, null, window.location.pathname + "?listing_id=<?php echo $listing_id; ?>");
            }, 3000);
        </script>
    <?php endif; ?>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="user-profile-section">
                <img src="<?php echo $profile_pic; ?>" alt="Profile" class="nav-logo">
                <div class="user-info-display">
                    <h2 class="user-name"><?php echo $full_name; ?></h2>
                    <p class="user-id"><?php echo "$role, ID: $dlsu_id_number"; ?></p>
                </div>
            </div>
            <nav class="nav-menu">
                <a href="home.php">Home</a>
                <a href="mylistings.php">My Listings</a>
                <a href="myclaims.php">My Claims</a>
                <a href="editprofile.php">Edit Profile</a>
                <?php if ($admin_role_id == 1 || $admin_role_id == 2): ?>
                    <a href="#">Admin Dashboard</a>
                <?php endif; ?>
                <?php if ($admin_role_id == 1): ?>
                    <a href="#">Assign Admins</a>
                <?php endif; ?>
                <hr class="nav-divider">
                <a href="logout.php" class="logout-link">Logout</a>
            </nav>
        </aside>
        <main class="main-content">
            <header class="top-bar" style="margin-bottom: 20px;">
                <form action="viewitem.php?listing_id=<?php echo $listing_id; ?>" method="POST" class="top-bar-form">
                    <div class="search-wrapper">
                        <input type="text" placeholder="Search for items...">
                    </div>
                    <div class="header-actions">
                        <button type="submit" class="cart-btn" name="action" value="viewcart">Cart (<?php echo $cart_count; ?>)</button>
                        <button type="submit" class="create-listing-btn" name="action" value="createlisting">+ Create Listing</button>
                    </div>
                </form>
            </header>
            <div class="item-container">
                <div class="image-section">
                    <div class="main-image-box">
                        <?php if (!empty($main_image)): ?>
                            <img src="<?php echo $main_image; ?>" alt="Main Product Image" class="main-img" id="mainImage">
                        <?php else: ?>
                            <div class="no-image-large">No Image Available</div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($images)): ?>
                        <div class="thumbnail-row">
                            <?php foreach ($images as $img): ?>
                                <img src="<?php echo $img; ?>" alt="Thumbnail" class="thumbnail" onclick="document.getElementById('mainImage').src=this.src;">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="details-section">
                    <div class="category-wrapper">
                        <?php foreach ($categories as $cat): ?>
                            <span class="category-badge"><?php echo $cat; ?></span>
                        <?php endforeach; ?>
                    </div>
                    <h1 class="product-title"><?php echo $item['product_name']; ?></h1>
                    <div class="seller-info">
                        <span>Sold by: <strong><?php echo $item['seller_name']; ?></strong></span>
                        <?php if ($item['avg_rating'] > 0): ?>
                            <span class="seller-rating">★ <?php echo number_format($item['avg_rating'], 1); ?></span>
                        <?php else: ?>
                            <span class="seller-rating">★ N/A</span>
                        <?php endif; ?>
                    </div>
                    <div class="price-box">
                        <h2 class="price">₱<?php echo number_format($item['price'], 2); ?></h2>
                        <span class="stock">Stock: <?php echo intval($item['quantity']); ?></span>
                    </div>
                    <div class="description-box">
                        <h3>Description</h3>
                        <p><?php echo nl2br($item['description']); ?></p>
                    </div>
                    <?php if ($item['seller_id'] == $user_id): ?>
                        <div style="background: #e8e6d9; color: #4a543e; padding: 15px; border-radius: 10px; text-align: center; font-weight: bold; border: 1px dashed #798367;">
                            This is your own listing!
                        </div>
                    <?php else: ?>
                        <form action="cart_action.php" method="POST" class="purchase-form">
                            <input type="hidden" name="action" value="addtocart">
                            <input type="hidden" name="listing_id" value="<?php echo $item['listing_id']; ?>">
                            <div class="qty-input">
                                <label for="buy_qty">Quantity to buy:</label>
                                <input type="number" id="buy_qty" name="buy_qty" min="1" max="<?php echo intval($item['quantity']); ?>" value="1" required>
                            </div>
                            <button type="submit" class="add-cart-btn">Add to Cart</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>

</html>