<?php
session_start();
require '../includes/config.php';

// Debugging: Log POST data
error_log("POST Data: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Combine the individual OTP digits
    $otp = '';
    for ($i = 1; $i <= 6; $i++) {
        if (isset($_POST['otp'.$i]) && !empty($_POST['otp'.$i])) {
            $otp .= $conn->real_escape_string($_POST['otp'.$i]);
        } else {
            // If any OTP digit is missing
            $_SESSION['error'] = "Please enter all 6 digits of the OTP";
            header("Location: enter_otp.php");
            exit();
        }
    }

    // Debugging: Log combined OTP
    error_log("Combined OTP: " . $otp);

    if (!isset($_SESSION['email'])) {
        $_SESSION['error'] = "Session expired. Please request a new OTP.";
        header("Location: enter_otp.php");
        exit();
    }

    $email = $_SESSION['email'];
    $currentTime = date('Y-m-d H:i:s');
    
    // Debugging: Log query details
    error_log("Email: $email, Current Time: $currentTime");
    
    // Check OTP in database
    $stmt = $conn->prepare("SELECT * FROM otps1 WHERE email1 = ? AND otp_code = ? AND expires_at > ?");
    $stmt->bind_param("sss", $email, $otp, $currentTime);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Debugging: Log query results
    error_log("Query matched rows: " . $result->num_rows);

    if ($result->num_rows > 0) {
        // Valid OTP
        $_SESSION['loggedin'] = true;
        
        // Clear used OTP
        $deleteStmt = $conn->prepare("DELETE FROM otps1 WHERE email1 = ?");
        $deleteStmt->bind_param("s", $email);
        $deleteStmt->execute();
        
        // Debugging: Log successful login
        error_log("OTP verified successfully for $email");
        
        header("Location: ../Dasboard/dasboard.php");
        exit();
    } else {
        // Debugging: Log verification failure
        error_log("OTP verification failed for $email");
        
        // Check if OTP exists but expired
        $expiredCheck = $conn->prepare("SELECT * FROM otps1 WHERE email1 = ? AND otp_code = ?");
        $expiredCheck->bind_param("ss", $email, $otp);
        $expiredCheck->execute();
        
        if ($expiredCheck->get_result()->num_rows > 0) {
            $_SESSION['error'] = "OTP has expired. Please request a new one.";
        } else {
            $_SESSION['error'] = "Invalid OTP. Please try again.";
        }
        
        header("Location: enter_otp.php");
        exit();
    }
}
?>