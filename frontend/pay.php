<?php
session_start();
require '../includes/config.php';

// Check if checkout data exists
if (!isset($_SESSION['checkout_form_data']) || !isset($_SESSION['order_id'])) {
    $_SESSION['payment_error'] = "Invalid payment request. Please try again.";
    header("Location: checkout.php");
    exit();
}

$checkout_data = $_SESSION['checkout_form_data'];

// Verify it's a Khalti payment
if ($checkout_data['payment_method'] !== 'khalti') {
    $_SESSION['payment_error'] = "Invalid payment method selected";
    header("Location: checkout.php");
    exit();
}

// Prepare Khalti payload
$payload = [
    "return_url" => "http://localhost/ecommerce/ECOMMERCE/frontend/payment_response.php", // UPDATE THIS
    "website_url" => "https://dev.khalti.com/api/v2/", // UPDATE THIS
    "amount" => (int)round($checkout_data['total'] * 100), // Amount in paisa
    "purchase_order_id" => $checkout_data['order_number'],
    "purchase_order_name" => "Order #" . $checkout_data['order_number'],
    "customer_info" => [
        "name" => $checkout_data['name'],
        "email" => $checkout_data['email'],
        "phone" => $checkout_data['phone']
    ]
];

// Initiate Khalti payment
$headers = [
    'Authorization: Key 490b53d6897d4e64b6a7aabdc30f1923', // REPLACE WITH YOUR ACTUAL KHALTI SECRET KEY
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
    error_log("Khalti API Error: " . $error);
    $_SESSION['payment_error'] = "Payment gateway connection failed";
    header("Location: checkout.php");
    exit();
}

$result = json_decode($response, true);

if (empty($result['payment_url'])) {
    error_log("Khalti Error Response: " . print_r($result, true));
    $_SESSION['payment_error'] = $result['detail'] ?? "Payment initiation failed";
    header("Location: checkout.php");
    exit();
}

// Store verification data in session
$_SESSION['khalti_verify'] = [
    'pidx' => $result['pidx'],
    'order_id' => $_SESSION['order_id'],
    'amount' => $checkout_data['total'],
    'order_number' => $checkout_data['order_number']
];

// Finally, redirect to Khalti payment page
header("Location: " . $result['payment_url']);
exit();
?>