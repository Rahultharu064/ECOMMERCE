<?php
session_start();
include '../includes/config.php';

// Check if user is logged in and has admin privileges
// You should implement proper authentication here
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
//     header("Location: ../frontend/login.php");
//     exit();
// }

// Check if product ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No product ID provided for deletion";
    header("Location: productsdisplay.php");
    exit();
}

$product_id = intval($_GET['id']);

// First, get the product image path to delete the file later
$query = "SELECT image_path FROM products WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    $_SESSION['error'] = "Product not found";
    header("Location: productsdisplay.php");
    exit();
}

// Delete the product from database
$delete_query = "DELETE FROM products WHERE id = ?";
$delete_stmt = mysqli_prepare($conn, $delete_query);
mysqli_stmt_bind_param($delete_stmt, "i", $product_id);

if (mysqli_stmt_execute($delete_stmt)) {
    // Delete the associated image file
    if (file_exists($product['image_path'])) {
        unlink($product['image_path']);
    }
    
    $_SESSION['message'] = "Product deleted successfully";
} else {
    $_SESSION['error'] = "Error deleting product: " . mysqli_error($conn);
}

mysqli_stmt_close($delete_stmt);
header("Location: productsdisplay.php");
exit();
?>