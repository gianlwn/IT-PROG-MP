<?php
session_start();
require 'db.php';

// check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: loginpage.php");
    exit();
}

$success_msg = "";
$error_msg = "";

// get all categories
$categories = [];
$cat_query = "SELECT category_id, category_name
              FROM categories
              ORDER BY category_id ASC";
$cat_result = $conn->query($cat_query);

if ($cat_result == TRUE && $cat_result->num_rows > 0) {
    while ($row = $cat_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["action"]) && $_POST["action"] === "discard") {
        header("Location: home.php");
        exit();
    } else if (isset($_POST["action"]) && $_POST["action"] === "add") {
        $seller_id = $_SESSION["user_id"];
        $product_name = $conn->real_escape_string($_POST["product_name"]);
        $description = $conn->real_escape_string($_POST["description"]);
        $category1_id = !empty($_POST["category1_id"]) ? intval($_POST["category1_id"]) : "NULL";
        $category2_id = !empty($_POST["category2_id"]) ? intval($_POST["category2_id"]) : "NULL";
        $category3_id = !empty($_POST["category3_id"]) ? intval($_POST["category3_id"]) : "NULL";
        $quantity = intval($_POST["quantity"]);
        $price = floatval($_POST["price"]);

        $insert_listing = "INSERT INTO listings (seller_id, product_name, description, price, quantity, category1_id, category2_id, category3_id) 
                           VALUES ('$seller_id', '$product_name', '$description', '$price', '$quantity', $category1_id, $category2_id, $category3_id)";

        if ($conn->query($insert_listing) === TRUE) {
            $new_listing_id = $conn->insert_id;
            $upload_dir = "uploads/";

            // creates the folder if it doesnt exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $images = ["image1", "image2", "image3"];

            foreach ($images as $img_field) {
                if (isset($_FILES[$img_field]) && $_FILES[$img_field]["error"] == 0) {
                    $file_extension = pathinfo($_FILES[$img_field]["name"], PATHINFO_EXTENSION);
                    // create a unique file name in this format: listing_ID_randomstring.jpg
                    $new_file_name = "listing_" . $new_listing_id . "_" . uniqid() . "." . $file_extension;
                    $target_path = $upload_dir . $new_file_name;

                    // move from temporary storage to the uploads folder
                    if (move_uploaded_file($_FILES[$img_field]["tmp_name"], $target_path)) {
                        // insert the image path into the listing_images table
                        $insert_img = "INSERT INTO listing_images (listing_id, image_path) VALUES ('$new_listing_id', '$target_path')";
                        $conn->query($insert_img);
                    }
                }
            }

            $success_msg = "Product listed successfully!";
            $conn->close();
        } else {
            $error_msg = "Error creating listing: " . $conn->error;
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
            <hr>
        </div>
        <?php if ($success_msg): ?>
            <div class="alert success"><?php echo $success_msg; ?></div>
            <script>
                setTimeout(() => {
                    window.location.href = 'home.php?createlisting=success';
                }, 3000);
            </script>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert error"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        <form action="createlisting.php" method="POST" enctype="multipart/form-data">
            <div class="form-box">
                <div class="left-col">
                    <label for="product_name">Product Name</label>
                    <input type="text" id="product_name" name="product_name" placeholder="Enter product name here..." required>
                    <label for="description">Product Description</label>
                    <textarea id="description" name="description" placeholder="Enter product description here..." required></textarea>
                    <label>Product Categories:</label>
                    <select name="category1_id" required>
                        <option value="">Choose Category (required)</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?php echo $c["category_id"] ?>"><?php echo $c["category_name"] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="category2_id">
                        <option value="">Choose Category (optional)</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?php echo $c["category_id"] ?>"><?php echo $c["category_name"] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="category3_id">
                        <option value="">Choose Category (optional)</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?php echo $c["category_id"] ?>"><?php echo $c["category_name"] ?></option>
                        <?php endforeach; ?>
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
                        <label class="image-upload-box" id="box1">
                            <span id="text1">Click to Upload (required)</span>
                            <input type="file" name="image1" id="image1" accept="image/*" required onchange="updateBoxText(this, 'text1', 'box1')">
                        </label>
                        <label class="image-upload-box" id="box2">
                            <span id="text2">Click to Upload (optional)</span>
                            <input type="file" name="image2" id="image2" accept="image/*" onchange="updateBoxText(this, 'text2', 'box2')">
                        </label>
                        <label class="image-upload-box" id="box3">
                            <span id="text3">Click to Upload (optional)</span>
                            <input type="file" name="image3" id="image3" accept="image/*" onchange="updateBoxText(this, 'text3', 'box3')">
                        </label>
                    </div>
                    <label for="price">Pricing</label>
                    <div class="pricing-input">
                        <span class="currency">₱</span>
                        <input type="number" id="price" name="price" step="0.01" value="180.00" min="0" required>
                    </div>
                    <div class="button-group">
                        <button name="action" value="discard" class="btn-discard" formnovalidate>Discard</button>
                        <button name="action" value="add" class="btn-add">Add Product</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <script>
        function updateBoxText(inputElement, textId, boxId) {
            const textSpan = document.getElementById(textId);
            const box = document.getElementById(boxId);

            if (inputElement.files && inputElement.files.length > 0) {
                textSpan.innerHTML = "Image Selected!<br>✓";
                box.style.backgroundColor = "#eeece0";
                box.style.borderColor = "#4a543e";
            }
        }
    </script>
</body>

</html>