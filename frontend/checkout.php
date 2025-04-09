<?php
session_start();
require '../includes/config.php';

// Redirect if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

// Get cart items with details
$cart_items = [];
$subtotal = 0;

foreach ($_SESSION['cart'] as $product_id => $quantity) {
    $stmt = $conn->prepare("SELECT id, product_name, price, image_path FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $product = $result->fetch_assoc();
        $item_total = $product['price'] * $quantity;
        $subtotal += $item_total;
        
        $cart_items[] = [
            'id' => $product['id'],
            'name' => $product['product_name'],
            'price' => $product['price'],
            'quantity' => $quantity,
            'total' => $item_total,
            'image_path' => $product['image_path']
        ];
    }
}

// Calculate shipping and total
$shipping_fee = ($subtotal > 1000) ? 0 : 50;
$total = $subtotal + $shipping_fee;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $payment_method = $_POST['payment_method'];
    
    $errors = [];
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) $errors[] = "Valid 10-digit phone number is required";
    if (empty($address)) $errors[] = "Address is required";
    if (!in_array($payment_method, ['cod', 'khalti', 'esewa', 'card'])) {
        $errors[] = "Invalid payment method";
    }
    
    if (empty($errors)) {
        // Save order to database
        $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
        $status = 'pending';
        $payment_details = '';
        
        $stmt = $conn->prepare("INSERT INTO orders (order_number, name, email, phone, address, total_amount, shipping_fee, payment_method, payment_details, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssdssss", $order_number, $name, $email, $phone, $address, $total, $shipping_fee, $payment_method, $payment_details, $status);
        
        if ($stmt->execute()) {
            $order_id = $stmt->insert_id;
            
            // Save order items
            foreach ($cart_items as $item) {
                $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, name, price, quantity, total) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iisdid", $order_id, $item['id'], $item['name'], $item['price'], $item['quantity'], $item['total']);
                $stmt->execute();
            }
            
            // Store all necessary data in session for payment processing
            $_SESSION['order_id'] = $order_id;
            $_SESSION['checkout_form_data'] = [
                'order_number' => $order_number,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'total' => $total,
                'shipping_fee' => $shipping_fee,
                'payment_method' => $payment_method,
                'cart_items' => $cart_items
            ];
            
            // Handle different payment methods
            switch ($payment_method) {
                case 'khalti':
                    header("Location: pay.php");
                    exit();
                    
                case 'esewa':
                    header("Location: esewa_payment.php?order_id=" . $order_id);
                    exit();
                    
                case 'card':
                    // Implement card payment redirection here
                    // header("Location: card_payment.php");
                    // exit();
                    // For now, just mark as paid
                    unset($_SESSION['cart']);
                    header("Location: order_success.php?order_id=" . $order_id);
                    exit();
                    
                case 'cod':
                default:
                    // For COD, just show success
                    unset($_SESSION['cart']);
                    header("Location: order_success.php?order_id=" . $order_id);
                    exit();
            }
        } else {
            $errors[] = "Failed to create order. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | PharmaCare</title>
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
            --border-color: #e0e0e0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            line-height: 1.6;
        }
        
        .checkout-container {
            max-width: 1200px;
            margin: 280px auto 50px;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 30px;
        }
        
        .checkout-form {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--dark-color);
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-group input, 
        .form-group textarea, 
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 15px;
        }
        
        .form-group input:focus, 
        .form-group textarea:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        .grid-2-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .payment-method {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .payment-method:hover {
            border-color: var(--primary-color);
        }
        
        .payment-method.selected {
            border-color: var(--primary-color);
            background-color: rgba(52, 152, 219, 0.05);
        }
        
        .payment-method input {
            margin-right: 15px;
        }
        
        .payment-method i {
            margin-right: 10px;
            font-size: 20px;
            color: var(--primary-color);
        }
        
        .order-summary {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 100px;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .order-total {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .order-total.grand-total {
            font-weight: 600;
            font-size: 18px;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid var(--border-color);
        }
        
        .btn-place-order {
            width: 100%;
            padding: 15px;
            background: var(--success-color);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-place-order:hover {
            background: #219653;
            transform: translateY(-2px);
        }
        
        .btn-place-order i {
            margin-right: 10px;
        }
        
        .alert.error {
            background-color: #fdecea;
            color: #d32f2f;
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
                margin-top: 80px;
            }
            
            .order-summary {
                position: static;
            }
            
            .grid-2-col {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="checkout-container">
        <form method="post" class="checkout-form">
            <h1>Checkout</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="alert error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <h2 class="section-title">Shipping Information</h2>
            
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
            </div>
            
            <div class="grid-2-col">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="phone">Phone (10 digits)</label>
                    <input type="tel" id="phone" name="phone" pattern="[0-9]{10}" title="Please enter a 10-digit phone number" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" rows="4" required><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
            </div>
            
            <div class="payment-methods">
                <h2 class="section-title">Payment Method</h2>
                
                <label class="payment-method">
                    <input type="radio" name="payment_method" value="cod" checked>
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Cash on Delivery</span>
                </label>
                
                <label class="payment-method">
                    <input type="radio" name="payment_method" value="khalti">
                    <i class="fas fa-mobile-alt"></i>
                    <span>Khalti</span>
                </label>
                
                <label class="payment-method">
                    <input type="radio" name="payment_method" value="esewa">
                    <i class="fas fa-wallet"></i>
                    <span>eSewa</span>
                </label>
                
                <label class="payment-method">
                    <input type="radio" name="payment_method" value="card">
                    <i class="far fa-credit-card"></i>
                    <span>Credit/Debit Card</span>
                </label>
            </div>
            
            <button type="submit" class="btn-place-order">
                <i class="fas fa-shopping-bag"></i> Place Order
            </button>
        </form>
        
        <div class="order-summary">
            <h2>Order Summary</h2>
            <div class="order-items">
                <?php foreach ($cart_items as $item): ?>
                    <div class="order-item">
                        <span><?php echo htmlspecialchars($item['name']); ?> × <?php echo $item['quantity']; ?></span>
                        <span>₹<?php echo number_format($item['total'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="order-totals">
                <div class="order-total">
                    <span>Subtotal</span>
                    <span>₹<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="order-total">
                    <span>Shipping</span>
                    <span><?php echo $shipping_fee > 0 ? '₹' . number_format($shipping_fee, 2) : 'FREE'; ?></span>
                </div>
                <div class="order-total grand-total">
                    <span>Total</span>
                    <span>₹<?php echo number_format($total, 2); ?></span>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    
    <script>
        // Highlight selected payment method
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.payment-method').forEach(method => {
                    method.classList.remove('selected');
                });
                this.closest('.payment-method').classList.add('selected');
            });
            
            // Initialize selected state
            if (radio.checked) {
                radio.closest('.payment-method').classList.add('selected');
            }
        });

        // Client-side phone number validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const phoneInput = document.getElementById('phone');
            const phoneRegex = /^[0-9]{10}$/;
            
            if (!phoneRegex.test(phoneInput.value)) {
                alert('Please enter a valid 10-digit phone number');
                phoneInput.focus();
                e.preventDefault();
            }
        });
    </script>
</body>
</html>