<?php
session_start();
include '../includes/config.php';

// Check if user is admin/pharmacist
// if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'pharmacist') {
//     header("Location: login.php");
//     exit();
// }

// Handle post deletion
if (isset($_GET['delete'])) {
    $post_id = intval($_GET['delete']);
    
    // First, get the image path to delete it from server
    $query = "SELECT image FROM posts WHERE id = $post_id";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        $post = mysqli_fetch_assoc($result);
        
        if ($post && !empty($post['image'])) {
            $image_path = "../uploads/posts/" . $post['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        // Then delete the post
        $query = "DELETE FROM posts WHERE id = $post_id";
        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = "Post deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting post: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = "Error fetching post: " . mysqli_error($conn);
    }
    
    header("Location: manage-posts.php");
    exit();
}

// Handle bulk actions
if (isset($_POST['bulk_action'])) {
    if (!empty($_POST['selected_posts'])) {
        $post_ids = implode(",", array_map('intval', $_POST['selected_posts']));
        
        switch ($_POST['bulk_action']) {
            case 'delete':
                // First get images to delete from server
                $query = "SELECT image FROM posts WHERE id IN ($post_ids)";
                $result = mysqli_query($conn, $query);
                
                if ($result) {
                    while ($post = mysqli_fetch_assoc($result)) {
                        if (!empty($post['image'])) {
                            $image_path = "../uploads/posts/" . $post['image'];
                            if (file_exists($image_path)) {
                                unlink($image_path);
                            }
                        }
                    }
                    
                    // Then delete the posts
                    $query = "DELETE FROM posts WHERE id IN ($post_ids)";
                    if (mysqli_query($conn, $query)) {
                        $_SESSION['success'] = "Selected posts deleted successfully!";
                    } else {
                        $_SESSION['error'] = "Error deleting posts: " . mysqli_error($conn);
                    }
                } else {
                    $_SESSION['error'] = "Error fetching posts: " . mysqli_error($conn);
                }
                break;
                
            case 'publish':
                $query = "UPDATE posts SET published = 1 WHERE id IN ($post_ids)";
                if (mysqli_query($conn, $query)) {
                    $_SESSION['success'] = "Selected posts published successfully!";
                } else {
                    $_SESSION['error'] = "Error publishing posts: " . mysqli_error($conn);
                }
                break;
                
            case 'unpublish':
                $query = "UPDATE posts SET published = 0 WHERE id IN ($post_ids)";
                if (mysqli_query($conn, $query)) {
                    $_SESSION['success'] = "Selected posts unpublished successfully!";
                } else {
                    $_SESSION['error'] = "Error unpublishing posts: " . mysqli_error($conn);
                }
                break;
        }
    }
    
    header("Location: manage-posts.php");
    exit();
}

// Get all posts with author and category information
$query = "SELECT p.*, u.name as author_name, c.categories_name as category_name 
          FROM posts p
          LEFT JOIN users u ON p.author_id = u.id
          LEFT JOIN categories c ON p.category_id = c.id
          ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $query);

// Check if query was successful
if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}

$posts = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_free_result($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Posts | PharmaCare</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        /* Paper-inspired Design */
        body {
            background-color: #f9f9f7;
            color: #333;
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
        }
        
        .paper-container {
            background-color: white;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
            padding: 2rem;
            margin: 2rem auto;
            max-width: 1200px;
        }
        
        .page-header {
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title {
            font-weight: 400;
            color: #444;
            margin: 0;
        }
        
        /* Table Styling */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
            font-size: 0.9rem;
        }
        
        .table th {
            background-color: #f5f5f5;
            color: #555;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 0.75rem 1rem;
            border-bottom: 2px solid #e0e0e0;
            text-align: left;
        }
        
        .table td {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }
        
        .table tr:hover td {
            background-color: #fafafa;
        }
        
        /* Status Badges */
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-weight: 500;
            display: inline-block;
        }
        
        .published {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .draft {
            background-color: #fff8e1;
            color: #ff8f00;
        }
        
        /* Image Thumbnails */
        .post-image-thumb {
            width: 60px;
            height: 40px;
            object-fit: cover;
            border-radius: 2px;
            border: 1px solid #eee;
        }
        
        /* Action Buttons */
        .btn-action {
            padding: 0.375rem 0.75rem;
            border-radius: 3px;
            font-size: 0.8rem;
            margin-right: 0.25rem;
        }
        
        /* Bulk Actions */
        .bulk-actions {
            background-color: #f5f5f5;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .bulk-actions select {
            width: 180px;
            margin-right: 0.5rem;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #777;
        }
        
        .empty-state i {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #ddd;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .paper-container {
                padding: 1rem;
            }
            
            .table-responsive {
                border: 0;
            }
            
            .table thead {
                display: none;
            }
            
            .table tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid #eee;
                border-radius: 4px;
            }
            
            .table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-bottom: 1px solid #f5f5f5;
            }
            
            .table td:before {
                content: attr(data-label);
                font-weight: 500;
                color: #666;
                margin-right: 1rem;
            }
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
                <div class="paper-container">
                    <div class="page-header">
                        <h1 class="page-title">Manage Posts</h1>
                        <a href="create-post.php" class="btn btn-sm btn-primary">
                            <i class="fa fa-plus"></i> New Post
                        </a>
                    </div>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
                    <?php endif; ?>

                    <form method="post" action="manage-posts.php" id="bulk-action-form">
                        <div class="bulk-actions">
                            <select name="bulk_action" class="form-select form-select-sm" required>
                                <option value="">Bulk Actions</option>
                                <option value="publish">Publish</option>
                                <option value="unpublish">Unpublish</option>
                                <option value="delete">Delete</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-outline-secondary">Apply</button>
                        </div>

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="select-all"></th>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Category</th>
                                        <th>Image</th>
                                        <th>Views</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($posts)): ?>
                                        <?php foreach ($posts as $post): ?>
                                            <tr>
                                                <td data-label="Select"><input type="checkbox" name="selected_posts[]" value="<?php echo $post['id']; ?>"></td>
                                                <td data-label="Title">
                                                    <a href="post.php?slug=<?php echo urlencode($post['slug']); ?>" target="_blank">
                                                        <?php echo htmlspecialchars($post['title']); ?>
                                                    </a>
                                                </td>
                                                <td data-label="Author"><?php echo htmlspecialchars($post['author_name']); ?></td>
                                                <td data-label="Category"><?php echo htmlspecialchars($post['category_name']); ?></td>
                                                <td data-label="Image">
                                                    <?php if (!empty($post['image'])): ?>
                                                        <img src="../uploads/posts/<?php echo htmlspecialchars($post['image']); ?>" 
                                                             alt="Post thumbnail" class="post-image-thumb">
                                                    <?php endif; ?>
                                                </td>
                                                <td data-label="Views"><?php echo intval($post['views']); ?></td>
                                                <td data-label="Status">
                                                    <span class="status-badge <?php echo $post['published'] ? 'published' : 'draft'; ?>">
                                                        <?php echo $post['published'] ? 'Published' : 'Draft'; ?>
                                                    </span>
                                                </td>
                                                <td data-label="Date"><?php echo date('M j, Y', strtotime($post['created_at'])); ?></td>
                                                <td data-label="Actions">
                                                    <a href="edit-post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-action btn-outline-primary" title="Edit">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    <a href="manage-posts.php?delete=<?php echo $post['id']; ?>" 
                                                       class="btn btn-sm btn-action btn-outline-danger" 
                                                       title="Delete"
                                                       onclick="return confirm('Are you sure you want to delete this post?');">
                                                        <i class="fa fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="empty-state">
                                                <i class="fa fa-file-text-o"></i>
                                                <h4>No posts found</h4>
                                                <p>Create your first post to get started</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/jquery-3.6.0.min.js"></script>

    <script>
    // Toggle select all checkboxes
    document.getElementById('select-all').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('input[name="selected_posts[]"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Confirm before bulk actions
    document.getElementById('bulk-action-form').addEventListener('submit', function(e) {
        const action = this.elements['bulk_action'].value;
        const checked = document.querySelectorAll('input[name="selected_posts[]"]:checked').length;
        
        if (!action) {
            e.preventDefault();
            alert('Please select a bulk action');
            return false;
        }
        
        if (checked === 0) {
            e.preventDefault();
            alert('Please select at least one post');
            return false;
        }
        
        if (action === 'delete') {
            if (!confirm(`Are you sure you want to delete ${checked} post(s)? This cannot be undone.`)) {
                e.preventDefault();
                return false;
            }
        }
    });
    </script>
</body>
</html>