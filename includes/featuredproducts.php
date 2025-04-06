<?php
include '../includes/config.php';

// Get category ID (hardcoded to 17 for featured products)
$category_id = 17;

// Get category name
$category_query = "SELECT categories_name FROM categories WHERE id = $category_id";
$category_result = mysqli_query($conn, $category_query);

if (!$category_result || mysqli_num_rows($category_result) == 0) {
    die("Invalid category or category not found");
}

$category_data = mysqli_fetch_assoc($category_result);
$category_name = $category_data['categories_name'];

// Product query for the category (removed pagination)
$query = "SELECT p.*, c.categories_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.category_id = $category_id";
          
$result = mysqli_query($conn, $query);

$products = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
}

// Get all categories for navigation
$categories = [];
$cat_query = "SELECT id, categories_name FROM categories";
$cat_result = mysqli_query($conn, $cat_query);
if ($cat_result) {
    while ($row = mysqli_fetch_assoc($cat_result)) {
        $categories[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy - <?php echo htmlspecialchars($category_name); ?></title>
    <link rel="stylesheet" href="../assets/css/pharmacy.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/products.css">
    <style>
        /* Your existing CSS styles here */
        :root {
            --primary-color: #0077b6;
            --secondary-color1: #3f37c9;
            --accent-color: #4895ef;
            --light-color: #f8f9fa;
            --secondary-color: #2a9d8f;
            --success-color: #4cc9f0;
            --danger-color: #f72585;
            --warning-color: #f8961e;
            --border-radius: 8px;
            --dark-color: #264653;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        /* Header Section - Updated to match .feature-title */
        .header {
            text-align: center;
            font-size: 2.5rem;
            color: #1a237e;
            margin-bottom: 3rem;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid #2065d1;
            display: inline-block;
            width: auto;
            margin-left: 50%;
            transform: translateX(-50%);
            font-weight: 600;
        }

        /* Category Header */
        .category-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color1) 100%);
            color: white;
            padding: 3rem 1rem;
            margin-bottom: 2rem;
            text-align: center;
            border-radius: var(--border-radius);
            position: relative;
            overflow: hidden;
        }

        .category-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1587854692152-cbe660dbde88?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') center/cover;
            opacity: 0.15;
            z-index: 0;
        }

        .category-header-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            margin: 0 auto;
        }

        .category-title {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
        }

        .category-description {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        /* Products Container - Updated for slider */
        .products-container {
            padding: 1rem;
            max-width: 1400px;
            margin: 0 auto;
            overflow: hidden;
            position: relative;
        }

        .products-slider {
            display: flex;
            transition: transform 0.5s ease;
            will-change: transform;
        }

        /* Product Card */
        .product-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            position: relative;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(0, 0, 0, 0.05);
            min-width: 280px;
            margin: 0 0.75rem;
            flex-shrink: 0;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .category-badge {
            background-color:#4895ef;
            color: white;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            position: absolute;
            top: 1rem;
            left: 1rem;
            z-index: 2;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .product-image-container {
            height: 200px;
            overflow: hidden;
            position: relative;
            background: #f5f7fa;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .product-image {
            max-width: 80%;
            max-height: 80%;
            object-fit: contain;
            transition: var(--transition);
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .no-image {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .product-content {
            padding: 1.25rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .product-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--dark-color);
            line-height: 1.4;
        }

        .product-description {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 1rem;
            flex-grow: 1;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.5;
        }

        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
        }

        .product-price {
            font-size: 1.25rem;
            font-weight: 700;
            color:#0077b6 ;
        }

        .product-prescription {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.35rem 0.75rem;
            border-radius: 4px;
        }

        .prescription-required {
            background-color: #ffebee;
            color: #c62828;
        }

        .prescription-not-required {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .product-actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            flex-grow: 1;
            text-decoration: none;
            gap: 0.5rem;
        }

        .btn-view {
            background-color: #0077b6;
            color: white;
            border: 1px solid #0077b6;
        }

        .btn-view:hover {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-cart {
            background-color:#2a9d8f;
            color: white;
        }

        .btn-cart:hover {
            background-color: #2a9d8f;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            grid-column: 1 / -1;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .empty-state i {
            font-size: 3rem;
            color: #adb5bd;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #6c757d;
            max-width: 500px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Slider Navigation */
        .slider-nav {
            display: flex;
            justify-content: center;
            margin-top: 1.5rem;
            gap: 0.5rem;
        }

        .slider-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #ccc;
            cursor: pointer;
            transition: var(--transition);
        }

        .slider-dot.active {
            background-color: var(--primary-color);
            transform: scale(1.2);
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .product-card {
                min-width: 240px;
            }
            
            .category-title {
                font-size: 2rem;
            }
            
            .header {
                font-size: 2rem;
                padding-bottom: 0.4rem;
                border-bottom-width: 2px;
            }
        }

        @media (max-width: 480px) {
            .product-card {
                min-width: 85%;
                margin: 0 0.5rem;
            }
            
            .product-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            .category-header {
                padding: 2rem 1rem;
            }
            
            .category-title {
                font-size: 1.75rem;
            }
            
            .header {
                font-size: 1.8rem;
                margin-bottom: 2rem;
                white-space: nowrap;
            }
        }
    </style>
</head>
<body>
    <main class="main-container">
        <div class="hero-section">
            <h2 class="header"><?php echo htmlspecialchars($category_name); ?></h2>
        </div>

        <section class="products-container">
            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h3>No Products in This Category</h3>
                    <p>We currently don't have any products in this category. Please check back later.</p>
                </div>
            <?php else: ?>
                <div class="products-slider" id="productsSlider">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <span class="category-badge"><?php echo htmlspecialchars($product['categories_name']); ?></span>
                            
                            <div class="product-image-container">
                                <?php if (!empty($product['image_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" class="product-image">
                                <?php else: ?>
                                    <div class="no-image">No Image Available</div>
                                <?php endif; ?>
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
                </div>

                <!-- Slider navigation dots -->
                <div class="slider-nav" id="sliderDots"></div>
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

        // Slider functionality
        document.addEventListener('DOMContentLoaded', function() {
            const slider = document.getElementById('productsSlider');
            const dotsContainer = document.getElementById('sliderDots');
            const productCards = document.querySelectorAll('.product-card');
            
            if (!slider || !productCards.length) return;
            
            const cardWidth = productCards[0].offsetWidth + 30; // width + margin
            const visibleCards = Math.min(Math.floor(slider.parentElement.offsetWidth / cardWidth), productCards.length);
            let currentIndex = 0;
            let autoSlideInterval;
            
            // Create dots
            for (let i = 0; i < productCards.length; i++) {
                const dot = document.createElement('div');
                dot.classList.add('slider-dot');
                if (i === 0) dot.classList.add('active');
                dot.addEventListener('click', () => {
                    goToSlide(i);
                });
                dotsContainer.appendChild(dot);
            }
            
            const dots = document.querySelectorAll('.slider-dot');
            
            function updateSlider() {
                const offset = -currentIndex * cardWidth;
                slider.style.transform = `translateX(${offset}px)`;
                
                // Update dots
                dots.forEach((dot, index) => {
                    dot.classList.toggle('active', index === currentIndex);
                });
            }
            
            function goToSlide(index) {
                currentIndex = Math.max(0, Math.min(index, productCards.length - visibleCards));
                updateSlider();
                resetAutoSlide();
            }
            
            function nextSlide() {
                if (currentIndex < productCards.length - visibleCards) {
                    currentIndex++;
                } else {
                    currentIndex = 0;
                }
                updateSlider();
            }
            
            function prevSlide() {
                if (currentIndex > 0) {
                    currentIndex--;
                } else {
                    currentIndex = productCards.length - visibleCards;
                }
                updateSlider();
            }
            
            function startAutoSlide() {
                autoSlideInterval = setInterval(nextSlide, 3000); // Change slide every 3 seconds
            }
            
            function resetAutoSlide() {
                clearInterval(autoSlideInterval);
                startAutoSlide();
            }
            
            // Start auto-sliding
            startAutoSlide();
            
            // Pause on hover
            slider.addEventListener('mouseenter', () => {
                clearInterval(autoSlideInterval);
            });
            
            slider.addEventListener('mouseleave', () => {
                startAutoSlide();
            });
            
            // Touch support for mobile devices
            let touchStartX = 0;
            let touchEndX = 0;
            
            slider.addEventListener('touchstart', (e) => {
                touchStartX = e.changedTouches[0].screenX;
                clearInterval(autoSlideInterval);
            }, {passive: true});
            
            slider.addEventListener('touchend', (e) => {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe();
                startAutoSlide();
            }, {passive: true});
            
            function handleSwipe() {
                const threshold = 50; // Minimum swipe distance
                const difference = touchStartX - touchEndX;
                
                if (difference > threshold) {
                    nextSlide(); // Swipe left
                } else if (difference < -threshold) {
                    prevSlide(); // Swipe right
                }
            }
        });
    </script>
</body>
</html>