<?php
session_start();
include "../includes/config.php";

// Check if admin is logged in
if (!isset($_SESSION['user_email']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../frontend/Homepage.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $province = mysqli_real_escape_string($conn, $_POST['province']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Check if email already exists for another user
    $checkEmail = "SELECT * FROM users WHERE email = '$email' AND id != '$id'";
    $result = mysqli_query($conn, $checkEmail);
    
    if (mysqli_num_rows($result) > 0) {
        echo "<script>alert('Email already exists for another user!'); window.history.back();</script>";
        exit();
    }

    // Update user
    $sql = "UPDATE users SET 
            name = '$name',
            email = '$email',
            phone = '$phone',
            gender = '$gender',
            dob = '$dob',
            address = '$address',
            province = '$province',
            city = '$city',
            role = '$role',
            status = '$status'
            WHERE id = '$id'";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('User updated successfully!'); window.location.href='admin_dashboard.php';</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "'); window.history.back();</script>";
    }
}
?>