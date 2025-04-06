
<?php
session_start();
include '../includes/config.php';
$db = new mysqli($host, $username, $password, $dbname, $port);

// Check connection
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Get article ID
$article_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch article data
$article_query = "SELECT * FROM articles WHERE id = ?";
$stmt = $db->prepare($article_query);
$stmt->bind_param("i", $article_id);
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();

if (!$article) {
    $_SESSION['error_message'] = "Article not found";
    header('Location: manage_articles.php');
    exit;
}

// Fetch categories for dropdown
$catego_query = "SELECT * FROM catego ORDER BY name";
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
    
    // Handle image upload if new file was provided
    $image_url = $article['image_url'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/posts/';
        $file_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $target_path = $upload_dir . $file_name;
        
        // Check if image file is a actual image
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check !== false) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                // Delete old image if it exists
                if ($image_url && file_exists('../' . $image_url)) {
                    unlink('../' . $image_url);
                }
                $image_url = '../uploads/posts/' . $file_name;
            }
        }
    }
    
    // Update in database
    $update_query = "UPDATE articles SET 
                    title = ?, 
                    excerpt = ?, 
                    content = ?, 
                    image_url = ?, 
                    catego_id = ?, 
                    author = ?, 
                    publish_date = ?, 
                    read_time = ?, 
                    is_featured = ?, 
                    is_new = ? 
                    WHERE id = ?";
    $stmt = $db->prepare($update_query);
    $stmt->bind_param("ssssissiiii", 
        $title, $excerpt, $content, $image_url, $catego_id, 
        $author, $publish_date, $read_time, $is_featured, $is_new, 
        $article_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Article updated successfully!";
        header("Location: edit_blog.php?id=$article_id");
        exit;
    } else {
        $error_message = "Error updating article: " . $db->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Blog Article</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- TinyMCE CDN -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        .form-container {
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .image-preview {
            max-width: 100%;
            height: auto;
            margin-top: 10px;
            border: 1px solid #ddd;
            padding: 5px;
        }
        .tox-tinymce {
            border-radius: 4px !important;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'admin_nav.php'; ?>
    
    <div class="container">
        <div class="form-container">
            <h2 class="mb-4">Edit Blog Article</h2>
            
            <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success_message'] ?></div>
            <?php unset($_SESSION['success_message']); endif; ?>
            
            <form action="edit_blog.php?id=<?= $article_id ?>" method="POST" enctype="multipart/form-data">
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="form-group mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" id="title" name="title" class="form-control" value="<?= htmlspecialchars($article['title']) ?>" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="excerpt" class="form-label">Excerpt</label>
                            <textarea id="excerpt" name="excerpt" class="form-control" rows="3" required><?= htmlspecialchars($article['excerpt']) ?></textarea>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="content" class="form-label">Content</label>
                            <textarea id="content" name="content" class="form-control" rows="10"><?= htmlspecialchars($article['content']) ?></textarea>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="catego_id" class="form-label">Category</label>
                            <select id="catego_id" name="catego_id" class="form-select" required>
                                <?php while($catego = $catego_result->fetch_assoc()): ?>
                                <option value="<?= $catego['id'] ?>" <?= $catego['id'] == $article['catego_id'] ? 'selected' : '' ?>>
                                    <?= $catego['name'] ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="author" class="form-label">Author</label>
                            <input type="text" id="author" name="author" class="form-control" value="<?= htmlspecialchars($article['author']) ?>" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="publish_date" class="form-label">Publish Date</label>
                            <input type="date" id="publish_date" name="publish_date" class="form-control" 
                                   value="<?= date('Y-m-d', strtotime($article['publish_date'])) ?>" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="read_time" class="form-label">Read Time (minutes)</label>
                            <input type="number" id="read_time" name="read_time" class="form-control" 
                                   min="1" value="<?= $article['read_time'] ?>" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="image" class="form-label">Featured Image</label>
                            <input type="file" id="image" name="image" class="form-control" accept="image/*">
                            <?php if ($article['image_url']): ?>
                            <div class="mt-2">
                                <small>Current Image:</small>
                                <img src="<?= '../' . $article['image_url'] ?>" class="image-preview">
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input type="checkbox" id="is_featured" name="is_featured" class="form-check-input" <?= $article['is_featured'] ? 'checked' : '' ?>>
                            <label for="is_featured" class="form-check-label">Featured Article</label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input type="checkbox" id="is_new" name="is_new" class="form-check-input" <?= $article['is_new'] ? 'checked' : '' ?>>
                            <label for="is_new" class="form-check-label">Mark as New</label>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">Update Article</button>
                    <a href="delete_article.php?id=<?= $article_id ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this article?')">
                        Delete Article
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize TinyMCE editor with proper configuration
        tinymce.init({
            selector: '#content',
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview', 'anchor',
                'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | ' +
                'bold italic forecolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | help | image',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }',
            height: 500,
            image_title: true,
            automatic_uploads: true,
            file_picker_types: 'image',
            file_picker_callback: function(cb, value, meta) {
                var input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');
                
                input.onchange = function() {
                    var file = this.files[0];
                    var reader = new FileReader();
                    
                    reader.onload = function() {
                        var id = 'blobid' + (new Date()).getTime();
                        var blobCache = tinymce.activeEditor.editorUpload.blobCache;
                        var base64 = reader.result.split(',')[1];
                        var blobInfo = blobCache.create(id, file, base64);
                        blobCache.add(blobInfo);
                        
                        // call the callback and populate the Title field with the file name
                        cb(blobInfo.blobUri(), { title: file.name });
                    };
                    reader.readAsDataURL(file);
                };
                
                input.click();
            },
            setup: function(editor) {
                editor.on('change', function() {
                    editor.save();
                });
            }
        });

        // Preview image before upload
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    let preview = document.querySelector('.image-preview');
                    if (!preview) {
                        preview = document.createElement('img');
                        preview.className = 'image-preview';
                        document.querySelector('.form-group.mb-3').appendChild(preview);
                    }
                    preview.src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>