<?php
session_start();
require 'db.php';

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

// handle top nav bar actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["action"]) && $_POST["action"] === "createlisting") {
        header("Location: createlisting.php");
        exit();
    } else if (isset($_POST["action"]) && $_POST["action"] === "viewcart") {
        header("Location: viewcart.php");
        exit();
    }
}

// get all cart items
$cart_items = [];

$cart_query = "SELECT c.cart_id, c.quantity AS cart_qty, 
                      l.listing_id, l.product_name, l.price, l.quantity,
                      CONCAT(u.first_name, ' ', u.last_name) AS seller_name,
                      (SELECT IFNULL(ROUND(AVG(rating_value), 1), 0) FROM ratings WHERE rated_user_id = u.user_id) AS avg_rating,
                      (SELECT image_path FROM listing_images WHERE listing_id = l.listing_id LIMIT 1) AS main_image_path
               FROM cart c
               JOIN listings l ON c.listing_id = l.listing_id
               JOIN users u ON l.seller_id = u.user_id
               WHERE c.buyer_id = '$user_id'";

$cart_result = $conn->query($cart_query);

if ($cart_result == TRUE && $cart_result->num_rows > 0) {
    while ($cart_row = $cart_result->fetch_assoc()) {
        $cart_items[] = $cart_row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stylesheets/home.css" />
    <link rel="stylesheet" href="stylesheets/viewcart.css" />
    <title>DLSU Marketplace | My Cart</title>
</head>

<body>
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
                <a href="home.php">Home</a>
                <a href="mylistings.php">My Listings</a>
                <a href="myclaims.php">My Claims</a>
                <a href="editprofile.php">Edit Profile</a>
                <hr class="nav-divider">
                <a href="logout.php" class="logout-link">Logout</a>
            </nav>
        </aside>
        <main class="main-content cart-content">
            <header class="top-bar cart-top-bar">
                <form action="viewcart.php" method="POST" class="top-bar-form">
                    <div class="search-wrapper">
                        <input type="text" placeholder="Search for items...">
                    </div>
                    <div class="header-actions">
                        <button type="submit" class="cart-btn" name="action" value="viewcart">Cart (<?php echo count($cart_items); ?>)</button>
                        <button type="submit" class="create-listing-btn" name="action" value="createlisting">+ Create Listing</button>
                    </div>
                </form>
            </header>
            <div class="cart-header">
                <h1>My Cart</h1>
                <a href="home.php" class="back-link">← Back to Market</a>
            </div>
            <div class="cart-layout">
                <div class="cart-items-section">
                    <?php if (empty($cart_items)): ?>
                        <div class="empty-cart">
                            <h2>Your cart is currently empty!</h2>
                            <p>Looks like you haven't added anything yet.</p>
                            <a href="home.php" class="browse-btn">Browse Items</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($cart_items as $item): ?>
                            <div href="<?php echo "viewitem.php?listing_id=" . $item["listing_id"] ?>" class="cart-item-link">
                                <div class="cart-item-card clickable-card" data-link="<?php echo "viewitem.php?listing_id=" . $item["listing_id"]; ?>">
                                    <div class="item-img-box">
                                        <?php if (!empty($item["main_image_path"])): ?>
                                            <img src="<?php echo $item["main_image_path"]; ?>" alt="Product">
                                        <?php else: ?>
                                            <div class="no-img">No Image</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="item-details">
                                        <h3 class="item-title"><?php echo $item["product_name"]; ?></h3>
                                        <p class="item-seller">
                                            Sold by: <?php echo $item["seller_name"]; ?>
                                            <span class="seller-rating">
                                                <?php if ($item["avg_rating"] > 0): ?>
                                                    ★ <?php echo $item["avg_rating"]; ?>
                                                <?php else: ?>
                                                    <span class="no-rating">★ N/A</span>
                                                <?php endif; ?>
                                            </span>
                                        </p>
                                        <p class="item-price">₱<?php echo number_format($item["price"], 2); ?></p>
                                        <p class="item-subtotal">Subtotal: ₱<?php echo number_format($item["price"] * $item["cart_qty"], 2); ?></p>
                                    </div>
                                    <div class="item-actions-panel">
                                        <form action="cart_action.php" method="POST" class="action-form">
                                            <input type="hidden" name="cart_id" value="<?php echo $item["cart_id"]; ?>">
                                            <div class="qty-control">
                                                <input type="number" name="new_qty" min="1" max="<?php echo $item["quantity"]; ?>" value="<?php echo $item["cart_qty"]; ?>" class="qty-input">
                                                <button type="submit" name="action" value="updatecart" class="btn update-btn">Update</button>
                                            </div>
                                        </form>
                                        <form action="contact_seller.php" method="POST" class="action-form">
                                            <input type="hidden" name="listing_id" value="<?php echo $item["listing_id"]; ?>">
                                            <button type="submit" class="btn contact-btn">Contact Seller</button>
                                        </form>
                                        <form action="cart_action.php" method="POST" class="action-form">
                                            <input type="hidden" name="cart_id" value="<?php echo $item["cart_id"]; ?>">
                                            <button type="submit" name="action" value="removefromcart" class="btn remove-btn">Remove</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <script>
        document.querySelectorAll(".clickable-card").forEach(function(card) {
            card.addEventListener("click", function(e) {
                // prevent redirect if clicking buttons, inputs, forms
                if (e.target.closest("button, input, form")) {
                    return;
                }
                window.location.href = card.dataset.link;
            });
        });
    </script>
</body>

</html>