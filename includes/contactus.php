<?php
session_start();
include "../includes/config.php"; // make sure this sets up $conn as your DB connection

$success_msg = $error_msg = "";
$name = $email = $phone = $subject = $message = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize inputs
    $name = mysqli_real_escape_string($conn, trim($_POST["name"]));
    $email = mysqli_real_escape_string($conn, trim($_POST["email"]));
    $phone = mysqli_real_escape_string($conn, trim($_POST["phone"]));
    $subject = mysqli_real_escape_string($conn, trim($_POST["subject"]));
    $message = mysqli_real_escape_string($conn, trim($_POST["message"]));

    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_msg = "कृपया सबै आवश्यक जानकारीहरू भर्नुहोस्।";
    } else {
        // Insert into database
        $sql = "INSERT INTO contact_messages (name, email, phone, subject, message)
                VALUES ('$name', '$email', '$phone', '$subject', '$message')";

        if (mysqli_query($conn, $sql)) {
            $success_msg = "तपाईंको सन्देश सफलतापूर्वक पठाइयो। धन्यवाद!";
            // Clear form data after success
            $name = $email = $phone = $subject = $message = "";
        } else {
            $error_msg = "सन्देश पठाउन त्रुटि भयो: " . mysqli_error($conn);
        }
    }
}
?>




<!DOCTYPE html>
<html lang="ne">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>सम्पर्क गर्नुहोस् - फार्माकेयर</title>
    <style>
        body {
            font-family: 'Arial', 'Poppins', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            background-color: #4CAF50;
            color: white;
            padding: 20px 0;
            text-align: center;
        }
        .contact-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-top: 30px;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .contact-info, .contact-form {
            flex: 1;
            min-width: 300px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        textarea {
            height: 150px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px 20px;
            cursor: pointer;
            font-size: 16px;
            border-radius: 4px;
            width: 100%;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .alert-error {
            background-color: #f2dede;
            color: #a94442;
        }
        .map-container {
            height: 300px;
            width: 100%;
            margin-top: 15px;
            border-radius: 8px;
            overflow: hidden;
        }
        footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            background-color: #333;
            color: white;
        }
        @media (max-width: 768px) {
            .contact-container {
                flex-direction: column;
            }
        }
        .required:after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>फार्माकेयरसँग सम्पर्क गर्नुहोस्</h1>
            <p>हामी तपाईंको स्वास्थ्य सेवामा सहयोग गर्न तयार छौं</p>
        </div>
    </header>
    
    <div class="container">
        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-error"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        
        <div class="contact-container">
            <div class="contact-info">
                <h2>हाम्रो स्थान</h2>
                <p>इटहरी-४, सुनसरी, नेपाल</p>
                
                <div class="map-container">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3561.105620396233!2d87.2740073150426!3d26.79460898317501!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39ef41d9a4c1e8b9%3A0x6a5d3e7b4e4e4e4e!2sItahari!5e0!3m2!1sen!2snp!4v1620000000000!5m2!1sen!2snp" 
                            allowfullscreen loading="lazy"></iframe>
                </div>
                
                <h3>सम्पर्क जानकारी</h3>
                <p><strong>फोन:</strong> +९७७ ९८०-१२३४५६७</p>
                <p><strong>इमेल:</strong> contact@pharmacare.com.np</p>
                
                <h3>कार्य समय</h3>
                <p><strong>आइतबार-शुक्रबार:</strong> ८:०० बिहान - ८:०० बेलुका</p>
                <p><strong>शनिबार:</strong> ९:०० बिहान - ६:०० बेलुका</p>
            </div>
            
            <div class="contact-form">
                <h2>हामीलाई सन्देश पठाउनुहोस्</h2>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-group">
                        <label for="name" class="required">पुरा नाम</label>
                        <input type="text" id="name" name="name" value="<?php echo $name; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="required">इमेल ठेगाना</label>
                        <input type="email" id="email" name="email" value="<?php echo $email; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">फोन नम्बर</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo $phone; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="subject" class="required">विषय</label>
                        <select id="subject" name="subject" required>
                            <option value="">विषय छान्नुहोस्</option>
                            <option value="सामान्य जानकारी">सामान्य जानकारी</option>
                            <option value="प्रिस्क्रिप्सन सम्बन्धी प्रश्न">प्रिस्क्रिप्सन सम्बन्धी प्रश्न</option>
                            <option value="अर्डर स्थिति">अर्डर स्थिति</option>
                            <option value="उत्पादन उपलब्धता">उत्पादन उपलब्धता</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message" class="required">सन्देश</label>
                        <textarea id="message" name="message" required><?php echo $message; ?></textarea>
                    </div>
                    
                    <button type="submit">सन्देश पठाउनुहोस्</button>
                </form>
            </div>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <a href="../frontend/Homepage.php">Homepage</a>
            <p>&copy; <?php echo date("Y"); ?> फार्माकेयर इटहरी। सर्वाधिकार सुरक्षित।</p>
        </div>
    </footer>
</body>
</html> -->