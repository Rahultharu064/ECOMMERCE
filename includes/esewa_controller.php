<?php
// Constants
define('ESEWA_PAYMENT_URL', 'https://rc-epay.esewa.com.np/api/epay/main/v2/form');
define('ESEWA_VERIFY_URL', 'https://uat.esewa.com.np/epay/transrec');
define('MERCHANT_ID', '9806800001');
define('SUCCESS_URL', 'http://yourdomain.com/payment/success.php');
define('FAILURE_URL', 'http://yourdomain.com/payment/failure.php');

// Store transaction in database
function storeTransaction($txn_id, $amount, $product_id) {
    // Implement your database insertion logic
    
}

// Verify payment
function verifyPayment($txn_id, $amount) {
    $url = ESEWA_VERIFY_URL;
    
    $data = [
        'amt'          => $amount,
        'rid'          => $txn_id,
        'pid'          => $txn_id,
        'scd'         => MERCHANT_ID
    ];

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    curl_close($curl);

    // Parse response
    $xml = simplexml_load_string($response);
    return (string)$xml->response_code === 'Success';
}
?>