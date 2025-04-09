<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About PharmaCare - Your Trusted Online Pharmacy</title>
    <link rel="stylesheet" href="../assets/css/pharmacy.css">
    <style>
        :root {
            --primary-color: #2a7fba;
            --secondary-color: #3bb77e;
            --dark-color: #253d4e;
            --light-color: #f5f7fa;
            --text-color: #555;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            color: var(--text-color);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header Styles */
        header {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }
        
        .logo {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .logo span {
            color: var(--secondary-color);
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
                        url('https://images.unsplash.com/photo-1631815588090-d4bfec5b1ccb?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            text-align: center;
            padding: 100px 20px;
        }
        
        .hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
        }
        
        .hero p {
            font-size: 20px;
            max-width: 800px;
            margin: 0 auto 30px;
        }
        
        /* About Section */
        .about-section {
            padding: 80px 0;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 50px;
            color: var(--dark-color);
        }
        
        .section-title h2 {
            font-size: 36px;
            margin-bottom: 15px;
        }
        
        .section-title p {
            max-width: 700px;
            margin: 0 auto;
            font-size: 18px;
        }
        
        .about-content {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 40px;
        }
        
        .about-text {
            flex: 1;
            min-width: 300px;
        }
        
        .about-text h3 {
            font-size: 28px;
            color: var(--dark-color);
            margin-bottom: 20px;
        }
        
        .about-text p {
            margin-bottom: 20px;
        }
        
        .about-image {
            flex: 1;
            min-width: 300px;
        }
        
        .about-image img {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        /* Features Section */
        .features-section {
            padding: 80px 0;
            background-color: var(--light-color);
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }
        
        .feature-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
        }
        
        .feature-icon {
            font-size: 50px;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .feature-card h3 {
            font-size: 22px;
            color: var(--dark-color);
            margin-bottom: 15px;
        }
        
        /* Team Section */
        .team-section {
            padding: 80px 0;
        }
        
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }
        
        .team-member {
            text-align: center;
        }
        
        .team-member img {
            width: 100%;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .team-member h3 {
            font-size: 22px;
            color: var(--dark-color);
            margin-bottom: 5px;
        }
        
        .team-member p {
            color: var(--primary-color);
            font-weight: 500;
        }
        
        /* Mission Section */
        .mission-section {
            padding: 80px 0;
            background: linear-gradient(rgba(59, 183, 126, 0.9), rgba(59, 183, 126, 0.9));
            color: white;
        }
        
        .mission-content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }
        
        .mission-content h2 {
            font-size: 36px;
            margin-bottom: 20px;
        }
        
        /* Footer */
        footer {
            background-color: var(--dark-color);
            color: white;
            padding: 60px 0 20px;
        }
        
        .footer-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .footer-column h3 {
            font-size: 20px;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer-column h3::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 2px;
            background-color: var(--secondary-color);
        }
        
        .footer-column ul {
            list-style: none;
        }
        
        .footer-column ul li {
            margin-bottom: 10px;
        }
        
        .footer-column ul li a {
            color: #ddd;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-column ul li a:hover {
            color: var(--secondary-color);
        }
        
        .copyright {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 36px;
            }
            
            .hero p {
                font-size: 18px;
            }
            
            .section-title h2 {
                font-size: 30px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Header -->
     <?php
     include '../includes/header.php'
     ?>
   

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>About PharmaCare</h1>
            <p>Your trusted partner in health and wellness, delivering quality medications and healthcare products right to your doorstep.</p>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h3>Our Story</h3>
                    <p>Founded in 2015, PharmaCare began as a small local pharmacy with a big vision: to make healthcare accessible, affordable, and convenient for everyone. What started as a single storefront has now grown into a leading online pharmacy serving customers nationwide.</p>
                    <p>Our journey has been guided by a commitment to excellence in pharmaceutical care, innovative technology solutions, and most importantly, our customers' well-being.</p>
                    <p>Today, PharmaCare is proud to be a trusted name in online pharmacy services, with a team of licensed pharmacists and healthcare professionals dedicated to your health.</p>
                </div>
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1587854692152-cbe660dbde88?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="Pharmacy team">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
   <?php
   include '../frontend/services.php'
    ?>

    <!-- Team Section -->
    <section class="team-section">
        <div class="container">
            <div class="section-title">
                <h2>Meet Our Expert Team</h2>
                <p>Behind PharmaCare is a team of dedicated healthcare professionals committed to your well-being</p>
            </div>
            
            <div class="team-grid">
                <div class="team-member">
                    <img src="https://images.unsplash.com/photo-1559839734-2b71ea197ec2?ixlib=rb-1.2.1&auto=format&fit=crop&w=634&q=80" alt="Dr. Sarah Johnson">
                    <h3>Dr. Sarah Johnson</h3>
                    <p>Chief Pharmacist</p>
                </div>
                
                <div class="team-member">
                    <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?ixlib=rb-1.2.1&auto=format&fit=crop&w=634&q=80" alt="Michael Chen">
                    <h3>Michael Chen</h3>
                    <p>Head of Operations</p>
                </div>
                
                <div class="team-member">
                    <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-1.2.1&auto=format&fit=crop&w=634&q=80" alt="Dr. Priya Patel">
                    <h3>Dr. Priya Patel</h3>
                    <p>Clinical Pharmacist</p>
                </div>
                
                <div class="team-member">
                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-1.2.1&auto=format&fit=crop&w=634&q=80" alt="David Wilson">
                    <h3>David Wilson</h3>
                    <p>Customer Care Manager</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission Section -->
    <section class="mission-section">
        <div class="container">
            <div class="mission-content">
                <h2>Our Mission</h2>
                <p>To revolutionize healthcare accessibility by providing a seamless, secure, and personalized pharmacy experience that puts our customers' health and convenience first. We believe everyone deserves access to quality medications and professional pharmaceutical care, regardless of location or mobility.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
     <?php include '../includes/footer.php'
     ?>
   
</body>
</html>