<?php
// session_start();
require '../includes/config.php';

function processOrder($payment_method, $payment_details = null) {
    global $conn;
    
    if (empty($_SESSION['checkout_form_data'])) {
        throw new Exception("Checkout data missing");
    }

    $data = $_SESSION['checkout_form_data'];
    $order_number = $payment_method === 'khalti' 
        ? $_SESSION['khalti_verify']['purchase_order_id']
        : 'ORD-' . date('YmdHis') . '-' . mt_rand(1000, 9999);
    
    $conn->begin_transaction();
    try {
        // Prepare payment details
        $payment_data = [];
        if ($payment_method === 'khalti') {
            if (!isset($payment_details['transaction_id'], $payment_details['pidx'], $payment_details['amount'])) {
                throw new Exception("Missing payment details for Khalti.");
            }
            $payment_data = [
                'transaction_id' => $payment_details['transaction_id'],
                'pidx' => $payment_details['pidx'],
                'amount' => $payment_details['amount']
            ];
        $stmt = $conn->prepare("INSERT INTO orders (
            order_number, name, email, phone, address,
            total_amount, shipping_fee, payment_method, payment_details, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Failed to prepare statement for orders: " . $conn->error);
        }
        
        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (
            order_number, name, email, phone, address,
            total_amount, shipping_fee, payment_method, payment_details, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $status = 'completed';
        $payment_json = json_encode($payment_data);
        
        $stmt->bind_param(
            "sssssdssss",
            $order_number,
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['address'],
            $data['total'],
            $data['shipping_fee'],
            $payment_method,
            $payment_json,
            $status
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to save order: " . $stmt->error);
        }
        
        $order_id = $conn->insert_id;
        
        // Insert order items
        foreach ($data['cart_items'] as $item) {
            $stmt = $conn->prepare("INSERT INTO order_items (
                order_id, product_id, name, price, quantity, total, image_path
            ) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->bind_param(
                "iisdids",
                $order_id,
                $item['id'],
                $item['name'],
                $item['price'],
                $item['quantity'],
                $item['total'],
                $item['image_path']
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to save order item: " . $stmt->error);
            }
        }
        
        $conn->commit();
        
        // Store order data for success page
        $_SESSION['order_data'] = [
            'order_id' => $order_id,
            'order_number' => $order_number,
            'total_amount' => $data['total'],
            'shipping_fee' => $data['shipping_fee'],
            'payment_method' => $payment_method,
            'items' => $data['cart_items'],
            'customer_info' => [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $data['address']
            ],
            'payment_details' => $payment_data,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return true;
    }
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}
?>