<?php
session_start();
require '../includes/config.php'; // Ensure this file connects to your database

// Check if the 'data' parameter is present
if (!isset($_GET['data'])) {
    die("Invalid request");
}

// Decode the base64-encoded data
$data = base64_decode($_GET['data']);
if ($data === false) {
    die("Invalid data format");
}

// Parse the JSON data
$payment_data = json_decode($data, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("Invalid JSON data");
}

// Extract necessary fields from the JSON
$transaction_code = $payment_data['transaction_code'] ?? null;
$status = $payment_data['status'] ?? null;
$total_amount = $payment_data['total_amount'] ?? null;
$transaction_uuid = $payment_data['transaction_uuid'] ?? null;
$signature = $payment_data['signature'] ?? null;

// Validate the presence of required fields
if (!$transaction_uuid || !$total_amount || !$status || !$signature) {
    die("Missing required payment data");
}

// Placeholder for signature verification
// In production, verify the signature using your payment gateway's secret key
// Example: if (!verifySignature($payment_data, $signature, $secret_key)) { die("Invalid signature"); }

// Fetch the order from the database using transaction_uuid (order_number)
$stmt = $conn->prepare("SELECT id, total_amount, status FROM orders WHERE order_number = ?");
$stmt->bind_param("s", $transaction_uuid);
$stmt->execute();
$result = $stmt->get_result();

// Check if the order exists
if ($result->num_rows === 0) {
    die("Order not found");
}

$order = $result->fetch_assoc();

// Verify the order is still pending
if ($order['status'] !== 'pending') {
    die("Order is not pending");
}

// Verify the amount matches the order total
if ($total_amount != $order['total_amount']) {
    die("Amount mismatch");
}

// Update the order status to 'paid'
$new_status = 'paid';
$payment_details = 'eSewa transaction_code: ' . $transaction_code;

$stmt = $conn->prepare("UPDATE orders SET status = ?, payment_details = ? WHERE id = ?");
$stmt->bind_param("ssi", $new_status, $payment_details, $order['id']);
$stmt->execute();

// Clear session data related to the cart and checkout
unset($_SESSION['cart']);
unset($_SESSION['checkout_form_data']);
unset($_SESSION['order_id']);

// Redirect to the order success page with the order ID
header("Location: order_success.php?order_id=" . $order['id']);
exit();
?>