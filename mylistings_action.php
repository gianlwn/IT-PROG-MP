<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: loginpage.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == "removefromlisting") {
        // get the listing id of that item
        $listing_id = $_POST['listing_id'];

        if ($listing_id > 0) {
            // get all images first
            $get_images = "SELECT image_path
                           FROM listing_images
                           WHERE listing_id = ?";

            $stmt = $conn->prepare($get_images);

            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }

            $stmt->bind_param("i", $listing_id);
            $stmt->execute();
            $result = $stmt->get_result();

            // delete images
            while ($row = $result->fetch_assoc()) {
                $file = $row['image_path'];

                if (file_exists($file)) {
                    unlink($file);
                }
            }

            // delete from the listing_images
            $delete_images = "DELETE FROM listing_images
                              WHERE listing_id = ?";

            $stmt = $conn->prepare($delete_images);

            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }

            $stmt->bind_param("i", $listing_id);
            $stmt->execute();

            // delete from the listings table
            $delete_listing = "DELETE FROM listings
                               WHERE listing_id = ? AND seller_id = ?";

            $stmt = $conn->prepare($delete_listing);

            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }

            $stmt->bind_param("ii", $listing_id, $user_id);
            $stmt->execute();

            header("Location: mylistings.php?remove=success");
            exit();
        }
    }
} else {
    header("Location: home.php");
    exit();
}
?>

<?php
# do not remove this
?>