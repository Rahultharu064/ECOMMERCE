<?php
session_start();
include '../includes/config.php';

// Check if user is logged in as admin
// if (!isset($_SESSION['admin_logged_in']) {
//     header('Location: admin_login.php');
//     exit;
// }

// Database connection
require_once '../includes/config.php';
$db = new mysqli($host, $username, $password, $dbname, $port);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Fetch categories for dropdown
$catego_query = "SELECT * FROM cat";
$catego_result = $db->query($catego_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $db->real_escape_string($_POST['title']);
    $excerpt = $db->real_escape_string($_POST['excerpt']);
    $content = $db->real_escape_string($_POST['content']);
    $catego_id = intval($_POST['catego_id']);
    $author = $db->real_escape_string($_POST['author']);
    $publish_date = $db->real_escape_string($_POST['publish_date']);
    $read_time = intval($_POST['read_time']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    
    // Handle image upload
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../Dasboard/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $image_url = $target_path;
        }
    }
    
    // Insert into database
    $insert_query = "INSERT INTO art (title, excerpt, content, image_url, catego_id, author, publish_date, read_time, is_featured, is_new) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($insert_query);
    $stmt->bind_param("ssssissiii", $title, $excerpt, $content, $image_url, $catego_id, $author, $publish_date, $read_time, $is_featured, $is_new);
    
    if ($stmt->execute()) {
        $success_message = "Blog article created successfully!";
        header('Location: ../frontend/blog.php');
        exit;
    } else {
        $error_message = "Error creating blog article: " . $db->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Blog Article</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-control, .form-select {
            padding: 10px;
            border-radius: 4px;
        }
        .btn-submit {
            background-color: #0077b6;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-submit:hover {
            background-color: #023e8a;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2 class="mb-4">Create New Blog Article</h2>
            
            <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <?= $success_message ?>
                <a href="create_blog.php" class="float-end">Create Another</a>
            </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
            <?php endif; ?>
            
            <form action="create_blog.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="excerpt">Excerpt (Short Description)</label>
                    <textarea id="excerpt" name="excerpt" class="form-control" rows="3" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea id="content" name="content" class="form-control" rows="10" required></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="catego_id">Category</label>
                            <select id="catego_id" name="catego_id" class="form-select" required>
                                <option value="">Select a category</option>
                                <?php while($catego = $catego_result->fetch_assoc()): ?>
                                <option value="<?= $catego['id'] ?>"><?= $catego['name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="author">Author</label>
                            <input type="text" id="author" name="author" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="publish_date">Publish Date</label>
                            <input type="date" id="publish_date" name="publish_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="read_time">Read Time (minutes)</label>
                            <input type="number" id="read_time" name="read_time" class="form-control" min="1" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="image">Featured Image</label>
                            <input type="file" id="image" name="image" class="form-control" accept="image/*" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group form-check">
                    <input type="checkbox" id="is_featured" name="is_featured" class="form-check-input">
                    <label for="is_featured" class="form-check-label">Featured Article</label>
                </div>
                
                <div class="form-group form-check">
                    <input type="checkbox" id="is_new" name="is_new" class="form-check-input">
                    <label for="is_new" class="form-check-label">Mark as New</label>
                </div>
                
                <button type="submit" class="btn btn-submit">Create Article</button>
                <a href="../Dasboard/admin_dasboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set default publish date to today
        document.getElementById('publish_date').valueAsDate = new Date();
        
        // Initialize rich text editor (you would replace this with your preferred editor)
        document.addEventListener('DOMContentLoaded', function() {
            // This would be replaced with actual editor initialization
            console.log('Rich text editor would be initialized here');
        });
    </script>
</body>
</html>