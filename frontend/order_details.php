<?php
session_start();
require '../includes/config.php';
// require '../includes/auth.php';

// // Only logged in users can view order details
// Auth::requireLogin();

// Check if order ID is provided
if (!isset($_GET['id'])) {
    header("Location: order.php");
    exit();
}

$order_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Payment method mapping
$payment_methods = [
    'cod' => 'Cash on Delivery',
    'esewa' => 'eSewa',
    'khalti' => 'Khalti',
    'card' => 'Credit/Debit Card',
    'phonepay' => 'PhonePe'
];

// Status CSS classes
$status_classes = [
    'pending' => 'status-pending',
    'processing' => 'status-processing',
    'shipped' => 'status-shipped',
    'delivered' => 'status-delivered',
    'completed' => 'status-completed',
    'cancelled' => 'status-cancelled'
];

// Fetch order details
$order = [];
try {
    $stmt = $conn->prepare("SELECT 
        o.id, o.order_number, o.total_amount, o.shipping_fee, 
        o.payment_method, o.payment_details, o.status, o.created_at,
        o.name, o.email, o.phone, o.address, o.user_id,
        COUNT(oi.id) as item_count
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.id = ? AND o.user_id = ?
        GROUP BY o.id");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("ii", $order_id, $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
    } else {
        throw new Exception("No order found with ID: " . $order_id);
    }
} catch (Exception $e) {
    error_log("Order fetch error: " . $e->getMessage());
    header("Location: order.php");
    exit();
}

// Fetch order items
$order_items = [];
try {
    $stmt = $conn->prepare("SELECT 
        oi.name, oi.price, oi.quantity, oi.total,
        p.id as product_id, p.image_path
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $order_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $order_items[] = $row;
    }
    
} catch (Exception $e) {
    error_log("Order items fetch error: " . $e->getMessage());
    $order_items = []; // Ensure empty array on error
}

// Function to format currency
function format_currency($amount) {
    return '₹' . number_format((float)$amount, 2, '.', ',');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details | PharmaCare</title>
    <link rel="stylesheet" href="../assets/css/pharmacy.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- jsPDF library for PDF generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        .order-details-container {
            max-width: 1000px;
            margin: 220px auto 50px;
            padding: 20px;
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
            margin: 0;
        }
        
        .order-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-print {
            padding: 8px 15px;
            background: #6c757d;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-print:hover {
            background: #5a6268;
        }
        
        .btn-back {
            padding: 8px 15px;
            background: #f8f9fa;
            color: #212529;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-back:hover {
            background: #e2e6ea;
        }
        
        .order-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .order-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .order-section {
            margin-bottom: 20px;
        }
        
        .order-section h3 {
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-row {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .info-label {
            font-weight: 600;
            color: #444;
        }
        
        .order-status {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-processing {
            background: #cce5ff;
            color: #004085;
        }
        
        .status-shipped {
            background: #d4edda;
            color: #155724;
        }
        
        .status-delivered {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .order-items {
            margin-top: 20px;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .order-item-image {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 15px;
        }
        
        .order-item-details {
            flex-grow: 1;
        }
        
        .order-item-name {
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .order-item-meta {
            font-size: 14px;
            color: #6c757d;
        }
        
        .order-item-total {
            font-weight: 600;
            min-width: 100px;
            text-align: right;
        }
        
        .order-totals {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }
        
        .order-total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .grand-total {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .product-link {
            color: #3498db;
            margin-left: 5px;
        }
        
        .status-timeline {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .timeline-item {
            display: flex;
            margin-bottom: 15px;
            align-items: center;
        }
        
        .timeline-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .timeline-icon.active {
            background: #28a745;
            color: white;
        }
        
        .timeline-icon.pending {
            background: #ffc107;
            color: white;
        }
        
        .timeline-icon.inactive {
            background: #e9ecef;
            color: #6c757d;
        }
        
        .timeline-content {
            flex-grow: 1;
        }
        
        .timeline-date {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        @media (max-width: 768px) {
            .order-details-container {
                margin-top: 80px;
                padding: 15px;
            }
            
            .info-row {
                grid-template-columns: 1fr;
                gap: 5px;
            }
            
            .order-item {
                flex-wrap: wrap;
            }
            
            .order-item-total {
                text-align: left;
                margin-top: 10px;
                width: 100%;
            }
        }
        
        /* Print-specific styles */
        @media print {
            body * {
                visibility: hidden;
            }
            .order-card, .order-card * {
                visibility: visible;
            }
            .order-card {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                padding: 20px;
                box-shadow: none;
            }
            .order-actions {
                display: none !important;
            }
            .order-header {
                page-break-after: avoid;
            }
            .order-items {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="order-details-container">
        <div class="order-header">
            <h1 class="order-title">Order Details</h1>
            <div class="order-actions">
                <a href="order.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Orders
                </a>
                <button class="btn-print" onclick="window.print()">
                    <i class="fas fa-print"></i> Print
                </button>
                <button class="btn-print" onclick="saveAsPDF()">
                    <i class="fas fa-file-pdf"></i> Save as PDF
                </button>
            </div>
        </div>
        
        <div class="order-card" id="order-details-content">
            <div class="order-info-grid">
                <div class="order-section">
                    <h3><i class="fas fa-info-circle"></i> Order Information</h3>
                    <div class="info-row">
                        <span class="info-label">Order Number:</span>
                        <span><?= htmlspecialchars($order['order_number']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Date:</span>
                        <span><?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status:</span>
                        <span class="order-status <?= $status_classes[$order['status']] ?>">
                            <?= ucfirst($order['status']) ?>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Items:</span>
                        <span><?= $order['item_count'] ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Payment Method:</span>
                        <span><?= $payment_methods[$order['payment_method']] ?? 'Unknown Method' ?></span>
                    </div>
                    <?php if (!empty($order['payment_details'])): ?>
                    <div class="info-row">
                        <span class="info-label">Payment Details:</span>
                        <span><?= htmlspecialchars($order['payment_details']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="order-section">
                    <h3><i class="fas fa-truck"></i> Shipping Information</h3>
                    <div class="info-row">
                        <span class="info-label">Name:</span>
                        <span><?= htmlspecialchars($order['name']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span><?= htmlspecialchars($order['email']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone:</span>
                        <span><?= htmlspecialchars($order['phone']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Address:</span>
                        <span><?= nl2br(htmlspecialchars($order['address'])) ?></span>
                    </div>
                </div>
            </div>
            
            <div class="order-section">
                <h3><i class="fas fa-shopping-basket"></i> Order Items</h3>
                <div class="order-items">
                    <?php if (!empty($order_items)): ?>
                        <?php foreach ($order_items as $item): ?>
                            <div class="order-item">
                                <?php if (!empty($item['image_path'])): ?>
                                    <img src="<?= htmlspecialchars($item['image_path']) ?>" 
                                         alt="<?= htmlspecialchars($item['name']) ?>" 
                                         class="order-item-image">
                                <?php else: ?>
                                    <div class="order-item-image" style="background: #f5f5f5; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-pills" style="font-size: 24px; color: #6c757d;"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="order-item-details">
                                    <div class="order-item-name">
                                        <?= htmlspecialchars($item['name']) ?>
                                        <?php if (!empty($item['product_id'])): ?>
                                            <a href="../frontend/product_details.php?id=<?= $item['product_id'] ?>" 
                                               class="product-link" target="_blank">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="order-item-meta">
                                        <span class="quantity"><?= $item['quantity'] ?> ×</span>
                                        <span class="price"><?= format_currency($item['price']) ?></span>
                                    </div>
                                </div>
                                <div class="order-item-total">
                                    <?= format_currency($item['total']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-items" style="text-align: center; padding: 20px; color: #6c757d;">
                            <i class="fas fa-exclamation-circle"></i> No items found in this order
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="order-totals">
                    <div class="order-total-row">
                        <span>Subtotal:</span>
                        <span><?= format_currency($order['total_amount'] - $order['shipping_fee']) ?></span>
                    </div>
                    <div class="order-total-row">
                        <span>Shipping Fee:</span>
                        <span><?= format_currency($order['shipping_fee']) ?></span>
                    </div>
                    <div class="order-total-row grand-total">
                        <span>Total:</span>
                        <span><?= format_currency($order['total_amount']) ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Status Timeline -->
            <div class="status-timeline">
                <h3><i class="fas fa-history"></i> Order Status Timeline</h3>
                
                <div class="timeline-item">
                    <div class="timeline-icon active">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="timeline-content">
                        <strong>Order Placed</strong>
                        <div class="timeline-date"><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></div>
                        <p>Your order has been received and is awaiting processing</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-icon <?= in_array($order['status'], ['processing', 'shipped', 'delivered', 'completed']) ? 'active' : ($order['status'] == 'pending' ? 'pending' : 'inactive') ?>">
                        <?php if (in_array($order['status'], ['processing', 'shipped', 'delivered', 'completed'])): ?>
                            <i class="fas fa-check"></i>
                        <?php elseif ($order['status'] == 'pending'): ?>
                            <i class="fas fa-clock"></i>
                        <?php else: ?>
                            <i class="fas fa-circle"></i>
                        <?php endif; ?>
                    </div>
                    <div class="timeline-content">
                        <strong>Processing</strong>
                        <?php if (in_array($order['status'], ['processing', 'shipped', 'delivered', 'completed'])): ?>
                            <div class="timeline-date"><?= date('M j, Y g:i A', strtotime($order['created_at'] . ' +1 day')) ?></div>
                            <p>We are preparing your order for shipment</p>
                        <?php elseif ($order['status'] == 'pending'): ?>
                            <p>Your order is awaiting processing</p>
                        <?php else: ?>
                            <p>Order processing has not started yet</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-icon <?= in_array($order['status'], ['shipped', 'delivered', 'completed']) ? 'active' : (in_array($order['status'], ['processing']) ? 'pending' : 'inactive') ?>">
                        <?php if (in_array($order['status'], ['shipped', 'delivered', 'completed'])): ?>
                            <i class="fas fa-check"></i>
                        <?php elseif (in_array($order['status'], ['processing'])): ?>
                            <i class="fas fa-clock"></i>
                        <?php else: ?>
                            <i class="fas fa-circle"></i>
                        <?php endif; ?>
                    </div>
                    <div class="timeline-content">
                        <strong>Shipped</strong>
                        <?php if (in_array($order['status'], ['shipped', 'delivered', 'completed'])): ?>
                            <div class="timeline-date"><?= date('M j, Y g:i A', strtotime($order['created_at'] . ' +2 days')) ?></div>
                            <p>Your order has been shipped and is on its way</p>
                        <?php elseif (in_array($order['status'], ['processing'])): ?>
                            <p>Your order is being prepared for shipment</p>
                        <?php else: ?>
                            <p>Order has not been shipped yet</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-icon <?= in_array($order['status'], ['delivered', 'completed']) ? 'active' : (in_array($order['status'], ['shipped']) ? 'pending' : 'inactive') ?>">
                        <?php if (in_array($order['status'], ['delivered', 'completed'])): ?>
                            <i class="fas fa-check"></i>
                        <?php elseif (in_array($order['status'], ['shipped'])): ?>
                            <i class="fas fa-clock"></i>
                        <?php else: ?>
                            <i class="fas fa-circle"></i>
                        <?php endif; ?>
                    </div>
                    <div class="timeline-content">
                        <strong>Delivered</strong>
                        <?php if (in_array($order['status'], ['delivered', 'completed'])): ?>
                            <div class="timeline-date"><?= date('M j, Y g:i A', strtotime($order['created_at'] . ' +4 days')) ?></div>
                            <p>Your order has been delivered successfully</p>
                        <?php elseif (in_array($order['status'], ['shipped'])): ?>
                            <p>Your order is on its way to you</p>
                        <?php else: ?>
                            <p>Order has not been delivered yet</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($order['status'] == 'completed'): ?>
                <div class="timeline-item">
                    <div class="timeline-icon active">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="timeline-content">
                        <strong>Completed</strong>
                        <div class="timeline-date"><?= date('M j, Y g:i A', strtotime($order['created_at'] . ' +7 days')) ?></div>
                        <p>Your order has been completed successfully</p>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($order['status'] == 'cancelled'): ?>
                <div class="timeline-item">
                    <div class="timeline-icon inactive" style="background: #f8d7da; color: #721c24;">
                        <i class="fas fa-times"></i>
                    </div>
                    <div class="timeline-content">
                        <strong>Cancelled</strong>
                        <div class="timeline-date"><?= date('M j, Y g:i A') ?></div>
                        <p>Your order has been cancelled</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <?php if ($order['status'] == 'pending' || $order['status'] == 'processing'): ?>
                <a href="cancel_order.php" class="btn-print" style="background: #dc3545;" onclick="confirmCancel(<?= $order['id'] ?>)">
                    <i class="fas fa-times"></i> Cancel Order
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    
    <script>
        // Initialize jsPDF
        const { jsPDF } = window.jspdf;
        
        function confirmCancel(orderId) {
            if (confirm('Are you sure you want to cancel this order?')) {
                window.location.href = 'cancel_order.php?id=' + orderId;
            }
            return false;
        }
        
        // Function to save as PDF
        function saveAsPDF() {
            const element = document.getElementById('order-details-content');
            
            html2canvas(element, {
                scale: 2,
                logging: true,
                useCORS: true
            }).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const pdf = new jsPDF('p', 'mm', 'a4');
                const imgWidth = 210; // A4 width in mm
                const pageHeight = 295; // A4 height in mm
                const imgHeight = canvas.height * imgWidth / canvas.width;
                let heightLeft = imgHeight;
                let position = 0;

                pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;

                while (heightLeft >= 0) {
                    position = heightLeft - imgHeight;
                    pdf.addPage();
                    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                }

                pdf.save('order_<?= $order['order_number'] ?>.pdf');
            });
        }
    </script>
</body>
</html>