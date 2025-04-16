 
 
 <?php
 include 'config.php';
 
?>
 
 
 <!-- Top Bar -->
 <div class="top-bar">
        <div class="top-bar-container">
            <div class="contact-info">
                <a href="tel:9815760082"><i class="fas fa-phone"></i> 9815760082</a>
                <a href="mailto:support@pharmacare.com"><i class="fas fa-envelope"></i> support@pharmacare.com</a>
            </div>
            <div class="top-bar-right">
                <div class="social-links">
                    <a href="#" title="Facebook"><i class="fab fa-facebook"></i></a>
                    <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" title="LinkedIn"><i class="fab fa-linkedin"></i></a>
                </div>
                <div class="additional-options">
                    <select class="language-select">
                        <option value="en">English</option>
                        <option value="es">Español</option>
                        <option value="fr">Français</option>
                        <option value="nep">Nepali</option>

                    </select>
                    <select class="currency-select">
                        <option value="usd">USD</option>
                        <option value="eur">EUR</option>
                        <option value="gbp">GBP</option>
                        <option value="npr">NPR</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <a href="../frontend/Homepage.php" class="logos"><i class="fas fa-clinic-medical"></i>
                <span>PharmaCare</span></a>
                
            </div>
           <?php include '../frontend/search.php'; ?>
           

            <div class="nav-icons">
                <div class="icon-item location">
                    <a href="../includes/vconsultant.php" class="virtual-consultant-link">
                        <i class="fas fa-user-md"></i>
                        <span class="voi">Virtual Consultant</span>
                    </a>
                </div>
                <style>
                    .virtual-consultant-link {
                        display: flex;
                        align-items: center;
                        text-decoration: none;
                        color: #333;
                        font-size: 14px;
                        transition: color 0.3s ease;
                    }

                    .virtual-consultant-link i {
                        margin-right: 5px;
                        font-size: 18px;
                    }

                    .virtual-consultant-link:hover {
                        color: #007bff;
                    }
                    .voi{
                        font-size: 14px;
                        font-weight: bold;
                    align-items: bottom;
                    }
                </style>
                <a href="../frontend/upload_prescription.php" class="upload-rx-link">
                    <i class="fas fa-file-prescription"></i>
                    <span>Upload Rx</span>
                </a>
                <style>
                    .upload-rx-link {
                        display: flex;
                        align-items: center;
                        text-decoration: none;
                        color: #333;
                        font-size: 14px;
                        transition: color 0.3s ease;
                    }

                    .upload-rx-link i {
                        margin-right: 5px;
                        font-size: 18px;
                    }

                    .upload-rx-link:hover {
                        color: #007bff;
                    }
                </style>
                </div>
                <div class="account-menu">
    <div class="account-menu__trigger">
        <a href="#" class="account-link">
            <i class="fas fa-user"></i>
            <span class="account-label">Account</span>
        </a>
    </div>
    <ul class="account-dropdown">
        <li class="dropdown-item"><a href="login.php" class="dropdown-link">Login</a></li>
        <li class="dropdown-item"><a href="../signup.php" class="dropdown-link">Sign Up</a></li>
        <li class="dropdown-item"><a href="../includes/logout.php" class="dropdown-link">Logout</a></li>
    </ul>
</div>
                <div class="icon-item">
                    <a href="../includes/bmi.php" class="bmi-link">
                        <i class="fas fa-calculator"></i>
                        <span>BMI</span>
                    </a>
                </div>
                <div class="icon-item cart">
                    <i class="fas fa-shopping-cart"></i>
                    <a href="../frontend/cart.php" class="cart-link">Cart
                        <span class="cart-count">0</span>
                    </a>
                </div>
               
            </div>
        </div>

        <div class="nav-categories">
            <ul>
                <li><a href="../Dasboard/products.php"><i class="fas fa-box"></i> All Products</a></li>
                <li><a href="../frontend/medicines.php"><i class="fas fa-pills"></i>Medicines</a></li>
                <li><a href="../frontend/healthcare.php"><i class="fas fa-heartbeat"></i>Healthcare</a></li>
                <li><a href="../frontend/personalcare.php"><i class="fas fa-pump-medical"></i>Personal Care</a></li>
                <li><a href="../frontend/vitamins.php"><i class="fas fa-prescription-bottle"></i>Vitamins</a></li>
                <li><a href="../frontend/healthpackages.php"><i class="fas fa-kit-medical"></i>Health Packages</a></li>
                <li><a href="../frontend/labtests.php"><i class="fas fa-stethoscope"></i>Lab Tests</a></li>
                <li><a href="../frontend/blog.php"><i class="fas fa-notes-medical"></i>Health Blog</a></li>
                
            </ul>
        </div>
    </nav>