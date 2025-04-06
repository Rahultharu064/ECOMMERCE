<?php
session_start();
include "../includes/config.php";

// Check if admin is logged in
// if (!isset($_SESSION['user_email']) || $_SESSION['user_role'] !== 'admin') {
//     header("Location: ../frontend/Homepage.php");
//     exit();
// }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Check if email already exists
    $checkEmail = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $checkEmail);
    
    if (mysqli_num_rows($result) > 0) {
        echo "<script>alert('Email already exists!'); window.history.back();</script>";
        exit();
    }

    // Insert new user
    $sql = "INSERT INTO users (name, email, password, role, status) 
            VALUES ('$name', '$email', '$password', '$role', '$status')";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('User added successfully!'); window.location.href='admin_dashboard.php';</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "'); window.history.back();</script>";
    }
}
?>