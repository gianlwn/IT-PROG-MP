<?php
session_start();
include 'db.php';

// check if user is logged in
if (!isset($_SESSION["dlsu_email"])) {
    header("Location: loginpage.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stylesheets/createlisting.css" />
    <title>Add New Product - DLSU Marketplace</title>
</head>

<body>

    <div class="card-container">
        <div class="header">
            <h1>Add New Product</h1>
            <hr>
        </div>

        <form action="createlisting.php" method="POST" enctype="multipart/form-data">
            <div class="form-box">

                <div class="left-col">
                    <label for="product_name">Product Name</label>
                    <input type="text" id="product_name" name="product_name" placeholder="labubu... (ifykyk lang)" required>

                    <label for="description">Product Description</label>
                    <textarea id="description" name="description" placeholder="Enter product description here..." required></textarea>

                    <label>Product Categories:</label>
                    <select name="category1_id" required>
                        <option value="">Choose Category (required)</option>
                        <option value="1">CCS</option>
                        <option value="2">COS</option>
                    </select>

                    <select name="category2_id">
                        <option value="">Choose Category (optional)</option>
                        <option value="1">CCS</option>
                        <option value="2">COS</option>
                    </select>

                    <select name="category3_id">
                        <option value="">Choose Category (optional)</option>
                        <option value="1">CCS</option>
                        <option value="2">COS</option>
                    </select>
                </div>

                <div class="right-col">
                    <h3>Inventory</h3>
                    <div class="quantity-group">
                        <label for="quantity">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" required>
                    </div>

                    <label>Add Image/s</label>
                    <div class="image-upload-group">
                        <label class="image-upload-box">
                            Click to Upload
                            <input type="file" name="image1" accept="image/*" required>
                        </label>
                        <label class="image-upload-box">
                            Click to Upload
                            <input type="file" name="image2" accept="image/*">
                        </label>
                        <label class="image-upload-box">
                            Click to Upload
                            <input type="file" name="image3" accept="image/*">
                        </label>
                    </div>

                    <label for="price">Pricing</label>
                    <div class="pricing-input">
                        <span class="currency">₱</span>
                        <input type="number" id="price" name="price" step="0.01" value="180.00" min="0" required>
                    </div>

                    <div class="button-group">
                        <button type="button" class="btn-discard">Discard</button>
                        <button type="submit" class="btn-add">Add Product</button>
                    </div>
                </div>

            </div>
        </form>
    </div>

</body>

</html>