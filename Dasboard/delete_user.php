<?php
session_start();
include "../includes/config.php";

// Check if admin is logged in
if (!isset($_SESSION['user_email']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../frontend/Homepage.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Prevent admin from deleting themselves
    $checkUser = "SELECT email FROM users WHERE id = '$id'";
    $result = mysqli_query($conn, $checkUser);
    $user = mysqli_fetch_assoc($result);
    
    if ($user['email'] === $_SESSION['user_email']) {
        echo "<script>alert('You cannot delete your own account!'); window.location.href='admin_dashboard.php';</script>";
        exit();
    }
    
    // Delete user
    $sql = "DELETE FROM users WHERE id = '$id'";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('User deleted successfully!'); window.location.href='admin_dashboard.php';</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "'); window.location.href='admin_dashboard.php';</script>";
    }
}
?>