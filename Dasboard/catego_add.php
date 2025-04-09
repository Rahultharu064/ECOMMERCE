<?php
session_start();

// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'pharmacare';
$port = 4308;

$conn = new mysqli($host, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in as admin
// if (!isset($_SESSION['admin_logged_in'])) {
//     header('Location: login.php');
//     exit;
// }

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $icon = $conn->real_escape_string($_POST['icon']);
    $bg_color = $conn->real_escape_string($_POST['bg_color']);
    
    // Validate color format
    if (!preg_match('/^#[a-f0-9]{6}$/i', $bg_color)) {
        $error_message = "Invalid color format. Please use hex format (e.g., #4e73df)";
    } else {
        // Insert into database
        $insert_query = "INSERT INTO cat (name, icon, bg_color) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("sss", $name, $icon, $bg_color);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Category added successfully!";
            header('Location: manage_catego.php');
            exit;
        } else {
            $error_message = "Error adding category: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Category</title>
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
            border-radius: 4px;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s;
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <?php include 'Navbar.php'; ?>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container">
            <div class="form-container">
                <h2 class="mb-4">Add New Category</h2>
                
                <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
                <?php endif; ?>
                
                <form action="catego_add.php" method="POST" id="categoryForm">
                    <div class="mb-3">
                        <label for="name" class="form-label">Category Name</label>
                        <input type="text" id="name" name="name" class="form-control" required maxlength="255">
                    </div>
                    
                    <div class="mb-3">
                        <label for="icon" class="form-label">Font Awesome Icon</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-icons"></i></span>
                            <input type="text" id="icon" name="icon" class="form-control" placeholder="fas fa-heartbeat" required>
                            <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#iconModal">
                                <i class="fas fa-search"></i> Browse Icons
                            </button>
                        </div>
                        <small class="text-muted">Enter Font Awesome icon class (e.g., fas fa-heartbeat)</small>
                        <div id="iconPreview" class="mt-2">
                            <span class="text-muted">Preview:</span>
                            <i id="iconDisplay" class="ms-2"></i>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="bg_color" class="form-label">Background Color</label>
                        <div class="input-group">
                            <input type="color" id="color_picker" class="form-control form-control-color" value="#4e73df" title="Choose color">
                            <input type="text" id="bg_color" name="bg_color" class="form-control" value="#4e73df" required pattern="^#[0-9A-Fa-f]{6}$">
                            <span class="color-preview" id="color_preview" style="background-color: #4e73df;"></span>
                        </div>
                        <small class="text-muted">Hex color code (e.g., #4e73df)</small>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="manage_catego.php" class="btn btn-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Icon Selection Modal -->
    <div class="modal fade" id="iconModal" tabindex="-1" aria-labelledby="iconModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="iconModalLabel">Select an Icon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Common Health Icons</h6>
                            <div class="icon-grid">
                                <button type="button" class="btn btn-sm btn-outline-secondary m-1 icon-btn" data-icon="fas fa-heartbeat"><i class="fas fa-heartbeat me-2"></i>Heartbeat</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary m-1 icon-btn" data-icon="fas fa-pills"><i class="fas fa-pills me-2"></i>Pills</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary m-1 icon-btn" data-icon="fas fa-stethoscope"><i class="fas fa-stethoscope me-2"></i>Stethoscope</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary m-1 icon-btn" data-icon="fas fa-user-md"><i class="fas fa-user-md me-2"></i>Doctor</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary m-1 icon-btn" data-icon="fas fa-ambulance"><i class="fas fa-ambulance me-2"></i>Ambulance</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary m-1 icon-btn" data-icon="fas fa-syringe"><i class="fas fa-syringe me-2"></i>Syringe</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary m-1 icon-btn" data-icon="fas fa-procedures"><i class="fas fa-procedures me-2"></i>Procedures</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary m-1 icon-btn" data-icon="fas fa-prescription-bottle"><i class="fas fa-prescription-bottle me-2"></i>Prescription</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Other Useful Icons</h6>
                            <div class="icon-grid">
                                <button type="button" class="btn btn-sm btn-outline-secondary m-1 icon-btn" data-icon="fas fa-apple-alt"><i class="fas fa-apple-alt me-2"></i>Nutrition</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary m-1 icon-btn" data-icon="fas fa-baby"><i class="fas fa-baby me-2"></i>Baby</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary m-1 icon-btn" data-icon="fas fa-brain"><i class="fas fa-brain me-2"></i>Brain</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary m-1 icon-btn" data-icon="fas fa-eye"><i class="fas fa-eye me-2"></i>Eye</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary m-1 icon-btn" data-icon="fas fa-tooth"><i class="fas fa-tooth me-2"></i>Tooth</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary m-1 icon-btn" data-icon="fas fa-spa"><i class="fas fa-spa me-2"></i>Spa</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary m-1 icon-btn" data-icon="fas fa-running"><i class="fas fa-running me-2"></i>Exercise</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary m-1 icon-btn" data-icon="fas fa-shield-virus"><i class="fas fa-shield-virus me-2"></i>Virus</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
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
            document.getElementById('iconDisplay').className = this.value;
        });

        // Handle icon selection from modal
        document.querySelectorAll('.icon-btn').forEach(button => {
            button.addEventListener('click', function() {
                const iconClass = this.getAttribute('data-icon');
                document.getElementById('icon').value = iconClass;
                document.getElementById('iconDisplay').className = iconClass;
                const modal = bootstrap.Modal.getInstance(document.getElementById('iconModal'));
                modal.hide();
            });
        });

        // Form validation
        document.getElementById('categoryForm').addEventListener('submit', function(e) {
            const bgColor = document.getElementById('bg_color').value;
            if (!/^#[0-9A-F]{6}$/i.test(bgColor)) {
                e.preventDefault();
                alert('Please enter a valid hex color code (e.g., #4e73df)');
            }
        });
    </script>
</body>
</html>