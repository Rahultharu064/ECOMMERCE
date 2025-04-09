<?php
session_start();

// Check if session data exists
if (!isset($_SESSION['checkout_form_data'])) {
    header("Location: checkout.php");
    exit();
}

// Retrieve order data from session
$data = $_SESSION['checkout_form_data'];
$order_number = $data['order_number'];
$total = $data['total'];
$shipping_fee = $data['shipping_fee'];

// Calculate subtotal
$subtotal = $total - $shipping_fee;

// Define eSewa form parameters
$product_code = "EPAYTEST"; // Test merchant code
$transaction_uuid = $order_number; // Unique transaction ID
$amount = $subtotal; // Base amount before charges
$tax_amount = 0; // No tax in current setup
$product_service_charge = 0; // No service charge
$product_delivery_charge = $shipping_fee; // Shipping fee
$total_amount = $amount + $tax_amount + $product_service_charge + $product_delivery_charge; // Must match $total
$success_url = "http://localhost/ecommerce/ECOMMERCE/frontend/payment_success.php"; // Replace with actual success URL
$failure_url = "http://localhost/ecommerce/ECOMMERCE/frontend/payment_failure.php"; // Replace with actual failure URL
$signed_field_names = "total_amount,transaction_uuid,product_code"; // Fields for signature
$secret_key = "8gBm/:&EnhH.1/q"; // Test secret key; replace with actual key in production

// Generate signature
$data_string = "total_amount=$total_amount,transaction_uuid=$transaction_uuid,product_code=$product_code";
$signature = base64_encode(hash_hmac('sha256', $data_string, $secret_key, true));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting to eSewa...</title>
</head>
<body>
    <!-- eSewa payment form -->
    <form id="esewaForm" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" method="POST">
        <input type="hidden" name="amount" value="<?php echo $amount; ?>">
        <input type="hidden" name="tax_amount" value="<?php echo $tax_amount; ?>">
        <input type="hidden" name="total_amount" value="<?php echo $total_amount; ?>">
        <input type="hidden" name="transaction_uuid" value="<?php echo $transaction_uuid; ?>">
        <input type="hidden" name="product_code" value="<?php echo $product_code; ?>">
        <input type="hidden" name="product_service_charge" value="<?php echo $product_service_charge; ?>">
        <input type="hidden" name="product_delivery_charge" value="<?php echo $product_delivery_charge; ?>">
        <input type="hidden" name="success_url" value="<?php echo $success_url; ?>">
        <input type="hidden" name="failure_url" value="<?php echo $failure_url; ?>">
        <input type="hidden" name="signed_field_names" value="<?php echo $signed_field_names; ?>">
        <input type="hidden" name="signature" value="<?php echo $signature; ?>">
    </form>

    <!-- Auto-submit the form -->
    <script>
        document.getElementById('esewaForm').submit();
    </script>
</body>
</html>