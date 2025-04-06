<?php
include '../includes/config.php';

// Initialize variables
$error = $success = '';
$category = null;

// Get category ID
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch category data
if($category_id > 0) {
    $sql = "SELECT * FROM categories WHERE categories_id = $category_id";
    $result = mysqli_query($conn, $sql);
    
    if($result && mysqli_num_rows($result) > 0) {
        $category = mysqli_fetch_assoc($result);
    } else {
        $error = "Category not found.";
        header("Location: categoriesupdate.php");
        exit;
    }
} else {
    header("Location: categoriesupdate.php");
    exit;
}

// Process form submission
if(isset($_POST['update'])) {
    // Sanitize and validate inputs
    $categories_name = mysqli_real_escape_string($conn, trim($_POST['categories_name']));
    $categories_description = mysqli_real_escape_string($conn, trim($_POST['categories_description']));
    $categories_slug = mysqli_real_escape_string($conn, trim($_POST['categories_slug']));
    
    // Validate inputs
    if(empty($categories_name) || empty($categories_slug)) {
        $error = "Category name and slug are required fields.";
    } else {
        // Check if slug already exists (excluding current category)
        $check_sql = "SELECT categories_id FROM categories WHERE categories_slug = '$categories_slug' AND categories_id != $category_id";
        $check_result = mysqli_query($conn, $check_sql);
        
        if(mysqli_num_rows($check_result) > 0) {
            $error = "This slug is already in use by another category. Please choose a different one.";
        } else {
            // Update category
            $update_sql = "UPDATE categories SET 
                          categories_name = '$categories_name',
                          categories_description = '$categories_description',
                          categories_slug = '$categories_slug'
                          WHERE categories_id = $category_id";
            
            if(mysqli_query($conn, $update_sql)) {
                $success = "Category updated successfully!";
                // Refresh category data
                $result = mysqli_query($conn, $sql);
                $category = mysqli_fetch_assoc($result);
            } else {
                $error = "Error updating category: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category - Pharmacy Admin</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .category-form {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-heading {
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
            <div class="category-form">
                <h2 class="form-heading">Edit Category</h2>
                
                <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if($category): ?>
                <form action="" method="post">
                    <div class="mb-3">
                        <label for="categories_name" class="form-label">Category Name *</label>
                        <input type="text" class="form-control" name="categories_name" id="categories_name" 
                               value="<?php echo htmlspecialchars($category['categories_name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="categories_description" class="form-label">Description</label>
                        <textarea class="form-control" name="categories_description" id="categories_description" 
                                  rows="3"><?php echo htmlspecialchars($category['categories_description']); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="categories_slug" class="form-label">Slug *</label>
                        <input type="text" class="form-control" name="categories_slug" id="categories_slug" 
                               value="<?php echo htmlspecialchars($category['categories_slug']); ?>" required>
                        <small class="text-muted">This will be used in the URL (e.g., 'health-tips')</small>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="submit" name="update" class="btn btn-primary">Update Category</button>
                        <a href="categoriesupdate.php" class="btn btn-outline-secondary">Back to Categories</a>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </main>
</section>

<script src="../assets/js/dashboard_style.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Auto-generate slug from category name
    document.getElementById('categories_name').addEventListener('input', function() {
        const slugInput = document.getElementById('categories_slug');
        if(!slugInput.value || slugInput.value === '<?php echo $category['categories_slug']; ?>') {
            const slug = this.value.toLowerCase()
                .replace(/\s+/g, '-')     // Replace spaces with -
                .replace(/[^\w\-]+/g, '')  // Remove all non-word chars
                .replace(/\-\-+/g, '-')    // Replace multiple - with single -
                .replace(/^-+/, '')        // Trim - from start of text
                .replace(/-+$/, '');       // Trim - from end of text
            slugInput.value = slug;
        }
    });
</script>
</body>
</html>