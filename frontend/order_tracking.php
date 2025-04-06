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

// Get shipping information
$order_query = "
    SELECT o.*, 
           os.tracking_number, os.shipping_method, os.estimated_delivery, os.actual_delivery, os.status AS shipping_status,
           sc.carrier_name, sc.tracking_url, sc.logo_url,
           MAX(oh.created_at) AS last_update
    FROM orders o
    LEFT JOIN order_shipping os ON o.id = os.order_id
    LEFT JOIN shipping_carriers sc ON os.carrier_id = sc.id
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

// Generate tracking URL if shipping info exists
$tracking_url = null;
if ($order['tracking_number'] && $order['tracking_url_pattern']) {
    $tracking_url = str_replace('{tracking_number}', urlencode($order['tracking_number']), $order['tracking_url_pattern']);
}

// Get order history for tracking
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

// Tracking stages configuration
$tracking_stages = [
    'processing' => ['title' => 'Processing', 'icon' => 'fa-cogs'],
    'shipped' => ['title' => 'Shipped', 'icon' => 'fa-shipping-fast'],
    'out_for_delivery' => ['title' => 'Out for Delivery', 'icon' => 'fa-truck'],
    'delivered' => ['title' => 'Delivered', 'icon' => 'fa-check-circle']
];

// Determine current stage
$current_stage = $order['shipping_status'] ?? 'processing';
$stage_index = array_search($current_stage, array_keys($tracking_stages));
$progress_percent = (($stage_index + 1) / count($tracking_stages)) * 100;

// Calculate days remaining for delivery
$days_remaining = null;
if ($order['estimated_delivery'] && $order['shipping_status'] !== 'delivered') {
    $delivery_date = new DateTime($order['estimated_delivery']);
    $today = new DateTime();
    $days_remaining = $delivery_date->diff($today)->days;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Tracking - PharmaCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/pharmacy.css">
    <style>
        :root {
            --primary: #4A90E2;
            --primary-dark: #3a7bc8;
            --secondary: #6C5CE7;
            --success: #00C853;
            --warning: #FFAB00;
            --danger: #FF5252;
            --dark: #2D3436;
            --light: #F8F9FA;
            --light-dark: #e9ecef;
            --border: #dee2e6;
            --text-light: #6c757d;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--light);
            line-height: 1.6;
            color: var(--dark);
        }

        .order-tracking-container {
            max-width: 1280px;
            margin: 280px auto 50px;
            padding: 0 32px;
        }

        .order-card {
            background: white;
            border-radius: 24px;
            box-shadow: var(--shadow-md);
            overflow: hidden;
            transition: var(--transition);
            border: 1px solid var(--border);
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .order-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
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

        .order-content {
            padding: 48px;
        }

        .order-section {
            background: white;
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 32px;
            border: 1px solid var(--border);
            transition: var(--transition);
        }

        .order-section:hover {
            box-shadow: var(--shadow-sm);
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
            background: <?= ($progress_percent >= 95) ? 'var(--success)' : '#fff' ?>;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            border: 3px solid <?= ($progress_percent >= 95) ? 'var(--success)' : 'rgba(0,0,0,0.1)' ?>;
            color: <?= ($progress_percent >= 95) ? '#fff' : 'rgba(0,0,0,0.3)' ?>;
            transition: var(--transition);
        }

        .step-title {
            font-size: 0.9rem;
            color: var(--dark);
            font-weight: 500;
        }

        .step-date {
            font-size: 0.8rem;
            color: var(--text-light);
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
            border: 1px solid var(--border);
        }

        .carrier-detail {
            flex: 1;
        }

        .carrier-detail h3 {
            font-size: 1rem;
            margin-bottom: 8px;
            color: var(--dark);
        }

        .carrier-detail p {
            color: var(--text-light);
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

        .tracking-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            margin-top: 5px;
        }

        .tracking-link:hover {
            text-decoration: underline;
        }

        /* Additional styles for enhanced tracking */
        .delivery-estimate {
            background: var(--light);
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border-left: 4px solid var(--primary);
        }
        
        .delivery-estimate h3 {
            margin-top: 0;
            color: var(--primary);
        }
        
        .tracking-status {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .tracking-status i {
            font-size: 1.2rem;
            color: var(--primary);
        }

        .carrier-logo {
            max-height: 30px;
            vertical-align: middle;
            margin-right: 10px;
        }

        .tracking-number {
            font-family: monospace;
            font-size: 1.1em;
            color: var(--dark);
        }

        .days-remaining {
            color: var(--primary);
            font-weight: 500;
        }

        .delivered-status {
            color: var(--success);
            font-weight: 500;
        }

        .btn {
            padding: 12px 28px;
            border-radius: 12px;
            font-weight: 500;
            transition: var(--transition);
            border: 2px solid transparent;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-secondary {
            background: var(--dark);
            color: white;
        }

        .btn-secondary:hover {
            background: var(--dark);
            opacity: 0.9;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .order-tracking-container {
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
    
    <div class="order-tracking-container">
        <div class="order-card">
            <div class="order-header">
                <h1 class="order-title">Order Tracking #<?= htmlspecialchars($order['order_number']) ?></h1>
                <div class="status-container">
                    <span class="status-badge">
                        <i class="fas fa-<?= ($order['shipping_status'] === 'delivered') ? 'check-circle' : 'shipping-fast' ?>"></i>
                        <?= ucfirst($order['shipping_status']) ?>
                    </span>
                </div>
            </div>

            <div class="order-content">
                <!-- Delivery Tracking Section -->
                <div class="order-section" id="tracking">
                    <h2>Delivery Tracking</h2>
                    
                    <?php if ($order['tracking_number']): ?>
                        <div class="delivery-estimate">
                            <div class="tracking-status">
                                <i class="fas fa-<?= ($order['shipping_status'] === 'delivered') ? 'check-circle' : 'shipping-fast' ?>"></i>
                                <h3>
                                    <?= ($order['shipping_status'] === 'delivered') ? 
                                        'Delivered on ' . date('M j, Y', strtotime($order['actual_delivery'])) : 
                                        'Estimated Delivery: ' . date('M j, Y', strtotime($order['estimated_delivery'])) ?>
                                </h3>
                            </div>
                            
                            <?php if ($order['shipping_status'] !== 'delivered' && $days_remaining !== null): ?>
                                <p>
                                    <span class="days-remaining">
                                        Approximately <?= $days_remaining ?> day<?= $days_remaining > 1 ? 's' : '' ?> remaining
                                    </span>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="tracking-progress">
                            <div class="progress-bar">
                                <div class="progress-fill"></div>
                            </div>
                            
                            <div class="tracking-steps">
                                <?php foreach ($tracking_stages as $key => $stage): ?>
                                <?php 
                                    $is_current = ($key === $current_stage);
                                    $is_completed = array_search($key, array_keys($tracking_stages)) < $stage_index;
                                    $stage_date = '';
                                    
                                    // Find the first history entry for this stage
                                    foreach ($history as $event) {
                                        if (strtolower($event['status']) === $key) {
                                            $stage_date = date('M j', strtotime($event['created_at']));
                                            break;
                                        }
                                    }
                                ?>
                                <div class="tracking-step <?= $is_current ? 'current-step' : '' ?> <?= $is_completed ? 'completed-step' : '' ?>">
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
                        <div class="carrier-info">
                            <div class="carrier-detail">
                                <h3>Carrier Information</h3>
                                <div class="carrier-header">
                                    <?php if ($order['logo_url']): ?>
                                        <img src="<?= htmlspecialchars($order['logo_url']) ?>" class="carrier-logo" alt="<?= htmlspecialchars($order['carrier_name']) ?>">
                                    <?php endif; ?>
                                    <p><?= htmlspecialchars($order['carrier_name']) ?></p>
                                </div>
                                <?php if (!empty($order['shipping_method'])): ?>
                                <p class="text-muted"><?= htmlspecialchars($order['shipping_method']) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="carrier-detail">
                                <h3>Tracking Number</h3>
                                <p class="tracking-number"><?= htmlspecialchars($order['tracking_number']) ?></p>
                                <?php if ($tracking_url): ?>
                                <a href="<?= $tracking_url ?>" 
                                   target="_blank" 
                                   class="tracking-link">
                                    <i class="fas fa-external-link-alt"></i> Track Package
                                </a>
                                <?php endif; ?>
                            </div>
                            <div class="carrier-detail">
                                <h3><?= ($order['shipping_status'] === 'delivered') ? 'Delivered On' : 'Estimated Delivery' ?></h3>
                                <p><?= date('M j, Y', strtotime(($order['shipping_status'] === 'delivered') ? $order['actual_delivery'] : $order['estimated_delivery'])) ?></p>
                            </div>
                        </div>

                        <!-- Map Visualization -->
                        <div class="tracking-map">
                            <div class="map-overlay">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php if ($order['shipping_status'] === 'delivered'): ?>
                                    Delivered on <?= date('M j, g:i A', strtotime($order['actual_delivery'])) ?>
                                <?php else: ?>
                                    Last scan: <?= date('M j, g:i A', strtotime($order['last_update'])) ?> - 
                                    <?= ($order['shipping_status'] === 'shipped') ? 'In transit to final destination' : 
                                       (($order['shipping_status'] === 'out_for_delivery') ? 'Out for delivery' : 'Processing your order') ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert" style="background: var(--light); padding: 15px; border-radius: 8px; border: 1px solid var(--border);">
                            <i class="fas fa-info-circle"></i> Shipping information will be available once your order is processed.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Order Actions -->
                <div class="order-actions">
                    <a href="order.php?order_id=<?= $order_id ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Order Details
                    </a>
                    <?php if ($tracking_url): ?>
                    <a href="<?= $tracking_url ?>" 
                       class="btn btn-primary" target="_blank">
                        <i class="fas fa-external-link-alt"></i> Track on Carrier Website
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Progress bar animation
    document.addEventListener('DOMContentLoaded', function() {
        // Animation for progress bar
        const progressFill = document.querySelector('.progress-fill');
        if (progressFill) {
            progressFill.style.width = '0';
            setTimeout(() => {
                progressFill.style.width = '<?= $progress_percent ?>%';
            }, 500);
        }
        
        // Real-time tracking simulation (demo only)
        <?php if ($order['tracking_number'] && $order['shipping_status'] !== 'delivered'): ?>
        const statusMessages = {
            'processing': [
                "Preparing your order",
                "Items being packed",
                "Quality check in progress"
            ],
            'shipped': [
                "Package received at sorting facility",
                "In transit to regional distribution center",
                "Arrived at local delivery hub"
            ],
            'out_for_delivery': [
                "With delivery driver",
                "On vehicle for delivery",
                "In your area"
            ]
        };
        
        setInterval(() => {
            const mapOverlay = document.querySelector('.map-overlay');
            if (mapOverlay) {
                const messages = statusMessages['<?= $order['shipping_status'] ?>'] || ["Processing your order"];
                const randomUpdate = messages[Math.floor(Math.random() * messages.length)];
                const time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                mapOverlay.innerHTML = `<i class="fas fa-map-marker-alt"></i> Last scan: ${time} - ${randomUpdate}`;
            }
        }, 10000);
        <?php endif; ?>
    });
    </script>
</body>
</html>