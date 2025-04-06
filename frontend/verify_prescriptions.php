<?php
require_once '../includes/config.php';
session_start();
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    header("Location: ../login.php");
    exit();
}


// Get pending prescriptions
$stmt = $conn->prepare("SELECT p.*, u.name as patient_name 
                       FROM prescriptions p 
                       JOIN users u ON p.user_id = u.id 
                       WHERE p.status = 'pending' 
                       ORDER BY p.upload_date DESC");
$stmt->execute();
$result = $stmt->get_result();
$prescriptions = $result->fetch_all(MYSQLI_ASSOC);

// Handle verification
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['prescription_id'])) {
    $prescription_id = $_POST['prescription_id'];
    $status = $_POST['status'];
    $notes = $_POST['notes'] ?? '';
    
    // Check if InnoDB is being used (for transaction support)
    $conn->query("SET autocommit=0"); // Disable autocommit
    
    $success = true;
    $error_message = '';
    
    // Update prescription status
    $stmt = $conn->prepare("UPDATE prescriptions 
                           SET status = ?, verified_by = ?, verification_date = NOW(), notes = ?
                           WHERE id = ?");
    $stmt->bind_param("sisi", $status, $_SESSION['user_id'], $notes, $prescription_id);
    
    if (!$stmt->execute()) {
        $success = false;
        $error_message = "Error updating prescription: " . $stmt->error;
    }
    
    // Add comment if provided
    if ($success && !empty($notes)) {
        $stmt = $conn->prepare("INSERT INTO prescription_comments (prescription_id, user_id, comment) 
                              VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $prescription_id, $_SESSION['user_id'], $notes);
        
        if (!$stmt->execute()) {
            $success = false;
            $error_message = "Error adding comment: " . $stmt->error;
        }
    }
    
    if ($success) {
        $conn->commit();
        $_SESSION['message'] = "Prescription verification updated successfully!";
    } else {
        $conn->rollback();
        $_SESSION['message'] = $error_message;
    }
    
    // Re-enable autocommit
    $conn->query("SET autocommit=1");
    header("Location: verify_prescriptions.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Prescriptions - PharmaCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/pharmacy.css">
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            line-height: 1.6; 
            color: #333; 
            background-color: #f5f5f5; 
        }
        
        .container { 
            width: 100%; 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 20px; 
            overflow: hidden; /* Prevent container overflow */
        }
        
        .row { 
            display: flex; 
            flex-wrap: wrap; 
            margin: 0 -10px; /* Adjusted margin */
        }
        
        .col-12 { 
            flex: 0 0 100%; 
            max-width: 100%; 
            padding: 0 10px; /* Adjusted padding */
        }
        
        .col-md-6 { 
            flex: 0 0 50%; 
            max-width: 50%; 
            padding: 0 10px; /* Adjusted padding */
        }
        
        .my-5 { 
            margin-top: 300px !important; /* Reduced margin */
            margin-bottom: 2rem !important; 
        }
        
        .card { 
            position: relative; 
            display: flex; 
            flex-direction: column; 
            min-width: 0; 
            word-wrap: break-word; 
            background-color: #fff; 
            background-clip: border-box; 
            border: 1px solid rgba(0,0,0,0.125); 
            border-radius: 0.5rem; 
            height: auto; /* Changed from 100% to auto */
            margin-bottom: 20px; /* Added margin */
            overflow: hidden; /* Prevent card content overflow */
        }
        
        .card-header { 
            padding: 1rem; 
            margin-bottom: 0; 
            background-color: #ffc107; 
            border-bottom: 1px solid rgba(0,0,0,0.125); 
            color: white; 
            border-radius: 0.5rem 0.5rem 0 0 !important; 
        }
        
        .card-body { 
            flex: 1 1 auto; 
            padding: 1rem; 
            overflow: hidden; /* Prevent content overflow */
        }
        
        .prescription-container { 
            max-height: 500px; /* Reduced height */
            overflow-y: auto; 
            margin-bottom: 1rem; 
        }
        
        .prescription-img { 
            max-width: 100%; 
            max-height: 300px; /* Reduced height */
            display: block; 
            margin: 0 auto 1rem auto; 
            object-fit: contain; /* Ensure images maintain aspect ratio */
        }
        
        iframe {
            width: 100%;
            height: 400px;
            border: none;
            max-height: 400px; /* Added max-height */
        }
        
        /* Rest of your existing styles... */
        .mb-4 { margin-bottom: 1.5rem !important; }
        .mb-3 { margin-bottom: 1rem !important; }
        .me-2 { margin-right: 0.5rem !important; }
        .card-header.bg-success { background-color: #198754 !important; }
        .card-header.bg-danger { background-color: #dc3545 !important; }
        .form-label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        .form-control { display: block; width: 100%; padding: 0.5rem 0.75rem; font-size: 1rem; line-height: 1.5; color: #495057; background-color: #fff; background-clip: padding-box; border: 1px solid #ced4da; border-radius: 0.375rem; transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out; }
        textarea.form-control { min-height: calc(1.5em + 0.75rem + 2px); resize: vertical; }
        .btn { display: inline-block; font-weight: 400; text-align: center; white-space: nowrap; vertical-align: middle; user-select: none; border: 1px solid transparent; padding: 0.5rem 1rem; font-size: 1rem; line-height: 1.5; border-radius: 0.25rem; transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out, transform 0.2s ease; cursor: pointer; }
        .btn-success { color: #fff; background-color: #198754; border-color: #198754; }
        .btn-danger { color: #fff; background-color: #dc3545; border-color: #dc3545; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .flex-grow-1 { flex-grow: 1; }
        .d-flex { display: flex !important; }
        .justify-content-between { justify-content: space-between !important; }
        .badge { display: inline-block; padding: 0.35em 0.65em; font-size: 0.75em; font-weight: 700; line-height: 1; color: #fff; text-align: center; white-space: nowrap; vertical-align: baseline; border-radius: 0.25rem; background-color: #f8f9fa; color: #212529; }
        .float-end { float: right; }
        .alert { position: relative; padding: 1rem 1rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: 0.375rem; }
        .alert-success { color: #0f5132; background-color: #d1e7dd; border-color: #badbcc; }
        .alert-danger { color: #842029; background-color: #f8d7da; border-color: #f5c6cb; }
        .alert-info { color: #055160; background-color: #cff4fc; border-color: #b6effb; }
        h2 { font-size: 2rem; margin-bottom: 1.5rem; color: #333; }
        
        @media (max-width: 768px) {
            .col-md-6 { 
                flex: 0 0 100%; 
                max-width: 100%; 
            }
            
            .my-5 {
                margin-top: 80px !important;
            }
            
            .d-flex { 
                flex-direction: column; 
            }
            
            .flex-grow-1 { 
                width: 100%; 
                margin-bottom: 0.5rem; 
            }
            
            .me-2 { 
                margin-right: 0 !important; 
                margin-bottom: 0.5rem; 
            }
            
            .prescription-img, iframe {
                max-height: 250px;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container my-5">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Verify Prescriptions</h2>
                    <a href="../frontend/my_prescriptions.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
                
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo strpos($_SESSION['message'], 'Error') !== false ? 'danger' : 'success'; ?>">
                        <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (empty($prescriptions)): ?>
                    <div class="alert alert-info">No pending prescriptions to verify.</div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($prescriptions as $prescription): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-warning text-white">
                                        Prescription from <?php echo htmlspecialchars($prescription['patient_name']); ?>
                                        <span class="badge float-end" style="margin-right: 50px;">
                                            <?php echo date('M d, Y h:i A', strtotime($prescription['upload_date'])); ?>
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <div class="prescription-container mb-3">
                                            <?php 
                                            $file_url = '../' . htmlspecialchars($prescription['file_path']);
                                            if ($prescription['file_type'] == 'image'): ?>
                                                <img src="<?php echo $file_url; ?>" 
                                                     class="prescription-img" 
                                                     alt="Prescription">
                                            <?php else: ?>
                                                <iframe src="<?php echo $file_url; ?>" 
                                                        style="width: 100%; height: 400px; border: none;"></iframe>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <form method="post">
                                            <input type="hidden" name="prescription_id" value="<?php echo $prescription['id']; ?>">
                                            
                                            <div class="mb-3">
                                                <label for="notes" class="form-label">Verification Notes</label>
                                                <textarea class="form-control" id="notes" name="notes" rows="3" 
                                                          placeholder="Add any comments about this prescription..."></textarea>
                                            </div>
                                            
                                            <div class="d-flex justify-content-between">
                                                <button type="submit" name="status" value="verified" 
                                                        class="btn btn-success flex-grow-1 me-2">
                                                    <i class="fas fa-check"></i> Verify
                                                </button>
                                                <button type="submit" name="status" value="rejected" 
                                                        class="btn btn-danger flex-grow-1">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    
    <script>
        // Auto-dismiss alerts after 5 seconds
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                alert.style.display = 'none';
            }, 5000);
        });
    </script>
</body>
</html>