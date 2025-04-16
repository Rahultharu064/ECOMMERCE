<?php
// session_start();

include '../includes/config.php';

// Handle product deletion
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Product deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting product: " . $stmt->error;
    }
    header("Location: ../Dasboard/productsdisplay.php");
    exit();
}

// Pagination setup
$per_page = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

// Get total number of products
$count_query = "SELECT COUNT(*) as total FROM products";
$count_result = mysqli_query($conn, $count_query);
$total_products = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_products / $per_page);

// Get products with pagination
$query = "SELECT p.*, c.categories_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id
          ORDER BY p.id DESC
          LIMIT $per_page OFFSET $offset";
$result = mysqli_query($conn, $query);
$products = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Products</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    :root {
        --primary-color: #3498db;
        --secondary-color: #2c3e50;
        --success-color: #27ae60;
        --warning-color: #f39c12;
        --danger-color: #e74c3c;
        --light-color: #ecf0f1;
        --dark-color: #34495e;
        --border-color: #dfe6e9;
    }
    
    /* Main Admin Layout */
    .admin-container {
        display: flex;
        min-height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f8f9fa;
    }
    
    .admin-content {
        flex: 1;
        padding: 25px;
        margin-left: 250px; /* Matches sidebar width */
    }
    
    /* Header Styles */
    .admin-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .admin-header h1 {
        color: var(--secondary-color);
        margin: 0;
        font-size: 1.8rem;
    }
    
    /* Button Styles */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 15px;
        border-radius: 6px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        gap: 8px;
    }
    
    .btn-add {
        background-color: var(--success-color);
        color: white;
    }
    
    .btn-add:hover {
        background-color: #219653;
        transform: translateY(-2px);
    }
    
    /* Alert Messages */
    .alert {
        padding: 12px 15px;
        border-radius: 6px;
        margin-bottom: 20px;
        font-weight: 500;
    }
    
    .alert.success {
        background-color: #d4edda;
        color: #155724;
        border-left: 4px solid var(--success-color);
    }
    
    .alert.error {
        background-color: #f8d7da;
        color: #721c24;
        border-left: 4px solid var(--danger-color);
    }
    
    /* Table Container */
    .table-responsive {
        overflow-x: auto;
        border-radius: 10px;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        background: white;
    }
    
    /* Products Table */
    .products-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        min-width: 800px;
    }
    
    .products-table thead {
        background-color: var(--secondary-color);
        color: white;
        position: sticky;
        top: 0;
    }
    
    .products-table th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8em;
        letter-spacing: 0.5px;
    }
    
    .products-table td {
        padding: 12px 15px;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
        color: var(--dark-color);
    }
    
    .products-table tr:last-child td {
        border-bottom: none;
    }
    
    .products-table tr:hover {
        background-color: rgba(52, 152, 219, 0.05);
    }
    
    /* Table Elements */
    .product-thumbnail {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid var(--border-color);
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        min-width: 60px;
        text-align: center;
    }
    
    .badge-warning {
        background-color: #fff3cd;
        color: #856404;
    }
    
    .badge-success {
        background-color: #d4edda;
        color: #155724;
    }
    
    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 8px;
    }
    
    .btn-action {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
    }
    
    .btn-edit {
        background-color: #e3f2fd;
        color: var(--primary-color);
    }
    
    .btn-edit:hover {
        background-color: #bbdefb;
        transform: scale(1.1);
    }
    
    .btn-delete {
        background-color: #ffebee;
        color: var(--danger-color);
    }
    
    .btn-delete:hover {
        background-color: #ffcdd2;
        transform: scale(1.1);
    }
    
    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 25px;
        gap: 5px;
        flex-wrap: wrap;
    }
    
    .page-link {
        padding: 8px 14px;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        color: var(--primary-color);
        text-decoration: none;
        transition: all 0.2s;
        font-weight: 500;
    }
    
    .page-link:hover {
        background-color: var(--light-color);
    }
    
    .page-link.active {
        background-color: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }
    
    /* Status Colors */
    .status-active {
        color: var(--success-color);
    }
    
    .status-inactive {
        color: var(--danger-color);
    }
    
    /* Responsive Adjustments */
    @media (max-width: 1200px) {
        .admin-content {
            margin-left: 0;
            padding: 20px;
        }
    }
    
    @media (max-width: 992px) {
        .products-table {
            font-size: 13px;
        }
        
        .products-table th, 
        .products-table td {
            padding: 10px 12px;
        }
    }
    
    @media (max-width: 768px) {
        .admin-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .products-table {
            min-width: 100%;
        }
        
        .product-thumbnail {
            width: 40px;
            height: 40px;
        }
        
        .btn-action {
            width: 30px;
            height: 30px;
            font-size: 12px;
        }
    }
    
    @media (max-width: 576px) {
        .admin-content {
            padding: 15px;
        }
        
        .products-table {
            font-size: 12px;
        }
        
        .products-table th, 
        .products-table td {
            padding: 8px 10px;
        }
        
        .badge {
            min-width: 50px;
            padding: 3px 6px;
            font-size: 11px;
        }
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: var(--dark-color);
    }
    
    .empty-state i {
        font-size: 50px;
        color: var(--light-color);
        margin-bottom: 15px;
    }
    
    .empty-state h3 {
        margin: 10px 0;
        color: var(--secondary-color);
    }
    </style>
</head>
<body>
    <?php include '../Dasboard/Navbar.php'; ?>
    
    <div class="admin-container">
        <?php include '../Dasboard/Sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>Manage Products</h1>
                <a href="add_product.php" class="btn btn-add">
                    <i class="fas fa-plus"></i> Add New Product
                </a>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert success">
                    <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert error">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Prescription</th>
                            <th>Expiry</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state">
                                        <i class="fas fa-box-open"></i>
                                        <h3>No Products Found</h3>
                                        <p>Add your first product to get started</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo $product['id']; ?></td>
                                    <td>
                                        <?php if (!empty($product['image_path'])): ?>
                                            <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                                                 class="product-thumbnail">
                                        <?php else: ?>
                                            <div class="product-thumbnail" style="background: #f1f1f1; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-pills" style="color: #ccc; font-size: 20px;"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['categories_name'] ?? 'Uncategorized'); ?></td>
                                    <td>₹<?php echo number_format($product['price'], 2); ?></td>
                                    <td>
                                        <?php if ($product['quantity'] > 10): ?>
                                            <span class="status-active"><?php echo $product['quantity']; ?></span>
                                        <?php elseif ($product['quantity'] > 0): ?>
                                            <span class="status-warning"><?php echo $product['quantity']; ?> (Low)</span>
                                        <?php else: ?>
                                            <span class="status-inactive">0 (Out of stock)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $product['prescription_required'] == 'Yes' ? 'badge-warning' : 'badge-success'; ?>">
                                            <?php echo $product['prescription_required']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($product['expiry_date'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                                               class="btn-action btn-edit" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete_product.php?id=<?php echo $product['id']; ?>" 
                                               class="btn-action btn-delete" title="Delete"
                                               onclick="return confirm('Are you sure you want to delete this product?');">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="productsdisplay.php?page=<?php echo $page - 1; ?>" class="page-link">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>

                    <?php 
                    // Show page numbers with ellipsis for large page counts
                    $max_visible_pages = 5;
                    $start_page = max(1, $page - floor($max_visible_pages / 2));
                    $end_page = min($total_pages, $start_page + $max_visible_pages - 1);
                    
                    if ($start_page > 1) {
                        echo '<a href="productsdisplay.php?page=1" class="page-link">1</a>';
                        if ($start_page > 2) echo '<span class="page-link">...</span>';
                    }
                    
                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <a href="productsdisplay.php?page=<?php echo $i; ?>" 
                           class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; 
                    
                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) echo '<span class="page-link">...</span>';
                        echo '<a href="productsdisplay.php?page='.$total_pages.'" class="page-link">'.$total_pages.'</a>';
                    }
                    ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="productsdisplay.php?page=<?php echo $page + 1; ?>" class="page-link">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script src="../assets/js/admin.js"></script>
    <script>
    // Confirm before deleting
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this product?')) {
                e.preventDefault();
            }
        });
    });
    
    // Responsive table enhancements
    function handleResponsiveTable() {
        const table = document.querySelector('.products-table');
        if (window.innerWidth < 768) {
            // Add data attributes for mobile view
            const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent);
            table.querySelectorAll('tbody tr').forEach(row => {
                Array.from(row.querySelectorAll('td')).forEach((td, i) => {
                    td.setAttribute('data-label', headers[i]);
                });
            });
        }
    }
    
    // Run on load and resize
    window.addEventListener('load', handleResponsiveTable);
    window.addEventListener('resize', handleResponsiveTable);
    </script>
</body>
</html>