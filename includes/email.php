<?php
// This is included in the payment_response.php to generate email content
$order_number = isset($order_number) ? $order_number : 'N/A'; // Assign a default value if not set
$checkout_data = $_SESSION['checkout_form_data'];
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #3498db; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .order-details { margin: 20px 0; }
        .item { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .footer { margin-top: 20px; text-align: center; font-size: 12px; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Order Confirmation</h1>
        </div>
        
        <div class="content">
            <p>Dear <?php echo htmlspecialchars($checkout_data['name']); ?>,</p>
            <p>Thank you for your order! We've received it and it's now being processed.</p>
            
            <div class="order-details">
                <h3>Order #<?php echo $order_number; ?></h3>
                
                <h4>Items Ordered:</h4>
                <?php foreach ($checkout_data['cart_items'] as $item): ?>
                <div class="item">
                    <span><?php echo htmlspecialchars($item['name']); ?> × <?php echo $item['quantity']; ?></span>
                    <span>₹<?php echo number_format($item['total'], 2); ?></span>
                </div>
                <?php endforeach; ?>
                
                <div style="margin-top: 20px; border-top: 1px solid #ddd; padding-top: 10px;">
                    <div class="item">
                        <strong>Subtotal:</strong>
                        <span>₹<?php echo number_format($checkout_data['subtotal'], 2); ?></span>
                    </div>
                    <div class="item">
                        <strong>Shipping:</strong>
                        <span>₹<?php echo number_format($checkout_data['shipping_fee'], 2); ?></span>
                    </div>
                    <div class="item">
                        <strong>Total:</strong>
                        <span>₹<?php echo number_format($checkout_data['total'], 2); ?></span>
                    </div>
                </div>
            </div>
            
            <h4>Shipping Information:</h4>
            <p>
                <?php echo htmlspecialchars($checkout_data['name']); ?><br>
                <?php echo htmlspecialchars($checkout_data['address']); ?><br>
                Phone: <?php echo htmlspecialchars($checkout_data['phone']); ?><br>
                Email: <?php echo htmlspecialchars($checkout_data['email']); ?>
            </p>
            
            <p>We'll send you another email when your order ships. If you have any questions, please contact our support team.</p>
        </div>
        
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> PharmaCare. All rights reserved.</p>
        </div>
    </div>
</body>
</html>