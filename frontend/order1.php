

<?php
session_start();
require '../includes/config.php';
// require '../includes/auth.php';

// // Only logged in users can view orders
// Auth::requireLogin();

$user_id = $_SESSION['user_id'];

// Get all orders for this user
$orders = [];
$stmt = $conn->prepare("SELECT 
    id, order_number, total_amount, status, created_at 
    FROM orders 
    WHERE user_id = ? 
    ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | PharmaCare</title>
    <link rel="stylesheet" href="../assets/css/pharmacy.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .orders-container {
            max-width: 1000px;
            margin: 350px auto;
            padding: 0 20px;
        }
        
        .orders-header {
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .orders-list {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .order-card {
            padding: 20px;
            border-bottom: 1px solid #eee;
            transition: background 0.2s;
        }
        
        .order-card:hover {
            background: #f9f9f9;
        }
        
        .order-card-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }
        
        .order-number {
            font-weight: 600;
            color: #3498db;
        }
        
        .order-date {
            color: #777;
        }
        
        .order-status {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-processing {
            background: #cce5ff;
            color: #004085;
        }
        
        .status-shipped {
            background: #d4edda;
            color: #155724;
        }
        
        .status-delivered {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .order-details {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        
        .order-amount {
            font-weight: 600;
        }
        
        .order-actions {
            margin-top: 15px;
        }
        
        .btn-view {
            padding: 8px 15px;
            background: #3498db;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
        }
        
        .btn-view:hover {
            background: #2980b9;
        }
        
        .empty-orders {
            text-align: center;
            padding: 50px;
            color: #777;
        }
        
        .empty-orders-icon {
            font-size: 50px;
            margin-bottom: 20px;
            color: #ddd;
        }
        
        @media (max-width: 600px) {
            .order-card-header {
                flex-direction: column;
                gap: 5px;
            }
            
            .order-details {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="orders-container">
        <div class="orders-header">
            <h1>My Orders</h1>
            <a href="../Dasboard/products.php" class="btn-view">
                <i class="fas fa-shopping-bag"></i> Continue Shopping
            </a>
        </div>
        
        <?php if (empty($orders)): ?>
            <div class="empty-orders">
                <div class="empty-orders-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h3>No orders yet</h3>
                <p>You haven't placed any orders with us yet.</p>
                <a href="../Dasboard/products.php" class="btn-view" style="margin-top: 20px;">
                    <i class="fas fa-shopping-bag"></i> Browse Products
                </a>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-card-header">
                            <span class="order-number">Order #<?php echo htmlspecialchars($order['order_number']); ?></span>
                            <span class="order-date">Placed on <?php echo date('M j, Y', strtotime($order['created_at'])); ?></span>
                            <span class="order-status status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                        
                        <div class="order-details">
                            <span>Total: <span class="order-amount">₹<?php echo number_format($order['total_amount'], 2); ?></span></span>
                            <div class="order-actions">
                                <a href="../frontend/order_success.php?id=<?php echo $order['id']; ?>" class="btn-view">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>



