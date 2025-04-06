<?php
session_start();
require '../includes/config.php';

// Debugging
error_log("Payment response received. GET: " . print_r($_GET, true));
error_log("Session data: " . print_r($_SESSION, true));

// Validate incoming data
if (empty($_GET['pidx']) || empty($_SESSION['khalti_verify'])) {
    error_log("Missing pidx or session verification data");
    $_SESSION['payment_error'] = "Invalid payment verification";
    header("Location: ../checkout.php");
    exit();
}

// Verify payment with Khalti
$pidx = $_GET['pidx'];
$verify_data = $_SESSION['khalti_verify'];
$order_id = $verify_data['order_id'];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://a.khalti.com/api/v2/epayment/lookup/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(['pidx' => $pidx]),
    CURLOPT_HTTPHEADER => [
        'Authorization: Key 490b53d6897d4e64b6a7aabdc30f1923',
        'Content-Type: application/json',
    ],
]);

$response = curl_exec($ch);
$result = json_decode($response, true);

if ($result['status'] === 'Completed') {
    // Begin transaction
    $conn->begin_transaction();

    try {
        // Check if the table structure is correct
        $update_query = "
            UPDATE orders 
            SET payment_status = 'paid', 
                payment_method = 'khalti',
                status = 'completed',
                pidx = ?
            WHERE id = ?
        ";

        $stmt = $conn->prepare($update_query);

        // Debug if prepare() fails
        if ($stmt === false) {
            error_log("Prepare failed: " . $conn->error);
            throw new Exception("Database error: " . $conn->error);
        }

        $stmt->bind_param("si", $pidx, $order_id);
        $stmt->execute();

        // Check if the update was successful
        if ($stmt->affected_rows === 0) {
            throw new Exception("No rows updated. Order ID may not exist.");
        }

        $conn->commit();

        // Clear session data
        unset($_SESSION['khalti_verify']);
        unset($_SESSION['cart']); // Clear cart if exists

        // Redirect to success page
        $_SESSION['order_id'] = $order_id;
        header("Location: order_success.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Database error: " . $e->getMessage());
        $_SESSION['payment_error'] = "Failed to update order. Please contact support.";
        header("Location: checkout.php");
        exit();
    }
} else {
    $_SESSION['payment_error'] = "Payment verification failed: " . ($result['detail'] ?? 'Unknown error');
    header("Location: checkout.php");
    exit();
}