<?php
session_start();
include '../includes/config.php';

// Check if user is admin/pharmacist
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'pharmacist') {
    header("Location: login.php");
    exit();
}

// Function to create slug with improved security
function createSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    
    return empty($text) ? 'n-a' : $text;
}

// Get categories for dropdown
$categoriesQuery = "SELECT * FROM categories ORDER BY categories_name";
$categoriesResult = mysqli_query($conn, $categoriesQuery);
$categories = mysqli_fetch_all($categoriesResult, MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $title = isset($_POST['title']) ? mysqli_real_escape_string($conn, trim($_POST['title'])) : '';
    $slug = createSlug($title);
    $content = isset($_POST['content']) ? mysqli_real_escape_string($conn, trim($_POST['content'])) : '';
    $excerpt = isset($_POST['excerpt']) ? mysqli_real_escape_string($conn, trim($_POST['excerpt'])) : '';
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $author_id = $_SESSION['user_id'];
    $meta_title = isset($_POST['meta_title']) ? mysqli_real_escape_string($conn, trim($_POST['meta_title'])) : '';
    $meta_description = isset($_POST['meta_description']) ? mysqli_real_escape_string($conn, trim($_POST['meta_description'])) : '';
    $published = isset($_POST['published']) ? 1 : 0;
    $views = 0;
    $current_time = date('Y-m-d H:i:s');

    // Validate required fields
    if (empty($title) || empty($content) || empty($excerpt) || $category_id <= 0) {
        $_SESSION['error'] = "Please fill all required fields";
        header("Location: create-post.php");
        exit();
    }

    // Handle image upload with security checks
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/posts/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $file_name = $_FILES['image']['name'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_size = $_FILES['image']['size'];
        $file_error = $_FILES['image']['error'];

        // Get file extension
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Allowed extensions
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($file_ext, $allowed)) {
            if ($file_error === 0) {
                if ($file_size <= 2097152) { // 2MB limit
                    $file_name_new = uniqid('', true) . '.' . $file_ext;
                    $file_destination = $uploadDir . $file_name_new;

                    if (move_uploaded_file($file_tmp, $file_destination)) {
                        $image = $file_name_new;
                    } else {
                        $_SESSION['error'] = "Error uploading file";
                    }
                } else {
                    $_SESSION['error'] = "File size too large (max 2MB)";
                }
            } else {
                $_SESSION['error'] = "Error uploading file";
            }
        } else {
            $_SESSION['error'] = "Invalid file type. Only JPG, PNG, GIF, and WEBP allowed";
        }
    }

    // Insert post if no errors
    if (!isset($_SESSION['error'])) {
        $query = "INSERT INTO posts (
                    title, slug, content, excerpt, image, 
                    author_id, category_id, views, published, 
                    meta_title, meta_description, created_at, updated_at
                  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param(
            $stmt, 
            "sssssiiiissss", 
            $title, $slug, $content, $excerpt, $image, 
            $author_id, $category_id, $views, $published, 
            $meta_title, $meta_description, $current_time, $current_time
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Post created successfully!";
            header("Location: post.php?slug=" . urlencode($slug));
            exit();
        } else {
            $_SESSION['error'] = "Error creating post: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Post | PharmaCare</title>
    <!-- Include your CSS files -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <!-- Include secure CKEditor -->
    <script src="https://cdn.ckeditor.com/4.25.1-lts/standard-all/ckeditor.js"></script>
    <style>
        .editor-container {
            margin-bottom: 20px;
        }
        .form-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .sidebar {
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <?php include '../Dasboard/navbar.php'; ?>
    <?php include '../Dasboard/sidebar.php'; ?>
   

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 d-md-block sidebar">
                <!-- Sidebar content -->
                
            </div>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="form-container mt-5">
                    <h2 class="mb-4">Create New Blog Post</h2>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Post Title *</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="category_id" class="form-label">Category *</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Select a category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo intval($category['id']); ?>">
                                            <?php echo htmlspecialchars($category['categories_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="image" class="form-label">Featured Image</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                                <small class="text-muted">Max size: 2MB. Allowed types: JPG, PNG, GIF, WEBP</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="excerpt" class="form-label">Excerpt (Short Description) *</label>
                            <textarea class="form-control" id="excerpt" name="excerpt" rows="3" maxlength="160" required></textarea>
                            <small class="text-muted">Max 160 characters - shown in post previews</small>
                        </div>

                        <div class="editor-container mb-3">
                            <label for="content" class="form-label">Content *</label>
                            <textarea class="form-control" id="content" name="content" required></textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="meta_title" class="form-label">Meta Title (SEO)</label>
                                <input type="text" class="form-control" id="meta_title" name="meta_title" maxlength="60">
                                <small class="text-muted">Recommended: 50-60 characters</small>
                            </div>
                            <div class="col-md-6">
                                <label for="meta_description" class="form-label">Meta Description (SEO)</label>
                                <textarea class="form-control" id="meta_description" name="meta_description" rows="2" maxlength="160"></textarea>
                                <small class="text-muted">Recommended: 150-160 characters</small>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="published" name="published" checked>
                            <label class="form-check-label" for="published">Publish Immediately</label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Create Post</button>
                            <a href="blog.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/jquery-3.6.0.min.js"></script>

    <script>
    // Initialize CKEditor with secure configuration
    CKEDITOR.replace('content', {
        height: 400,
        // Use the standard-all package features
        extraPlugins: 'autogrow',
        autoGrow_minHeight: 200,
        autoGrow_maxHeight: 600,
        autoGrow_bottomSpace: 50,
        // Security-related configurations
        allowedContent: false, // Don't allow all HTML
        disallowedContent: 'script; *[on*]', // Disallow scripts and on* attributes
        extraAllowedContent: 'img[alt,!src]{width,height}; a[!href,target]',
        // Protected source (prevent script injection)
        protectedSource: [
            /<script[\s\S]*?<\/script>/gi,
            /<noscript[\s\S]*?<\/noscript>/gi,
            /<style[\s\S]*?<\/style>/gi
        ],
        // File upload configuration
        filebrowserUploadUrl: '../includes/secure_ckeditor_upload.php',
        filebrowserUploadMethod: 'form',
        // Content Security Policy
        contentsCss: ['../assets/css/bootstrap.min.css', '../assets/css/dashboard.css'],
        // Basic toolbar configuration
        toolbar: [
            { name: 'document', items: [ 'Source', '-', 'Save', 'NewPage', 'Preview', 'Print' ] },
            { name: 'clipboard', items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
            { name: 'editing', items: [ 'Find', 'Replace', '-', 'SelectAll', '-', 'Scayt' ] },
            { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'CopyFormatting', 'RemoveFormat' ] },
            { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
            { name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
            { name: 'insert', items: [ 'Image', 'Table', 'HorizontalRule', 'SpecialChar' ] },
            { name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
            { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
            { name: 'tools', items: [ 'Maximize', 'ShowBlocks' ] }
        ],
        // Prevent XSS
        htmlEncodeOutput: true,
        entities: true,
        basicEntities: true
    });

    // Auto-generate slug when title changes
    document.getElementById('title').addEventListener('blur', function() {
        const title = this.value.trim();
        if (title) {
            // This is just for display - the actual slug is generated server-side
            const slugPreview = title.toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
            console.log('Slug preview:', slugPreview);
        }
    });

    // Auto-fill meta title if empty
    document.getElementById('title').addEventListener('blur', function() {
        const metaTitle = document.getElementById('meta_title');
        if (!metaTitle.value && this.value) {
            metaTitle.value = this.value.substring(0, 60);
        }
    });

    // Auto-fill meta description if empty
    document.getElementById('excerpt').addEventListener('blur', function() {
        const metaDesc = document.getElementById('meta_description');
        if (!metaDesc.value && this.value) {
            metaDesc.value = this.value.substring(0, 160);
        }
    });

    // Validate file size before upload
    document.getElementById('image').addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const maxSize = 2 * 1024 * 1024; // 2MB
            if (file.size > maxSize) {
                alert('File size exceeds 2MB limit. Please choose a smaller file.');
                this.value = '';
            }
        }
    });
    </script>
</body>
</html>