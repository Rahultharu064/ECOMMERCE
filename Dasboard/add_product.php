<?php
session_start();
include '../includes/config.php';
// include '../includes/auth.php'; // Ensure only admins can add products

// Initialize variables
$message = '';
$error = '';
$product_data = [
    'product_name' => '',
    'description' => '',
    'category_id' => '',
    'price' => '',
    'quantity' => '',
    'prescription_required' => 'No',
    'expiry_date' => '',
    'manufacturer' => '',
    'active_ingredients' => '',
    'bmi_category' => ''
];

// Fetch categories for dropdown
$categories = [];
$cat_query = "SELECT id, categories_name FROM categories ORDER BY categories_name";
$cat_result = mysqli_query($conn, $cat_query);
while($row = mysqli_fetch_assoc($cat_result)) {
    $categories[] = $row;
}

// BMI categories for dropdown
$bmi_categories = [
    '' => 'None',
    'Severely Underweight' => 'Severely Underweight',
    'Underweight' => 'Underweight',
    'Normal Weight' => 'Normal Weight',
    'Overweight' => 'Overweight',
    'Obese Class I' => 'Obese Class I',
    'Obese Class II' => 'Obese Class II',
    'Obese Class III' => 'Obese Class III'
];

if(isset($_POST['submit'])) {
    // Sanitize and validate inputs
    $product_data = [
        'product_name' => mysqli_real_escape_string($conn, trim($_POST['product_name'])),
        'description' => mysqli_real_escape_string($conn, trim($_POST['description'])),
        'category_id' => intval($_POST['category_id']),
        'price' => floatval($_POST['price']),
        'quantity' => intval($_POST['quantity']),
        'prescription_required' => in_array($_POST['prescription_required'], ['Yes', 'No']) ? $_POST['prescription_required'] : 'No',
        'expiry_date' => mysqli_real_escape_string($conn, $_POST['expiry_date']),
        'manufacturer' => mysqli_real_escape_string($conn, trim($_POST['manufacturer'])),
        'active_ingredients' => mysqli_real_escape_string($conn, trim($_POST['active_ingredients'])),
        'bmi_category' => isset($_POST['bmi_category']) && array_key_exists($_POST['bmi_category'], $bmi_categories) ? $_POST['bmi_category'] : ''
    ];

    // Validate required fields
    $required = ['product_name', 'category_id', 'price', 'quantity', 'expiry_date', 'manufacturer'];
    foreach ($required as $field) {
        if (empty($product_data[$field])) {
            $error = "Please fill all required fields!";
            break;
        }
    }

    // Validate price and quantity
    if ($product_data['price'] <= 0) {
        $error = "Price must be greater than 0";
    }
    if ($product_data['quantity'] < 0) {
        $error = "Quantity cannot be negative";
    }

    // Validate expiry date
    if (strtotime($product_data['expiry_date']) < strtotime('today')) {
        $error = "Expiry date cannot be in the past";
    }

    if(empty($error)) {
        // Handle file upload
        $image_path = '';
        if(isset($_FILES["product_image"]) && $_FILES["product_image"]["error"] == UPLOAD_ERR_OK) {
            $target_dir = "../uploads/products/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_ext = strtolower(pathinfo($_FILES["product_image"]["name"], PATHINFO_EXTENSION));
            $allowed_exts = ["jpg", "jpeg", "png", "gif", "webp"];
            
            if(in_array($file_ext, $allowed_exts)) {
                $file_name = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.]/', '_', $_FILES["product_image"]["name"]);
                $target_file = $target_dir . $file_name;
                
                if ($_FILES["product_image"]["size"] > 2000000) {
                    $error = "Image size must be less than 2MB";
                } else {
                    if(move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
                        $image_path = $target_file;
                    } else {
                        $error = "Sorry, there was an error uploading your file.";
                    }
                }
            } else {
                $error = "Only JPG, JPEG, PNG, GIF & WEBP files are allowed.";
            }
        } else {
            $error = "Product image is required";
        }

        if(empty($error)) {
            // Insert product into database
            $sql = "INSERT INTO products (
                product_name, 
                description, 
                category_id, 
                price, 
                quantity, 
                prescription_required, 
                expiry_date, 
                manufacturer, 
                active_ingredients, 
                bmi_category,
                image_path,
                created_at
            ) VALUES (
                '{$product_data['product_name']}',
                '{$product_data['description']}',
                {$product_data['category_id']},
                {$product_data['price']},
                {$product_data['quantity']},
                '{$product_data['prescription_required']}',
                '{$product_data['expiry_date']}',
                '{$product_data['manufacturer']}',
                '{$product_data['active_ingredients']}',
                " . (!empty($product_data['bmi_category']) ? "'{$product_data['bmi_category']}'" : "NULL") . ",
                '{$image_path}',
                NOW()
            )";
            
            if(mysqli_query($conn, $sql)) {
                $_SESSION['message'] = "Product added successfully!";
                header("Location: products.php");
                exit();
            } else {
                $error = "Database error: " . mysqli_error($conn);
                // Delete uploaded file if database insert failed
                if(!empty($image_path) && file_exists($image_path)) {
                    unlink($image_path);
                }
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
    <title>Add Pharmaceutical Product</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/pharmacy-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2A9D8F;
            --secondary: #264653;
            --accent: #E9C46A;
            --danger: #E76F51;
            --success: #588157;
            --light-bg: #f8fbfe;
        }

        .product-form {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--primary);
        }

        .form-header h2 {
            color: var(--secondary);
            font-size: 1.8rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--secondary);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(42,157,143,0.2);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .image-preview {
            width: 150px;
            height: 150px;
            border: 2px dashed #ccc;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-top: 0.5rem;
            background-color: #f9f9f9;
        }

        .image-preview img {
            max-width: 100%;
            max-height: 100%;
            display: none;
        }

        .file-upload {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }

        .file-upload-btn {
            padding: 0.6rem 1.2rem;
            background-color: var(--primary);
            color: white;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .file-upload-btn:hover {
            background-color: var(--secondary);
        }

        .file-upload input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: block;
            width: 100%;
            max-width: 300px;
            margin: 2rem auto 0;
            text-align: center;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .error {
            color: var(--danger);
            background-color: #fdecea;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--danger);
        }

        .success {
            color: var(--success);
            background-color: #e8f5e9;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--success);
        }

        .required-field::after {
            content: " *";
            color: var(--danger);
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php include '../Dasboard/Navbar.php'; ?>
<section style="display: flex; width: 100%">
    <?php include '../Dasboard/Sidebar.php'; ?>
    
    <main class="content">
        <div class="product-form">
            <div class="form-header">
                <h2><i class="fas fa-pills"></i> Add New Pharmaceutical Product</h2>
                <p>Fill out the form below to add a new product to your inventory</p>
            </div>
            
            <?php if(!empty($error)): ?>
                <div class="error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
            <?php endif; ?>
            
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="required-field">Product Name</label>
                        <input type="text" name="product_name" value="<?= htmlspecialchars($product_data['product_name']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="required-field">Category</label>
                        <select name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= $product_data['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['categories_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="required-field">Price (₹)</label>
                        <input type="number" step="0.01" min="0.01" name="price" value="<?= htmlspecialchars($product_data['price']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="required-field">Quantity in Stock</label>
                        <input type="number" min="0" name="quantity" value="<?= htmlspecialchars($product_data['quantity']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="required-field">Prescription Required</label>
                        <select name="prescription_required" required>
                            <option value="No" <?= $product_data['prescription_required'] == 'No' ? 'selected' : '' ?>>No</option>
                            <option value="Yes" <?= $product_data['prescription_required'] == 'Yes' ? 'selected' : '' ?>>Yes</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="required-field">Expiry Date</label>
                        <input type="date" name="expiry_date" value="<?= htmlspecialchars($product_data['expiry_date']) ?>" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="required-field">Manufacturer</label>
                        <input type="text" name="manufacturer" value="<?= htmlspecialchars($product_data['manufacturer']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>BMI Category Recommendation</label>
                        <select name="bmi_category">
                            <?php foreach($bmi_categories as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $product_data['bmi_category'] == $value ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description"><?= htmlspecialchars($product_data['description']) ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Active Ingredients/Tests Included</label>
                        <textarea name="active_ingredients"><?= htmlspecialchars($product_data['active_ingredients']) ?></textarea>
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="required-field">Product Image</label>
                        <div class="file-upload">
                            <button type="button" class="file-upload-btn">
                                <i class="fas fa-cloud-upload-alt"></i> Choose Image
                            </button>
                            <input type="file" name="product_image" accept="image/*" required onchange="previewImage(this)">
                        </div>
                        <small class="file-info">JPEG, PNG, GIF or WEBP (Max 2MB)</small>
                        <div class="image-preview">
                            <img id="imagePreview" src="#" alt="Preview">
                            <span id="noImageText"><i class="fas fa-image"></i> No image selected</span>
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="submit" class="btn-submit">
                    <i class="fas fa-plus-circle"></i> Add Product
                </button>
            </form>
        </div>
    </main>
</section>

<script src="../assets/js/dashboard.js"></script>
<script>
    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        const noImageText = document.getElementById('noImageText');
        const fileInfo = input.files[0];
        
        if (fileInfo) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                noImageText.style.display = 'none';
            }
            
            reader.readAsDataURL(fileInfo);
        } else {
            preview.style.display = 'none';
            noImageText.style.display = 'block';
        }
    }

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        let valid = true;
        const requiredFields = document.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.style.borderColor = 'var(--danger)';
                valid = false;
            } else {
                field.style.borderColor = '#e0e0e0';
            }
        });
        
        if (!valid) {
            e.preventDefault();
            alert('Please fill all required fields!');
        }
    });
</script>
</body>
</html>