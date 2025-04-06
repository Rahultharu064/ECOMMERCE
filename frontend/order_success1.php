<?php
session_start();
require '../includes/config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug session state
error_log("Session data: " . print_r($_SESSION, true));

// Redirect if no order ID in session
if (!isset($_SESSION['order_id'])) {
    error_log("No order_id in session - redirecting to order.php");
    header("Location: ../frontend/order.php");
    exit();
}

$order_id = $_SESSION['order_id'];
error_log("Processing order ID: " . $order_id);

// Fetch order details
$order = [];
try {
    $stmt = $conn->prepare("SELECT 
        o.id, o.order_number, o.total_amount, o.shipping_fee, 
        o.payment_method, o.payment_details, o.status, o.created_at,
        o.name, o.email, o.phone, o.address,
        COUNT(oi.id) as item_count
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.id = ?
        GROUP BY o.id");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $order_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        error_log("Order data retrieved: " . print_r($order, true));
        
        // Generate order number if missing (fallback)
        if (empty($order['order_number'])) {
            $order['order_number'] = 'ORD-' . date('Ymd') . '-' . str_pad($order_id, 4, '0', STR_PAD_LEFT);
            error_log("Generated fallback order number: " . $order['order_number']);
        }
    } else {
        throw new Exception("No order found with ID: " . $order_id);
    }
} catch (Exception $e) {
    error_log("Order fetch error: " . $e->getMessage());
    die("Error retrieving order details. Please contact support.");
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
    error_log("Found " . count($order_items) . " order items");
    
} catch (Exception $e) {
    error_log("Order items fetch error: " . $e->getMessage());
    $order_items = []; // Ensure empty array on error
}

// Clear session after successful data retrieval
unset($_SESSION['order_id']);
error_log("Session order_id cleared");

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
    <title>Order Confirmation | PharmaCare</title>
    <link rel="stylesheet" href="../assets/css/pharmacy.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- jsPDF library for PDF generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        .order-success-container {
            max-width: 800px;
            margin: 260px auto;
            padding: 2rem;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }

        .success-header {
            text-align: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            position: relative;
        }

        .print-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 10px;
        }

        .print-btn {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px 10px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }

        .print-btn:hover {
            background: #f0f0f0;
        }

        .success-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 1rem;
        }

        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .order-section {
            margin-bottom: 1.5rem;
        }

        .order-section h3 {
            border-bottom: 2px solid #eee;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }

        .order-info {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .order-info .label {
            font-weight: 600;
            color: #444;
        }

        .order-items {
            margin-top: 1.5rem;
        }

        .order-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }

        .order-item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 1rem;
        }

        .order-totals {
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 2px solid #eee;
        }

        .order-total {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .grand-total {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
        }

        .debug-info {
            background: #f8f9fa;
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 8px;
            font-family: monospace;
            display: none; /* Set to block to view debug info */
        }

        /* Print-specific styles */
        @media print {
            body * {
                visibility: hidden;
            }
            .order-success-container, .order-success-container * {
                visibility: visible;
            }
            .order-success-container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                padding: 20px;
                box-shadow: none;
            }
            .print-actions, .action-buttons {
                display: none !important;
            }
            .success-header {
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

    <div class="debug-info">
        <h4>Debug Information</h4>
        <p>Order ID: <?= htmlspecialchars($order_id) ?></p>
        <pre>Order Data: <?= print_r($order, true) ?></pre>
        <pre>Order Items: <?= print_r($order_items, true) ?></pre>
    </div>

    <div class="order-success-container" id="order-confirmation">
        <div class="success-header">
            <div class="print-actions">
                <button class="print-btn" onclick="window.print()">
                    <i class="fas fa-print"></i> Print
                </button>
                <button class="print-btn" onclick="saveAsPDF()">
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
                <button class="print-btn" onclick="saveAsImage()">
                    <i class="fas fa-image"></i> Image
                </button>
            </div>
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Order Confirmed!</h1>
            <p>Your order <strong><?= htmlspecialchars($order['order_number']) ?></strong> has been received.</p>
        </div>

        <div class="order-details">
            <div class="order-section">
                <h3>Order Information</h3>
                <div class="order-info">
                    <span class="label">Order Number:</span>
                    <span><?= htmlspecialchars($order['order_number'] ?? 'N/A') ?></span>
                </div>
                <div class="order-info">
                    <span class="label">Date:</span>
                    <span><?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?></span>
                </div>
                <div class="order-info">
                    <span class="label">Status:</span>
                    <span class="status"><?= ucfirst($order['status'] ?? 'pending') ?></span>
                </div>
                <div class="order-info">
                    <span class="label">Items:</span>
                    <span><?= $order['item_count'] ?? 0 ?></span>
                </div>
                <div class="order-info">
                    <span class="label">Payment Method:</span>
                    <span>
                        <?php
                        $payment_methods = [
                            'cod' => 'Cash on Delivery',
                            'esewa' => 'eSewa',
                            'khalti' => 'Khalti',
                            'card' => 'Credit/Debit Card',
                            'phonepay' => 'PhonePe'
                        ];
                        echo $payment_methods[$order['payment_method']] ?? 'Unknown Method';
                        ?>
                    </span>
                </div>
                <?php if (!empty($order['payment_details'])): ?>
                <div class="order-info">
                    <span class="label">Payment Details:</span>
                    <span><?= htmlspecialchars($order['payment_details']) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="order-section">
                <h3>Shipping Information</h3>
                <div class="order-info">
                    <span class="label">Name:</span>
                    <span><?= htmlspecialchars($order['name'] ?? 'N/A') ?></span>
                </div>
                <div class="order-info">
                    <span class="label">Email:</span>
                    <span><?= htmlspecialchars($order['email'] ?? 'N/A') ?></span>
                </div>
                <div class="order-info">
                    <span class="label">Phone:</span>
                    <span><?= htmlspecialchars($order['phone'] ?? 'N/A') ?></span>
                </div>
                <div class="order-info">
                    <span class="label">Address:</span>
                    <span><?= nl2br(htmlspecialchars($order['address'] ?? 'N/A')) ?></span>
                </div>
            </div>
        </div>

        <div class="order-section">
            <h3>Order Items</h3>
            <div class="order-items">
                <?php if (!empty($order_items)): ?>
                    <?php foreach ($order_items as $item): ?>
                        <div class="order-item">
                            <?php if (!empty($item['image_path'])): ?>
                                <img src="<?= htmlspecialchars($item['image_path']) ?>" 
                                     alt="<?= htmlspecialchars($item['name']) ?>" 
                                     class="order-item-image">
                            <?php else: ?>
                                <div class="order-item-image" style="background: #f5f5f5;">
                                    <i class="fas fa-pills"></i>
                                </div>
                            <?php endif; ?>
                            <div class="order-item-details">
                                <div class="order-item-name">
                                    <?= htmlspecialchars($item['name']) ?>
                                    <?php if (!empty($item['product_id'])): ?>
                                        <a href="../frontend/product_details.php?id=<?= $item['product_id'] ?>" 
                                           class="product-link">
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
                    <div class="no-items">
                        <i class="fas fa-exclamation-circle"></i>
                        No items found in this order
                    </div>
                <?php endif; ?>
            </div>

            <div class="order-totals">
                <div class="order-total">
                    <span>Subtotal:</span>
                    <span><?= format_currency($order['total_amount'] - $order['shipping_fee']) ?></span>
                </div>
                <div class="order-total">
                    <span>Shipping:</span>
                    <span><?= format_currency($order['shipping_fee']) ?></span>
                </div>
                <div class="order-total grand-total">
                    <span>Total:</span>
                    <span><?= format_currency($order['total_amount']) ?></span>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <a href="../frontend/order.php" class="btn btn-primary">
                <i class="fas fa-clipboard-list"></i> View Orders
            </a>
            <a href="../products.php" class="btn btn-secondary">
                <i class="fas fa-shopping-bag"></i> Continue Shopping
            </a>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Initialize jsPDF
        const { jsPDF } = window.jspdf;

        // Function to save as PDF
        function saveAsPDF() {
            const element = document.getElementById('order-confirmation');
            
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

        // Function to save as image
        function saveAsImage() {
            const element = document.getElementById('order-confirmation');
            
            html2canvas(element, {
                scale: 2,
                logging: true,
                useCORS: true
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = 'order_<?= $order['order_number'] ?>.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            });
        }

        // Add print styles dynamically
        function beforePrint() {
            document.body.style.zoom = "80%";
        }

        function afterPrint() {
            document.body.style.zoom = "100%";
        }

        // Add event listeners for print
        if (window.matchMedia) {
            const mediaQueryList = window.matchMedia('print');
            mediaQueryList.addListener(mql => {
                if (mql.matches) {
                    beforePrint();
                } else {
                    afterPrint();
                }
            });
        }

        window.onbeforeprint = beforePrint;
        window.onafterprint = afterPrint;
    </script>
</body>
</html>