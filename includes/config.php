<?php
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'pharmacy-ecommerce';
$port=4307;
$conn = new mysqli($host, $username, $password, $dbname, $port);
if($conn){
    // echo "Connected to database";
}
// function truncateDescription($text, $maxLength = 100) {
//     if (mb_strlen($text) > $maxLength) {
//         $text = mb_substr($text, 0, $maxLength);
//         $lastSpace = mb_strrpos($text, ' ');
//         if ($lastSpace !== false) {
//             $text = mb_substr($text, 0, $lastSpace);
//         }
//         return $text . '...';
//     }
//     return $text;
// }