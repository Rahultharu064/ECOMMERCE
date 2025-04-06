<?php
session_start();
require '../includes/config.php';

// Check if user is logged in (simple check without auth.php)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    header("Location: order.php");
    exit();
}

$order_id = intval($_GET['order_id']);

// Fetch order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: order.php");
    exit();
}

// Fetch order items
$stmt = $conn->prepare("SELECT oi.*, p.image_path, p.quantity, p.description FROM order_items oi 
                       LEFT JOIN products p ON oi.product_id = p.id 
                       WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate total items and subtotal
$total_items = array_sum(array_column($order_items, 'quantity'));
$subtotal = $order['total_amount'] - $order['shipping_fee'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details | PharmaCare</title>
    <link rel="stylesheet" href="../assets/css/pharmacy.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --accent-color: #e74c3c;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --success-color: #27ae60;
            --warning-color: #ffc107;
            --border-color: #e0e0e0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            line-height: 1.6;
        }
        
        .order-details-container {
            max-width: 1200px;
            margin: 280px auto 50px;
            padding: 0 20px;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .order-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .order-number {
            font-size: 18px;
            color: #555;
            background: #f1f1f1;
            padding: 8px 15px;
            border-radius: 20px;
        }
        
        .order-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .order-items {
            margin-bottom: 30px;
        }
        
        .order-item {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
            gap: 20px;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 80px;
            height: 80px;
            border-radius: 6px;
            object-fit: cover;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-weight: 500;
            margin-bottom: 5px;
            font-size: 16px;
        }
        
        .item-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 5px;
            flex-wrap: wrap;
        }
        
        .item-price {
            color: #555;
        }
        
        .item-quantity {
            color: #777;
        }
        
        .item-total {
            font-weight: 600;
            min-width: 100px;
            text-align: right;
            align-self: center;
        }
        
        .item-description {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .order-summary {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        
        .summary-label {
            color: #555;
        }
        
        .summary-value {
            font-weight: 500;
        }
        
        .grand-total {
            font-weight: 600;
            font-size: 18px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid var(--border-color);
        }
        
        .shipping-address {
            line-height: 1.8;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 6px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 14px;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .status-shipped {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            margin-right: 10px;
            font-size: 14px;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .btn-outline {
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline:hover {
            background: rgba(52, 152, 219, 0.1);
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: var(--accent-color);
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: var(--success-color);
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .btn-warning {
            background: var(--warning-color);
            color: #212529;
        }
        
        .btn-warning:hover {
            background: #e0a800;
            transform: translateY(-2px);
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .timeline {
            position: relative;
            padding-left: 30px;
            margin-top: 20px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--border-color);
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -30px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary-color);
            border: 2px solid white;
        }
        
        .timeline-date {
            font-size: 13px;
            color: #777;
        }
        
        .timeline-content {
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .order-details-container {
                margin-top: 80px;
            }
            
            .order-summary {
                grid-template-columns: 1fr;
            }
            
            .order-item {
                flex-direction: column;
            }
            
            .item-total {
                text-align: left;
                margin-top: 10px;
            }
        }
        
        /* Track order button styles */
        .track-order-btn {
            background: var(--success-color);
            color: white;
        }
        
        .track-order-btn:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="order-details-container">
        <div class="order-header">
            <h1 class="order-title">Order Details</h1>
            <div class="order-number">Order #<?php echo htmlspecialchars($order['order_number']); ?></div>
        </div>
        
        <div class="order-card">
            <h2 class="card-title">
                <span>Order Summary</span>
                <span class="status-badge <?php echo 'status-' . $order['status']; ?>">
                    <?php echo ucfirst($order['status']); ?>
                </span>
            </h2>
            
            <div class="order-items">
                <?php foreach ($order_items as $item): ?>
                    <div class="order-item">
                        <img src="<?php echo htmlspecialchars($item['image_path'] ?? '../assets/images/placeholder-product.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                             class="item-image">
                        <div class="item-details">
                            <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="item-meta">
                                <span class="item-price">₹<?php echo number_format($item['price'], 2); ?></span>
                                <span class="item-quantity">Quantity: <?php echo $item['quantity']; ?></span>
                            </div>
                            <?php if (!empty($item['description'])): ?>
                                <div class="item-description"><?php echo htmlspecialchars($item['description']); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="item-total">₹<?php echo number_format($item['total'], 2); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="order-summary">
                <div>
                    <h3 class="card-title">Payment Details</h3>
                    <div class="summary-row">
                        <span class="summary-label">Subtotal (<?php echo $total_items; ?> items):</span>
                        <span class="summary-value">₹<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Shipping Fee:</span>
                        <span class="summary-value"><?php echo $order['shipping_fee'] > 0 ? '₹' . number_format($order['shipping_fee'], 2) : 'FREE'; ?></span>
                    </div>
                    <div class="summary-row grand-total">
                        <span class="summary-label">Total Amount:</span>
                        <span class="summary-value">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Payment Method:</span>
                        <span class="summary-value">
                            <?php 
                            $payment_methods = [
                                'cod' => 'Cash on Delivery',
                                'khalti' => 'Khalti',
                                'esewa' => 'eSewa',
                                'card' => 'Credit/Debit Card'
                            ];
                            echo $payment_methods[$order['payment_method']] ?? $order['payment_method'];
                            ?>
                        </span>
                    </div>
                </div>
                
                <div>
                    <h3 class="card-title">Customer Information</h3>
                    <div class="shipping-address">
                        <p><strong><?php echo htmlspecialchars($order['name']); ?></strong></p>
                        <p><?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
                        <p>Email: <?php echo htmlspecialchars($order['email']); ?></p>
                        <p>Phone: <?php echo htmlspecialchars($order['phone']); ?></p>
                    </div>
                    
                    <h3 class="card-title" style="margin-top: 30px;">Order Status Timeline</h3>
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-date"><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></div>
                            <div class="timeline-content">Order placed</div>
                        </div>
                        <?php if ($order['status'] === 'completed'): ?>
                            <div class="timeline-item">
                                <div class="timeline-date"><?php echo date('M j, Y', strtotime($order['created_at'] . ' +1 day')); ?></div>
                                <div class="timeline-content">Order processed</div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-date"><?php echo date('M j, Y', strtotime($order['created_at'] . ' +2 days')); ?></div>
                                <div class="timeline-content">Order shipped</div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-date"><?php echo date('M j, Y', strtotime($order['created_at'] . ' +3 days')); ?></div>
                                <div class="timeline-content">Order delivered</div>
                            </div>
                        <?php elseif ($order['status'] === 'cancelled'): ?>
                            <div class="timeline-item">
                                <div class="timeline-date"><?php echo date('M j, Y', strtotime($order['updated_at'])); ?></div>
                                <div class="timeline-content">Order cancelled</div>
                            </div>
                        <?php else: ?>
                            <div class="timeline-item">
                                <div class="timeline-date">Expected</div>
                                <div class="timeline-content">Your order will be processed soon</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="Homepage.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Back to Home
            </a>
            <a href="products.php" class="btn btn-outline">
                <i class="fas fa-shopping-bag"></i> Continue Shopping
            </a>
            <a href="" class="btn btn-primary">
                <i class="fas fa-history"></i> View Order History
            </a>
            <a href="track_order.php?order_id=<?php echo $order_id; ?>" class="btn track-order-btn">
                <i class="fas fa-map-marker-alt"></i> Track Order
            </a>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>