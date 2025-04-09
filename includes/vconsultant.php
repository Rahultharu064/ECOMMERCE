<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtual Doctor Consultation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="../assets/css/pharmacy.css" rel="stylesheet">
    <link href="../assets/frontendcss/vconsultant.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <section class="container">
        <div class="consultation-banner">
            <div class="consultation-content">
                <h2 style="color: #2065d1; font-weight: bold;">Virtual Doctor Consultation</h2>
                <p style="font-size: 1.1rem; color: #555;">Connect with certified physicians 24/7</p>
                <ul class="consultation-features">
                    <li><i class="fas fa-video" style="color: #2065d1;"></i> Video Consultations</li>
                    <li><i class="fas fa-prescription" style="color: #2065d1;"></i> E-Prescriptions</li>
                    <li><i class="fas fa-clock" style="color: #2065d1;"></i> Instant Appointments</li>
                    <li><i class="fas fa-comments" style="color: #2065d1;"></i> Live Chat with Doctors</li>
                    <li><i class="fas fa-bell" style="color: #2065d1;"></i> Medication Reminders</li>
                    <li><i class="fas fa-heartbeat" style="color: #2065d1;"></i> Personalized Health Tips</li>
                </ul>
                <button class="consult-button" onclick="window.location.href='../includes/book.php'">Book Now</button>
                <button class="action-button" onclick="window.location.href='chatwith.php'"><i class="fas fa-comments"></i> Live Chat</button>
                <button class="action-button" onclick="window.location.href='set_reminder.php"><i class="fas fa-bell"></i> Set Reminder</button>
            </div>
            <div class="consultation-image">
                <div class="image-overlay"></div>
            </div>
        </div>
    </section>

  
   

    

    <?php include 'footer.php'; ?>
</body>
</html>