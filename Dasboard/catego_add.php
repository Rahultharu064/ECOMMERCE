<?php
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'pharmacy-ecommerce';
$port=4307;
$conn = new mysqli($host, $username, $password, $dbname, $port);
if($conn){
    // echo "Connected to database";
}

// Check if user is logged in as admin


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $icon = $conn->real_escape_string($_POST['icon']);
    $bg_color = $conn->real_escape_string($_POST['bg_color']);
    
    // Insert into database
    $insert_query = "INSERT INTO catego (name, icon, bg_color) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("sss", $name, $icon, $bg_color);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Category added successfully!";
        header('Location: manage_catego.php');
        exit;
    } else {
        $error_message = "Error adding category: " . $db->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Category</title>
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
            <h2 class="mb-4">Add New Category</h2>
            
            <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
            <?php endif; ?>
            
            <form action="catego_add.php" method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Category Name</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label for="icon" class="form-label">Font Awesome Icon</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-icons"></i></span>
                        <input type="text" id="icon" name="icon" class="form-control" placeholder="fas fa-heartbeat" required>
                    </div>
                    <small class="text-muted">Enter Font Awesome icon class (e.g., fas fa-heartbeat)</small>
                </div>
                
                <div class="mb-3">
                    <label for="bg_color" class="form-label">Background Color</label>
                    <div class="input-group">
                        <input type="color" id="color_picker" class="form-control form-control-color" value="#e3f2fd" title="Choose color">
                        <input type="text" id="bg_color" name="bg_color" class="form-control" value="#e3f2fd" required>
                        <span class="color-preview" id="color_preview" style="background-color: #e3f2fd;"></span>
                    </div>
                    <small class="text-muted">Hex color code (e.g., #e3f2fd)</small>
                </div>
                
                <button type="submit" class="btn btn-primary">Add Category</button>
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
    </script>
</body>
</html>