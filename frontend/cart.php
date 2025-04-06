<?php
// session_start();
include '../includes/config.php';



include '../includes/auth.php'; // Adjust the path to the file containing requireLogin()

// requireLogin();

// // Get current user data
// $user = getCurrentUser();






// Initialize variables
$message = '';
$error = '';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add item to cart
    if (isset($_POST['add_to_cart'])) {
        $product_id = intval($_POST['product_id']);
        $quantity = intval($_POST['quantity']);
        
        // Validate product exists and has stock
        $stmt = $conn->prepare("SELECT quantity, prescription_required FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $product = $result->fetch_assoc();
            
            // Check stock
            if ($product['quantity'] >= $quantity) {
                // Check prescription requirement
                if ($product['prescription_required'] == 'Yes' && !isset($_SESSION['prescription_approved'][$product_id])) {
                    $_SESSION['prescription_needed'] = $product_id;
                    header("Location: upload_prescription.php?product_id=$product_id");
                    exit();
                }
                
                // Add to cart
                if (!isset($_SESSION['cart'])) {
                    $_SESSION['cart'] = [];
                }
                
                if (isset($_SESSION['cart'][$product_id])) {
                    $_SESSION['cart'][$product_id] += $quantity;
                } else {
                    $_SESSION['cart'][$product_id] = $quantity;
                }
                
                $message = "Product added to cart successfully!";
            } else {
                $error = "Not enough stock available";
            }
        } else {
            $error = "Product not found";
        }
    }
    // Update cart quantities
    elseif (isset($_POST['update_cart'])) {
        foreach ($_POST['quantity'] as $product_id => $quantity) {
            $product_id = intval($product_id);
            $quantity = intval($quantity);
            
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$product_id]);
            } else {
                // Verify product exists and has stock
                $stmt = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    $product = $result->fetch_assoc();
                    
                    if ($product['quantity'] >= $quantity) {
                        $_SESSION['cart'][$product_id] = $quantity;
                    } else {
                        $error = "Not enough stock available for some items";
                    }
                }
            }
        }
        
        if (empty($error)) {
            $message = "Cart updated successfully";
        }
    }
    // Remove item from cart
    elseif (isset($_POST['remove_item'])) {
        $product_id = intval($_POST['product_id']);
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            $message = "Item removed from cart";
        }
    }
    // Clear cart
    elseif (isset($_POST['clear_cart'])) {
        unset($_SESSION['cart']);
        $message = "Cart cleared successfully";
    }
}

// Get cart items with product details
$cart_items = [];
$total = 0;
$total_items = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $stmt = $conn->prepare("SELECT id, product_name, price, image_path, prescription_required FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $product = $result->fetch_assoc();
            $subtotal = $product['price'] * $quantity;
            $total += $subtotal;
            $total_items += $quantity;
            
            $cart_items[] = [
                'id' => $product['id'],
                'name' => $product['product_name'],
                'price' => $product['price'],
                'image' => $product['image_path'],
                'quantity' => $quantity,
                'subtotal' => $subtotal,
                'prescription_required' => $product['prescription_required']
            ];
        } else {
            // Remove invalid product from cart
            unset($_SESSION['cart'][$product_id]);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Shopping Cart | PharmaCare</title>
    <link rel="stylesheet" href="../assets/css/pharmacy.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 300px auto;
            padding: 0 20px;
        }
        
        .cart-header {
            margin-bottom: 30px;
        }
        
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .cart-table th {
            text-align: left;
            padding: 15px 10px;
            background: #f5f5f5;
            border-bottom: 2px solid #ddd;
        }
        
        .cart-table td {
            padding: 15px 10px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        .cart-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #eee;
        }
        
        .quantity-input {
            width: 60px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }
        
        .btn-remove {
            color: #e74c3c;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn-remove:hover {
            color: #c0392b;
        }
        
        .cart-summary {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .summary-total {
            font-weight: bold;
            font-size: 18px;
            color: #2c3e50;
        }
        
        .cart-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            display: inline-block;
        }
        
        .btn-continue {
            background: #f5f5f5;
            color: #333;
        }
        
        .btn-clear {
            background: #e74c3c;
            color: white;
            border: none;
            cursor: pointer;
        }
        
        .btn-checkout {
            background: #27ae60;
            color: white;
        }
        
        .empty-cart {
            text-align: center;
            padding: 50px 0;
        }
        
        .empty-cart-icon {
            font-size: 60px;
            color: #bdc3c7;
            margin-bottom: 20px;
        }
        
        .prescription-notice {
            color: #e67e22;
            font-size: 14px;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .cart-table thead {
                display: none;
            }
            
            .cart-table tr {
                display: block;
                margin-bottom: 20px;
                border: 1px solid #eee;
                border-radius: 8px;
                padding: 10px;
            }
            
            .cart-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                border: none;
            }
            
            .cart-table td::before {
                content: attr(data-label);
                font-weight: bold;
                margin-right: 20px;
            }
            
            .cart-actions {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <?php 
    include "../includes/header.php";
    ?>
    
    
    <div class="cart-container">
        <div class="cart-header">
            <h1> Shopping Cart</h1>
            <?php if (!empty($message)): ?>
                <div class="alert success">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added any items to your cart yet.</p>
                <a href="../Dasboard/products.php" class="btn btn-continue">Continue Shopping</a>
            </div>
        <?php else: ?>
            <form method="post" action="cart.php">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td data-label="Product">
                                    <div style="display: flex; align-items: center; gap: 15px;">
                                        <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                             class="cart-item-image">
                                        <div>
                                            <a href="product_details.php?id=<?php echo $item['id']; ?>">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                            </a>
                                            <?php if ($item['prescription_required'] == 'Yes'): ?>
                                                <div class="prescription-notice">
                                                    <i class="fas fa-prescription-bottle-alt"></i> Prescription required
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Price">₹<?php echo number_format($item['price'], 2); ?></td>
                                <td data-label="Quantity">
                                    <input type="number" name="quantity[<?php echo $item['id']; ?>]" 
                                           class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1">
                                </td>
                                <td data-label="Subtotal">₹<?php echo number_format($item['subtotal'], 2); ?></td>
                                <td data-label="Action">
                                    <button type="submit" name="remove_item" class="btn-remove" 
                                            formnovalidate formaction="cart.php">
                                        <i class="fas fa-trash"></i>
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="cart-summary">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>₹<?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span>Calculated at checkout</span>
                    </div>
                    <div class="summary-row summary-total">
                        <span>Total</span>
                        <span>₹<?php echo number_format($total, 2); ?></span>
                    </div>
                </div>
                
                <div class="cart-actions">
                    <a href="../Dasboard/products.php" class="btn btn-continue">
                        <i class="fas fa-arrow-left"></i> Continue Shopping
                    </a>
                    <button type="submit" name="clear_cart" class="btn btn-clear" formnovalidate>
                        <i class="fas fa-trash"></i> Clear Cart
                    </button>
                    <button type="submit" name="update_cart" class="btn">
                        <i class="fas fa-sync-alt"></i> Update Cart
                    </button>
                    <a href="checkout.php" class="btn btn-checkout">
                        Proceed to Checkout <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>
    
    <script src="../assets/js/pharmacy.js"></script>
    <script>
        // Quantity input validation
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                if (this.value < 1) this.value = 1;
            });
        });
    </script>
</body>
</html>