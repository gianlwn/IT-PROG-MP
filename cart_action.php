<?php
session_start();
include 'db.php';

// check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: loginpage.php");
    exit();
}

$buyer_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]) && $_POST["action"] == "addtocart") {
    
    // clean the inputs
    $listing_id = intval($_POST["listing_id"]);
    $buy_qty = intval($_POST["buy_qty"]);

    // get the max available stock for this item
    $stock_query = "SELECT quantity
                    FROM listings
                    WHERE listing_id = '$listing_id'";
    $stock_result = $conn->query($stock_query);
    $item_data = $stock_result->fetch_assoc();
    $max_stock = intval($item_data["quantity"]);

    // check if this item is already in the user's cart
    $check_query = "SELECT cart_id, quantity
                    FROM cart
                    WHERE buyer_id = '$buyer_id' AND listing_id = '$listing_id'";
    $check_result = $conn->query($check_query);

    if ($check_result && $check_result->num_rows > 0) {
        // if its already in the cart, update the qty
        $row = $check_result->fetch_assoc();
        $new_qty = $row["quantity"] + $buy_qty;
        
        // prevent them to add more than the stock
        if ($new_qty > $max_stock) {
            $new_qty = $max_stock;
        }
        
        $update_query = "UPDATE cart
                         SET quantity = '$new_qty'
                         WHERE buyer_id = '$buyer_id' AND listing_id = '$listing_id'";
        $conn->query($update_query);
    } else {
        // if its not in the cart yet, insert a new row
        // prevent them to add more than the stock
        if ($buy_qty > $max_stock) {
            $buy_qty = $max_stock;
        }

        $insert_query = "INSERT INTO cart (buyer_id, listing_id, quantity)
                         VALUES ('$buyer_id', '$listing_id', '$buy_qty')";
        $conn->query($insert_query);
    }

    // send the user back to the item page with a success flag in the URL
    header("Location: viewitem.php?listing_id=" . $listing_id . "&success=added");
    exit();
} else {
    // check if a listing ID was provided in the URL
    header("Location: home.php");
    exit();
}
?>