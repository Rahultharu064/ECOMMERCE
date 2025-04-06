<?php
session_start();
require '../includes/config.php';

// Enable debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Validate session and payment method
if (empty($_SESSION['checkout_form_data']) || 
    empty($_SESSION['order_id']) ||
    ($_SESSION['checkout_form_data']['payment_method'] ?? '') !== 'khalti') {
    
    error_log("Invalid session data in pay.php");
    $_SESSION['payment_error'] = "Invalid payment request. Please try again.";
    header("Location: ../checkout.php");
    exit();
}

// 2. Get complete order details with address
$order_id = $_SESSION['order_id'];
$stmt = $conn->prepare("
    SELECT o.order_number, o.total_amount, o.address, o.name, o.email, o.phone,
           GROUP_CONCAT(oi.name SEPARATOR ', ') as items
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    WHERE o.id = ?
    GROUP BY o.id
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    error_log("Order not found: $order_id");
    $_SESSION['payment_error'] = "Order not found. Please contact support.";
    header("Location: checkout.php");
    exit();
}

// 3. Prepare validated Khalti payload with address
$checkout_data = $_SESSION['checkout_form_data'];
$total_amount = (float)$order['total_amount'];

$payload = [
    "return_url" => "http://localhost/ecommerce/ECOMMERCE/frontend/payment_response.php",
    "website_url" => "https://dev.khalti.com/api/v2/",
    "amount" => (int)round($total_amount * 100), // Convert to paisa
    "purchase_order_id" => $order['order_number'],
    "purchase_order_name" => "Order #" . $order['order_number'] . " - " . substr($order['items'], 0, 50) . (strlen($order['items']) > 50 ? '...' : ''),
    "customer_info" => [
        "name" => filter_var($order['name'], FILTER_SANITIZE_STRING),
        "email" => filter_var($order['email'], FILTER_SANITIZE_EMAIL),
        "phone" => preg_replace('/[^0-9]/', '', $order['phone']),
        "address" => [
            "street" => substr(filter_var($order['address'], FILTER_SANITIZE_STRING), 0, 100),
            "city" => "Kathmandu", // Default or get from DB
            "state" => "Bagmati", // Default or get from DB
            "postal_code" => "44600" // Default or get from DB
        ]
    ]
];

// 4. Initiate Khalti payment
$headers = [
    'Authorization: Key 490b53d6897d4e64b6a7aabdc30f1923', // Test secret key
    'Content-Type: application/json',
    'Accept: application/json'
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://a.khalti.com/api/v2/epayment/initiate/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_SSL_VERIFYPEER => true
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// 5. Handle Khalti response
if ($error || $http_code !== 200) {
    error_log("Khalti API Error: " . $error . " | HTTP Code: " . $http_code);
    $_SESSION['payment_error'] = "Payment gateway connection failed (Error: $http_code)";
    header("Location: checkout.php");
    exit();
}

$result = json_decode($response, true);

if (empty($result['payment_url'])) {
    $error_detail = $result['detail'] ?? json_encode($result);
    error_log("Khalti Payment Error: " . $error_detail);
    $_SESSION['payment_error'] = "Payment initiation failed: " . 
        (isset($result['detail']) ? $result['detail'] : "Please try again later");
    header("Location: checkout.php");
    exit();
}

// 6. Store verification data
$_SESSION['khalti_verify'] = [
    'pidx' => $result['pidx'],
    'order_id' => $order_id,
    'amount' => $total_amount,
    'timestamp' => time()
];

// 7. Ensure clean redirect
header("Location: " . $result['payment_url']);
exit();
?>