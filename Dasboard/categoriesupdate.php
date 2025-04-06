<?php
include '../includes/config.php';

// Handle category deletion
if(isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Check if category is used in any posts
    $check_posts = mysqli_query($conn, "SELECT post_id FROM posts WHERE category_id = $delete_id");
    
    if(mysqli_num_rows($check_posts) > 0) {
        $error = "Cannot delete category because it is assigned to one or more posts.";
    } else {
        $delete_sql = "DELETE FROM categories WHERE categories_id = $delete_id";
        if(mysqli_query($conn, $delete_sql)) {
            $success = "Category deleted successfully!";
        } else {
            $error = "Error deleting category: " . mysqli_error($conn);
        }
    }
}

// Get all categories
$categories = [];
$sql = "SELECT * FROM categories ORDER BY categories_name";
$result = mysqli_query($conn, $sql);

if($result && mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Pharmacy Admin</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .category-table {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .table-actions {
            white-space: nowrap;
        }
        .table-actions a {
            margin: 0 3px;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>
<body>
<?php include '../Dasboard/Navbar.php'; ?>
    
<section style="display: flex; width: 100%">
    <?php include '../Dasboard/Sidebar.php'; ?>
    
    <main class="content">
        <div class="container-fluid">
            <div class="category-table">
                <div class="page-header">
                    <h2>Manage Categories</h2>
                    <a href="categoriesAdd.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Category
                    </a>
                </div>
                
                <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if(isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if(empty($categories)): ?>
                <div class="alert alert-info">No categories found. <a href="categories.php">Add your first category</a>.</div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Category Name</th>
                                <th>Description</th>
                                <th>Slug</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($categories as $category): ?>
                            <tr>
                                <td><?php echo $category['id']; ?></td>
                                <td><?php echo htmlspecialchars($category['categories_name']); ?></td>
                                <td><?php echo htmlspecialchars($category['categories_description']); ?></td>
                                <td><code><?php echo htmlspecialchars($category['categories_slug']); ?></code></td>
                                <td class="table-actions">
                                    <a href="categoriesedit.php?id=<?php echo $category['id']; ?>" 
                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="categoriesupdate.php?delete_id=<?php echo $category['id']; ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       title="Delete"
                                       onclick="return confirm('Are you sure you want to delete this category?');">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</section>

<script src="../assets/js/dashboard_style.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>