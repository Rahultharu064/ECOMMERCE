<?php
session_start();
require '../includes/config.php';

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if order_id is provided in URL or session
$order_id = $_GET['order_id'] ?? $_SESSION['order_id'] ?? null;

// Redirect if no valid order reference
if (!$order_id) {
    $_SESSION['error'] = "Order not found";
    header("Location: order_history.php");
    exit();
}

// Store order_id in session for subsequent requests
$_SESSION['order_id'] = $order_id;

// Get complete order details
$order_query = "
    SELECT o.*, 
           COUNT(oi.id) AS item_count,
           MAX(oh.created_at) AS last_update
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN order_history oh ON o.id = oh.order_id
    WHERE o.id = ?
    GROUP BY o.id
";

$stmt = $conn->prepare($order_query);
if (!$stmt) die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
$stmt->bind_param("i", $order_id);
if (!$stmt->execute()) die("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    $_SESSION['error'] = "Order not found in database";
    header("Location: order_history.php");
    exit();
}

// Get order items
$items_query = "
    SELECT oi.*, p.image_path, p.quantity 
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
";
$stmt = $conn->prepare($items_query);
if (!$stmt) die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
$stmt->bind_param("i", $order_id);
if (!$stmt->execute()) die("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get order history
$history_query = "
    SELECT * FROM order_history 
    WHERE order_id = ? 
    ORDER BY created_at DESC
";
$stmt = $conn->prepare($history_query);
if (!$stmt) die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
$stmt->bind_param("i", $order_id);
if (!$stmt->execute()) die("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
$history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get shipping information
$shipping_query = "
    SELECT carrier_name, tracking_number, estimated_delivery 
    FROM order_shipping 
    WHERE order_id = ?
";
$stmt = $conn->prepare($shipping_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$shipping_info = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Tracking stages configuration
$tracking_stages = [
    'ordered' => ['title' => 'Order Placed', 'icon' => 'fa-box'],
    'processed' => ['title' => 'Processing', 'icon' => 'fa-cogs'],
    'shipped' => ['title' => 'Shipped', 'icon' => 'fa-shipping-fast'],
    'out_for_delivery' => ['title' => 'Out for Delivery', 'icon' => 'fa-truck'],
    'delivered' => ['title' => 'Delivered', 'icon' => 'fa-check-circle']
];

// Determine current stage and progress
$current_stage = strtolower($order['status']);
$stage_index = array_search($current_stage, array_keys($tracking_stages)) + 1;
$progress_percent = ($stage_index / count($tracking_stages)) * 100;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - PharmaCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/pharmacy.css">
    <style>
    :root {
        --primary: #4A90E2;
        --secondary: #6C5CE7;
        --success: #00C853;
        --warning: #FFAB00;
        --danger: #FF5252;
        --dark: #2D3436;
        --light: #F8F9FA;
        --gradient: linear-gradient(135deg, #6C5CE7 0%, #4A90E2 100%);
        --shadow: 0 8px 24px rgba(0,0,0,0.08);
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: var(--light);
        line-height: 1.6;
        color: var(--dark);
    }

    .order-details-container {
        max-width: 1280px;
        margin: 280px auto 50px;
        padding: 0 32px;
    }

    .order-card {
        background: white;
        border-radius: 24px;
        box-shadow: var(--shadow);
        overflow: hidden;
        transform: translateY(0);
        transition: transform 0.3s ease;
    }

    .order-card:hover {
        transform: translateY(-4px);
    }

    .order-header {
        background: var(--gradient);
        color: white;
        padding: 48px;
        position: relative;
        clip-path: polygon(0 0, 100% 0, 100% 90%, 0 100%);
    }

    .order-title {
        font-size: 2.4rem;
        font-weight: 700;
        letter-spacing: -0.5px;
        margin-bottom: 12px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 8px 24px;
        border-radius: 100px;
        background: rgba(255,255,255,0.15);
        backdrop-filter: blur(4px);
        gap: 12px;
        font-weight: 500;
        font-size: 0.95rem;
    }

    .payment-method {
        display: block;
        margin-top: 8px;
        opacity: 0.9;
        font-size: 0.95rem;
    }

    .order-content {
        padding: 48px;
    }

    .order-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 40px;
    }

    .order-section {
        background: white;
        border-radius: 16px;
        padding: 32px;
        margin-bottom: 32px;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .order-section h2 {
        font-size: 1.4rem;
        font-weight: 600;
        margin-bottom: 24px;
        color: var(--dark);
        position: relative;
        padding-bottom: 12px;
    }

    .order-section h2::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 40px;
        height: 3px;
        background: var(--primary);
        border-radius: 2px;
    }

    .order-items {
        display: grid;
        gap: 16px;
    }

    .order-item {
        display: flex;
        gap: 20px;
        padding: 20px;
        background: var(--light);
        border-radius: 12px;
        transition: transform 0.2s ease;
        border: 1px solid rgba(0,0,0,0.04);
    }

    .order-item:hover {
        transform: translateX(4px);
    }

    .item-image {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        object-fit: cover;
        border: 2px solid white;
        box-shadow: var(--shadow);
    }

    .item-details h3 {
        font-size: 1.1rem;
        margin-bottom: 8px;
        font-weight: 600;
    }

    .item-meta {
        display: flex;
        gap: 16px;
        font-size: 0.95rem;
        color: #666;
    }

    .timeline {
        position: relative;
        padding-left: 24px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 9px;
        top: 8px;
        bottom: 8px;
        width: 2px;
        background: rgba(0,0,0,0.08);
    }

    .timeline-item {
        position: relative;
        margin-bottom: 32px;
        padding-left: 32px;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -1px;
        top: 5px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: white;
        border: 4px solid var(--primary);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .timeline-status {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 6px;
    }

    .timeline-date {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 8px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .summary-row:last-child {
        border-bottom: none;
    }

    .summary-row.total {
        font-weight: 600;
        color: var(--dark);
        font-size: 1.1rem;
    }

    .shipping-info .info-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .shipping-info .info-row i {
        width: 24px;
        text-align: center;
        color: var(--primary);
    }

    .order-actions {
        display: flex;
        gap: 16px;
        margin-top: 48px;
        flex-wrap: wrap;
    }

    .btn {
        padding: 12px 28px;
        border-radius: 12px;
        font-weight: 500;
        transition: all 0.2s ease;
        border: 2px solid transparent;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background: var(--secondary);
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(106,90,231,0.2);
    }

    .btn-secondary {
        background: var(--dark);
        color: white;
    }

    .btn-print {
        background: var(--success);
    }

    .btn-print:hover {
        background: #009245;
    }

    /* Tracking Progress Styles */
    .tracking-progress {
        margin: 40px 0;
        position: relative;
    }

    .progress-bar {
        height: 8px;
        background: rgba(0,0,0,0.1);
        border-radius: 4px;
        position: relative;
    }

    .progress-fill {
        height: 100%;
        background: var(--success);
        border-radius: 4px;
        width: <?= $progress_percent ?>%;
        transition: width 0.5s ease;
    }

    .tracking-steps {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
    }

    .tracking-step {
        text-align: center;
        position: relative;
        flex: 1;
    }

    .step-icon {
        width: 50px;
        height: 50px;
        background: <?= $progress_percent >= 95 ? 'var(--success)' : '#fff' ?>;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        border: 3px solid <?= $progress_percent >= 95 ? 'var(--success)' : 'rgba(0,0,0,0.1)' ?>;
        color: <?= $progress_percent >= 95 ? '#fff' : 'rgba(0,0,0,0.3)' ?>;
        transition: all 0.3s ease;
    }

    .step-title {
        font-size: 0.9rem;
        color: var(--dark);
        font-weight: 500;
    }

    .step-date {
        font-size: 0.8rem;
        color: #666;
        margin-top: 5px;
    }

    .current-step .step-icon {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
        transform: scale(1.1);
    }

    .carrier-info {
        display: flex;
        gap: 20px;
        background: var(--light);
        padding: 20px;
        border-radius: 12px;
        margin: 30px 0;
    }

    .carrier-detail {
        flex: 1;
    }

    .carrier-detail h3 {
        font-size: 1rem;
        margin-bottom: 8px;
    }

    .tracking-map {
        height: 200px;
        background: #ddd;
        border-radius: 12px;
        margin: 20px 0;
        position: relative;
        overflow: hidden;
    }

    .map-overlay {
        position: absolute;
        bottom: 0;
        width: 100%;
        background: rgba(0,0,0,0.5);
        color: white;
        padding: 15px;
        font-size: 0.9rem;
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
        .order-details-container {
            padding: 0 16px;
            margin-top: 160px;
        }
        
        .order-header {
            padding: 32px;
            clip-path: polygon(0 0, 100% 0, 100% 95%, 0 100%);
        }
        
        .order-content {
            padding: 32px;
        }
        
        .order-grid {
            grid-template-columns: 1fr;
            gap: 24px;
        }
        
        .order-section {
            padding: 24px;
        }
        
        .order-title {
            font-size: 1.8rem;
        }
        
        .tracking-steps {
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .tracking-step {
            flex: 50%;
        }
        
        .carrier-info {
            flex-direction: column;
        }
    }

    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .order-card {
        animation: fadeIn 0.6s ease forwards;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    .current-step .step-icon {
        animation: pulse 2s infinite;
    }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="order-details-container">
        <div class="order-card">
            <div class="order-header">
                <h1 class="order-title">Order #<?= htmlspecialchars($order['order_number']) ?></h1>
                <div class="status-container">
                    <span class="status-badge">
                        <i class="fas fa-<?= $order['payment_status'] === 'paid' ? 'check-circle' : 'clock' ?>"></i>
                        <?= ucfirst($order['status']) ?>
                    </span>
                    <span class="payment-method">
                        <?= strtoupper($order['payment_method']) ?> 
                        <?= $order['payment_status'] === 'paid' ? '(Paid)' : '(Pending)' ?>
                    </span>
                </div>
            </div>

            <div class="order-content">
                <!-- Delivery Tracking Section -->
                <div class="order-section">
                    <h2>Delivery Tracking</h2>
                    
                    <div class="tracking-progress">
                        <div class="progress-bar">
                            <div class="progress-fill"></div>
                        </div>
                        
                        <div class="tracking-steps">
                            <?php foreach ($tracking_stages as $key => $stage): ?>
                            <?php 
                                $is_current = $key === $current_stage;
                                $is_completed = array_search($key, array_keys($tracking_stages)) < array_search($current_stage, array_keys($tracking_stages));
                                $stage_date = date('M j', strtotime($order['created_at']) + (array_search($key, array_keys($tracking_stages)) * 86400));
                            ?>
                            <div class="tracking-step <?= $is_current ? 'current-step' : '' ?>">
                                <div class="step-icon">
                                    <i class="fas <?= $stage['icon'] ?>"></i>
                                </div>
                                <div class="step-title"><?= $stage['title'] ?></div>
                                <?php if ($is_completed || $is_current): ?>
                                <div class="step-date"><?= $stage_date ?></div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Carrier Information -->
                    <?php if ($shipping_info): ?>
                    <div class="carrier-info">
                        <div class="carrier-detail">
                            <h3>Carrier Information</h3>
                            <p><?= htmlspecialchars($shipping_info['carrier_name']) ?></p>
                        </div>
                        <div class="carrier-detail">
                            <h3>Tracking Number</h3>
                            <p><?= htmlspecialchars($shipping_info['tracking_number']) ?></p>
                        </div>
                        <div class="carrier-detail">
                            <h3>Estimated Delivery</h3>
                            <p><?= date('M j, Y', strtotime($shipping_info['estimated_delivery'])) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Map Visualization -->
                    <div class="tracking-map">
                        <div class="map-overlay">
                            <i class="fas fa-map-marker-alt"></i>
                            Last scan: <?= date('M j, g:i A', strtotime($order['last_update'])) ?> - In transit to final destination
                        </div>
                    </div>
                </div>

                <div class="order-grid">
                    <!-- Left Column -->
                    <div>
                        <!-- Order Items Section -->
                        <div class="order-section">
                            <h2><?= $order['item_count'] ?> Item<?= $order['item_count'] > 1 ? 's' : '' ?></h2>
                            <div class="order-items">
                                <?php foreach ($items as $item): ?>
                                <div class="order-item">
                                    <?php if ($item['image_path']): ?>
                                    <img src="<?= htmlspecialchars($item['image_path']) ?>" 
                                         class="item-image" 
                                         alt="<?= htmlspecialchars($item['name']) ?>">
                                    <?php endif; ?>
                                    <div class="item-details">
                                        <h3><?= htmlspecialchars($item['name']) ?></h3>
                                        <div class="item-meta">
                                            <span>Qty: <?= $item['quantity'] ?></span>
                                            <span>Price: ₹<?= number_format($item['price'], 2) ?></span>
                                            <?php if (isset($item['stock_quantity']) && $item['stock_quantity'] < 10): ?>
                                            <span class="stock-warning">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                Low Stock
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Order Timeline Section -->
                        <div class="order-section">
                            <h2>Order Progress</h2>
                            <div class="timeline">
                                <?php if (!empty($history)): ?>
                                    <?php foreach ($history as $event): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-status">
                                            <?= ucfirst($event['status']) ?>
                                        </div>
                                        <div class="timeline-date">
                                            <?= date('M j, Y g:i A', strtotime($event['created_at'])) ?>
                                        </div>
                                        <?php if ($event['notes']): ?>
                                        <p class="timeline-notes">
                                            <?= htmlspecialchars($event['notes']) ?>
                                        </p>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="no-history">
                                        <i class="fas fa-clock"></i>
                                        Order progress updates will appear here
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div>
                        <!-- Order Summary Section -->
                        <div class="order-section">
                            <h2>Payment Summary</h2>
                            <div class="summary-item">
                                <div class="summary-row">
                                    <span>Order Date:</span>
                                    <span><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></span>
                                </div>
                                <div class="summary-row">
                                    <span>Subtotal:</span>
                                    <span>₹<?= number_format($order['total_amount'] - $order['shipping_fee'], 2) ?></span>
                                </div>
                                <div class="summary-row">
                                    <span>Shipping:</span>
                                    <span>₹<?= number_format($order['shipping_fee'], 2) ?></span>
                                </div>
                                <div class="summary-row total">
                                    <span>Total Paid:</span>
                                    <span>₹<?= number_format($order['total_amount'], 2) ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Shipping Details Section -->
                        <div class="order-section">
                            <h2>Delivery Information</h2>
                            <div class="shipping-info">
                                <div class="info-row">
                                    <i class="fas fa-user"></i>
                                    <?= htmlspecialchars($order['name']) ?>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?= nl2br(htmlspecialchars($order['address'])) ?>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-phone"></i>
                                    <?= htmlspecialchars($order['phone']) ?>
                                </div>
                                <?php if ($order['email']): ?>
                                <div class="info-row">
                                    <i class="fas fa-envelope"></i>
                                    <?= htmlspecialchars($order['email']) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Actions -->
                <div class="order-actions">
                    <a href="../products/" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Continue Shopping
                    </a>
                    <?php if ($shipping_info): ?>
                    <a href="https://carrier-tracking.com/?track=<?= htmlspecialchars($shipping_info['tracking_number']) ?>" 
                       class="btn btn-primary" target="_blank">
                        <i class="fas fa-map-pin"></i> Live Tracking
                    </a>
                    <?php endif; ?>
                    <button onclick="window.print()" class="btn btn-print">
                        <i class="fas fa-print"></i> Print Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Print Optimization
    function beforePrint() {
        document.querySelectorAll('.btn, .header, .footer').forEach(el => {
            el.style.display = 'none';
        });
        document.querySelector('.order-card').style.marginTop = '50px';
    }
    
    // Progress bar animation
    document.addEventListener('DOMContentLoaded', function() {
        // Animation for progress bar
        const progressFill = document.querySelector('.progress-fill');
        progressFill.style.width = '0';
        setTimeout(() => {
            progressFill.style.width = '<?= $progress_percent ?>%';
        }, 500);
        
        // Add event listener for print
        window.addEventListener('beforeprint', beforePrint);
        
        // Real-time tracking simulation (demo only)
        <?php if ($shipping_info && $order['status'] !== 'delivered'): ?>
        setInterval(() => {
            const mapOverlay = document.querySelector('.map-overlay');
            const locations = [
                "Package received at sorting facility",
                "In transit to regional distribution center",
                "Arrived at local delivery hub",
                "Out for delivery"
            ];
            const randomUpdate = locations[Math.floor(Math.random() * locations.length)];
            const time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            mapOverlay.innerHTML = `<i class="fas fa-map-marker-alt"></i> Last scan: ${time} - ${randomUpdate}`;
        }, 10000);
        <?php endif; ?>
    });
    </script>
</body>
</html>