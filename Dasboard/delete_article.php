<?php
session_start();
include '../includes/config.php';

$db = new mysqli($host, $username, $password, $dbname, $port);
if ($db) {
    // echo "Connected to database";
}





// Check connection
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Get article ID
$article_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($article_id > 0) {
    // First get the image path to delete the file
    $image_query = "SELECT image_url FROM articles WHERE id = ?";
    $stmt = $db->prepare($image_query);
    $stmt->bind_param("i", $article_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $article = $result->fetch_assoc();
    
    // Delete from database
    $delete_query = "DELETE FROM articles WHERE id = ?";
    $stmt = $db->prepare($delete_query);
    $stmt->bind_param("i", $article_id);
    
    if ($stmt->execute()) {
        // Delete the image file if it exists
        if ($article && !empty($article['image_url']) && file_exists($article['image_url'])) {
            unlink($article['image_url']);
        }
        $_SESSION['success_message'] = "Article deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Error deleting article: " . $db->error;
    }
} else {
    $_SESSION['error_message'] = "Invalid article ID";
}

header('Location: manage_articles.php');
exit;
?>