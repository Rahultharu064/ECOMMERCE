<?php
session_start();
include '../includes/config.php';
include '../includes/header.php';

// Set category for Personal Care
$category_id = 11; // Change to your actual category ID for Personal Care
$category_name = "Personal Care";

// Rest of the code similar to products.php but filtered for this category
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

$query = "SELECT p.*, c.categories_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.category_id = $category_id";

if (!empty($search)) {
    $query .= " AND (p.product_name LIKE '%$search%' OR p.description LIKE '%$search%')";
}

$query .= " LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

$count_query = "SELECT COUNT(*) as total FROM products WHERE category_id = $category_id";
if (!empty($search)) {
    $count_query .= " AND (product_name LIKE '%$search%' OR description LIKE '%$search%')";
}
$count_result = mysqli_query($conn, $count_query);
$total_products = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_products / $limit);

$categories = [];
$cat_query = "SELECT id, categories_name FROM categories";
$cat_result = mysqli_query($conn, $cat_query);
while ($row = mysqli_fetch_assoc($cat_result)) {
    $categories[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy - <?php echo $category_name; ?></title>
    <link rel="stylesheet" href="../assets/css/pharmacy.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Your existing CSS styles here */

        :root {
            --primary-color: #0077b6;
            --primary-hover: #005b8c;
            --secondary-color: #2a9d8f;
            --secondary-hover: #21867a;
            --accent-color: #e9c46a;
            --dark-color: #264653;
            --light-color: #f8f9fa;
            --text-color: #212529;
            --text-light: #6c757d;
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.5;
            color: var(--light-color);
            background-color: #f5f7fa;
        }

      
       
        /* Main Content Layout */
        .main-container {
            max-width: 1200px;
            margin: 260px auto;
            padding: 1rem 20px;
        }

        /* Filter Section */
        .filter-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.25rem;
            margin: 1rem 0 2rem;
            box-shadow: var(--box-shadow);
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: flex-end;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark-color);
            font-size: 0.9rem;
        }

        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 0.65rem 0.75rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 0.95rem;
            transition: var(--transition);
        }

        .filter-group input:focus,
        .filter-group select:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 119, 182, 0.1);
        }

        .filter-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.65rem 1.25rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
        }

        .filter-btn:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
        }

        /* Products Grid */
        .products-container {
            margin-bottom: 2rem;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.25rem;
        }

        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }

        /* Product Card */
        .product-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        }

        .product-image-container {
            position: relative;
        }

        .product-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            display: block;
        }

        .product-badge {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            background-color: var(--accent-color);
            color: var(--dark-color);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .product-content {
            padding: 1.25rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .product-title {
            font-size: 1.05rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
            line-height: 1.3;
        }

        .product-description {
            color: var(--text-light);
            font-size: 0.8rem;
            margin-bottom: 1rem;
            flex-grow: 1;
            line-height: 1.4;
        }

        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .product-price {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.05rem;
        }

        .product-prescription {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-weight: 500;
        }

        .prescription-required {
            background-color: #ffe0e0;
            color: #d32f2f;
        }

        .prescription-not-required {
            background-color: #e0ffe0;
            color: #2e7d32;
        }

        .product-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: auto;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 0.75rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            font-size: 0.8rem;
            flex: 1;
            text-align: center;
        }

        .btn-view {
            background-color: var(--primary-color);
            color: white;
            border: 1px solid var(--primary-color);
        }

        .btn-view:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        .btn-cart {
            background-color: var(--secondary-color);
            color: white;
            border: 1px solid var(--secondary-color);
        }

        .btn-cart:hover {
            background-color: var(--secondary-hover);
            border-color: var(--secondary-hover);
        }

        .btn i {
            margin-right: 0.4rem;
            font-size: 0.8rem;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin: 2rem 0 1rem;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .pagination a, .pagination span {
            padding: 0.5rem 0.75rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            color: var(--text-color);
            transition: var(--transition);
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            font-size: 0.85rem;
        }

        .pagination a:hover {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .pagination .active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
            font-weight: 500;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 2rem;
            grid-column: 1 / -1;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .empty-state i {
            font-size: 2.5rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
        }

        .empty-state p {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .product-card {
            animation: fadeIn 0.4s ease forwards;
            opacity: 0;
        }

        /* Staggered animation */
        .product-card:nth-child(1) { animation-delay: 0.05s; }
        .product-card:nth-child(2) { animation-delay: 0.1s; }
        .product-card:nth-child(3) { animation-delay: 0.15s; }
        .product-card:nth-child(4) { animation-delay: 0.2s; }
        .product-card:nth-child(5) { animation-delay: 0.25s; }
        .product-card:nth-child(6) { animation-delay: 0.3s; }
        .product-card:nth-child(7) { animation-delay: 0.35s; }
        .product-card:nth-child(8) { animation-delay: 0.4s; }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="main-container">
        <h3 style="font-size: 1.5rem; font-weight: 600; color: var(--dark-color); margin-bottom: 1rem; text-align: center;">
            <?php echo $category_name; ?>
        </h3>
        
        <section class="filter-section">
            <form method="GET" action="personalcare.php" class="filter-form">
                <div class="filter-group">
                    <label for="search">Search <?php echo $category_name; ?></label>
                    <input type="text" id="search" name="search" placeholder="Search <?php echo $category_name; ?>..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <button type="submit" class="filter-btn">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
            </form>
        </section>

        <section class="products-container">
            <div class="products-grid">
                <?php if (empty($products)): ?>
                    <div class="empty-state">
                        <i class="fas fa-pump-medical"></i>
                        <h3>No <?php echo $category_name; ?> Found</h3>
                        <p>Try adjusting your search criteria</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <?php if (isset($product['is_featured']) && $product['is_featured']): ?>
                                <span class="product-badge">Featured</span>
                            <?php endif; ?>
                            
                            <div class="product-image-container">
                                <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" class="product-image">
                            </div>
                            
                            <div class="product-content">
                                <h3 class="product-title"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                                <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                                
                                <div class="product-meta">
                                    <span class="product-price">₹<?php echo number_format($product['price'], 2); ?></span>
                                    <span class="product-prescription <?php echo $product['prescription_required'] == 'Yes' ? 'prescription-required' : 'prescription-not-required'; ?>">
                                        <?php echo $product['prescription_required'] == 'Yes' ? 'Rx' : 'OTC'; ?>
                                    </span>
                                </div>
                                
                                <div class="product-actions">
                                    <a href="../frontend/product_details.php?id=<?php echo $product['id']; ?>" class="btn btn-view">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="#" class="btn btn-cart" onclick="addToCart(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-cart-plus"></i> Add
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="personalcare.php?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">
                            <i class="fas fa-chevron-left"></i> Prev
                        </a>
                    <?php endif; ?>

                    <?php 
                    $max_visible_pages = 5;
                    $start_page = max(1, $page - floor($max_visible_pages / 2));
                    $end_page = min($total_pages, $start_page + $max_visible_pages - 1);
                    
                    if ($start_page > 1) {
                        echo '<a href="personalcare.php?page=1&search='.urlencode($search).'">1</a>';
                        if ($start_page > 2) echo '<span>...</span>';
                    }
                    
                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <a href="personalcare.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"
                           class="<?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; 
                    
                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) echo '<span>...</span>';
                        echo '<a href="personalcare.php?page='.$total_pages.'&search='.urlencode($search).'">'.$total_pages.'</a>';
                    }
                    ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="personalcare.php?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
    
    <script>
        function addToCart(productId) {
            event.preventDefault();
            
            const button = event.target.closest('.btn-cart');
            const originalHtml = button.innerHTML;
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            button.style.pointerEvents = 'none';
            
            setTimeout(() => {
                button.innerHTML = '<i class="fas fa-check"></i> Added';
                button.style.backgroundColor = '#21867a';
                
                updateCartCount(1);
                
                setTimeout(() => {
                    button.innerHTML = originalHtml;
                    button.style.backgroundColor = '';
                    button.style.pointerEvents = '';
                }, 1500);
            }, 600);
        }
        
        function updateCartCount(quantityChange) {
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) {
                const currentCount = parseInt(cartCount.textContent) || 0;
                cartCount.textContent = currentCount + quantityChange;
                
                cartCount.style.transform = 'scale(1.2)';
                setTimeout(() => {
                    cartCount.style.transform = 'scale(1)';
                }, 200);
            }
        }
    </script>
</body>
</html>