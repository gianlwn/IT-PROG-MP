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

// user data for display
$first_name = $_SESSION["first_name"];
$last_name = $_SESSION["last_name"];
$full_name = trim($first_name . " " . $last_name);
$user_id = $_SESSION["user_id"];
$profile_pic = $_SESSION["profile_picture"] ?? "login-icon.png";
$pic_path = "images/" . $profile_pic;

$listing_id = intval($_GET["listing_id"]);
$item_query = "SELECT c1.category_name AS cat1, c2.category_name AS cat2, c3.category_name AS cat3,
                      CONCAT(u.first_name, ' ', u.last_name) AS seller_name, IFNULL(ROUND(AVG(r.rating_value), 1), 0) AS avg_rating,
                      l.listing_id, l.product_name, l.price, l.quantity, l.description, l.status
                FROM listings l
                LEFT JOIN users u ON u.user_id = l.seller_id
                LEFT JOIN ratings r ON r.rated_user_id = u.user_id
                LEFT JOIN categories c1 ON c1.category_id = l.category1_id
                LEFT JOIN categories c2 ON c2.category_id = l.category2_id
                LEFT JOIN categories c3 ON c3.category_id = l.category3_id
                WHERE l.listing_id = '$listing_id'
                GROUP BY l.listing_id";

$item_result = $conn->query($item_query);

// if the item doesnt exist, go back to home
if ($item_result->num_rows == 0) {
    header("Location: home.php");
    exit();
}

$item = $item_result->fetch_assoc();

// get all images for this listing
$images = [];
$image_query = "SELECT image_path
                FROM listing_images
                WHERE listing_id = '$listing_id'";
$image_result = $conn->query($image_query);

if ($image_result == TRUE && $image_result->num_rows > 0) {
    while ($img_row = $image_result->fetch_assoc()) {
        $images[] = $img_row["image_path"];
    }
}

// get all the categories for this listng
$categories = [];
if (!empty($item["cat1"])) $categories[] = $item["cat1"];
if (!empty($item["cat2"])) $categories[] = $item["cat2"];
if (!empty($item["cat3"])) $categories[] = $item["cat3"];
$category_display = implode(', ', $categories);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stylesheets/viewitem.css" />
    <title><?php echo $item["product_name"]; ?> | DLSU Marketplace</title>
</head>

<body>
    <form action="viewitem.php" method="post">
        <div class="dashboard-container">
            <aside class="sidebar">
                <div class="user-profile-section">
                    <img src="<?php echo $pic_path; ?>" alt="Profile" class="nav-logo">
                    <div class="user-info-display">
                        <h2 class="user-name"><?php echo $full_name; ?></h2>
                        <p class="user-id">ID: <?php echo $user_id; ?></p>
                    </div>
                </div>
                <nav class="nav-menu">
                    <a href="home.php" class="active">Home</a>
                    <a href="mylistings.php">My Listings</a>
                    <a href="myclaims.php">My Claims</a>
                    <a href="editprofile.php">Edit Profile</a>
                    <hr class="nav-divider">
                    <a href="destroy.php" class="logout-link">Logout</a>
                </nav>
            </aside>
        </div>
    </form>
</body>

</html>