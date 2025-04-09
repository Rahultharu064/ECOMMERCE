<?php
session_start();
require '../includes/config.php';

// Validate session and payment method
if (empty($_SESSION['checkout_form_data']) || 
    ($_SESSION['checkout_form_data']['payment_method'] ?? '') !== 'khalti') {
    $_SESSION['payment_error'] = "Invalid payment request";
    header("Location: checkout.php");
    exit();
}

// Extract checkout data
$checkout_data = $_SESSION['checkout_form_data'];
$total = (float)$checkout_data['total'];
$cart_items = $checkout_data['cart_items'];

// Create temporary order before payment
$conn->begin_transaction();
try {
    // Generate order details
    $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
    $user_id = $_SESSION['user_id'] ?? null;
    $payment_method = 'khalti';
    
    // Prepare order data
    $name = $checkout_data['name'];
    $email = $checkout_data['email'];
    $phone = $checkout_data['phone'];
    $address = $checkout_data['address'];
    $shipping_fee = (float)$checkout_data['shipping_fee'];

    // Insert into orders1
    $stmt = $conn->prepare("INSERT INTO orders1 
        (order_number, user_id, name, email, phone, address, 
        total_amount, shipping_fee, payment_method, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    
    $stmt->bind_param("sisssddss",
        $order_number,
        $user_id,
        $name,
        $email,
        $phone,
        $address,
        $total,
        $shipping_fee,
        $payment_method
    );
    $stmt->execute();
    $order_id = $stmt->insert_id;

    // Insert into order_items1
    foreach ($cart_items as $item) {
        $product_id = (int)$item['id'];
        $item_name = $item['name'];
        $price = (float)$item['price'];
        $quantity = (int)$item['quantity'];
        $item_total = (float)$item['total'];

        $stmt = $conn->prepare("INSERT INTO order_items1 
            (order_id, product_id, name, price, quantity, total) 
            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisdid",
            $order_id,
            $product_id,
            $item_name,
            $price,
            $quantity,
            $item_total
        );
        $stmt->execute();
    }

    // Insert into order_history
    $status = 'pending';
    $notes = "Order initialized via Khalti";
    $history_stmt = $conn->prepare("INSERT INTO order_history 
        (order_id, status, notes) 
        VALUES (?, ?, ?)");
    $history_stmt->bind_param("iss", $order_id, $status, $notes);
    $history_stmt->execute();

    $conn->commit();
    $_SESSION['order_id'] = $order_id;

} catch (Exception $e) {
    $conn->rollback();
    error_log("Database error: " . $e->getMessage());
    $_SESSION['payment_error'] = "Failed to initialize payment";
    header("Location: checkout.php");
    exit();
}

// Prepare Khalti payload
$payload = [
    "return_url" => "http://localhost/ecommerce/ECOMMERCE/frontend/payment_response.php",
    "website_url" => "https://dev.khalti.com/api/v2/",
    "amount" => (int)round($total * 100),
    "purchase_order_id" => $order_number,
    "purchase_order_name" => "Order #".$order_number,
    "customer_info" => [
        "name" => $name,
        "email" => $email,
        "phone" => $phone
    ]
];

// Initiate Khalti payment
$headers = [
    'Authorization: Key 490b53d6897d4e64b6a7aabdc30f1923',
    'Content-Type: application/json'
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://a.khalti.com/api/v2/epayment/initiate/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    error_log("Khalti API Error: $error");
    $_SESSION['payment_error'] = "Payment gateway connection failed";
    header("Location: checkout.php");
    exit();
}

$result = json_decode($response, true);

if (empty($result['payment_url'])) {
    error_log("Khalti Error: " . print_r($result, true));
    $_SESSION['payment_error'] = $result['detail'] ?? "Payment initiation failed";
    header("Location: checkout.php");
    exit();
}

// Store verification data
$_SESSION['khalti_verify'] = [
    'pidx' => $result['pidx'],
    'order_id' => $order_id,
    'amount' => $total,
    'order_number' => $order_number
];

// Redirect to Khalti
header("Location: " . $result['payment_url']);
exit();
?>