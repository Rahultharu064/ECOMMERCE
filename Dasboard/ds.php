<?php
session_start();

// Sample cart data structure (usually stored in session or database)
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [
        [
            'id' => 1,
            'name' => 'Product 1',
            'price' => 29.99,
            'quantity' => 2,
            'image' => 'product1.jpg'
        ],
        [
            'id' => 2,
            'name' => 'Product 2',
            'price' => 49.99,
            'quantity' => 1,
            'image' => 'product2.jpg'
        ]
    ];
}

// Handle quantity updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_quantity'])) {
        $product_id = $_POST['product_id'];
        $new_quantity = (int)$_POST['quantity'];
        
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $product_id) {
                $item['quantity'] = max(1, $new_quantity); // Ensure minimum quantity of 1
                break;
            }
        }
    }
    
    if (isset($_POST['remove_item'])) {
        $product_id = $_POST['product_id'];
        $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($product_id) {
            return $item['id'] != $product_id;
        });
    }
}

// Calculate total
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <style>
        .cart-container {
            width: 80%;
            margin: 20px auto;
            padding: 20px;
        }
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .cart-table th, .cart-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .cart-table th {
            background-color: #f5f5f5;
        }
        .product-image {
            max-width: 100px;
            height: auto;
        }
        .quantity-input {
            width: 60px;
            padding: 5px;
        }
        .checkout-section {
            text-align: right;
        }
        .checkout-btn {
            background-color: #4CAF50;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="cart-container">
        <h2>Your Shopping Cart</h2>
        
        <?php if (empty($_SESSION['cart'])): ?>
            <p>Your cart is empty</p>
        <?php else: ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <tr>
                            <td>
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                     class="product-image">
                                <?php echo htmlspecialchars($item['name']); ?>
                            </td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <form method="POST">
                                    <input type="number" 
                                           name="quantity" 
                                           class="quantity-input"
                                           value="<?php echo $item['quantity']; ?>"
                                           min="1">
                                    <input type="hidden" 
                                           name="product_id" 
                                           value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="update_quantity">Update</button>
                                </form>
                            </td>
                            <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" 
                                           name="product_id" 
                                           value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="remove_item">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="checkout-section">
                <h3>Total: $<?php echo number_format($total, 2); ?></h3>
                <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>