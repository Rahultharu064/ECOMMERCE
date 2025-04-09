<?php
session_start();
include '../includes/config.php';
$conn = new mysqli($host, $username, $password, $dbname, $port);
if($conn){
    // echo "Connected to database";
}




// Handle category deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Check if category is being used by any articles
    $check_query = "SELECT COUNT(*) FROM art WHERE catego_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_row()[0];
    
    if ($count > 0) {
        $_SESSION['error_message'] = "Cannot delete category - it is being used by $count article(s).";
    } else {
        $delete_query = "DELETE FROM cat WHERE id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Category deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Error deleting category: " . $db->error;
        }
    }
    header('Location: manage_catego.php');
    exit;
}

// Fetch all categories
$catego_query = "SELECT * FROM cat ORDER BY name ASC";
$catego_result = $conn->query($catego_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <?php include 'Navbar.php'; ?>
    <?php include 'sidebar.php'; ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Categories</h2>
            <a href="catego_add.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add New Category
            </a>
        </div>
        
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success_message'] ?></div>
        <?php unset($_SESSION['success_message']); endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error_message'] ?></div>
        <?php unset($_SESSION['error_message']); endif; ?>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Icon</th>
                                <th>Color</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($catego = $catego_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($catego['name']) ?></td>
                                <td><i class="<?= $catego['icon'] ?>"></i> <?= $catego['icon'] ?></td>
                                <td>
                                    <span class="color-preview" style="background-color: <?= $catego['bg_color'] ?>;"></span>
                                    <?= $catego['bg_color'] ?>
                                </td>
                                <td>
                                    <a href="catego_edit.php?id=<?= $catego['id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="manage_catego.php?delete=<?= $catego['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this category?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .color-preview {
            width: 20px;
            height: 20px;
            display: inline-block;
            border: 1px solid #ddd;
            margin-right: 5px;
            vertical-align: middle;
        }
    </style>
</body>
</html>