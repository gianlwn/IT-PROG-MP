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
$admin_role_id = $_SESSION['admin_role_id'];

# get cart items count for top bar
$cart_query = "SELECT COUNT(*) AS cart_count
               FROM cart
               WHERE buyer_id = ?";

$stmt = $conn->prepare($cart_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$cart_result = $stmt->get_result();
$cart_row = $cart_result->fetch_assoc();
$cart_count = $cart_row['cart_count'];

# handle redirects for createlisting.php, viewcart.php, and viewitem.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'createlisting') {
        header('Location: createlisting.php');
        exit();
    } else if (isset($_POST['action']) && $_POST['action'] == 'viewcart') {
        header('Location: viewcart.php');
        exit();
    } else if (isset($_POST['viewitem'])) {
        header('Location: viewitem.php?listing_id=' . $_POST['viewitem']);
        exit();
    }
}

# search and filter logic
# determine the active filter
$active_filter = 'all';
if (isset($_POST['filter'])) {
    $active_filter = $_POST['filter']; # user clicked a category chip
} else if (isset($_POST['current_filter'])) {
    $active_filter = $_POST['current_filter']; # preserved from hidden input during a text search
}

# determine the search query
$search_query = $_POST['search_query'] ?? '';

# build the query
$listings = [];
$where_clauses = ["l.status = 'Available'", "l.quantity > 0"];
$params = [];
$param_types = '';

# if a specific category is selected, add it to the query
if ($active_filter !== 'all') {
    $category_id = intval($active_filter);
    $where_clauses[] = '(c1.category_id = ? OR c2.category_id = ? OR c3.category_id = ?)';
    $params[] = $category_id;
    $params[] = $category_id;
    $params[] = $category_id;
    $param_types .= 'iii';
}

# if the user typed something in the search bar, add it to the query
if (!empty($search_query)) {
    #search both product name and description
    $where_clauses[] = '(l.product_name LIKE ? OR l.description LIKE ?)';
    $search_like = '%' . $search_query . '%';
    $params[] = $search_like;
    $params[] = $search_like;
    $param_types .= 'ss';
}

$where_sql = implode(' AND ', $where_clauses);

$query = "SELECT l.listing_id, c1.category_name AS cat1, c2.category_name AS cat2, c3.category_name AS cat3,
                 l.product_name, CONCAT(u.first_name, ' ', u.last_name) AS full_name, IFNULL(ROUND(AVG(r.rating_value), 1), 0) AS avg_rating,
                 l.price, l.quantity, (SELECT image_path FROM listing_images WHERE listing_id = l.listing_id LIMIT 1) AS image_path
          FROM listings l
          LEFT JOIN users u ON u.user_id = l.seller_id
          LEFT JOIN ratings r ON r.rated_user_id = u.user_id
          LEFT JOIN categories c1 ON c1.category_id = l.category1_id
          LEFT JOIN categories c2 ON c2.category_id = l.category2_id
          LEFT JOIN categories c3 ON c3.category_id = l.category3_id
          WHERE $where_sql
          GROUP BY l.listing_id
          ORDER BY l.created_at DESC";

$stmt = $conn->prepare($query);

# bind parameters only if there are filters or searches applied
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}

$stmt->execute();
$listing_result = $stmt->get_result();

if ($listing_result->num_rows > 0) {
    while ($row = $listing_result->fetch_assoc()) {
        $listings[] = $row;
    }
}

# get all categories for the filter buttons
$categories = [];
$cat_query = "SELECT *
              FROM categories
              ORDER BY category_id ASC";

$stmt = $conn->prepare($cat_query);
$stmt->execute();
$cat_result = $stmt->get_result();

if ($cat_result->num_rows > 0) {
    while ($row = $cat_result->fetch_assoc()) {
        $categories[] = $row;
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
                    <img src="<?= htmlspecialchars($profile_pic); ?>" alt="Profile" class="nav-logo">
                    <div class="user-info-display">
                        <h2 class="user-name"><?= htmlspecialchars($full_name); ?></h2>
                        <p class="user-id"><?= htmlspecialchars("$role, ID: $dlsu_id_number"); ?></p>
                    </div>
                </div>
                <nav class="nav-menu">
                    <a href="home.php" class="active">Home</a>
                    <a href="mylistings.php">My Listings</a>
                    <a href="myclaims.php">My Claims</a>
                    <a href="editprofile.php">Edit Profile</a>
                    <?php if (!empty($admin_role_id)): ?>
                        <a href="admin_dashboard.php">Admin Dashboard</a>
                    <?php endif; ?>
                    <hr class="nav-divider">
                    <a href="logout.php" class="logout-link">Logout</a>
                </nav>
            </aside>
            <main class="main-content">
                <header class="top-bar">
                    <div class="search-wrapper">
                        <input type="text" name="search_query" placeholder="Search for items..." value="<?= htmlspecialchars($search_query); ?>">
                        <button type="submit" name="action" value="search" style="display: none;"></button>
                        <input type="hidden" name="current_filter" value="<?= htmlspecialchars($active_filter); ?>">
                    </div>
                    <div class="header-actions">
                        <button class="cart-btn" name="action" value="viewcart">Cart (<?= htmlspecialchars($cart_count); ?>)</button>
                        <button class="create-listing-btn" name="action" value="createlisting">+ Create Listing</button>
                    </div>
                </header>
                <section class="content-body">
                    <div class="section-container">
                        <h3 class="section-title">Filter Category</h3>
                        <div class="category-scroll">
                            <button class="filter-chip <?= ($active_filter == 'all') ? 'active-chip' : ''; ?>" name="filter" value="all">All</button>
                            <?php foreach ($categories as $c): ?>
                                <button class="filter-chip <?= ($active_filter == $c['category_id']) ? 'active-chip' : ''; ?>" name="filter" value="<?= $c['category_id']; ?>"><?= htmlspecialchars($c['category_name']); ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="section-container">
                        <h3 class="section-title">
                            <?php if (!empty($search_query)): ?>
                                Search Results for "<?= htmlspecialchars($search_query); ?>"
                            <?php else: ?>
                                All Items
                            <?php endif; ?>
                        </h3>
                        <div class="product-grid">
                            <?php if (empty($listings)): ?>
                                <div class="no-listings">
                                    <h3>No items found</h3>
                                    <p>We couldn't find any listings matching your search or filter criteria.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($listings as $l): ?>
                                    <a href="<?= "viewitem.php?listing_id=" . $l['listing_id']; ?>" class="cart-item-link">
                                        <div class="product-card">
                                            <?php if (!empty($l['image_path'])): ?>
                                                <img src="<?= htmlspecialchars($l['image_path']); ?>" alt="Product Image" class="product-image">
                                            <?php else: ?>
                                                <div class="product-image-placeholder">No Image</div>
                                            <?php endif; ?>
                                            <div class="product-info">
                                                <?php
                                                $cat_array = [];
                                                if (!empty($l['cat1'])) $cat_array[] = $l['cat1'];
                                                if (!empty($l['cat2'])) $cat_array[] = $l['cat2'];
                                                if (!empty($l['cat3'])) $cat_array[] = $l['cat3'];
                                                $category_display = implode(', ', $cat_array);
                                                ?>
                                                <span class="category-tag"><?= htmlspecialchars($category_display); ?></span>
                                                <h4 class="item-name"><?= htmlspecialchars($l['product_name']); ?></h4>
                                                <div class="seller-row">
                                                    <span class="seller-name"><?= htmlspecialchars($l['full_name']); ?></span>
                                                    <?php if ($l['avg_rating'] > 0): ?>
                                                        <span class="seller-rating">★ <?= htmlspecialchars($l['avg_rating']); ?></span>
                                                    <?php else: ?>
                                                        <span class="seller-rating">★ N/A</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="price-qty-row">
                                                    <p class="item-price">₱<?= htmlspecialchars($l['price']); ?></p>
                                                    <span class="item-quantity">Qty: <?= htmlspecialchars($l['quantity']); ?></span>
                                                </div>
                                                <button class="view-item-btn" name="viewitem" value="<?= $l['listing_id']; ?>">View Details</button>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </form>
</body>
</html>