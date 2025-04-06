
<?php
require_once '../includes/config.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location:login.php");
    exit();
}

// Get user's prescriptions
$stmt = $conn->prepare("SELECT p.*, u.name as pharmacist_name 
                       FROM prescriptions p 
                       LEFT JOIN users u ON p.verified_by = u.id 
                       WHERE p.user_id = ? 
                       ORDER BY p.upload_date DESC");
$stmt->bind_param("i", $_SESSION['user_id']); // Bind the parameter
$stmt->execute(); // Now execute without parameters
$prescriptions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get comments for prescriptions
$comments = [];
if (!empty($prescriptions)) {
    $prescription_ids = array_column($prescriptions, 'id');
    $placeholders = implode(',', array_fill(0, count($prescription_ids), '?'));
    $types = str_repeat('i', count($prescription_ids));
    
    $stmt = $conn->prepare("SELECT pc.*, u.name as commenter_name 
                           FROM prescription_comments pc
                           JOIN users u ON pc.user_id = u.id
                           WHERE pc.prescription_id IN ($placeholders)
                           ORDER BY pc.created_at DESC");
    
    // Bind parameters dynamically
    $stmt->bind_param($types, ...$prescription_ids);
    $stmt->execute();
    $comments_result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    foreach ($comments_result as $comment) {
        $comments[$comment['prescription_id']][] = $comment;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Prescriptions - PharmaCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/pharmacy.css">
    <style>
        /* CSS from original file - optimized */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; background-color: #f5f5f5; }
        .container { width: 100%; max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .row { display: flex; flex-wrap: wrap; margin: 0 -15px; }
        .col-12 { flex: 0 0 100%; max-width: 100%; padding: 0 15px; }
        .col-md-6 { flex: 0 0 50%; max-width: 50%; padding: 0 15px; }
        .my-5 { margin-top: 310px !important; margin-bottom: 3rem !important; }
        .mb-3 { margin-bottom: 1rem !important; }
        .mb-4 { margin-bottom: 1.5rem !important; }
        .mt-4 { margin-top: 1.5rem !important; }
        .mt-3 { margin-top: 1rem !important; }
        .me-3 { margin-right: 1rem !important; }
        .ms-2 { margin-left: 0.5rem !important; }
        .prescription-img { max-width: 100%; max-height: 300px; display: block; margin: 0 auto 1rem auto; }
        .accordion { width: 100%; }
        .accordion-item { border: 1px solid #ddd; border-radius: 0.5rem; margin-bottom: 1rem; overflow: hidden; }
        .accordion-header { background-color: #f8f9fa; }
        .accordion-button { width: 100%; padding: 1.25rem; text-align: left; background-color: #f8f9fa; border: none; outline: none; cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: background-color 0.3s ease; }
        .accordion-button:hover { background-color: #e9ecef; }
        .accordion-button:after { content: '\f078'; font-family: 'Font Awesome 6 Free'; font-weight: 900; transition: transform 0.3s ease; }
        .accordion-button:not(.collapsed):after { transform: rotate(180deg); }
        .accordion-button:not(.collapsed) { background-color: #f8f9fa; }
        .accordion-collapse { display: none; overflow: hidden; transition: height 0.3s ease; }
        .accordion-collapse.show { display: block; }
        .accordion-body { padding: 1.25rem; background-color: #fff; }
        .badge { display: inline-block; padding: 0.35em 0.65em; font-size: 0.75em; font-weight: 700; line-height: 1; text-align: center; white-space: nowrap; vertical-align: baseline; border-radius: 0.25rem; }
        .bg-warning { background-color: #ffc107; color: #000; }
        .bg-success { background-color: #198754; color: #fff; }
        .bg-danger { background-color: #dc3545; color: #fff; }
        .list-group { display: flex; flex-direction: column; padding-left: 0; margin-bottom: 0; border-radius: 0.5rem; }
        .list-group-item { position: relative; display: block; padding: 0.75rem 1.25rem; background-color: #fff; border: 1px solid rgba(0,0,0,0.125); }
        .list-group-item:first-child { border-top-left-radius: inherit; border-top-right-radius: inherit; }
        .list-group-item:last-child { border-bottom-left-radius: inherit; border-bottom-right-radius: inherit; }
        .btn { display: inline-block; font-weight: 400; text-align: center; white-space: nowrap; vertical-align: middle; user-select: none; border: 1px solid transparent; padding: 0.5rem 1rem; font-size: 1rem; line-height: 1.5; border-radius: 0.25rem; transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out, transform 0.2s ease; cursor: pointer; text-decoration: none; }
        .btn-primary { color: #fff; background-color: #0d6efd; border-color: #0d6efd; }
        .btn-primary:hover { background-color: #0b5ed7; border-color: #0a58ca; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .alert { position: relative; padding: 1rem 1rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: 0.375rem; }
        .alert-info { color: #055160; background-color: #cff4fc; border-color: #b6effb; }
        .text-muted { color: #6c757d !important; }
        .d-flex { display: flex !important; }
        .w-100 { width: 100% !important; }
        .justify-content-between { justify-content: space-between !important; }
        h2 { font-size: 2rem; margin-bottom: 1.5rem; color: #333; }
        h5 { font-size: 1.25rem; margin-bottom: 1rem; color: #333; }
        strong { font-weight: 600; }
        small { font-size: 0.875em; }
        @media (max-width: 768px) {
            .col-md-6 { flex: 0 0 100%; max-width: 100%; }
            .accordion-button { flex-direction: column; align-items: flex-start; }
            .d-flex.w-100.justify-content-between { flex-direction: column; gap: 0.5rem; }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container my-5">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>My Prescriptions</h2>
                    <a href="upload_prescription.php" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload New Prescription
                    </a>
                </div>
                
                <?php if (empty($prescriptions)): ?>
                    <div class="alert alert-info">You haven't uploaded any prescriptions yet.</div>
                <?php else: ?>
                    <div class="accordion" id="prescriptionsAccordion">
                        <?php foreach ($prescriptions as $prescription): ?>
                            <div class="accordion-item mb-3">
                                <div class="accordion-header" id="heading<?php echo $prescription['id']; ?>">
                                    <button class="accordion-button collapsed" type="button" 
                                            onclick="toggleAccordion('collapse<?php echo $prescription['id']; ?>')">
                                        <div class="d-flex w-100 justify-content-between me-3">
                                            <div>
                                                <?php echo htmlspecialchars($prescription['file_name']); ?>
                                                <span class="badge bg-<?php 
                                                    echo $prescription['status'] == 'pending' ? 'warning' : 
                                                        ($prescription['status'] == 'verified' ? 'success' : 'danger'); 
                                                ?> ms-2">
                                                    <?php 
                                                    echo $prescription['status'] == 'pending' ? 'Pending Review' : 
                                                        ($prescription['status'] == 'verified' ? 'Verified' : 'Rejected'); 
                                                    ?>
                                                </span>
                                            </div>
                                            <div class="text-muted">
                                                <?php echo date('M d, Y h:i A', strtotime($prescription['upload_date'])); ?>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                                <div id="collapse<?php echo $prescription['id']; ?>" class="accordion-collapse">
                                    <div class="accordion-body">
                                        <div class="row">
                                            <div class="col-md-6">
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
                                            <div class="col-md-6">
                                                <h5>Prescription Details</h5>
                                                <ul class="list-group mb-3">
                                                    <li class="list-group-item">
                                                        <strong>Status:</strong> 
                                                        <span class="badge bg-<?php 
                                                            echo $prescription['status'] == 'pending' ? 'warning' : 
                                                                ($prescription['status'] == 'verified' ? 'success' : 'danger'); 
                                                        ?>">
                                                            <?php 
                                                            echo $prescription['status'] == 'pending' ? 'Pending Review' : 
                                                                ($prescription['status'] == 'verified' ? 'Verified' : 'Rejected'); 
                                                            ?>
                                                        </span>
                                                    </li>
                                                    <?php if ($prescription['verified_by']): ?>
                                                        <li class="list-group-item">
                                                            <strong>Verified by:</strong> 
                                                            <?php echo htmlspecialchars($prescription['pharmacist_name']); ?>
                                                        </li>
                                                        <li class="list-group-item">
                                                            <strong>Verification date:</strong> 
                                                            <?php echo date('M d, Y h:i A', strtotime($prescription['verification_date'])); ?>
                                                        </li>
                                                    <?php endif; ?>
                                                    <?php if ($prescription['notes']): ?>
                                                        <li class="list-group-item">
                                                            <strong>Pharmacist Notes:</strong> 
                                                            <?php echo htmlspecialchars($prescription['notes']); ?>
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                                
                                                <?php if (!empty($comments[$prescription['id']])): ?>
                                                    <h5 class="mt-4">Comments</h5>
                                                    <div class="list-group">
                                                        <?php foreach ($comments[$prescription['id']] as $comment): ?>
                                                            <div class="list-group-item">
                                                                <div class="d-flex w-100 justify-content-between">
                                                                    <strong><?php echo htmlspecialchars($comment['commenter_name']); ?></strong>
                                                                    <small><?php echo date('M d, Y h:i A', strtotime($comment['created_at'])); ?></small>
                                                                </div>
                                                                <p class="mb-1"><?php echo htmlspecialchars($comment['comment']); ?></p>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
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
        // Simple accordion functionality
        function toggleAccordion(collapseId) {
            const collapseElement = document.getElementById(collapseId);
            const isCollapsed = collapseElement.classList.contains('show');
            
            // Close all accordion items first
            document.querySelectorAll('.accordion-collapse').forEach(item => {
                item.classList.remove('show');
            });
            
            // Toggle the clicked item if it was collapsed
            if (!isCollapsed) {
                collapseElement.classList.add('show');
            }
        }
    </script>
</body>
</html>