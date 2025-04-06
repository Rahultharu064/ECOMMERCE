<?php
session_start();
include '../includes/config.php';



// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch product details
$product = [];
$query = "SELECT p.*, c.categories_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: ../frontend/products.php?error=Product not found");
    exit();
}

$product = $result->fetch_assoc();

// Handle add to cart functionality
if (isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $quantity = intval($_POST['quantity']);
    
    if (array_key_exists($product_id, $_SESSION['cart'])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    
    $_SESSION['message'] = "Product added to cart successfully!";
    header("Location: ../frontend/cart.php?id=$product_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['product_name']); ?> | PharmaCare</title>
    <link rel="stylesheet" href="../assets/css/pharmacy.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .product-details-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 0 20px;
        }
        
        .product-detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 300px;
        }
        
        .product-image-container {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            text-align: center;
        }
        
        .product-main-image {
            max-width: 100%;
            height: auto;
            max-height: 400px;
            object-fit: contain;
        }
        
        .product-info-container {
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        }
        
        .product-title {
            font-size: 28px;
            margin-bottom: 10px;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .product-category {
            display: inline-block;
            background: #e8f4fd;
            color: #3498db;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .product-price {
            font-size: 24px;
            font-weight: 700;
            color: #27ae60;
            margin: 20px 0;
        }
        
        .detail-section {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .detail-label {
            font-weight: 600;
            color: #7f8c8d;
            display: block;
            margin-bottom: 8px;
            font-size: 15px;
        }
        
        .detail-value {
            color: #2c3e50;
            line-height: 1.6;
        }
        
        .add-to-cart-form {
            margin-top: 30px;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .quantity-input {
            width: 70px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
            font-size: 16px;
        }
        
        .btn-add-to-cart {
            background: #3498db;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-add-to-cart:hover {
            background: #2980b9;
        }
        
        .prescription-notice {
            background: #fff8e1;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .prescription-notice i {
            color: #ff9800;
            font-size: 20px;
        }
        
        .back-to-products {
            display: inline-block;
            margin-top: 20px;
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-to-products:hover {
            text-decoration: underline;
        }
        
        .stock-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            margin-left: 10px;
        }
        
        .in-stock {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        .low-stock {
            background: #fff3e0;
            color: #e65100;
        }
        
        .out-of-stock {
            background: #ffebee;
            color: #c62828;
        }
        
        @media (max-width: 768px) {
            .product-detail-grid {
                grid-template-columns: 1fr;
            }
            
            .product-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="product-details-container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert success">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>
        
        <div class="product-detail-grid">
            <div class="product-image-container">
                <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                     alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                     class="product-main-image">
            </div>
            
            <div class="product-info-container">
                <h1 class="product-title"><?php echo htmlspecialchars($product['product_name']); ?></h1>
                <!-- <span class="product-category"><?php echo htmlspecialchars($product['categories_name']); ?></span> -->
                
                <div class="product-price">₹<?php echo number_format($product['price'], 2); ?></div>
                
                <!-- Stock status indicator -->
                <?php 
                $stock_class = '';
                $stock_text = '';
                if ($product['quantity'] > 40) {
                    $stock_class = 'in-stock';
                    $stock_text = 'In Stock';
                } elseif ($product['quantity'] > 0) {
                    $stock_class = 'low-stock';
                    $stock_text = 'Low Stock';
                } else {
                    $stock_class = 'out-of-stock';
                    $stock_text = 'Out of Stock';
                }
                ?>
                <div class="detail-section">
                    <span class="detail-label">Availability</span>
                    <span class="stock-status <?php echo $stock_class; ?>">
                        <?php echo $stock_text; ?>
                    </span>
                </div>
                
                <div class="detail-section">
                    <span class="detail-label">Description</span>
                    <p class="detail-value"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>
                
                <div class="detail-section">
                    <span class="detail-label">Manufacturer/Diagnostic Center</span>
                    <p class="detail-value"><?php echo htmlspecialchars($product['manufacturer']); ?></p>
                </div>
                
                <div class="detail-section">
                    <span class="detail-label">Active Ingredients/Tests Included</span>
                    <p class="detail-value"><?php echo nl2br(htmlspecialchars($product['active_ingredients'])); ?></p>
                </div>
                
                <?php if ($product['prescription_required'] == 'Yes'): ?>
                <div class="prescription-notice">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <strong>Prescription Required</strong>
                        <p>This product requires a valid prescription from a licensed healthcare provider.</p>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="detail-section">
                    <span class="detail-label">Expiry Date</span>
                    <p class="detail-value"><?php echo date('F j, Y', strtotime($product['expiry_date'])); ?></p>
                </div>
                
                <form method="post" class="add-to-cart-form">
                    <div class="quantity-selector">
                        <label for="quantity" class="detail-label">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" class="quantity-input" 
                               value="1" min="1" max="<?php echo $product['quantity']; ?>" 
                               <?php echo $product['quantity'] <= 0 ? 'disabled' : ''; ?>>
                    </div>
                    
                    <button type="submit" name="add_to_cart" class="btn-add-to-cart" 
                        <?php echo $product['quantity'] <= 0 ? 'disabled' : ''; ?>>
                        <i class="fas fa-cart-plus"></i>
                        <?php echo $product['quantity'] > 0 ? 'Add to Cart' : 'Out of Stock'; ?>
                    </button>
                </form>
                
                <a href="../Dasboard/products.php" class="back-to-products">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    
    <script src="../assets/js/pharmacy.js"></script>
    <script>
        // Quantity input validation
        document.querySelector('.quantity-input').addEventListener('change', function() {
            const max = parseInt(this.getAttribute('max'));
            const value = parseInt(this.value);
            
            if (value < 1) this.value = 1;
            if (value > max) this.value = max;
        });
    </script>
</body>
</html>