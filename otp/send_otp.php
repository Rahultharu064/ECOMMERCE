<?php
require '../includes/config.php';
require '../vendor/autoload.php'; // Path to Composer's autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format");
    }

    // Generate 6-digit OTP
    $otp = random_int(100000, 999999);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    // Clear previous OTPs for this email
    if (!$conn->query("DELETE FROM otps1 WHERE email1 = '$email'")) {
        die("Error clearing old OTPs: " . $conn->error);
    }

    // Insert new OTP
    $sql = "INSERT INTO otps1 (email1, otp_code, expires_at) 
            VALUES ('$email', '$otp', '$expiresAt')";

    if ($conn->query($sql)) {
        // Send email using PHPMailer
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtpUsername;
            $mail->Password   = $smtpPassword;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use constant
            $mail->Port       = $smtpPort;

            // Recipients
            $mail->setFrom($smtpUsername, 'PharmaCare');
            $mail->addAddress($email);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Your PharmaCare Login OTP';
            $mail->Body    = "
                <h3>Your PharmaCare OTP</h3>
                <p>Your one-time password is: <strong>$otp</strong></p>
                <p>Valid for 5 minutes</p>
                <p><small>If you didn't request this, please ignore this email.</small></p>
            ";

            $mail->send();
            $_SESSION['email'] = $email;
            header("Location: enter_otp.php");
            exit();
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
            die("We couldn't send the OTP. Please try again later.");
        }
    } else {
        die("Error storing OTP: " . $conn->error);
    }
}
?>