<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: loginpage.php");
    exit();
}

$buyer_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"])) {
    if ($_POST["action"] == "addtocart") {
        $listing_id = intval($_POST["listing_id"]);
        $buy_qty = intval($_POST["buy_qty"]);

        // get max available stock
        $max_stock = 0;
        $stock_query = "SELECT quantity
                        FROM listings
                        WHERE listing_id = '$listing_id'";
        $stock_result = $conn->query($stock_query);

        if ($stock_result == TRUE && $stock_result->num_rows === 1) {
            $item_data = $stock_result->fetch_assoc();
            $max_stock = intval($item_data["quantity"]);
        }
        
        // check if the item is already out of stock
        if ($max_stock <= 0) {
            header("Location: viewitem.php?listing_id=" . $listing_id . "&error=nostock");
            exit();
        }

        $cart_query = "SELECT cart_id, quantity
                       FROM cart
                       WHERE buyer_id = '$buyer_id' AND listing_id = '$listing_id'";
        $cart_result = $conn->query($cart_query);

        if ($cart_result == TRUE && $cart_result->num_rows > 0) {
            $cart_row = $cart_result->fetch_assoc();
            $new_qty = $cart_row["quantity"] + $buy_qty;

            // error: trying to add more than available stock
            if ($new_qty > $max_stock) {
                header("Location: viewitem.php?listing_id=" . $listing_id . "&error=exceeds");
                exit();
            }

            $updatecart_query = "UPDATE cart
                                 SET quantity = '$new_qty'
                                 WHERE buyer_id = '$buyer_id' AND listing_id = '$listing_id'";
            $conn->query($updatecart_query);
        } else {
            // error: trying to add more than available stock on first add
            if ($buy_qty > $max_stock) {
                header("Location: viewitem.php?listing_id=" . $listing_id . "&error=exceeds");
                exit();
            }

            $insert_query = "INSERT INTO cart (buyer_id, listing_id, quantity)
                             VALUES ('$buyer_id', '$listing_id', '$buy_qty')";
            $conn->query($insert_query);
        }

        // success
        header("Location: viewitem.php?listing_id=" . $listing_id . "&success=added");
        exit();

        // update cart quantity
    } else if ($_POST["action"] == "updatecart") {
        // get the cart id and the new quantity of that item
        $cart_id = isset($_POST["cart_id"]) ? intval($_POST["cart_id"]) : 0;
        $new_qty = isset($_POST["new_qty"]) ? intval($_POST["new_qty"]) : 1;

        if ($cart_id > 0 && $new_qty > 0) {
            $update_query = "UPDATE cart
                             SET quantity = '$new_qty'
                             WHERE cart_id = '$cart_id' AND buyer_id = '$buyer_id'";
            $conn->query($update_query);

            // success
            header("Location: viewcart.php");
            exit();
        }

        // remove item from cart
    } else if ($_POST["action"] == "removefromcart") {
        // get the cart id of that item
        $cart_id = isset($_POST["cart_id"]) ? intval($_POST["cart_id"]) : 0;

        if ($cart_id > 0) {
            $delete_query = "DELETE FROM cart
                             WHERE cart_id = '$cart_id' AND buyer_id = '$buyer_id'";
            $conn->query($delete_query);

            // success
            header("Location: viewcart.php");
            exit();
        }
    }
} else {
    header("Location: home.php");
    exit();
}
