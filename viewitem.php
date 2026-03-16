<?php
session_start();
include 'db.php';

// check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: loginpage.php");
    exit();
}

// check if a listing ID was provided in the URL
if (!isset($_GET["listing_id"]) || empty($_GET["listing_id"])) {
    header("Location: home.php");
    exit();
}

$listing_id = intval($_GET["listing_id"]);
$item_query = "SELECT l.listing_id, c1.category_name AS cat1, c2.category_name AS cat2, c3.category_name AS cat3,
                         l.product_name, CONCAT(u.first_name, ' ', u.last_name) AS full_name, IFNULL(AVG(r.rating_value), 0) AS avg_rating,
                         l.price, l.quantity, (SELECT image_path FROM listing_images WHERE listing_id = l.listing_id LIMIT 1) AS image_path
                  FROM listings l
                  LEFT JOIN users u ON u.user_id = l.seller_id
                  LEFT JOIN ratings r ON r.rated_user_id = u.user_id
                  LEFT JOIN categories c1 ON c1.category_id = l.category1_id
                  LEFT JOIN categories c2 ON c2.category_id = l.category2_id
                  LEFT JOIN categories c3 ON c3.category_id = l.category3_id
                  WHERE l.listing_id = $listing_id
                  GROUP BY l.listing_id";

$item_result = $conn->query($item_query);

// if the item doesnt exist, go back to home
if ($item_result->num_rows == 0) {
    header("Location: home.php");
    exit();
}

$item = $item_result->fetch_assoc();

// get all images for this listing

// get all the categories for this listng

// get the main image
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ""; ?> | DLSU Marketplace</title>
</head>

<body>

</body>

</html>