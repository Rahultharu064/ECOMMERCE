<?php
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'ecommerce';
$port=3306;
$conn = new mysqli($host, $username, $password, $dbname, $port);
if($conn){
    echo "Connected to database";
}
