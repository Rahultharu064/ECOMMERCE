<?php
session_start();
require '../includes/config.php';

// Initialize variables
$tracking_number = '';
$order_data = null;
$order_items = [];
$error = '';
$status_updates = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['track_order'])) {
    $tracking_number = trim($_POST['tracking_number']);
    
    if (empty($tracking_number)) {
        $error = 'Please enter a tracking number';
    } else {
        // First check if order exists
        $stmt = $conn->prepare("SELECT id, order_number, total_amount, name, email, phone, address, created_at 
                               FROM orders 
                               WHERE order_number = ?");
        $stmt->bind_param("s", $tracking_number);
        $stmt->execute();
        $order_result = $stmt->get_result();
        
        if ($order_result->num_rows > 0) {
            $order_data = $order_result->fetch_assoc();
            
            // Get all status updates for this order
            $stmt = $conn->prepare("SELECT * FROM order_updates 
                                   WHERE order_id = ? 
                                   ORDER BY update_time DESC");
            $stmt->bind_param("i", $order_data['id']);
            $stmt->execute();
            $status_updates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            // If no updates exist, create initial status
            if (empty($status_updates)) {
                $initial_status = 'pending';
                $stmt = $conn->prepare("INSERT INTO order_updates 
                                      (order_id, status, message) 
                                      VALUES (?, ?, ?)");
                $message = "Your order has been received and is being processed";
                $stmt->bind_param("iss", $order_data['id'], $initial_status, $message);
                $stmt->execute();
                
                // Re-fetch updates
                $stmt = $conn->prepare("SELECT * FROM order_updates 
                                       WHERE order_id = ? 
                                       ORDER BY update_time DESC");
                $stmt->bind_param("i", $order_data['id']);
                $stmt->execute();
                $status_updates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            }
            
            // Get order items
            $stmt = $conn->prepare("SELECT oi.*, p.image_path, p.product_name 
                                   FROM order_items oi 
                                   LEFT JOIN products p ON oi.product_id = p.id 
                                   WHERE oi.order_id = ?");
            $stmt->bind_param("i", $order_data['id']);
            $stmt->execute();
            $order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
        } else {
            $error = 'No order found with that tracking number';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Tracking | PharmaCare</title>
    <link rel="stylesheet" href="../assets/css/pharmacy.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
       :root {
    --primary-color: #3498db;
    --secondary-color: #2980b9;
    --accent-color: #e74c3c;
    --light-color: #f8f9fa;
    --dark-color: #343a40;
    --success-color: #28a745;
    --warning-color: #ffc107;
    --info-color: #17a2b8;
    --border-color: #e0e0e0;
    --text-color: #333;
    --text-muted: #6c757d;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f5f7fa;
    line-height: 1.6;
    color: var(--text-color);
    margin: 0;
    padding: 0;
}

/* Header Styles */
.tracking-container {
    max-width: 1000px;
    margin: 290px auto 50px;
    padding: 0 20px;
}

.tracking-header {
    text-align: center;
    margin-bottom: 40px;
}

.tracking-title {
    font-size: 32px;
    font-weight: 600;
    color: var(--dark-color);
    margin-bottom: 10px;
}

.tracking-subtitle {
    font-size: 16px;
    color: var(--text-muted);
    max-width: 600px;
    margin: 0 auto;
}

/* Form Styles */
.tracking-form {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    padding: 30px;
    margin-bottom: 40px;
    text-align: center;
}

.form-group {
    margin-bottom: 20px;
    text-align: left;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
}

.form-input {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    font-size: 16px;
    transition: border-color 0.3s ease;
}

.form-input:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

/* Button Styles */
.btn {
    padding: 12px 25px;
    border-radius: 6px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
    cursor: pointer;
    font-size: 16px;
}

.btn-primary {
    background: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background: var(--secondary-color);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.btn i {
    margin-right: 8px;
}

.error-message {
    color: var(--accent-color);
    margin-top: 10px;
    text-align: center;
    font-size: 14px;
}

/* Results Container */
.tracking-results {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    padding: 30px;
    margin-bottom: 30px;
    display: none;
}

.no-results {
    text-align: center;
    padding: 40px 0;
    display: none;
}

.no-results-icon {
    font-size: 60px;
    color: #ddd;
    margin-bottom: 20px;
}

/* Status Card */
.current-status-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    padding: 20px;
    margin-bottom: 20px;
    text-align: center;
    border-left: 4px solid var(--primary-color);
}

.status-icon {
    font-size: 40px;
    margin-bottom: 10px;
}

.status-icon.pending { color: var(--warning-color); }
.status-icon.processing { color: var(--info-color); }
.status-icon.shipped { color: var(--primary-color); }
.status-icon.delivered { color: var(--success-color); }
.status-icon.cancelled { color: var(--accent-color); }

.status-heading {
    font-size: 22px;
    font-weight: 600;
    margin-bottom: 5px;
}

.status-message {
    color: var(--text-muted);
    margin-bottom: 10px;
}

.status-time {
    font-size: 14px;
    color: var(--text-muted);
}

/* Progress Tracker */
.tracking-progress {
    display: flex;
    justify-content: space-between;
    margin: 30px 0;
    position: relative;
}

.progress-line {
    position: absolute;
    top: 20px;
    left: 0;
    right: 0;
    height: 2px;
    background: #e9ecef;
    z-index: 0;
}

.progress-line-fill {
    height: 100%;
    background: var(--success-color);
    transition: width 0.3s ease;
}

.progress-step {
    text-align: center;
    flex: 1;
    position: relative;
    z-index: 1;
}

.step-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    color: var(--text-muted);
    transition: all 0.3s ease;
}

.step-icon.active {
    background: var(--primary-color);
    color: white;
}

.step-icon.completed {
    background: var(--success-color);
    color: white;
}

.step-label {
    font-size: 14px;
    color: var(--text-muted);
    transition: all 0.3s ease;
}

.step-label.active {
    color: var(--dark-color);
    font-weight: 500;
}

/* Order Information */
.order-overview {
    display: flex;
    justify-content: space-between;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

.order-info {
    flex: 1;
    min-width: 250px;
}

.order-info-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 15px;
    color: var(--dark-color);
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border-color);
}

.info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px dashed var(--border-color);
}

.info-label {
    color: var(--text-muted);
}

.info-value {
    font-weight: 500;
}

/* Order Items */
.items-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 20px;
    color: var(--dark-color);
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border-color);
}

.order-items {
    margin-bottom: 30px;
}

.item-card {
    display: flex;
    padding: 15px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    margin-bottom: 15px;
    gap: 20px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.item-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.item-image {
    width: 80px;
    height: 80px;
    border-radius: 6px;
    object-fit: cover;
    border: 1px solid #eee;
}

.item-details {
    flex: 1;
}

.item-name {
    font-weight: 500;
    margin-bottom: 5px;
}

.item-meta {
    display: flex;
    gap: 15px;
    margin-bottom: 5px;
    flex-wrap: wrap;
}

.item-price {
    color: var(--text-muted);
}

.item-quantity {
    color: var(--text-muted);
}

.item-total {
    font-weight: 600;
    min-width: 100px;
    text-align: right;
    align-self: center;
}

/* Status Updates */
.status-updates {
    margin-bottom: 30px;
}

.update-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    padding: 15px;
    margin-bottom: 15px;
    border-left: 3px solid var(--primary-color);
    transition: transform 0.3s ease;
}

.update-card:hover {
    transform: translateX(5px);
}

.update-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
}

.update-status {
    font-weight: 500;
}

.update-time {
    color: var(--text-muted);
    font-size: 14px;
}

.update-message {
    color: var(--text-color);
}

.update-location {
    color: var(--text-muted);
    font-size: 14px;
    margin-top: 5px;
}

.update-location i {
    margin-right: 5px;
    color: var(--primary-color);
}

/* Delivery Info */
.delivery-info {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    margin-top: 30px;
}

.delivery-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 15px;
    color: var(--dark-color);
}

/* Responsive Styles */
@media (max-width: 768px) {
    .tracking-container {
        margin-top: 80px;
        padding: 0 15px;
    }
    
    .order-overview {
        flex-direction: column;
    }
    
    .item-card {
        flex-direction: column;
    }
    
    .item-total {
        text-align: left;
        margin-top: 10px;
    }
    
    .tracking-progress {
        flex-wrap: wrap;
        gap: 20px;
    }
    
    .progress-step {
        flex: 0 0 calc(50% - 10px);
    }
    
    .progress-line {
        display: none;
    }
}

@media (max-width: 480px) {
    .tracking-title {
        font-size: 24px;
    }
    
    .tracking-form {
        padding: 20px;
    }
    
    .current-status-card {
        padding: 15px;
    }
    
    .status-heading {
        font-size: 18px;
    }
    
    .progress-step {
        flex: 0 0 100%;
    }
    
    .update-header {
        flex-direction: column;
    }
    
    .update-time {
        margin-top: 5px;
    }
}
        
        /* (All previous CSS styles remain the same) */
        
        .tracking-help {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            border-left: 4px solid var(--primary-color);
        }
        
        .help-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark-color);
        }
        
        .help-list {
            padding-left: 20px;
        }
        
        .help-list li {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="tracking-container">
        <div class="tracking-header">
            <h1 class="tracking-title">Track Your Order</h1>
            <p class="tracking-subtitle">Enter your order number to view real-time updates</p>
        </div>
        
        <form method="post" class="tracking-form">
            <div class="form-group">
                <label for="tracking_number">Order Number</label>
                <input type="text" id="tracking_number" name="tracking_number" class="form-input" 
                       placeholder="e.g. ORD-20230515-ABC123" value="<?php echo htmlspecialchars($tracking_number); ?>"
                       required>
            </div>
            <button type="submit" name="track_order" class="btn btn-primary">
                <i class="fas fa-search"></i> Track Order
            </button>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    <?php if ($error == 'No order found with that tracking number'): ?>
                        <div class="tracking-help" style="margin-top: 15px;">
                            <div class="help-title">Can't find your order number?</div>
                            <ul class="help-list">
                                <li>Check your confirmation email</li>
                                <li>Look in your account order history</li>
                                <li>Contact our customer support</li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </form>
        
        <div class="no-results" style="display: <?php echo ($order_data || $error) ? 'none' : 'block'; ?>">
            <div class="no-results-icon">
                <i class="fas fa-box-open"></i>
            </div>
            <h3>Ready to track your order?</h3>
            <p>Enter your order number above to view your order status and updates</p>
        </div>
        
        <?php if ($order_data): ?>
            <div class="tracking-results" style="display: block;">
                <!-- Current Status Card -->
                <div class="current-status-card">
                    <?php
                    $status_icons = [
                        'pending' => 'fas fa-clock',
                        'processing' => 'fas fa-cog',
                        'shipped' => 'fas fa-truck',
                        'delivered' => 'fas fa-check-circle',
                        'cancelled' => 'fas fa-times-circle'
                    ];
                    $current_status = $status_updates[0]['status'];
                    ?>
                    <div class="status-icon <?php echo $current_status; ?>">
                        <i class="<?php echo $status_icons[$current_status] ?? 'fas fa-info-circle'; ?>"></i>
                    </div>
                    <div class="status-heading">
                        Order <?php echo ucfirst($current_status); ?>
                    </div>
                    <?php if (!empty($status_updates[0]['message'])): ?>
                        <div class="status-message"><?php echo htmlspecialchars($status_updates[0]['message']); ?></div>
                    <?php endif; ?>
                    <div class="status-time">
                        Last updated: <?php echo date('F j, Y g:i A', strtotime($status_updates[0]['update_time'])); ?>
                    </div>
                </div>
                
                <!-- Tracking Progress -->
                <div class="tracking-progress">
                    <div class="progress-line">
                        <div class="progress-line-fill" style="width: <?php 
                            echo $current_status === 'pending' ? '0%' : 
                                 ($current_status === 'processing' ? '33%' : 
                                 ($current_status === 'shipped' ? '66%' : '100%'));
                        ?>"></div>
                    </div>
                    
                    <div class="progress-step">
                        <div class="step-icon <?php 
                            echo $current_status === 'pending' ? 'active' : 'completed';
                        ?>">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="step-label <?php echo $current_status === 'pending' ? 'active' : ''; ?>">
                            Ordered
                        </div>
                    </div>
                    
                    <div class="progress-step">
                        <div class="step-icon <?php 
                            echo $current_status === 'processing' ? 'active' : 
                                (in_array($current_status, ['shipped', 'delivered']) ? 'completed' : '');
                        ?>">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div class="step-label <?php echo $current_status === 'processing' ? 'active' : ''; ?>">
                            Processing
                        </div>
                    </div>
                    
                    <div class="progress-step">
                        <div class="step-icon <?php 
                            echo $current_status === 'shipped' ? 'active' : 
                                ($current_status === 'delivered' ? 'completed' : '');
                        ?>">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="step-label <?php echo $current_status === 'shipped' ? 'active' : ''; ?>">
                            Shipped
                        </div>
                    </div>
                    
                    <div class="progress-step">
                        <div class="step-icon <?php echo $current_status === 'delivered' ? 'active' : ''; ?>">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="step-label <?php echo $current_status === 'delivered' ? 'active' : ''; ?>">
                            Delivered
                        </div>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="order-overview">
                    <div class="order-info">
                        <h3 class="order-info-title">Order Summary</h3>
                        <div class="info-row">
                            <span class="info-label">Order Number:</span>
                            <span class="info-value"><?php echo htmlspecialchars($order_data['order_number']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Order Date:</span>
                            <span class="info-value"><?php echo date('F j, Y', strtotime($order_data['created_at'])); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Total Amount:</span>
                            <span class="info-value">₹<?php echo number_format($order_data['total_amount'], 2); ?></span>
                        </div>
                    </div>
                    
                    <div class="order-info">
                        <h3 class="order-info-title">Delivery Information</h3>
                        <div class="info-row">
                            <span class="info-label">Recipient:</span>
                            <span class="info-value"><?php echo htmlspecialchars($order_data['name']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Address:</span>
                            <span class="info-value"><?php echo nl2br(htmlspecialchars($order_data['address'])); ?></span>
                        </div>
                        <?php if (!empty($status_updates[0]['tracking_code'])): ?>
                            <div class="info-row">
                                <span class="info-label">Tracking Code:</span>
                                <span class="info-value"><?php echo htmlspecialchars($status_updates[0]['tracking_code']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Order Items -->
                <h3 class="items-title">Order Items</h3>
                <div class="order-items">
                    <?php foreach ($order_items as $item): ?>
                        <div class="item-card">
                            <img src="<?php echo htmlspecialchars($item['image_path'] ?? '../assets/images/placeholder-product.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                 class="item-image">
                            <div class="item-details">
                                <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                <div class="item-meta">
                                    <span class="item-price">₹<?php echo number_format($item['price'], 2); ?></span>
                                    <span class="item-quantity">Quantity: <?php echo $item['quantity']; ?></span>
                                </div>
                            </div>
                            <div class="item-total">₹<?php echo number_format($item['total'], 2); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Status Updates -->
                <h3 class="items-title">Recent Updates</h3>
                <div class="status-updates">
                    <?php foreach ($status_updates as $update): ?>
                        <div class="update-card">
                            <div class="update-header">
                                <div class="update-status">
                                    <?php echo ucfirst($update['status']); ?>
                                </div>
                                <div class="update-time">
                                    <?php echo date('M j, Y g:i A', strtotime($update['update_time'])); ?>
                                </div>
                            </div>
                            <?php if (!empty($update['message'])): ?>
                                <div class="update-message"><?php echo htmlspecialchars($update['message']); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($update['location'])): ?>
                                <div class="update-location">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($update['location']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Support Information -->
                <div class="delivery-info">
                    <h3 class="delivery-title">Need Help With Your Order?</h3>
                    <p>Our customer support team is available to help with any questions:</p>
                    <p>
                        <i class="fas fa-phone"></i> +977 9801234567<br>
                        <i class="fas fa-envelope"></i> support@pharmacare.com<br>
                        <i class="fas fa-clock"></i> 9AM-6PM, 7 days a week
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>
    
    <script>
        // Auto-focus the tracking number input
        document.getElementById('tracking_number').focus();
        
        // If URL has tracking number parameter, submit the form
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const trackingNumber = urlParams.get('tracking_number');
            
            if (trackingNumber) {
                document.getElementById('tracking_number').value = trackingNumber;
                document.querySelector('form').submit();
            }
        });
        document.getElementById('tracking_number').focus();
    
    // If URL has tracking number parameter, submit the form
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const trackingNumber = urlParams.get('tracking_number');
        
        if (trackingNumber) {
            document.getElementById('tracking_number').value = trackingNumber;
            document.querySelector('form').submit();
        }
        
        // Clear the search box after form submission
        const form = document.querySelector('form');
        form.addEventListener('submit', function() {
            if (window.location.search.includes('tracking_number')) {
                // If we came from a URL with tracking number, don't clear
                return;
            }
            setTimeout(() => {
                document.getElementById('tracking_number').value = '';
            }, 100);
        });
    });
    </script>
</body>
</html>