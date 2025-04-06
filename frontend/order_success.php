<?php
session_start();
require '../includes/config.php';

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    header("Location: index.php");
    exit();
}

$order_id = intval($_GET['order_id']);

// Fetch order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: index.php");
    exit();
}

// Fetch order items
$stmt = $conn->prepare("SELECT oi.*, p.image_path FROM order_items oi 
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
    <title>Order Confirmation | PharmaCare</title>
    <link rel="stylesheet" href="../assets/css/pharmacy.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Include required libraries for printing and PDF generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --accent-color: #e74c3c;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --success-color: #27ae60;
            --border-color: #e0e0e0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            line-height: 1.6;
        }
        
        .order-success-container {
            max-width: 800px;
            margin: 300px auto 50px;
            padding: 0 20px;
        }
        
        .order-success-card {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            text-align: center;
        }
        
        .success-icon {
            font-size: 80px;
            color: var(--success-color);
            margin-bottom: 20px;
        }
        
        .order-success-title {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--dark-color);
        }
        
        .order-success-message {
            font-size: 16px;
            color: #555;
            margin-bottom: 30px;
        }
        
        .order-details-summary {
            background: #f9f9f9;
            border-radius: 8px;
            padding: 25px;
            margin: 30px 0;
            text-align: left;
        }
        
        .order-detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        
        .order-detail-label {
            font-weight: 500;
            color: #555;
        }
        
        .order-detail-value {
            font-weight: 600;
        }
        
        .order-number {
            font-size: 20px;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .btn-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 25px;
            border-radius: 6px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
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
        
        .btn-success {
            background: var(--success-color);
            color: white;
        }
        
        .btn-success:hover {
            background: #219653;
            transform: translateY(-2px);
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        /* Print-specific styles */
        @media print {
            body * {
                visibility: hidden;
            }
            #receipt-content, #receipt-content * {
                visibility: visible;
            }
            #receipt-content {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                padding: 20px;
                box-shadow: none;
            }
            .btn-container {
                display: none !important;
            }
            .success-icon {
                font-size: 60px;
            }
        }
        
        /* Additional styles for the receipt content */
        .receipt-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 20px;
        }
        
        .receipt-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .receipt-subtitle {
            font-size: 16px;
            color: #666;
        }
        
        .receipt-details {
            margin: 20px 0;
        }
        
        .receipt-items {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .receipt-items th {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .receipt-items td {
            padding: 10px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .receipt-items .text-right {
            text-align: right;
        }
        
        .receipt-totals {
            margin-top: 20px;
            border-top: 2px solid var(--border-color);
            padding-top: 20px;
        }
        
        .receipt-total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .receipt-footer {
            margin-top: 30px;
            text-align: center;
            font-size: 14px;
            color: #666;
            border-top: 1px solid var(--border-color);
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="order-success-container">
        <div class="order-success-card" id="receipt-content">
            <div class="receipt-header">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1 class="receipt-title">Order Confirmed</h1>
                <div class="receipt-subtitle">Thank you for your purchase</div>
                <div class="order-number">Order #<?php echo htmlspecialchars($order['order_number']); ?></div>
            </div>
            
            <div class="receipt-details">
                <div class="order-detail-row">
                    <span class="order-detail-label">Date:</span>
                    <span class="order-detail-value"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></span>
                </div>
                <div class="order-detail-row">
                    <span class="order-detail-label">Status:</span>
                    <span class="order-detail-value" style="color: <?php 
                        echo $order['status'] === 'completed' ? 'var(--success-color)' : 
                             ($order['status'] === 'cancelled' ? 'var(--accent-color)' : 'var(--primary-color)'); 
                    ?>">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </div>
            </div>
            
            <h3 class="whats-next-title">Order Items</h3>
            <table class="receipt-items">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="text-right">Price</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td class="text-right">₹<?php echo number_format($item['price'], 2); ?></td>
                            <td class="text-right"><?php echo $item['quantity']; ?></td>
                            <td class="text-right">₹<?php echo number_format($item['total'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="receipt-totals">
                <div class="receipt-total-row">
                    <span>Subtotal:</span>
                    <span>₹<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="receipt-total-row">
                    <span>Shipping:</span>
                    <span><?php echo $order['shipping_fee'] > 0 ? '₹' . number_format($order['shipping_fee'], 2) : 'FREE'; ?></span>
                </div>
                <div class="receipt-total-row" style="font-weight: bold; font-size: 1.1em;">
                    <span>Total:</span>
                    <span>₹<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>
            
            <div class="receipt-details">
                <h3>Payment Method</h3>
                <p>
                    <?php 
                    $payment_methods = [
                        'cod' => 'Cash on Delivery',
                        'khalti' => 'Khalti',
                        'esewa' => 'eSewa',
                        'card' => 'Credit/Debit Card'
                    ];
                    echo $payment_methods[$order['payment_method']] ?? $order['payment_method'];
                    ?>
                </p>
                
                <h3>Customer Information</h3>
                <p><strong><?php echo htmlspecialchars($order['name']); ?></strong></p>
                <p><?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
                <p>Email: <?php echo htmlspecialchars($order['email']); ?></p>
                <p>Phone: <?php echo htmlspecialchars($order['phone']); ?></p>
            </div>
            
            <div class="receipt-footer">
                <p>Thank you for shopping with PharmaCare</p>
                <p>For any questions, please contact support@pharmacare.com</p>
            </div>
            
            <div class="btn-container">
                <a href="order.php?order_id=<?php echo $order_id; ?>" class="btn btn-primary">
                    <i class="fas fa-receipt"></i> View Order Details
                </a>
                <a href="../index.php" class="btn btn-outline">
                    <i class="fas fa-home"></i> Back to Home
                </a>
                <button onclick="printReceipt()" class="btn btn-success">
                    <i class="fas fa-print"></i> Print Receipt
                </button>
                <button onclick="generatePDF()" class="btn btn-success">
                    <i class="fas fa-file-pdf"></i> Save as PDF
                </button>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    
    <script>
        // Print the receipt
        function printReceipt() {
            window.print();
        }
        
        // Generate PDF from the receipt content
        function generatePDF() {
            // Temporarily hide buttons before capturing
            const buttons = document.querySelectorAll('.btn-container button, .btn-container a');
            buttons.forEach(btn => btn.style.visibility = 'hidden');
            
            // Use html2canvas to capture the receipt
            html2canvas(document.getElementById('receipt-content'), {
                scale: 2, // Higher quality
                logging: false,
                useCORS: true,
                scrollY: -window.scrollY
            }).then(canvas => {
                // Restore button visibility
                buttons.forEach(btn => btn.style.visibility = 'visible');
                
                // Initialize jsPDF
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF('p', 'mm', 'a4');
                const imgData = canvas.toDataURL('image/png');
                
                // Calculate PDF dimensions
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = pdf.internal.pageSize.getHeight();
                const imgWidth = canvas.width;
                const imgHeight = canvas.height;
                const ratio = Math.min(pdfWidth / imgWidth, pdfHeight / imgHeight);
                const imgX = (pdfWidth - imgWidth * ratio) / 2;
                const imgY = 10;
                
                // Add image to PDF
                pdf.addImage(imgData, 'PNG', imgX, imgY, imgWidth * ratio, imgHeight * ratio);
                
                // Save the PDF
                pdf.save('receipt_<?php echo $order['order_number']; ?>.pdf');
            });
        }
        
        // Initialize print functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Check if this is a print view
            if (window.location.search.includes('print=1')) {
                window.print();
                setTimeout(() => {
                    window.history.back();
                }, 500);
            }
        });
    </script>
</body>
</html>