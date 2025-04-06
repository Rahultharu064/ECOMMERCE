<?php
session_start();
include '../includes/config.php';

// Check if product ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No product selected for editing";
    header("Location: productsdisplay.php");
    exit();
}

$product_id = intval($_GET['id']);

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    $_SESSION['error'] = "Product not found";
    header("Location: productsdisplay.php");
    exit();
}

// Fetch categories for dropdown
$categories = [];
$cat_query = "SELECT id, categories_name FROM categories";
$cat_result = mysqli_query($conn, $cat_query);
if ($cat_result) {
    $categories = mysqli_fetch_all($cat_result, MYSQLI_ASSOC);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $category_id = intval($_POST['category_id']);
    $prescription_required = $_POST['prescription_required'] == 'Yes' ? 'Yes' : 'No';
    $expiry_date = mysqli_real_escape_string($conn, $_POST['expiry_date']);
    $manufacturer = mysqli_real_escape_string($conn, $_POST['manufacturer']);
    $active_ingredients = mysqli_real_escape_string($conn, $_POST['active_ingredients']);
    
    // Initialize image path with existing value
    $image_path = $product['image_path'];
    
    // Handle file upload if a new image is provided
    if (!empty($_FILES['image_path']['name'])) {
        $target_dir = "../uploads/products/";
        $original_filename = basename($_FILES["image_path"]["name"]);
        $imageFileType = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
        
        // Validate image
        $check = getimagesize($_FILES["image_path"]["tmp_name"]);
        if ($check === false) {
            $_SESSION['error'] = "File is not an image.";
            header("Location: edit_product.php?id=" . $product_id);
            exit();
        }
        
        // Check file size (max 2MB)
        if ($_FILES["product_image"]["size"] > 2000000) {
            $_SESSION['error'] = "Sorry, your file is too large (max 2MB allowed).";
            header("Location: edit_product.php?id=" . $product_id);
            exit();
        }
        
        // Allow certain file formats
        if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
            $_SESSION['error'] = "Only JPG, JPEG, PNG & GIF files are allowed.";
            header("Location: edit_product.php?id=" . $product_id);
            exit();
        }
        
        // Generate unique filename similar to add_product.php
        $new_filename = uniqid() . '_' . $original_filename;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["image_path"]["tmp_name"], $target_file)) {
            // Delete old image if it exists
            if (!empty($product['image_path']) && file_exists("../" . $product['image_path'])) {
                unlink("../" . $product['image_path']);
            }
            $image_path = "uploads/products/" . $new_filename;
        } else {
            $_SESSION['error'] = "Sorry, there was an error uploading your file.";
            header("Location: edit_product.php?id=" . $product_id);
            exit();
        }
    }
    
    // Update product in database
    $stmt = $conn->prepare("UPDATE products SET 
                          product_name = ?, 
                          description = ?, 
                          price = ?, 
                          quantity = ?, 
                          category_id = ?, 
                          prescription_required = ?, 
                          expiry_date = ?, 
                          manufacturer = ?,
                          active_ingredients = ?,
                          image_path = ? 
                          WHERE id = ?");
    
    $stmt->bind_param("ssdiisssssi", 
                     $product_name, 
                     $description, 
                     $price, 
                     $quantity, 
                     $category_id, 
                     $prescription_required, 
                     $expiry_date,
                     $manufacturer,
                     $active_ingredients,
                     $image_path, 
                     $product_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Product updated successfully";
        header("Location: productsdisplay.php");
        exit();
    } else {
        $_SESSION['error'] = "Error updating product: " . $stmt->error;
        header("Location: edit_product.php?id=" . $product_id);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Pharmacy Management</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Main Layout */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
            color: #333;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        .admin-content {
            flex: 1;
            padding: 30px;
            margin-left: 250px;
            transition: margin 0.3s ease;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e6ed;
        }

        .admin-header h1 {
            font-size: 24px;
            color: #2c3e50;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Form Styles */
        .form-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            padding: 35px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #4a5568;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background-color: #f8fafc;
            color: #4a5568;
        }

        .form-control:focus {
            border-color: #4299e1;
            outline: 0;
            box-shadow: 0 0 0 3px rgba(66,153,225,0.15);
            background-color: #fff;
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
            line-height: 1.5;
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%234a5568' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 12px;
        }

        /* Buttons */
        .btn-submit {
            background-color: #4299e1;
            color: white;
            padding: 13px 28px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 2px 5px rgba(66,153,225,0.2);
        }

        .btn-submit:hover {
            background-color: #3182ce;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(66,153,225,0.3);
        }

        .btn-add {
            background-color: #fff;
            color: #4a5568;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .btn-add:hover {
            background-color: #f7fafc;
            border-color: #cbd5e0;
            text-decoration: none;
            color: #2d3748;
        }

        /* Image Preview */
        .image-preview-container {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .image-preview {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .no-image-preview {
            width: 200px;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eee;
            border-radius: 8px;
            border: 1px dashed #ccc;
            color: #777;
            font-size: 14px;
            margin-bottom: 15px;
        }

        /* Radio Buttons */
        .radio-group {
            display: flex;
            gap: 25px;
            margin-top: 8px;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .radio-option input {
            width: 16px;
            height: 16px;
            margin: 0;
            accent-color: #4299e1;
        }

        .radio-option label {
            font-weight: 500;
            color: #4a5568;
            margin: 0;
            cursor: pointer;
        }

        /* Alerts */
        .alert {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 30px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 4px solid transparent;
        }

        .alert.error {
            background-color: #fff5f5;
            color: #c53030;
            border-left-color: #c53030;
        }

        .alert.success {
            background-color: #f0fff4;
            color: #2f855a;
            border-left-color: #2f855a;
        }

        .alert i {
            font-size: 18px;
        }

        /* Additional styles */
        .text-muted {
            color: #718096 !important;
            font-size: 13px;
            margin-top: 5px;
            display: block;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .admin-content {
                margin-left: 0;
                padding: 25px;
            }
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-group.full-width {
                grid-column: span 1;
            }
            
            .admin-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }

        @media (max-width: 576px) {
            .form-container {
                padding: 25px;
            }
            
            .radio-group {
                flex-direction: column;
                gap: 12px;
            }
            
            .image-preview, .no-image-preview {
                width: 150px;
                height: 150px;
            }
        }
    </style>
</head>
<body>
    <?php include '../Dasboard/Navbar.php'; ?>
    
    <div class="admin-container">
        <?php include '../Dasboard/Sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1><i class="fas fa-pills"></i> Edit Product</h1>
                <a href="productsdisplay.php" class="btn-add">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <form action="edit_product.php?id=<?php echo $product_id; ?>" method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="product_name">Product Name *</label>
                            <input type="text" id="product_name" name="product_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="price">Price (₹) *</label>
                            <input type="number" id="price" name="price" class="form-control" step="0.01" min="0"
                                   value="<?php echo htmlspecialchars($product['price']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="quantity">Stock Quantity *</label>
                            <input type="number" id="quantity" name="quantity" class="form-control" min="0"
                                   value="<?php echo htmlspecialchars($product['quantity']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="category_id">Category *</label>
                            <select id="category_id" name="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                        <?php if ($category['id'] == $product['category_id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($category['categories_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Prescription Required *</label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" id="prescription_yes" name="prescription_required" value="Yes" 
                                        <?php if ($product['prescription_required'] == 'Yes') echo 'checked'; ?> required>
                                    <label for="prescription_yes">Yes</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" id="prescription_no" name="prescription_required" value="No" 
                                        <?php if ($product['prescription_required'] == 'No') echo 'checked'; ?> required>
                                    <label for="prescription_no">No</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="expiry_date">Expiry Date *</label>
                            <input type="date" id="expiry_date" name="expiry_date" class="form-control" 
                                   value="<?php echo htmlspecialchars($product['expiry_date']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="manufacturer">Manufacturer *</label>
                            <input type="text" id="manufacturer" name="manufacturer" class="form-control" 
                                   value="<?php echo htmlspecialchars($product['manufacturer']); ?>" required>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="description">Description *</label>
                            <textarea id="description" name="description" class="form-control" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="active_ingredients">Active Ingredients</label>
                            <textarea id="active_ingredients" name="active_ingredients" class="form-control"><?php echo htmlspecialchars($product['active_ingredients']); ?></textarea>
                        </div>
                        
                        <div class="form-group full-width image-preview-container">
                            <label for="product_image">Product Image</label>
                            <?php if (!empty($product['image_path'])): ?>
                                <img src="../<?php echo htmlspecialchars($product['image_path']); ?>" class="image-preview" id="imagePreview">
                            <?php else: ?>
                                <div class="no-image-preview">No image available</div>
                            <?php endif; ?>
                            <input type="file" id="product_image" name="product_image" class="form-control" 
                                   onchange="previewImage(this)" accept="image/jpeg, image/png, image/gif">
                            <small class="text-muted">Leave blank to keep current image (JPEG, PNG, GIF only, max 2MB)</small>
                        </div>
                    </div>
                    
                    <div style="text-align: right; margin-top: 20px;">
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save"></i> Update Product
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const file = input.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // If there was a no-image placeholder, replace it
                    if (preview) {
                        preview.src = e.target.result;
                    } else {
                        // Create new image preview if it didn't exist
                        const img = document.createElement('img');
                        img.id = 'imagePreview';
                        img.className = 'image-preview';
                        img.src = e.target.result;
                        const noImage = document.querySelector('.no-image-preview');
                        if (noImage) {
                            noImage.parentNode.replaceChild(img, noImage);
                        }
                    }
                }
                reader.readAsDataURL(file);
            }
        }
        
        // Set minimum date for expiry date (tomorrow)
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            
            // Format as YYYY-MM-DD
            const minDate = tomorrow.toISOString().split('T')[0];
            const expiryDateField = document.getElementById('expiry_date');
            
            if (expiryDateField) {
                expiryDateField.min = minDate;
                
                // If current expiry date is in the past, keep it but show warning
                const currentExpiry = expiryDateField.value;
                if (currentExpiry && currentExpiry < minDate) {
                    console.warn("Current expiry date is in the past");
                }
            }
        });
    </script>
</body>
</html>