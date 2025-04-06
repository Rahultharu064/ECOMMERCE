<?php
session_start();
include '../includes/config.php';
include '../includes/header.php';

// Set category for Vitamins
$category_id = 16; // Change to your actual category ID for Vitamins
$category_name = "Vitamins & Supplements";

// Product query with category filter
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

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM products WHERE category_id = $category_id";
if (!empty($search)) {
    $count_query .= " AND (product_name LIKE '%$search%' OR description LIKE '%$search%')";
}
$count_result = mysqli_query($conn, $count_query);
$total_products = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_products / $limit);

// Get all categories for navigation
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
    <link rel="stylesheet" href="../assets/frontendcss/products.css">
    
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="main-container">
        <h3 style="font-size: 1.5rem; font-weight: 600; color: var(--dark-color); margin-bottom: 1rem; text-align: center;">
            <?php echo $category_name; ?>
        </h3>
        
        <section class="filter-section">
            <form method="GET" action="vitamins.php" class="filter-form">
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
                        <i class="fas fa-capsules"></i>
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
                        <a href="vitamins.php?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">
                            <i class="fas fa-chevron-left"></i> Prev
                        </a>
                    <?php endif; ?>

                    <?php 
                    $max_visible_pages = 5;
                    $start_page = max(1, $page - floor($max_visible_pages / 2));
                    $end_page = min($total_pages, $start_page + $max_visible_pages - 1);
                    
                    if ($start_page > 1) {
                        echo '<a href="vitamins.php?page=1&search='.urlencode($search).'">1</a>';
                        if ($start_page > 2) echo '<span>...</span>';
                    }
                    
                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <a href="vitamins.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"
                           class="<?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; 
                    
                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) echo '<span>...</span>';
                        echo '<a href="vitamins.php?page='.$total_pages.'&search='.urlencode($search).'">'.$total_pages.'</a>';
                    }
                    ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="vitamins.php?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">
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