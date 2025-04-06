<?php
session_start();
require_once '../includes/config.php';

// Verify admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

// Check if file was uploaded
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../uploads/posts/';
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $fileType = $_FILES['file']['type'];
    
    // Validate file type
    if (!in_array($fileType, $allowedTypes)) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'Invalid file type']);
        exit;
    }
    
    // Validate image
    if (!getimagesize($_FILES['file']['tmp_name'])) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'Invalid image file']);
        exit;
    }
    
    // Generate unique filename
    $filename = uniqid() . '_' . basename($_FILES['file']['name']);
    $targetPath = $uploadDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
        echo json_encode([
            'location' => BASE_URL . '../uploads/posts/' . $filename
        ]);
    } else {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => 'Failed to upload file']);
    }
} else {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'No file uploaded']);
}
?>