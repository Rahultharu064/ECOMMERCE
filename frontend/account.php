<?php
session_start();
require '../includes/config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    if ($current_tab === 'profile') {
        // Update profile information
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        
        if (empty($name)) $errors[] = "Name is required";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
        if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) $errors[] = "Valid 10-digit phone number is required";
        
        if (empty($errors)) {
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $email, $phone, $address, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Profile updated successfully";
                header("Location: account.php?tab=profile");
                exit();
            } else {
                $errors[] = "Failed to update profile. Please try again.";
            }
        }
    } elseif ($current_tab === 'password') {
        // Change password
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = "Current password is incorrect";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "New passwords don't match";
        } elseif (strlen($new_password) < 8) {
            $errors[] = "Password must be at least 8 characters";
        }
        
        if (empty($errors)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Password changed successfully";
                header("Location: account.php?tab=password");
                exit();
            } else {
                $errors[] = "Failed to change password. Please try again.";
            }
        }
    }
}

// Get user's orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE email = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $user['email']);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account | PharmaCare</title>
    <link rel="stylesheet" href="../assets/css/pharmacy.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --accent-color: #e74c3c;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --success-color: #27ae60;
            --border-color: #e0e0e0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            line-height: 1.6;
        }
        
        .account-container {
            max-width: 1200px;
            margin: 360px auto 50px;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 30px;
        }
        
        .account-sidebar {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 20px 0;
        }
        
        .account-nav-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--dark-color);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .account-nav-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .account-nav-item:hover, 
        .account-nav-item.active {
            background: rgba(52, 152, 219, 0.1);
            color: var(--primary-color);
            border-left: 3px solid var(--primary-color);
        }
        
        .account-nav-item.active {
            font-weight: 600;
        }
        
        .account-content {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 30px;
        }
        
        .account-header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .account-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 10px;
        }
        
        .welcome-message {
            color: #666;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-group input, 
        .form-group textarea, 
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 15px;
        }
        
        .form-group input:focus, 
        .form-group textarea:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        .btn {
            padding: 12px 25px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
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
        
        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .alert.success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .alert.error {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .order-card {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .order-card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .order-number {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .order-date {
            color: #666;
            font-size: 14px;
        }
        
        .order-status {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .status-completed {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        .status-pending {
            background: #fff8e1;
            color: #f57f17;
        }
        
        .status-cancelled {
            background: #ffebee;
            color: #c62828;
        }
        
        .order-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .order-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .btn-sm {
            padding: 8px 15px;
            font-size: 14px;
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline:hover {
            background: rgba(52, 152, 219, 0.1);
        }
        
        @media (max-width: 768px) {
            .account-container {
                grid-template-columns: 1fr;
                margin-top: 80px;
            }
            
            .account-sidebar {
                display: flex;
                overflow-x: auto;
                padding: 10px 0;
            }
            
            .account-nav-item {
                white-space: nowrap;
                border-left: 3px solid transparent;
            }
            
            .order-details {
                grid-template-columns: 1fr;
            }
            
            .order-actions {
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="account-container">
        <div class="account-sidebar">
            <a href="account.php?tab=dashboard" class="account-nav-item <?php echo $current_tab === 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="account.php?tab=orders" class="account-nav-item <?php echo $current_tab === 'orders' ? 'active' : ''; ?>">
                <i class="fas fa-clipboard-list"></i> My Orders
            </a>
            <a href="account.php?tab=profile" class="account-nav-item <?php echo $current_tab === 'profile' ? 'active' : ''; ?>">
                <i class="fas fa-user"></i> Profile
            </a>
            <a href="account.php?tab=password" class="account-nav-item <?php echo $current_tab === 'password' ? 'active' : ''; ?>">
                <i class="fas fa-lock"></i> Password
            </a>
            <a href="account.php?tab=addresses" class="account-nav-item <?php echo $current_tab === 'addresses' ? 'active' : ''; ?>">
                <i class="fas fa-map-marker-alt"></i> Addresses
            </a>
            <a href="../includes/logout.php" class="account-nav-item">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
        
        <div class="account-content">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert success">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($current_tab === 'dashboard'): ?>
                <div class="account-header">
                    <h1 class="account-title">Dashboard</h1>
                    <p class="welcome-message">Hello, <?php echo htmlspecialchars($user['name']); ?>!</p>
                </div>
                
                <div class="account-summary">
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px;">
                        <div style="background: #e3f2fd; padding: 20px; border-radius: 8px; text-align: center;">
                            <div style="font-size: 24px; font-weight: 600; color: var(--primary-color);">
                                <?php echo count($orders); ?>
                            </div>
                            <div>Total Orders</div>
                        </div>
                        <div style="background: #e8f5e9; padding: 20px; border-radius: 8px; text-align: center;">
                            <div style="font-size: 24px; font-weight: 600; color: var(--success-color);">
                                <?php 
                                    $completed = array_filter($orders, function($order) {
                                        return $order['status'] === 'completed';
                                    });
                                    echo count($completed);
                                ?>
                            </div>
                            <div>Completed</div>
                        </div>
                        <div style="background: #fff8e1; padding: 20px; border-radius: 8px; text-align: center;">
                            <div style="font-size: 24px; font-weight: 600; color: #f57f17;">
                                <?php 
                                    $pending = array_filter($orders, function($order) {
                                        return $order['status'] === 'pending';
                                    });
                                    echo count($pending);
                                ?>
                            </div>
                            <div>Pending</div>
                        </div>
                    </div>
                    
                    <h3 style="margin-bottom: 15px;">Recent Orders</h3>
                    <?php if (!empty($orders)): ?>
                        <?php foreach (array_slice($orders, 0, 3) as $order): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <div>
                                        <span class="order-number">Order #<?php echo htmlspecialchars($order['order_number']); ?></span>
                                        <div class="order-date"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></div>
                                    </div>
                                    <div>
                                        <span class="order-status status-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="order-details">
                                    <div>
                                        <div><strong>Total:</strong> ₹<?php echo number_format($order['total_amount'], 2); ?></div>
                                        <div><strong>Payment:</strong> <?php echo ucfirst($order['payment_method']); ?></div>
                                    </div>
                                    <div>
                                        <div><strong>Payment Status:</strong> <?php echo ucfirst($order['payment_status']); ?></div>
                                        <div><strong>Shipping:</strong> <?php echo $order['shipping_fee'] > 0 ? '₹' . number_format($order['shipping_fee'], 2) : 'FREE'; ?></div>
                                    </div>
                                </div>
                                <div class="order-actions">
                                    <a href="order_success.php?order_id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline">
                                        View Order
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (count($orders) > 3): ?>
                            <div style="text-align: center; margin-top: 20px;">
                                <a href="account.php?tab=orders" class="btn btn-outline">
                                    View All Orders
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>You haven't placed any orders yet.</p>
                        <a href="../products.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag"></i> Shop Now
                        </a>
                    <?php endif; ?>
                </div>
                
            <?php elseif ($current_tab === 'orders'): ?>
                <div class="account-header">
                    <h1 class="account-title">My Orders</h1>
                    <p class="welcome-message">View your order history and track current orders</p>
                </div>
                
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div>
                                    <span class="order-number">Order #<?php echo htmlspecialchars($order['order_number']); ?></span>
                                    <div class="order-date"><?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></div>
                                </div>
                                <div>
                                    <span class="order-status status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="order-details">
                                <div>
                                    <div><strong>Total:</strong> ₹<?php echo number_format($order['total_amount'], 2); ?></div>
                                    <div><strong>Payment:</strong> <?php echo ucfirst($order['payment_method']); ?></div>
                                </div>
                                <div>
                                    <div><strong>Payment Status:</strong> <?php echo ucfirst($order['payment_status']); ?></div>
                                    <div><strong>Shipping:</strong> <?php echo $order['shipping_fee'] > 0 ? '₹' . number_format($order['shipping_fee'], 2) : 'FREE'; ?></div>
                                </div>
                            </div>
                            <div class="order-actions">
                                <a href="order_success.php?order_id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline">
                                    View Details
                                </a>
                                <?php if ($order['status'] === 'pending' && $order['payment_method'] !== 'cod'): ?>
                                    <a href="../checkout.php?order_id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                                        Pay Now
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>You haven't placed any orders yet.</p>
                    <a href="../products.php" class="btn btn-primary">
                        <i class="fas fa-shopping-bag"></i> Shop Now
                    </a>
                <?php endif; ?>
                
            <?php elseif ($current_tab === 'profile'): ?>
                <div class="account-header">
                    <h1 class="account-title">Profile Information</h1>
                    <p class="welcome-message">Update your account's profile information</p>
                </div>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($user['name']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($user['email']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" pattern="[0-9]{10}" title="Please enter a 10-digit phone number" required value="<?php echo htmlspecialchars($user['phone']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" rows="4"><?php echo htmlspecialchars($user['address']); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
                
            <?php elseif ($current_tab === 'password'): ?>
                <div class="account-header">
                    <h1 class="account-title">Change Password</h1>
                    <p class="welcome-message">Ensure your account is using a strong password</p>
                </div>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </form>
                
            <?php elseif ($current_tab === 'addresses'): ?>
                <div class="account-header">
                    <h1 class="account-title">My Addresses</h1>
                    <p class="welcome-message">Manage your shipping addresses</p>
                </div>
                
                <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                    <h3 style="margin-bottom: 15px;">Default Shipping Address</h3>
                    <?php if (!empty($user['address'])): ?>
                        <p><?php echo nl2br(htmlspecialchars($user['address'])); ?></p>
                        <p style="margin-top: 10px;">
                            <strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?>
                        </p>
                    <?php else: ?>
                        <p>No default address set.</p>
                    <?php endif; ?>
                    <a href="account.php?tab=profile" class="btn btn-outline" style="margin-top: 15px;">
                        <i class="fas fa-edit"></i> Edit Address
                    </a>
                </div>
                
                <p>You can add multiple addresses during checkout.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>