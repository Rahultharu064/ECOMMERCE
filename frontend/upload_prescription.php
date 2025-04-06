
<?php
require_once '../includes/config.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Define the upload directory
define('UPLOAD_DIR', __DIR__ . '/../uploads/prescriptions/');

// Create upload directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['prescription'])) {
    $file = $_FILES['prescription'];
    
    // Validate file
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    if (!in_array($file['type'], $allowed_types)) {
        $error = "Only JPG, PNG, GIF, and PDF files are allowed.";
    } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB max
        $error = "File size must be less than 5MB.";
    } else {
        // Determine file type
        $file_type = strpos($file['type'], 'image') !== false ? 'image' : 'pdf';
        
        // Generate unique filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $relative_path = 'uploads/prescriptions/' . $filename;
        $filepath = UPLOAD_DIR . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Prepare and execute database query
            $stmt = $conn->prepare("INSERT INTO prescriptions (user_id, file_name, file_path, file_type) VALUES (?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param('isss', $_SESSION['user_id'], $file['name'], $relative_path, $file_type);
                if ($stmt->execute()) {
                    $success = "Successfully uploaded";
                    header("Location: verify_prescriptions.php");
                    exit();
                } else {                  $error = "Error saving prescription to database: " . $stmt->error;
                    unlink($filepath);
                }
                $stmt->close();
            } else {
                $error = "Error preparing database statement: " . $conn->error;
                unlink($filepath);
            }
        } else {
            $error = "Error uploading file.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Prescription - PharmaCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/pharmacy.css">
    <style>
        
          * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; background-color: #f5f5f5; }
        .container { width: 100%; max-width: 1200px; margin: 280px auto 40px; padding: 0 20px; }
        .row { display: flex; flex-wrap: wrap; margin: 0 -15px; }
        .justify-content-center { justify-content: center; }
        .col-lg-8 { flex: 0 0 66.666667%; max-width: 66.666667%; padding: 0 15px; }
        .upload-container { background-color: #f8f9fa; border-radius: 10px; padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .card { position: relative; display: flex; flex-direction: column; min-width: 0; word-wrap: break-word; background-color: #fff; background-clip: border-box; border: 1px solid rgba(0,0,0,0.125); border-radius: 10px; }
        .shadow-sm { box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); }
        .card-header { background: linear-gradient(135deg, #299B63, #1f7a4d); border-radius: 8px 8px 0 0; padding: 20px; margin-bottom: 0; color: white; border-bottom: 1px solid rgba(0,0,0,0.125); }
        .card-header h3 { margin: 0; font-size: 1.5rem; }
        .card-body { flex: 1 1 auto; padding: 30px; background-color: #fff; border-radius: 0 0 8px 8px; }
        .upload-form { max-width: 600px; margin: 0 auto; }
        .file-input-container { position: relative; margin-bottom: 25px; }
        .file-input-label { display: block; font-weight: 600; margin-bottom: 8px; color: #495057; }
        .form-control-file { width: 100%; padding: 12px; border: 2px dashed #299B63; border-radius: 8px; background-color: #f0f9ff; transition: all 0.3s ease; font-size: 1rem; }
        .form-control-file:hover { background-color: #e0f2fe; border-color: #1f7a4d; }
        .form-text { display: block; margin-top: 0.25rem; color: #6c757d; font-size: 0.875rem; }
        .btn { display: inline-block; font-weight: 400; text-align: center; white-space: nowrap; vertical-align: middle; user-select: none; border: 1px solid transparent; padding: 0.5rem 1rem; font-size: 1rem; line-height: 1.5; border-radius: 0.25rem; transition: all 0.3s ease; cursor: pointer; text-decoration: none; }
        .btn-upload { background: linear-gradient(135deg, #299B63, #1f7a4d); color: white; padding: 12px 30px; font-weight: 600; }
        .btn-upload:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(41,155,99,0.3); color: white; }
        .btn-secondary { background-color: #6c757d; color: white; padding: 12px 30px; font-weight: 600; }
        .btn-secondary:hover { background-color: #5a6268; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(108,117,125,0.3); color: white; }
        .d-flex { display: flex; }
        .flex-wrap { flex-wrap: wrap; }
        .gap-3 { gap: 1rem; }
        .alert { position: relative; padding: 1rem 1rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: 8px; }
        .alert-danger { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
        .alert-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
        .alert-dismissible { padding-right: 3rem; }
        .btn-close { position: absolute; top: 0; right: 0; padding: 1.25rem 1rem; background: transparent; border: 0; cursor: pointer; color: inherit; }
        @keyframes pulse { 0% { border-color: #299B63; } 50% { border-color: #1f7a4d; } 100% { border-color: #299B63; } }
        .form-control-file:focus { animation: pulse 2s infinite; outline: none; }
        @media (max-width: 768px) {
            .container { margin-top: 150px; }
            .col-lg-8 { flex: 0 0 100%; max-width: 100%; }
            .card-body { padding: 20px; }
            .btn-upload, .btn-secondary { width: 100%; margin-bottom: 10px; }
            .gap-3 { gap: 0.5rem; }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3 class="mb-0"><i class="fas fa-file-upload me-2"></i>Upload Prescription</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?php echo $error; ?>
                                <button type="button" class="btn-close">&times;</button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?php echo $success; ?>
                                <button type="button" class="btn-close">&times;</button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" enctype="multipart/form-data" class="upload-form">                            
                            <div class="file-input-container mb-4">
                                <label for="prescription" class="file-input-label">
                                    <i class="fas fa-file-prescription me-2"></i>Select Prescription File
                                </label>
                                <input type="file" class="form-control form-control-file" id="prescription" name="prescription" required>
                                <div class="form-text mt-2">
                                    <i class="fas fa-info-circle me-2"></i>Accepted formats: JPG, PNG, GIF, PDF (Max 5MB)
                                </div>
                            </div>
                            
                            <div class="d-flex flex-wrap gap-3">
                                <button type="submit" class="btn btn-upload">
                                    <i class="fas fa-cloud-upload-alt me-2"></i>Upload Prescription
                                </button>
                                <a href="my_prescriptions.php" class="btn btn-secondary">
                                    <i class="fas fa-list me-2"></i>View My Prescriptions
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    
    <script>
        // Simple close functionality for alerts
        document.querySelectorAll('.btn-close').forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });

        // File input change handler
        document.getElementById('prescription').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'No file selected';
            const label = document.querySelector('.file-input-label');
            label.innerHTML = `<i class="fas fa-file-prescription me-2"></i>${fileName}`;
        });
    </script>
</body>
</html>