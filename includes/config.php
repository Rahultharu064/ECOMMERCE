<?php



require '../vendor/autoload.php';
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'pharmacare';
$port=4308;
$conn = new mysqli($host, $username, $password, $dbname, $port);
if($conn){
    // echo "Connected to database";
}


// PHPMailer Configuration
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$smtpHost = 'smtp.gmail.com';
$smtpUsername = 'rahultharu980893@gmail.com';
$smtpPassword = 'xmnmyfubslcpecek';
$smtpPort = 587;
$smtpEncryption = 'tls';
?>



