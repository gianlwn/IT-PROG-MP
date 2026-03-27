<?php
session_start();
require 'db.php';

# check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: loginpage.php');
    exit();
}

# kick out anyone who is not an admin
$admin_role_id = isset($_SESSION['admin_role_id']) ? intval($_SESSION['admin_role_id']) : 0;
if ($admin_role_id !== 1 && $admin_role_id !== 2) {
    header('Location: home.php');
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

$success_msg = '';
$error_msg = '';

# handle top nav bar actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'createlisting') {
            header('Location: createlisting.php');
            exit();
        } else if ($_POST['action'] == 'viewcart') {
            header('Location: viewcart.php');
            exit();
        } else if ($_POST['action'] == 'approve' || $_POST['action'] == 'reject') {
            $target_listing_id = intval($_POST['target_listing_id']);

            if ($_POST['action'] == 'approve') {
                # fetch the actual admin_id from the admin_accounts table for the currently logged in user
                $admin_query = "SELECT admin_id
                                FROM admin_accounts
                                WHERE user_id = ?";

                $stmt = $conn->prepare($admin_query);
                $stmt->bind_param('i', $user_id);
                $stmt->execute();
                $admin_res = $stmt->get_result();

                if ($admin_row = $admin_res->fetch_assoc()) {
                    $admin_id = $admin_row['admin_id'];

                    # update status to Available and log who approved it
                    $approve_query = "UPDATE listings
                                      SET status = 'Available', approved_by = ?
                                      WHERE listing_id = ?";

                    $stmt = $conn->prepare($approve_query);
                    $stmt->bind_param('ii', $admin_id, $target_listing_id);
                    if ($stmt->execute()) {
                        $success_msg = 'Listing #' . $target_listing_id. ' has been approved and is now live!';
                    } else {
                        $error_msg = 'Failed to approve listing.';
                    }
                }
            } else if ($_POST['action'] == 'reject') {
                # update status to Rejected
                $reject_query = "UPDATE listings
                                 SET status = 'Rejected'
                                 WHERE listing_id = ?";

                $stmt = $conn->prepare($reject_query);
                $stmt->bind_param('i', $target_listing_id);
                if ($stmt->execute()) {
                    $success_msg = 'Listing #' . $target_listing_id . ' has been rejected.';
                } else {
                    $error_msg = 'Failed to reject listing.';
                }
            }
        }
    }
}

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

# fetch all pending listings
$pending_listings = [];
$pending_query = "SELECT l.*, CONCAT(u.first_name, ' ', u.last_name) AS seller_name 
                  FROM listings l 
                  JOIN users u ON l.seller_id = u.user_id 
                  WHERE l.status = 'Pending' 
                  ORDER BY l.updated_at DESC";

$pending_res = $conn->query($pending_query);

if ($pending_res && $pending_res->num_rows > 0) {
    while ($row = $pending_res->fetch_assoc()) {
        $pending_listings[] = $row;
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
    <title>DLSU Marketplace | Listing Approvals</title>
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
            <header class="top-bar admin-top-bar">
                <form action="admin_approvals.php" method="POST" class="top-bar-form admin-top-bar-form">
                    <div class="title-header">
                        <h2 style="margin-top: 3px;">DLSU Marketplace</h2>
                    </div>
                    <div class="header-actions">
                        <button type="submit" class="cart-btn" name="action" value="viewcart">Cart (<?= $cart_count; ?>)</button>
                        <button type="submit" class="create-listing-btn" name="action" value="createlisting">+ Create Listing</button>
                    </div>
                </form>
            </header>

            <div class="admin-header">
                <a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a>
                <h1>Listing Approvals</h1>
                <p>Review new marketplace items submitted by users to ensure they meet community guidelines.</p>
            </div>

            <?php if ($success_msg): ?>
                <div class="alert success"><?= htmlspecialchars($success_msg); ?></div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="alert error"><?= htmlspecialchars($error_msg); ?></div>
            <?php endif; ?>

            <div class="admin-panel">
                <h3>Pending Listings (<?= count($pending_listings); ?>)</h3>

                <?php if (empty($pending_listings)): ?>
                    <div style="text-align: center; padding: 40px; color: #798367;">
                        <p>No pending listings. You're all caught up!</p>
                    </div>
                <?php else: ?>
                    <table class="admin-table">
                        <tr>
                            <th>Item Details</th>
                            <th>Seller</th>
                            <th>Price</th>
                            <th>Last Activity</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                        <?php foreach ($pending_listings as $listing): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($listing['product_name']); ?></strong><br>
                                    <span style="font-size: 0.85rem; color: #798367;">Qty: <?= htmlspecialchars($listing['quantity']); ?></span>
                                </td>
                                <td><?= htmlspecialchars($listing['seller_name']); ?></td>
                                <td style="font-weight: bold; color: #4a543e;">₱<?= number_format($listing['price'], 2); ?></td>
                                <td><?= date('M d, Y h:i A', strtotime($listing['updated_at'])); ?></td>
                                <td>
                                    <form action="admin_approvals.php" method="POST" style="margin: 0; display: flex; gap: 8px; justify-content: flex-end;">
                                        <input type="hidden" name="target_listing_id" value="<?= $listing['listing_id']; ?>">

                                        <a href="viewitem.php?listing_id=<?= $listing['listing_id']; ?>" class="admin-btn" style="padding: 8px 12px; font-size: 0.85rem; background: #798367;" target="_blank">View</a>

                                        <button type="submit" name="action" value="approve" class="btn-approve" onclick="return confirm('Approve this listing to go live?');">Approve</button>
                                        <button type="submit" name="action" value="reject" class="btn-delete" onclick="return confirm('Reject this listing? It will not be shown on the marketplace.');">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>

</html>