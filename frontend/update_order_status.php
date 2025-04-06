<?php
session_start();
require '../includes/config.php';

// Check if user is logged in and is a pharmacist
// if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'pharmacist') {
//     $_SESSION['error'] = "Unauthorized access";
//     header("Location: login.php");
//     exit();
// }

// Initialize variables
$current_status_stmt = null;
$update_stmt = null;

try {
    // Get order ID and status
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : (isset($_GET['order_id']) ? intval($_GET['order_id']) : 0);
    $status = isset($_POST['status']) ? strtolower(trim($_POST['status'])) : (isset($_GET['status']) ? strtolower(trim($_GET['status'])) : '');

    // Validate inputs
    if ($order_id <= 0) {
        throw new Exception("Invalid order ID");
    }

    $valid_statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
    if (!in_array($status, $valid_statuses)) {
        throw new Exception("Invalid status selected");
    }

    // Start transaction
    $conn->begin_transaction();

    // 1. Get current order status (optional - if you want to track changes)
    $current_status_stmt = $conn->prepare("SELECT status FROM orders WHERE id = ?");
    if (!$current_status_stmt) {
        throw new Exception("Failed to prepare status query: " . $conn->error);
    }
    $current_status_stmt->bind_param("i", $order_id);
    $current_status_stmt->execute();
    $result = $current_status_stmt->get_result();
    $order = $result->fetch_assoc();
    
    if (!$order) {
        throw new Exception("Order not found");
    }

    // 2. Update order status
    $update_stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    if (!$update_stmt) {
        throw new Exception("Failed to prepare update statement: " . $conn->error);
    }
    $update_stmt->bind_param("si", $status, $order_id);
    $update_stmt->execute();

    if ($update_stmt->affected_rows === 0) {
        throw new Exception("No changes made to order status");
    }

    // Commit transaction
    $conn->commit();
    $_SESSION['success'] = "Order #$order_id status updated to " . ucfirst($status);

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = $e->getMessage();
} finally {
    // Close statements if they exist
    if ($current_status_stmt instanceof mysqli_stmt) $current_status_stmt->close();
    if ($update_stmt instanceof mysqli_stmt) $update_stmt->close();
    
    header("Location: pharmacist_orders.php");
    exit();
}