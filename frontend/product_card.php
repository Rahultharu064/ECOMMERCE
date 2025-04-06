<?php
require_once '../includes/config.php';

$sql = "SELECT * FROM mproducts ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .product-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin: 15px;
            transition: transform 0.3s;
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 6px;
            background: #f8f9fa;
        }
        .prescription-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        .stock-status {
            font-size: 0.9rem;
        }
        .out-of-stock {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4 text-center">Pharmacy Products</h1>
        
        <div class="row">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="col-md-4 col-lg-3 mb-4">
                        <div class="product-card position-relative">
                            <?php if($row['requires_prescription']): ?>
                                <span class="prescription-badge">Prescription Required</span>
                            <?php endif; ?>
                            
                            <img src="../uploads/<?= htmlspecialchars($row['image_url']) ?>" 
                                 alt="<?= htmlspecialchars($row['name']) ?>" 
                                 class="product-image mb-3">
                                 
                            <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                            <p class="text-muted manufacturer"><?= htmlspecialchars($row['manufacturer']) ?></p>
                            
                            <p class="card-text description">
                                <?= nl2br(htmlspecialchars(substr($row['description'], 0, 100))) ?>
                                <?= strlen($row['description']) > 100 ? '...' : '' ?>
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0 price">$<?= number_format($row['price'], 2) ?></h6>
                                    <small class="stock-status <?= $row['stock'] <= 0 ? 'out-of-stock' : 'text-muted' ?>">
                                        <?= $row['stock'] > 0 ? "In Stock: {$row['stock']}" : 'Out of Stock' ?>
                                    </small>
                                </div>
                                <form action="./addTocart.php" method="POST">
                                    <input type="hidden" name="pid" value="<?= $row['id'] ?>">
                                    <button type="submit" name = "addtocart" class="btn btn-primary btn-sm add-to-cart" data-product-id="<?= $row['id'] ?>">
                                       
                                Add to cart</button></form>
                                
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">No products available at the moment.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- <script>
        // Basic add to cart functionality
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', () => {
                const productId = button.dataset.productId;
                // You can implement cart functionality here
                console.log(`Added product ${productId} to cart`);
                button.textContent = 'Added!';
                button.disabled = true;
                setTimeout(() => {
                    button.textContent = 'Add to Cart';
                    button.disabled = false;
                }, 2000);
            });
        });
    </script> -->
</body>
</html>