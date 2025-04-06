<?php
session_start();
require '../includes/config.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if order ID is provided
if (!isset($_GET['id'])) {
    header("Location: order.php");
    exit();
}

$order_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Verify the order belongs to the user
$order = [];
try {
    $stmt = $conn->prepare("SELECT id, order_number FROM orders1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header("Location: order.php");
        exit();
    }
    
    $order = $result->fetch_assoc();
} catch (Exception $e) {
    error_log("Order verification failed: " . $e->getMessage());
    header("Location: order.php");
    exit();
}

// Get order history
$history = [];
try {
    $stmt = $conn->prepare("SELECT h.status, h.notes, h.created_at, 
                           IFNULL(u.username, 'System') as updated_by
                           FROM order_history h
                           LEFT JOIN users u ON h.updated_by = u.id
                           WHERE h.order_id = ?
                           ORDER BY h.created_at DESC");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
} catch (Exception $e) {
    error_log("History fetch failed: " . $e->getMessage());
}

// Status CSS classes (same as order.php)
$status_classes = [
    'pending' => 'status-pending',
    'processing' => 'status-processing',
    'shipped' => 'status-shipped',
    'delivered' => 'status-delivered',
    'completed' => 'status-completed',
    'cancelled' => 'status-cancelled'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History | PharmaCare</title>
    <link rel="stylesheet" href="../assets/css/pharmacy.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .history-container {
            max-width: 800px;
            margin: 220px auto 50px;
            padding: 0 20px;
        }
        
        .history-header {
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .history-list {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .history-item {
            padding: 20px;
            border-bottom: 1px solid #eee;
            position: relative;
            padding-left: 80px;
        }
        
        .history-item:last-child {
            border-bottom: none;
        }
        
        .history-status {
            position: absolute;
            left: 20px;
            top: 20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .history-details {
            min-height: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .history-date {
            color: #777;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .history-notes {
            margin-top: 5px;
            color: #555;
        }
        
        .history-updated-by {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #3498db;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        /* Status-specific colors */
        .status-pending-bg { background-color: #ffc107; }
        .status-processing-bg { background-color: #17a2b8; }
        .status-shipped-bg { background-color: #28a745; }
        .status-delivered-bg { background-color: #6c757d; }
        .status-completed-bg { background-color: #343a40; }
        .status-cancelled-bg { background-color: #dc3545; }
        
        @media (max-width: 768px) {
            .history-container {
                margin-top: 80px;
            }
            
            .history-item {
                padding-left: 20px;
                padding-top: 70px;
            }
            
            .history-status {
                top: 10px;
                left: 10px;
                width: 40px;
                height: 40px;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="history-container">
        <div class="history-header">
            <h1>Order History - #<?= htmlspecialchars($order['order_number']) ?></h1>
            <a href="order.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
        </div>
        
        <div class="history-list">
            <?php if (empty($history)): ?>
                <div class="history-item">
                    <div class="history-details">
                        <p>No history found for this order.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($history as $entry): ?>
                    <div class="history-item">
                        <div class="history-status status-<?= $entry['status'] ?>-bg">
                            <?= strtoupper(substr($entry['status'], 0, 1)) ?>
                        </div>
                        <div class="history-details">
                            <div class="history-date">
                                <?= date('M j, Y \a\t g:i A', strtotime($entry['created_at'])) ?>
                            </div>
                            <strong class="text-capitalize"><?= ucfirst($entry['status']) ?></strong>
                            <?php if (!empty($entry['notes'])): ?>
                                <div class="history-notes">
                                    <?= htmlspecialchars($entry['notes']) ?>
                                </div>
                            <?php endif; ?>
                            <div class="history-updated-by">
                                Updated by: <?= htmlspecialchars($entry['updated_by']) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <a href="order.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>