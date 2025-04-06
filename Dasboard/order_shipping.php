<?php
require_once '../includes/config.php';
// require_once '../includes/auth.php';

// // Verify admin access
// checkAdminAccess();

// Get order ID
$order_id = $_GET['order_id'] ?? 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $carrier_name = trim($_POST['carrier_name']);
    $tracking_number = trim($_POST['tracking_number']);
    $shipping_method = trim($_POST['shipping_method']);
    $estimated_delivery = trim($_POST['estimated_delivery']);
    
    // Generate tracking URL
    $tracking_url = getTrackingUrl($carrier_name, $tracking_number, $conn, $order_id);
    
    // Check if shipping info already exists
    $exists = $conn->query("SELECT id FROM order_shipping WHERE order_id = $order_id")->num_rows > 0;
    
    if ($exists) {
        $stmt = $conn->prepare("UPDATE order_shipping SET 
            carrier_name = ?,
            tracking_number = ?,
            tracking_url = ?,
            shipping_method = ?,
            estimated_delivery = ?
            WHERE order_id = ?");
        $stmt->bind_param("sssssi", $carrier_name, $tracking_number, $tracking_url, $shipping_method, $estimated_delivery, $order_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO order_shipping 
            (order_id, carrier_name, tracking_number, tracking_url, shipping_method, estimated_delivery)
            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $order_id, $carrier_name, $tracking_number, $tracking_url, $shipping_method, $estimated_delivery);
    }
    
    $stmt->execute();
    $stmt->close();
    
    // Update order status to "shipped"
    $conn->query("UPDATE orders SET status = 'shipped' WHERE id = $order_id");
    
    // Add to order history
    $notes = "Order shipped via $carrier_name. Tracking #: $tracking_number";
    $conn->query("INSERT INTO order_history (order_id, status, notes) VALUES ($order_id, 'shipped', '$notes')");
    
    $_SESSION['success'] = "Shipping information updated successfully";
    header("Location: order_shipping.php?order_id=$order_id");
    exit();
}

// Get order details
$order = $conn->query("SELECT * FROM orders WHERE id = $order_id")->fetch_assoc();
if (!$order) {
    $_SESSION['error'] = "Order not found";
    header("Location: orders.php");
    exit();
}

// Get shipping info if exists
$shipping_info = $conn->query("SELECT * FROM order_shipping WHERE order_id = $order_id")->fetch_assoc();

// Get available carriers
$carriers = $conn->query("SELECT * FROM shipping_carriers ORDER BY carrier_name")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Shipping Info - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include '../includes/admin_header.php'; ?>
    
    <div class="container">
        <h1>Update Shipping Information</h1>
        <h2>Order #<?= htmlspecialchars($order['order_number']) ?></h2>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Carrier</label>
                <select name="carrier_name" required>
                    <option value="">Select Carrier</option>
                    <?php foreach ($carriers as $carrier): ?>
                        <option value="<?= htmlspecialchars($carrier['carrier_name']) ?>"
                            <?= isset($shipping_info) && $shipping_info['carrier_name'] === $carrier['carrier_name'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($carrier['carrier_name']) ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="Other">Other (specify)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Tracking Number</label>
                <input type="text" name="tracking_number" required
                       value="<?= htmlspecialchars($shipping_info['tracking_number'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label>Shipping Method</label>
                <input type="text" name="shipping_method"
                       value="<?= htmlspecialchars($shipping_info['shipping_method'] ?? '') ?>"
                       placeholder="e.g., Standard, Express, Overnight">
            </div>
            
            <div class="form-group">
                <label>Estimated Delivery</label>
                <input type="date" name="estimated_delivery" required
                       value="<?= htmlspecialchars($shipping_info['estimated_delivery'] ?? '') ?>">
            </div>
            
            <button type="submit">Update Shipping Info</button>
            <a href="order_details.php?order_id=<?= $order_id ?>" class="button">Back to Order</a>
        </form>
    </div>
</body>
</html>