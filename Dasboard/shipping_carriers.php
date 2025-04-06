<?php
require_once '../includes/config.php';
// require_once '../includes/auth.php';

// // Verify admin access
// checkAdminAccess();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_carrier']) || isset($_POST['update_carrier'])) {
        $name = trim($_POST['carrier_name']);
        $url = trim($_POST['tracking_url']);
        $logo_url = trim($_POST['logo_url'] ?? '');
        $id = isset($_POST['update_carrier']) ? (int)$_POST['carrier_id'] : null;
        
        // Handle file upload
        if (!empty($_FILES['logo_upload']['name'])) {
            $uploadDir = '../uploads/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = uniqid() . '_' . basename($_FILES['logo_upload']['name']);
            $targetPath = $uploadDir . $fileName;
            
            // Check if image file is a actual image
            $check = getimagesize($_FILES['logo_upload']['tmp_name']);
            if ($check !== false) {
                if (move_uploaded_file($_FILES['logo_upload']['tmp_name'], $targetPath)) {
                    $logo_url = 'uploads/products/' . $fileName;
                    
                    // Delete old file if updating
                    if ($id && !empty($_POST['old_logo_path'])) {
                        $oldFile = '../' . $_POST['old_logo_path'];
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }
                }
            }
        }
        
        if (isset($_POST['add_carrier'])) {
            $stmt = $conn->prepare("INSERT INTO shipping_carriers (carrier_name, tracking_url, logo_url) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $url, $logo_url);
        } else {
            $stmt = $conn->prepare("UPDATE shipping_carriers SET carrier_name = ?, tracking_url = ?, logo_url = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $url, $logo_url, $id);
        }
        
        $stmt->execute();
        $stmt->close();
        
        $_SESSION['success'] = "Carrier " . (isset($_POST['add_carrier']) ? 'added' : 'updated') . " successfully";
    } elseif (isset($_GET['delete'])) {
        $id = (int)$_GET['delete'];
        
        // Get logo path before deleting
        $carrier = $conn->query("SELECT logo_url FROM shipping_carriers WHERE id = $id")->fetch_assoc();
        
        $stmt = $conn->prepare("DELETE FROM shipping_carriers WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        // Delete logo file if exists
        if (!empty($carrier['logo_url']) && strpos($carrier['logo_url'], 'assets/images/carriers/') === 0) {
            $filePath = '../' . $carrier['logo_url'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        $_SESSION['success'] = "Carrier deleted successfully";
    }
    header("Location: shipping_carriers.php");
    exit();
}

// Get all carriers
$carriers = $conn->query("SELECT * FROM shipping_carriers ORDER BY carrier_name")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Shipping Carriers - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --success-color: #2ecc71;
            --danger-color: #e74c3c;
            --light-gray: #f8f9fa;
            --border-color: #e0e0e0;
        }
        
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container-main {
            margin-left: 280px;
            padding: 30px;
            transition: all 0.3s;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
            padding: 15px 20px;
            border-radius: 10px 10px 0 0 !important;
        }
        
        .form-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .carrier-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .carrier-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            border-left: 4px solid var(--primary-color);
        }
        
        .carrier-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .carrier-logo {
            max-height: 50px;
            max-width: 150px;
            margin-bottom: 15px;
            object-fit: contain;
        }
        
        .logo-preview {
            max-width: 150px;
            max-height: 80px;
            margin: 10px 0;
            display: block;
            border: 1px dashed #ddd;
            padding: 5px;
            border-radius: 4px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 8px 20px;
            font-weight: 500;
        }
        
        .btn-outline-secondary {
            padding: 8px 20px;
            font-weight: 500;
        }
        
        .btn-sm {
            padding: 5px 12px;
            font-size: 13px;
        }
        
        .badge {
            font-weight: 500;
            padding: 5px 10px;
        }
        
        .upload-area {
            border: 2px dashed #ddd;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .upload-area:hover {
            border-color: var(--primary-color);
            background-color: rgba(52, 152, 219, 0.05);
        }
        
        .upload-hint {
            font-size: 13px;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        
        .tracking-url {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            background-color: var(--light-gray);
            padding: 5px 8px;
            border-radius: 4px;
            word-break: break-all;
        }
        
        h1, h2, h3 {
            color: var(--secondary-color);
            font-weight: 600;
        }
        
        h1 {
            margin-bottom: 25px;
            font-size: 28px;
        }
        
        .alert {
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .required-field::after {
            content: " *";
            color: var(--danger-color);
        }
    </style>
</head>
<body>
    <?php include 'Navbar.php'; ?>
    <?php include 'Sidebar.php'; ?>
    
    <div class="container-main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-truck me-2"></i> Shipping Carriers</h1>
            <div>
                <a href="shipping_carriers.php" class="btn btn-outline-secondary">
                    <i class="fas fa-sync-alt me-1"></i> Refresh
                </a>
            </div>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i> <?= $_SESSION['success'] ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="form-section">
                    <h2 class="h4 mb-4"><i class="fas fa-edit me-2"></i><?= isset($_GET['edit']) ? 'Edit' : 'Add New' ?> Carrier</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <?php
                        $edit_carrier = null;
                        if (isset($_GET['edit'])) {
                            $edit_id = (int)$_GET['edit'];
                            $edit_carrier = $conn->query("SELECT * FROM shipping_carriers WHERE id = $edit_id")->fetch_assoc();
                        }
                        ?>
                        <input type="hidden" name="carrier_id" value="<?= $edit_carrier['id'] ?? '' ?>">
                        <input type="hidden" name="old_logo_path" value="<?= $edit_carrier['logo_url'] ?? '' ?>">
                        
                        <div class="mb-4">
                            <label class="form-label required-field">Carrier Name</label>
                            <input type="text" class="form-control" name="carrier_name" required 
                                   value="<?= htmlspecialchars($edit_carrier['carrier_name'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label required-field">Tracking URL Pattern</label>
                            <input type="text" class="form-control" name="tracking_url" required 
                                   value="<?= htmlspecialchars($edit_carrier['tracking_url'] ?? '') ?>"
                                   placeholder="https://example.com/track?num={tracking_number}">
                            <div class="upload-hint">Use <code>{tracking_number}</code> as placeholder for the tracking number</div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Logo Image</label>
                            <div class="upload-area" onclick="document.getElementById('logoUpload').click()">
                                <i class="fas fa-cloud-upload-alt fa-2x mb-2 text-muted"></i>
                                <p class="mb-1">Click to upload logo</p>
                                <p class="upload-hint">PNG, JPG (Max 2MB)</p>
                            </div>
                            <input type="file" name="logo_upload" id="logoUpload" accept="image/*" style="display: none;" onchange="previewLogo(this)">
                            
                            <?php if (!empty($edit_carrier['logo_url'])): ?>
                                <img src="../<?= htmlspecialchars($edit_carrier['logo_url']) ?>" class="logo-preview mt-2" id="logoPreview">
                            <?php else: ?>
                                <img src="../assets/images/default-carrier.png" class="logo-preview mt-2" id="logoPreview" style="display: none;">
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">OR Logo URL</label>
                            <input type="text" class="form-control" name="logo_url" 
                                   value="<?= htmlspecialchars($edit_carrier['logo_url'] ?? '') ?>"
                                   placeholder="https://example.com/logo.png">
                            <div class="upload-hint">Provide a direct URL to the logo image if you don't want to upload</div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" name="<?= isset($_GET['edit']) ? 'update_carrier' : 'add_carrier' ?>">
                                <i class="fas fa-save me-1"></i> <?= isset($_GET['edit']) ? 'Update' : 'Add' ?> Carrier
                            </button>
                            
                            <?php if (isset($_GET['edit'])): ?>
                                <a href="shipping_carriers.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-info-circle me-2"></i> Instructions
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Use {tracking_number} in URL pattern</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Logo should be transparent PNG</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Ideal logo width: 150-300px</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i> Keep tracking URLs consistent</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0"><i class="fas fa-list me-2"></i> Available Carriers</h2>
                <span class="badge bg-primary"><?= count($carriers) ?> carriers</span>
            </div>
            <div class="card-body">
                <?php if (empty($carriers)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <h4>No carriers found</h4>
                        <p class="text-muted">Add your first shipping carrier using the form above</p>
                    </div>
                <?php else: ?>
                    <div class="carrier-grid">
                        <?php foreach ($carriers as $carrier): ?>
                            <div class="carrier-card">
                                <img src="../<?= htmlspecialchars($carrier['logo_url']) ?>" alt="<?= htmlspecialchars($carrier['carrier_name']) ?>" class="carrier-logo">
                                <h5><?= htmlspecialchars($carrier['carrier_name']) ?></h5>
                                <p class="tracking-url"><?= htmlspecialchars($carrier['tracking_url']) ?></p>
                                <div class="actions">
                                    <a href="shipping_carriers.php?edit=<?= $carrier['id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="shipping_carriers.php?delete=<?= $carrier['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this carrier?');">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </body>
                        </html>