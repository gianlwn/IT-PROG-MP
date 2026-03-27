<?php
session_start();
require 'db.php';

# check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: loginpage.php');
    exit();
}

$success_msg = '';
$error_msg = '';

# check if a listing ID was provided
if (!isset($_GET['listing_id'])) {
    header('Location: mylistings.php');
    exit();
}

$listing_id = intval($_GET['listing_id']);
$seller_id = $_SESSION['user_id'];

$list_query = "SELECT *
               FROM listings
               WHERE listing_id = ? AND seller_id = ?";

$stmt = $conn->prepare($list_query);
$stmt->bind_param('ii', $listing_id, $seller_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: mylistings.php');
    exit();
} else {
    $listing = $result->fetch_assoc();

    # get all categories
    $categories = [];
    $cat_query = "SELECT *
                  FROM categories
                  ORDER BY category_id ASC";

    $stmt = $conn->prepare($cat_query);
    $stmt->execute();
    $cat_result = $stmt->get_result();

    if ($cat_result->num_rows > 0) {
        while ($category = $cat_result->fetch_assoc()) {
            $categories[] = $category;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'cancel') {
        header('Location: mylistings.php');
        exit();
    } else if (isset($_POST['action']) && $_POST['action'] == 'update' && !empty($_POST['product_name']) && !empty($_POST['description']) && !empty($_POST['category1_id']) && !empty($_POST['quantity']) && !empty($_POST['price'])) {

        $product_name = $_POST['product_name'];
        $description = $_POST['description'];
        $category1_id = intval($_POST['category1_id']);
        $category2_id = !empty($_POST['category2_id']) ? intval($_POST['category2_id']) : NULL;
        $category3_id = !empty($_POST['category3_id']) ? intval($_POST['category3_id']) : NULL;
        $quantity = intval($_POST['quantity']);
        $price = floatval($_POST['price']);

        # update the listing and automatically set it back to 'Pending' for admin re-approval
        $update_query = "UPDATE listings 
                         SET product_name = ?, description = ?, category1_id = ?, category2_id = ?, category3_id = ?, quantity = ?, price = ?, status = 'Pending', approved_by = NULL 
                         WHERE listing_id = ? AND seller_id = ?";

        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('ssiiidiii', $product_name, $description, $category1_id, $category2_id, $category3_id, $quantity, $price, $listing_id, $seller_id);

        if ($stmt->execute()) {
            header('Location: mylistings.php?update=success');
            exit();
        } else {
            $error_msg = 'Failed to update listing.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stylesheets/createlisting.css" />
    <title>DLSU Marketplace | Edit Product</title>
</head>

<body>
    <div class="card-container">
        <div class="header">
            <h1>Edit Product Details</h1>
            <p class="subtitle">Update the information for your marketplace listing.</p>
            <hr>
        </div>
        <?php if ($success_msg): ?>
            <div class="alert success"><?= htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert error"><?= htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>
        <form action="editlisting.php?listing_id=<?= $listing_id; ?>" method="POST">
            <div class="form-section">
                <h3>Basic Information</h3>
                <div class="input-group">
                    <label for="product_name">Product Name</label>
                    <input type="text" id="product_name" name="product_name" value="<?= htmlspecialchars($listing['product_name']); ?>" required>
                </div>
                <div class="input-group">
                    <label for="description">Product Description</label>
                    <textarea id="description" name="description" rows="4" required><?= htmlspecialchars($listing['description']); ?></textarea>
                </div>
            </div>
            <div class="form-section">
                <h3>Categories</h3>
                <div class="row-inputs">
                    <div class="input-group">
                        <label>Primary Category</label>
                        <select name="category1_id" required>
                            <option value="">Choose Category (required)</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['category_id']; ?>" <?= ($listing['category1_id'] == $c['category_id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($c['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Secondary Category</label>
                        <select name="category2_id">
                            <option value="">Choose Category (optional)</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['category_id']; ?>" <?= ($listing['category2_id'] == $c['category_id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($c['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Tertiary Category</label>
                        <select name="category3_id">
                            <option value="">Choose Category (optional)</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['category_id']; ?>" <?= ($listing['category3_id'] == $c['category_id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($c['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-section">
                <h3>Inventory & Pricing</h3>
                <div class="row-inputs">
                    <div class="input-group">
                        <label for="quantity">Quantity Available</label>
                        <input type="number" id="quantity" name="quantity" value="<?= intval($listing['quantity']); ?>" min="1" required>
                    </div>
                    <div class="input-group">
                        <label for="price">Price</label>
                        <div class="pricing-input">
                            <span class="currency">₱</span>
                            <input type="number" id="price" name="price" step="0.01" value="<?= number_format($listing['price'], 2, '.', ''); ?>" min="0" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-section">
                <h3>Product Images</h3>
                <div style="background-color: #faf9f5; padding: 25px; border-radius: 8px; border: 1px dashed #ccc; color: #666; font-size: 0.95em; text-align: center;">
                    <i>Note: Product images cannot be changed after the listing is created. If you need to update your images, please remove this listing and create a new one.</i>
                </div>
            </div>
            <div class="button-group">
                <button name="action" value="cancel" class="btn-cancel" formnovalidate>Cancel</button>
                <button name="action" value="update" class="btn-add">Update Product</button>
            </div>
        </form>
    </div>
</body>

</html>