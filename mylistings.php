<?php
session_start();
require 'db.php';

# check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: loginpage.php');
    exit();
}

# user data for display
$user_id = $_SESSION['user_id'];
$dlsu_id_number = $_SESSION['dlsu_id_number'];
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
$full_name = trim($first_name . ' ' . $last_name);
$role = $_SESSION['role'];
$profile_pic = 'profile_pictures/' . $_SESSION['profile_picture'];
$admin_role_id = intval($_SESSION['admin_role_id']);

# handle top nav bar actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'createlisting') {
        header('Location: createlisting.php');
        exit();
    } else if (isset($_POST['action']) && $_POST['action'] == 'viewcart') {
        header('Location: viewcart.php');
        exit();
    }
}

# get all cart items
$cart_query = "SELECT COUNT(*) as cart_count
               FROM cart
               WHERE buyer_id = ?";

$stmt = $conn->prepare($cart_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$cart_result = $stmt->get_result();

if ($cart_result->num_rows == 1) {
    $cart_row = $cart_result->fetch_assoc();
}

# get all 'my listings'
$my_listings = [];

$ml_query = "SELECT l.listing_id, l.product_name, l.price, l.status, l.quantity, li.image_path,
                    CONCAT(u.first_name, ' ', u.last_name) AS seller_name,
                    (SELECT IFNULL(ROUND(AVG(rating_value), 1), 0) FROM ratings WHERE rated_user_id = u.user_id) AS avg_rating
             FROM listings l
             LEFT JOIN (
                SELECT listing_id, MIN(image_path) AS image_path
                FROM listing_images
                GROUP BY listing_id
             ) li ON li.listing_id = l.listing_id
             LEFT JOIN users u ON u.user_id = l.seller_id
             WHERE l.seller_id = ?";

$stmt = $conn->prepare($ml_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$ml_result = $stmt->get_result();

if ($ml_result->num_rows > 0) {
    while ($ml_row = $ml_result->fetch_assoc()) {
        $my_listings[] = $ml_row;
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
    <link rel="stylesheet" href="stylesheets/mylistings.css" />
    <title>DLSU Marketplace | My Listings</title>
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="user-profile-section">
                <img src="<?= $profile_pic; ?>" alt="Profile" class="nav-logo">
                <div class="user-info-display">
                    <h2 class="user-name"><?= htmlspecialchars($full_name); ?></h2>
                    <p class="user-id"><?= htmlspecialchars("$role, ID: $dlsu_id_number"); ?></p>
                </div>
            </div>
            <nav class="nav-menu">
                <a href="home.php">Home</a>
                <a href="mylistings.php" class="active">My Listings</a>
                <a href="myclaims.php">My Claims</a>
                <a href="editprofile.php">Edit Profile</a>
                <?php if (!empty($admin_role_id)): ?>
                    <a href="admin_dashboard.php">Admin Dashboard</a>
                <?php endif; ?>
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
                        <button type="submit" class="cart-btn" name="action" value="viewcart">Cart (<?= htmlspecialchars($cart_row['cart_count']); ?>)</button>
                        <button type="submit" class="create-listing-btn" name="action" value="createlisting">+ Create Listing</button>
                    </div>
                </form>
            </header>
            <div class="cart-header">
                <h1>My Listings</h1>
                <a href="home.php" class="back-link">← Back to Market</a>
            </div>
            <div class="cart-layout">
                <div class="cart-items-section">
                    <?php if (empty($my_listings)): ?>
                        <div class="empty-cart">
                            <h2>You currently do not have listings!</h2>
                            <p>Looks like you haven't added anything yet.</p>
                            <a href="createlisting.php" class="browse-btn">Create a Listing</a>
                            <a href="home.php" class="browse-btn">Browse Items</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($my_listings as $l): ?>
                            <div href="<?= "viewitem.php?listing_id=" . $l['listing_id']; ?>" class="cart-item-link">
                                <div class="cart-item-card clickable-card" data-link="<?= "viewitem.php?listing_id=" . $l['listing_id']; ?>">
                                    <div class="item-img-box">
                                        <?php if (!empty($l['image_path'])): ?>
                                            <img src="<?= $l['image_path']; ?>" alt="Product">
                                        <?php else: ?>
                                            <div class="no-img">No Image</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="item-details">
                                        <h3 class="item-title"><?= htmlspecialchars($l['product_name']); ?></h3>
                                        <p class="item-seller">
                                            Sold by: <?= htmlspecialchars($l['seller_name']); ?>
                                            <span class="seller-rating">
                                                <?php if ($l['avg_rating'] > 0): ?>
                                                    ★ <?= htmlspecialchars($l['avg_rating']); ?>
                                                <?php else: ?>
                                                    <span class="no-rating">★ N/A</span>
                                                <?php endif; ?>
                                            </span>
                                            <?php if ($l['status'] == 'Pending'): ?>
                                                <span class="listing-status" id="pending">Pending</span>
                                            <?php elseif ($l['status'] == 'Available'): ?>
                                                <span class="listing-status" id="live">Live</span>
                                            <?php elseif ($l['status'] == 'Rejected'): ?>
                                                <span class="listing-status" id="rejected">Rejected</span>
                                            <?php else: ?>
                                                <span class="listing-status" id="status"><?= htmlspecialchars($l['status']); ?></span>
                                            <?php endif; ?>
                                        </p>
                                        <p class="item-price">₱<?= htmlspecialchars(number_format($l['price'], 2)); ?></p>
                                    </div>
                                    <div class="item-actions-panel">
                                        <form action="mylistings_action.php" method="POST" class="action-form">
                                            <input type="hidden" name="listing_id" value="<?= $l['listing_id']; ?>">
                                            <div class="qty-control">
                                                <button name="action" value="updatelisting" class="btn update-btn">Update</button>
                                            </div>
                                        </form>
                                        <form action="mylistings_action.php" method="POST" class="action-form">
                                            <input type="hidden" name="listing_id" value="<?= $l['listing_id']; ?>">
                                            <button name="action" value="removefromlisting" class="btn remove-btn">Remove</button>
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