<?php
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'pharmacare';
$port=4308;
$conn = new mysqli($host, $username, $password, $dbname, $port);
if($conn){
    // echo "Connected to database";
}



