<?php
session_start();
require 'db.php';

# check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: loginpage.php');
    exit();
}

# kick out anyone who is not an admin
$admin_role_id = isset($_SESSION['admin_role_id']) ? $_SESSION['admin_role_id'] : 0;
if ($admin_role_id !== 1 && $admin_role_id !== 2) {
    header('Location: home.php');
    exit();
}

# user data for sidebar display
$user_id = $_SESSION['user_id'];
$dlsu_id_number = $_SESSION['dlsu_id_number'];
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
$full_name = trim($first_name . ' ' . $last_name);
$role = $_SESSION['role'];
$profile_pic = 'profile_pictures/' . $_SESSION['profile_picture'];

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

# get cart items count for top bar
$cart_query = "SELECT COUNT(*) AS cart_count
               FROM cart
               WHERE buyer_id = ?";

$stmt = $conn->prepare($cart_query);

if ($stmt) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $cart_result = $stmt->get_result();
    $cart_row = $cart_result->fetch_assoc();
    $cart_count = $cart_row['cart_count'];
} else {
    $cart_count = 0;
}

# get a count of how many listings are currently 'Pending'
$pending_count = 0;
$pending_query = "SELECT COUNT(*) AS count
                  FROM listings
                  WHERE status = 'Pending'";

$result = $conn->query($pending_query);

if ($result && $row = $result->fetch_assoc()) {
    $pending_count = $row['count'];
}

# get a count of how many reports are pending review
$pending_reports = 0;

$reports_query = "SELECT COUNT(*) AS count
                  FROM reports
                  WHERE status = 'Pending'";

$r_result = $conn->query($reports_query);
if ($r_result && $row = $r_result->fetch_assoc()) {
    $pending_reports = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stylesheets/home.css" />
    <link rel="stylesheet" href="stylesheets/admin.css" />
    <title>DLSU Marketplace | Admin Dashboard</title>
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="user-profile-section">
                <img src="<?= htmlspecialchars($profile_pic); ?>" alt="Profile" class="nav-logo">
                <div class="user-info-display">
                    <h2 class="user-name"><?= htmlspecialchars($full_name); ?></h2>
                    <p class="user-id">Admin, ID: <?= htmlspecialchars($dlsu_id_number); ?></p>
                </div>
            </div>
            <nav class="nav-menu">
                <a href="home.php">Home</a>
                <a href="mylistings.php">My Listings</a>
                <a href="myclaims.php">My Claims</a>
                <a href="editprofile.php">Edit Profile</a>
                <a href="admin_dashboard.php" class="active">Admin Dashboard</a>

                <hr class="nav-divider">
                <a href="logout.php" class="logout-link">Logout</a>
            </nav>
        </aside>
        <main class="main-content">
            <header class="top-bar admin-top-bar">
                <form action="admin_dashboard.php" method="POST" class="top-bar-form admin-top-bar-form">
                    <div class="search-wrapper">
                        <input type="text" placeholder="Search marketplace...">
                    </div>
                    <div class="header-actions">
                        <button type="submit" class="cart-btn" name="action" value="viewcart">Cart (<?= $cart_count; ?>)</button>
                        <button type="submit" class="create-listing-btn" name="action" value="createlisting">+ Create Listing</button>
                    </div>
                </form>
            </header>
            <div class="admin-header">
                <h1>Admin Dashboard</h1>
                <p>Welcome back, <?= htmlspecialchars($first_name); ?>. Here is an overview of the marketplace.</p>
            </div>
            <div class="admin-grid">
                <div class="admin-card">
                    <h2>
                        Listing Approvals
                        <?php if ($pending_count > 0): ?>
                            <span class="badge"><?= htmlspecialchars($pending_count); ?> Pending</span>
                        <?php endif; ?>
                    </h2>
                    <p>Review new marketplace items submitted by users. Ensure they follow community guidelines before making them live.</p>
                    <a href="admin_approvals.php" class="admin-btn">Review Listings</a>
                </div>
                <div class="admin-card">
                    <h2>Marketplace Categories</h2>
                    <p>Add new product categories or edit existing ones to help users better organize and find their listings.</p>
                    <a href="admin_categories.php" class="admin-btn">Manage Categories</a>
                </div>
                <div class="admin-card">
                    <h2>
                        User Reports
                        <?php if ($pending_reports > 0): ?>
                            <span class="badge badge-warning"><?= htmlspecialchars($pending_reports); ?> New</span>
                        <?php endif; ?>
                    </h2>
                    <p>Review and resolve marketplace reports regarding inappropriate items, disputes, or suspicious behavior.</p>
                    <a href="admin_reports.php" class="admin-btn">View Reports</a>
                </div>
                <div class="admin-card">
                    <h2>System Logs</h2>
                    <p>Monitor recent administrative actions, system events, and track changes made across the platform.</p>
                    <a href="admin_logs.php" class="admin-btn">View Logs</a>
                </div>
                <div class="admin-card">
                    <h2>Manage Users</h2>
                    <p>View registered accounts, handle reports, and suspend users who violate marketplace rules.</p>
                    <a href="#" class="admin-btn btn-disabled">Coming Soon</a>
                </div>
                <?php if ($admin_role_id == 1): ?>
                    <div class="admin-card">
                        <h2>Superadmin Management</h2>
                        <p>Elevate trusted users to administrative roles or revoke existing admin privileges.</p>
                        <a href="assign_admins.php" class="admin-btn">Manage Admins</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>

</html>