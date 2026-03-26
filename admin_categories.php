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
                $insert_query = "INSERT INTO categories (category_name)
                                 VALUES (?)";

                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param('s', $new_category);

                if ($stmt->execute()) {
                    $success_msg = 'Category ' . $new_category . ' was added successfully!';
                }
            }
        } else {
            $error_msg = 'Category name cannot be empty.';
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stylesheets/home.css">
    <link rel="stylesheet" href="stylesheets/admin.css">
    <title>DLSU Marketplace | Manage Categories</title>
</head>

<body>

</body>

</html>