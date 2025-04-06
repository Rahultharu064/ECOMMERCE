<?php
session_start();
require '../includes/config.php';

// Check if user is logged in and is a pharmacist
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'pharmacist') {
    header("Location: login.php");
    exit();
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$search_query = $_GET['search'] ?? '';

// Base SQL query
$sql = "SELECT * FROM orders WHERE 1=1";
$params = [];
$types = '';

// Apply filters
if ($status_filter !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($date_from)) {
    $sql .= " AND DATE(created_at) >= ?";
    $params[] = $date_from;
    $types .= 's';
}

if (!empty($date_to)) {
    $sql .= " AND DATE(created_at) <= ?";
    $params[] = $date_to;
    $types .= 's';
}

if (!empty($search_query)) {
    $sql .= " AND (order_number LIKE ? OR name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $search_term = "%$search_query%";
    $params = array_merge($params, array_fill(0, 4, $search_term));
    $types .= str_repeat('s', 4);
}

// Add sorting
$sort = $_GET['sort'] ?? 'newest';
switch ($sort) {
    case 'oldest':
        $sql .= " ORDER BY created_at ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY total_amount DESC";
        break;
    case 'price_low':
        $sql .= " ORDER BY total_amount ASC";
        break;
    default: // newest
        $sql .= " ORDER BY created_at DESC";
}

// Pagination
$per_page = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $per_page;

// Get total count for pagination
$count_sql = str_replace('SELECT *', 'SELECT COUNT(*) as total', $sql);
$stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_result = $stmt->get_result()->fetch_assoc();
$total_orders = $total_result['total'];
$total_pages = ceil($total_orders / $per_page);

// Add limit to main query
$sql .= " LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';

// Execute main query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management | PharmaCare</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --accent-color: #e74c3c;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --success-color: #27ae60;
            --warning-color: #ffc107;
            --border-color: #e0e0e0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            line-height: 1.6;
        }
        
        .orders-container {
            max-width: 1400px;
            margin: 60px 300px 140px;
            padding: 0 20px;
        }
        
        .page-title {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 30px;
            color: var(--dark-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .filters-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .filter-group {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .filter-item {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-item label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
        }
        
        .filter-item select,
        .filter-item input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 14px;
        }
        
        .filter-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            font-size: 14px;
            cursor: pointer;
            border: none;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .btn-outline {
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
            background: white;
        }
        
        .btn-outline:hover {
            background: rgba(52, 152, 219, 0.1);
            transform: translateY(-2px);
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .orders-table th {
            background-color: #f8f9fa;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
        }
        
        .orders-table td {
            padding: 12px 15px;
            border-top: 1px solid var(--border-color);
        }
        
        .orders-table tr:hover {
            background-color: #f5f7fa;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 12px;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .status-shipped {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .action-link {
            color: var(--primary-color);
            text-decoration: none;
            margin-right: 10px;
        }
        
        .action-link:hover {
            text-decoration: underline;
        }
        
        .action-link.danger {
            color: var(--accent-color);
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            gap: 5px;
        }
        
        .page-link {
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .page-link.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .page-link:hover:not(.active) {
            background: #f1f1f1;
        }
        
        .no-orders {
            background: #fff;
            padding: 30px;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        
        .summary-card-title {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .summary-card-value {
            font-size: 24px;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .summary-card.pending .summary-card-value {
            color: #856404;
        }
        
        .summary-card.processing .summary-card-value {
            color: #004085;
        }
        
        .summary-card.completed .summary-card-value {
            color: #155724;
        }
        
        .summary-card.revenue .summary-card-value {
            color: var(--success-color);
        }
        
        /* Status dropdown styles */
        select[name="status"] {
            padding: 4px 8px;
            border-radius: 4px;
            background-color: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
            font-size: 12px;
            cursor: pointer;
            outline: none;
        }
        
        select[name="status"]:hover {
            background-color: rgba(52, 152, 219, 0.1);
        }
        
        /* Alert messages */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        
        @media (max-width: 768px) {
            .orders-container {
                margin-top: 80px;
            }
            
            .filter-group {
                flex-direction: column;
                gap: 10px;
            }
            
            .filter-item {
                min-width: 100%;
            }
            
            .orders-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <?php include '../Dasboard/Navbar.php'; ?>
    <?php include '../Dasboard/sidebar.php'; ?>
    
    <div class="orders-container">
        <!-- Display success/error messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <h1 class="page-title">
            <span>Order Management</span>
            <a href="order.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Order
            </a>
        </h1>
        
        <!-- Summary Cards -->
        <?php
        // Get summary counts
        $summary_sql = "SELECT 
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(total_amount) as revenue
            FROM orders";
        $summary_result = $conn->query($summary_sql)->fetch_assoc();
        ?>
        <div class="summary-cards">
            <div class="summary-card pending">
                <div class="summary-card-title">Pending Orders</div>
                <div class="summary-card-value"><?php echo $summary_result['pending']; ?></div>
            </div>
            <div class="summary-card processing">
                <div class="summary-card-title">Processing Orders</div>
                <div class="summary-card-value"><?php echo $summary_result['processing']; ?></div>
            </div>
            <div class="summary-card completed">
                <div class="summary-card-title">Completed Orders</div>
                <div class="summary-card-value"><?php echo $summary_result['completed']; ?></div>
            </div>
            <div class="summary-card revenue">
                <div class="summary-card-title">Total Revenue</div>
                <div class="summary-card-value">₹<?php echo number_format($summary_result['revenue'], 2); ?></div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="filters-container">
            <form method="get" action="pharmacist_orders.php">
                <div class="filter-group">
                    <div class="filter-item">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label for="date_from">From Date</label>
                        <input type="date" id="date_from" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                    </div>
                    <div class="filter-item">
                        <label for="date_to">To Date</label>
                        <input type="date" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                    </div>
                    <div class="filter-item">
                        <label for="sort">Sort By</label>
                        <select id="sort" name="sort">
                            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                            <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Total (High to Low)</option>
                            <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Total (Low to High)</option>
                        </select>
                    </div>
                </div>
                <div class="filter-group">
                    <div class="filter-item" style="flex: 2;">
                        <label for="search">Search</label>
                        <input type="text" id="search" name="search" placeholder="Search by order #, name, email or phone" value="<?php echo htmlspecialchars($search_query); ?>">
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        <a href="pharmacist_orders.php" class="btn btn-outline">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Orders Table -->
        <?php if (count($orders) > 0): ?>
            <div style="overflow-x: auto;">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                <td>
                                    <div><?php echo htmlspecialchars($order['name']); ?></div>
                                    <div style="font-size: 12px; color: #666;"><?php echo htmlspecialchars($order['email']); ?></div>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <?php
                                    // Count items in this order
                                    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM order_items WHERE order_id = ?");
                                    $stmt->bind_param("i", $order['id']);
                                    $stmt->execute();
                                    $item_count = $stmt->get_result()->fetch_assoc()['count'];
                                    echo $item_count;
                                    ?>
                                </td>
                                <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <form method="post" action="update_order_status.php" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status" onchange="this.form.submit()" style="border: none; background: none; cursor: pointer;">
                                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <?php 
                                    $payment_methods = [
                                        'cod' => 'COD',
                                        'khalti' => 'Khalti',
                                        'esewa' => 'eSewa',
                                        'card' => 'Card'
                                    ];
                                    echo $payment_methods[$order['payment_method']] ?? $order['payment_method'];
                                    ?>
                                </td>
                                <td>
                                    <a href="order.php?order_id=<?php echo $order['id']; ?>" class="action-link" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($order['status'] !== 'completed' && $order['status'] !== 'cancelled'): ?>
                                        <a href="update_order_status.php?order_id=<?php echo $order['id']; ?>&status=cancelled" 
                                           class="action-link danger" 
                                           title="Cancel Order"
                                           onclick="return confirm('Are you sure you want to cancel this order?')">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="page-link">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="page-link">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-orders">
                <i class="fas fa-box-open" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                <h3>No orders found</h3>
                <p>Try adjusting your search filters</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Simple date validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const dateFrom = document.getElementById('date_from').value;
            const dateTo = document.getElementById('date_to').value;
            
            if (dateFrom && dateTo && dateFrom > dateTo) {
                alert('"From Date" cannot be after "To Date"');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>