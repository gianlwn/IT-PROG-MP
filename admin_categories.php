<?php
session_start();
require 'db.php';

// check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: loginpage.php');
    exit();
}

// kick out anyone who is not an admin
$admin_role_id = isset($_SESSION['admin_role_id']) ? intval($_SESSION['admin_role_id']) : 0;
if ($admin_role_id !== 1 && $admin_role_id !== 2) {
    header('Location: home.php');
    exit();
}

// user data for display
$user_id = $_SESSION['user_id'];
$dlsu_id_number = $_SESSION['dlsu_id_number'];
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
$full_name = trim($first_name . ' ' . $last_name);
$role = $_SESSION['role'];
$profile_pic = 'profile_pictures/' . $_SESSION['profile_picture'];
$admin_id = $_SESSION['admin_id'];
$admin_role_id = $_SESSION['admin_role_id'];
$success_msg = '';
$error_msg = '';

// handle top nav bar actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'createlisting') {
        header('Location: createlisting.php');
        exit();
    } else if (isset($_POST['action']) && $_POST['action'] == 'viewcart') {
        header('Location: viewcart.php');
        exit();
    } else if (isset($_POST['action']) && $_POST['action'] == 'addcategory') {
        $new_category = trim($_POST['category_name']);

        if (!empty($new_category)) {
            // check if category already exists to prevent duplicates
            $check_query = "SELECT category_id
                            FROM categories
                            WHERE category_name = ?";

            $stmt = $conn->prepare($check_query);

            $stmt->bind_param('s', $new_category);
            $stmt->execute();
            $check_res = $stmt->get_result();

            if ($check_res->num_rows > 0) {
                $error_msg = 'This category already exists!';
            } else {
                $insert_query = "INSERT INTO categories (category_name, created_by_admin_id)
                                 VALUES (?, ?)";

                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param('si', $new_category, $admin_id);

                if ($stmt->execute()) {
                    $success_msg = 'Category ' . $new_category . ' was added successfully!';
                }
            }
        } else {
            $error_msg = 'Category name cannot be empty.';
        }
    } else if ($_POST['action'] == 'deletecategory') {
        $cat_id = intval($_POST['delete_id']);

        $del_query = "DELETE FROM categories
                      WHERE category_id = ?";

        $stmt = $conn->prepare($del_query);
        $stmt->bind_param('i', $cat_id);

        if ($stmt->execute()) {
            $success_msg = "Category successfully deleted!";
        } else {
            $error_msg = "Failed to delete category.";
        }
    }
}

// get cart items count for top bar
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

// get all categories
$categories = [];
$cat_query = "SELECT c.*, CONCAT(u.first_name, ' ', u.last_name) AS full_name
              FROM categories c
              LEFT JOIN admin_accounts a ON a.admin_id = c.created_by_admin_id
              LEFT JOIN users u ON u.user_id = a.user_id
              ORDER BY c.created_at DESC";

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
    <link rel="stylesheet" href="stylesheets/admin.css" />
    <title>DLSU Marketplace | Manage Categories</title>
</head>

<body>
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
            <header class="top-bar" style="margin-bottom: 30px;">
                <form action="admin_categories.php" method="POST" class="top-bar-form" style="display: flex; width: 100%; justify-content: space-between;">
                    <div class="search-wrapper">
                        <input type="text" placeholder="Search marketplace...">
                    </div>
                    <div class="header-actions">
                        <button type="submit" class="cart-btn" name="action" value="viewcart">Cart (<?= htmlspecialchars($cart_count); ?>)</button>
                        <button type="submit" class="create-listing-btn" name="action" value="createlisting">+ Create Listing</button>
                    </div>
                </form>
            </header>
            <div class="admin-header">
                <a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a>
                <h1>Manage Categories</h1>
                <p>Add new categories to help users accurately organize their marketplace listings.</p>
            </div>
            <?php if ($success_msg): ?>
                <div class="alert success"><?= htmlspecialchars($success_msg); ?></div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="alert error"><?= htmlspecialchars($error_msg); ?></div>
            <?php endif; ?>
            <div class="admin-panel">
                <h3>Add a New Category</h3>
                <form action="admin_categories.php" method="POST" class="admin-inline-form">
                    <input type="text" name="category_name" class="admin-input" placeholder="e.g. Electronics" required>
                    <button type="submit" name="action" value="addcategory" class="admin-btn">Add Category</button>
                </form>
            </div>
            <div class="admin-panel" style="margin-top: 25px;">
                <h3>Current Categories</h3>
                <table class="admin-table">
                    <tr>
                        <th>Category Name</th>
                        <th>Created By</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td style="font-weight: 500; color: #4a543e;"><?= htmlspecialchars($cat['category_name']); ?></td>
                            <td><?= htmlspecialchars($cat['full_name']); ?></td>
                            <td>
                                <form action="admin_categories.php" method="POST" style="margin: 0;">
                                    <input type="hidden" name="delete_id" value="<?= $cat['category_id']; ?>">
                                    <button type="submit" name="action" value="deletecategory" class="btn-delete" onclick="return confirm('Are you sure you want to delete this category?');">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </main>
    </div>
</body>

</html>