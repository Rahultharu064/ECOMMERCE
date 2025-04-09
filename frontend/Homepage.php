<?php
include "../includes/config.php";

?>








<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaCare - Your Online Pharmacy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/pharmacy.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body>
   <?php
   include "../includes/header.php";
   ?>
   <?php
   include 'chatbot.php';
   ?>
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="slider">
            <div class="slide active">
                <img src="https://images.unsplash.com/photo-1576602976047-174e57a47881?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1920&q=80" alt="Pharmacy Slide 1">
                <div class="slide-content">
                    <h1>Your Health, Our Priority</h1>
                    <p>Get up to 25% off on all healthcare products</p>
                    <button class="cta-button">Shop Now</button>
                </div>
            </div>
            <div class="slide">
                <img src="https://images.unsplash.com/photo-1631549916768-4119b2e5f926?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1920&q=80" alt="Pharmacy Slide 2">
                <div class="slide-content">
                    <h1>Quality Healthcare Products</h1>
                    <p>Free delivery on orders above $50</p>
                    <button class="cta-button">Explore More</button>
                </div>
            </div>
            <div class="slide">
                <img src="https://images.unsplash.com/photo-1587854692152-cbe660dbde88?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1920&q=80" alt="Pharmacy Slide 3">
                <div class="slide-content">
                    <h1>Professional Healthcare Advice</h1>
                    <p>Consult with our expert pharmacists</p>
                    <button class="cta-button">Contact Us</button>
                </div>
            </div>
            <button class="slider-btn prev"><i class="fas fa-chevron-left"></i></button>
            <button class="slider-btn next"><i class="fas fa-chevron-right"></i></button>
            <div class="slider-dots"></div>
        </div>
    </section>

    <?php
    include '../includes/featuredproducts.php';
    ?>
     
    <!-- Fixed HTML Structure -->
     <?php
     include "../frontend/services.php";
     ?>

<?php
include "../includes/Newproducts.php"
?>


    

    <!-- Loyalty Program Section -->
    <section class="loyalty-section">
        <div class="container">
            <h2 class="feature-title">Loyalty Program</h2>
            <div class="loyalty-cards">
                <div class="loyalty-card">
                    <div class="badge"><i class="fas fa-capsules"></i></div>
                    <h4>Silver Tier</h4>
                    <p>Earn 5% cashback on all orders.</p>
                </div>
                <div class="loyalty-card">
                    <div class="badge"><i class="fas fa-star"></i></div>
                    <h4>Gold Tier</h4>
                    <p>Earn 10% cashback + priority support.</p>
                </div>
                <div class="loyalty-card">
                    <div class="badge"><i class="fas fa-gem"></i></div>
                    <h4>Platinum Tier</h4>
                    <p>Earn 15% cashback + free delivery.</p>
                </div>
            </div>
        </div>
    </section>


    <?php
    include "../includes/footer.php";
    ?>

    <script src="../assets/js/pharmacy.js"></script>
   
</body>
</html>