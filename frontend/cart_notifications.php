<div class="cart-notification">
    <a href="../frontend/cart.php" class="cart-link">
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-count">
            <?php 
            $count = 0;
            if (isset($_SESSION['cart'])) {
                $count = array_sum($_SESSION['cart']);
            }
            echo $count > 0 ? $count : '';
            ?>
        </span>
    </a>
    
    <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
    <div class="cart-preview">
        <div class="cart-preview-header">
            <h4>Your Cart</h4>
            <span><?php echo array_sum($_SESSION['cart']); ?> items</span>
        </div>
        
        <div class="cart-preview-items">
            <?php 
            require_once 'includes/config.php';
            require_once 'includes/cart.php';
            
            $cart = new Cart();
            $cartData = $cart->getCartWithDetails($conn);
            
            foreach ($cartData['items'] as $item): 
            ?>
                <div class="cart-preview-item">
                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    <div class="cart-preview-item-details">
                        <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                        <p><?php echo $item['quantity']; ?> x ₹<?php echo number_format($item['price'], 2); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="cart-preview-footer">
            <a href="../frontend/cart.php" class="btn-view-cart">View Cart</a>
            <a href="../frontend/checkout.php" class="btn-checkout">Checkout</a>
        </div>
    </div>
    <?php endif; ?>
</div>