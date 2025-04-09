<?php
session_start();
include '../includes/config.php';



// Database connection
$db = new mysqli($host, $username, $password, $dbname, $port);

// Check connection
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Get category ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch category data
$catego_query = "SELECT * FROM cat WHERE id = ?";
$stmt = $db->prepare($catego_query);
$stmt->bind_param("i", $id);
$stmt->execute();
$catego = $stmt->get_result()->fetch_assoc();

if (!$catego) {
    $_SESSION['error_message'] = "Category not found";
    header('Location: manage_catego.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $db->real_escape_string($_POST['name']);
    $icon = $db->real_escape_string($_POST['icon']);
    $bg_color = $db->real_escape_string($_POST['bg_color']);
    
    // Update in database
    $update_query = "UPDATE cat SET name = ?, icon = ?, bg_color = ? WHERE id = ?";
    $stmt = $db->prepare($update_query);
    $stmt->bind_param("sssi", $name, $icon, $bg_color, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Category updated successfully!";
        header('Location: manage_catego.php');
        exit;
    } else {
        $error_message = "Error updating category: " . $db->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 30px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .color-preview {
            width: 30px;
            height: 30px;
            display: inline-block;
            border: 1px solid #ddd;
            margin-left: 10px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
  <?php include 'Navbar.php'; ?>
  <?php include 'sidebar.php'; ?>
    
    <div class="container">
        <div class="form-container">
            <h2 class="mb-4">Edit Category</h2>
            
            <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
            <?php endif; ?>
            
            <form action="catego_edit.php?id=<?= $id ?>" method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Category Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($catego['name']) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="icon" class="form-label">Font Awesome Icon</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="<?= $catego['icon'] ?>"></i></span>
                        <input type="text" id="icon" name="icon" class="form-control" value="<?= $catego['icon'] ?>" required>
                    </div>
                    <small class="text-muted">Enter Font Awesome icon class (e.g., fas fa-heartbeat)</small>
                </div>
                
                <div class="mb-3">
                    <label for="bg_color" class="form-label">Background Color</label>
                    <div class="input-group">
                        <input type="color" id="color_picker" class="form-control form-control-color" value="<?= $catego['bg_color'] ?>" title="Choose color">
                        <input type="text" id="bg_color" name="bg_color" class="form-control" value="<?= $catego['bg_color'] ?>" required>
                        <span class="color-preview" id="color_preview" style="background-color: <?= $catego['bg_color'] ?>;"></span>
                    </div>
                    <small class="text-muted">Hex color code (e.g., #e3f2fd)</small>
                </div>
                
                <button type="submit" class="btn btn-primary">Update Category</button>
                <a href="manage_catego.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update color preview and text input when color picker changes
        document.getElementById('color_picker').addEventListener('input', function() {
            const color = this.value;
            document.getElementById('bg_color').value = color;
            document.getElementById('color_preview').style.backgroundColor = color;
        });
        
        // Update color picker and preview when text input changes
        document.getElementById('bg_color').addEventListener('input', function() {
            const color = this.value;
            if (/^#[0-9A-F]{6}$/i.test(color)) {
                document.getElementById('color_picker').value = color;
                document.getElementById('color_preview').style.backgroundColor = color;
            }
        });
        
        // Update icon preview when icon input changes
        document.getElementById('icon').addEventListener('input', function() {
            const icon = this.value;
            const iconPreview = this.previousElementSibling.querySelector('i');
            if (iconPreview) {
                iconPreview.className = icon;
            }
        });
    </script>
</body>
</html>