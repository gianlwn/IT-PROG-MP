<?php
session_start();
require 'db.php';

// check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: loginpage.php');
    exit();
}

$success_msg = '';
$error_msg = '';

// get all categories
$categories = [];
$cat_query = "SELECT *
              FROM categories
              ORDER BY category_id ASC";

$stmt = $conn->prepare($cat_query);

if (!$stmt) die('Prepare failed: ' . $conn->error);
$stmt->execute();
$cat_result = $stmt->get_result();

if ($cat_result->num_rows > 0) {
    while ($row = $cat_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'cancel') {
        header('Location: home.php');
        exit();
    } else if (isset($_POST['action']) && $_POST['action'] == 'add' && !empty($_POST['product_name']) && !empty($_POST['description']) && !empty($_POST['category1_id']) && !empty($_POST['quantity']) && !empty($_POST['price'])) {
        $seller_id = $_SESSION['user_id'];
        $product_name = $_POST['product_name'];
        $description = $_POST['description'];
        $quantity = intval($_POST['quantity']);
        $price = floatval($_POST['price']);

        $category1_id = intval($_POST['category1_id']);
        $category2_id = !empty($_POST['category2_id']) ? intval($_POST['category2_id']) : NULL;
        $category3_id = !empty($_POST['category3_id']) ? intval($_POST['category3_id']) : NULL;

        // prevent duplicate categories
        $cats = [$category1_id, $category2_id, $category3_id];
        $cats = array_filter($cats);

        if (count($cats) !== count(array_unique($cats))) {
            $error_msg = 'Duplicate categories selected.';
        } else {
            $insert_query = "INSERT INTO listings (seller_id, product_name, description, price, quantity, category1_id, category2_id, category3_id) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($insert_query);

            if (!$stmt) die('Prepare failed: ' . $conn->error);
            $stmt->bind_param('issdiiii', $seller_id, $product_name, $description, $price, $quantity, $category1_id, $category2_id, $category3_id);

            if ($stmt->execute()) {
                $new_listing_id = $stmt->insert_id;
                $upload_dir = 'uploads/';

                // creates the folder if it doesnt exist
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $images = ['image1', 'image2', 'image3'];

                foreach ($images as $img_field) {
                    if (isset($_FILES[$img_field]) && $_FILES[$img_field]['error'] == 0) {
                        $file_extension = pathinfo($_FILES[$img_field]['name'], PATHINFO_EXTENSION);

                        // create a unique file name in this format: listing_ID_randomstring.jpg
                        $new_file_name = 'listing_' . $new_listing_id . '_' . uniqid() . '.' . $file_extension;
                        $target_path = $upload_dir . $new_file_name;

                        // move from temporary storage to the uploads folder
                        if (move_uploaded_file($_FILES[$img_field]['tmp_name'], $target_path)) {
                            // insert the image path into the listing_images table
                            $insert_img_query = "INSERT INTO listing_images (listing_id, image_path)
                                                 VALUES (?, ?)";

                            $stmt = $conn->prepare($insert_img_query);

                            if (!$stmt) die('Prepare failed: ' . $conn->error);
                            $stmt->bind_param('is', $new_listing_id, $target_path);
                            $stmt->execute();
                        }
                    }
                }

                $success_msg = 'Product listed successfully!';
            } else {
                $error_msg = 'Error creating listing: ' . $conn->error;
            }
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
    <title>DLSU Marketplace | Add New Product</title>
</head>

<body>
    <div class="card-container">
        <div class="header">
            <h1>Add New Product</h1>
            <p class="subtitle">Fill out the details below to list your item on the marketplace.</p>
            <hr>
        </div>
        <?php if ($success_msg): ?>
            <div class="alert success"><?= htmlspecialchars($success_msg); ?></div>
            <script>
                setTimeout(() => {
                    window.location.href = 'home.php?createlisting=success';
                }, 3000);
            </script>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert error"><?= htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>
        <form action="createlisting.php" method="POST" enctype="multipart/form-data">
            <div class="form-section">
                <h3>Basic Information</h3>
                <div class="input-group">
                    <label for="product_name">Product Name</label>
                    <input type="text" id="product_name" name="product_name" placeholder="Enter the name of the item here..." required>
                </div>
                <div class="input-group">
                    <label for="description">Product Description</label>
                    <textarea id="description" name="description" placeholder="Describe the item's condition, features, and any other details..." rows="4" required></textarea>
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
                                <option value="<?= $c['category_id']; ?>"><?= htmlspecialchars($c['category_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Secondary Category</label>
                        <select name="category2_id">
                            <option value="">Choose Category (optional)</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['category_id']; ?>"><?= htmlspecialchars($c['category_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Tertiary Category</label>
                        <select name="category3_id">
                            <option value="">Choose Category (optional)</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['category_id']; ?>"><?= htmlspecialchars($c['category_name']); ?></option>
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
                        <input type="number" id="quantity" name="quantity" value="1" min="1" required>
                    </div>
                    <div class="input-group">
                        <label for="price">Price</label>
                        <div class="pricing-input">
                            <span class="currency">₱</span>
                            <input type="number" id="price" name="price" step="0.01" value="180.00" min="0" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-section">
                <h3>Product Images</h3>
                <p style="font-size: 0.85rem; color: #666; margin-bottom: 15px;">Upload up to 3 images. The first image will be your cover photo.</p>
                <div class="image-upload-group">
                    <div class="upload-wrapper">
                        <label class="image-upload-box" id="box1" for="image1">
                            <span class="upload-text" id="text1">Upload<br>(Required)</span>
                            <img src="" class="img-preview" id="preview1" alt="Preview">
                        </label>
                        <input type="file" name="image1" id="image1" accept="image/*" required onchange="handleImageUpload(this, 1)">
                        <button type="button" class="remove-img-btn" id="remove1" onclick="removeImage(1)">✕</button>
                    </div>
                    <div class="upload-wrapper">
                        <label class="image-upload-box" id="box2" for="image2">
                            <span class="upload-text" id="text2">Upload<br>(Optional)</span>
                            <img src="" class="img-preview" id="preview2" alt="Preview">
                        </label>
                        <input type="file" name="image2" id="image2" accept="image/*" onchange="handleImageUpload(this, 2)">
                        <button type="button" class="remove-img-btn" id="remove2" onclick="removeImage(2)">✕</button>
                    </div>
                    <div class="upload-wrapper">
                        <label class="image-upload-box" id="box3" for="image3">
                            <span class="upload-text" id="text3">Upload<br>(Optional)</span>
                            <img src="" class="img-preview" id="preview3" alt="Preview">
                        </label>
                        <input type="file" name="image3" id="image3" accept="image/*" onchange="handleImageUpload(this, 3)">
                        <button type="button" class="remove-img-btn" id="remove3" onclick="removeImage(3)">✕</button>
                    </div>
                </div>
            </div>
            <div class="button-group">
                <button name="action" value="cancel" class="btn-cancel" formnovalidate>Cancel</button>
                <button name="action" value="add" class="btn-add">Submit Product</button>
            </div>
        </form>
    </div>
    <script>
        function handleImageUpload(input, index) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    // show the image
                    const preview = document.getElementById('preview' + index);
                    preview.src = e.target.result;
                    preview.style.display = 'block';

                    // gide the text
                    document.getElementById('text' + index).style.display = 'none';

                    // show the remove button
                    document.getElementById('remove' + index).style.display = 'flex';

                    // change box style
                    const box = document.getElementById('box' + index);
                    box.style.borderStyle = 'solid';
                    box.style.borderColor = '#4a543e';
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        function removeImage(index) {
            // clear the file input
            const input = document.getElementById('image' + index);
            input.value = '';

            // hide the image preview
            const preview = document.getElementById('preview' + index);
            preview.src = '';
            preview.style.display = 'none';

            // show the text again
            document.getElementById('text' + index).style.display = 'block';

            // hide the remove button
            document.getElementById('remove' + index).style.display = 'none';

            // revert box style
            const box = document.getElementById('box' + index);
            box.style.borderStyle = 'dashed';
            box.style.borderColor = '#aaa';
        }
    </script>
</body>

</html>