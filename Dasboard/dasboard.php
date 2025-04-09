<?php
session_start();
require '../includes/config.php';

// Check if user is logged in and is a pharmacist
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'pharmacist') {
    header("Location: ../frontend/login.php");
    exit();
}

// Initialize all variables with default values
$categories_count = 0;
$pharmacists_count = 0;
$customers_count = 0;
$today_orders_count = 0;
$total_revenue = 0;
$delivered_orders_count = 0;
$pending_orders_count = 0;
$processing_orders_count = 0;
$products_count = 0;
$low_stock_count = 0;
$out_of_stock_count = 0;
$monthly_revenue = [];
$top_categories = [];
$recent_orders = [];

// Total Categories
$sql = "SELECT COUNT(*) AS count FROM categories";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $categories_count = $row['count'];
}

// Total Products
$sql = "SELECT COUNT(*) AS count FROM products";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $products_count = $row['count'];
}

// Low Stock Products (quantity < 10)
$sql = "SELECT COUNT(*) AS count FROM products WHERE quantity < 10 AND quantity > 0";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $low_stock_count = $row['count'];
}

// Out of Stock Products (quantity = 0)
$sql = "SELECT COUNT(*) AS count FROM products WHERE quantity = 0";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $out_of_stock_count = $row['count'];
}

// Total Pharmacists
$sql = "SELECT COUNT(*) AS count FROM users WHERE role = 'pharmacist'";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $pharmacists_count = $row['count'];
}

// Total Customers
$sql = "SELECT COUNT(*) AS count FROM users WHERE role = 'customer'";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $customers_count = $row['count'];
}

// Today's Orders
$sql = "SELECT COUNT(*) AS count FROM orders WHERE DATE(created_at) = CURDATE()";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $today_orders_count = $row['count'];
}

// Total Revenue
$sql = "SELECT COALESCE(SUM(total_amount), 0) AS revenue FROM orders";
$result = $conn->query($sql);
if ($result) {
    $row = $result->fetch_assoc();
    $total_revenue = $row['revenue'] ?? 0;
}

// Monthly Revenue (last 6 months)
$sql = "SELECT 
            DATE_FORMAT(created_at, '%Y-%m') AS month, 
            COALESCE(SUM(total_amount), 0) AS revenue 
        FROM orders 
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $monthly_revenue[] = $row;
    }
}

// Completed Orders
$sql = "SELECT COUNT(*) AS count FROM orders WHERE status = 'completed'";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $delivered_orders_count = $row['count'];
}

// Pending Orders
$sql = "SELECT COUNT(*) AS count FROM orders WHERE status = 'pending'";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $pending_orders_count = $row['count'];
}

// Processing Orders
$sql = "SELECT COUNT(*) AS count FROM orders WHERE status = 'processing'";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $processing_orders_count = $row['count'];
}

// Top Categories by Sales
$sql = "SELECT 
            c.id,
            c.name AS category_name, 
            COUNT(DISTINCT o.id) AS order_count,
            COALESCE(SUM(oi.quantity), 0) AS total_quantity,
            COALESCE(SUM(oi.price * oi.quantity), 0) AS total_revenue
        FROM categories c
        LEFT JOIN products p ON c.id = p.category_id
        LEFT JOIN order_items oi ON p.id = oi.product_id
        LEFT JOIN orders o ON oi.order_id = o.id AND o.status = 'completed'
        GROUP BY c.id
        HAVING total_quantity > 0
        ORDER BY total_quantity DESC
        LIMIT 5";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $top_categories[] = $row;
    }
}

// Recent Orders (last 5)
$sql = "SELECT 
            o.id, 
            o.order_number, 
            u.name AS customer_name, 
            o.total_amount, 
            o.status,
            DATE_FORMAT(o.created_at, '%d %b %Y %h:%i %p') AS order_date
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
        LIMIT 5";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recent_orders[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link rel="stylesheet" href="../assets/css/dashboard.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    :root {
      --primary-color: #2c3e50;
      --secondary-color: #3498db;
      --pending-color: #f39c12;
      --processing-color: #2980b9;
      --success-color: #27ae60;
      --revenue-color: #2ecc71;
      --warning-color: #e67e22;
      --danger-color: #e74c3c;
      --text-color: #34495e;
      --card-bg: #ffffff;
      --chart-bg: #f8f9fa;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f5f7fa;
      margin: 0;
      padding: 0;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 1.5rem;
      padding: 1.5rem;
    }

    .card {
      background: var(--card-bg);
      border-radius: 15px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      overflow: hidden;
      position: relative;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
    }

    .card-header {
      padding: 1.5rem;
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .card.pending .card-header {
      background: linear-gradient(135deg, var(--pending-color) 0%, #f1c40f 100%);
    }

    .card.processing .card-header {
      background: linear-gradient(135deg, var(--processing-color) 0%, #3498db 100%);
    }

    .card.completed .card-header {
      background: linear-gradient(135deg, var(--success-color) 0%, #2ecc71 100%);
    }

    .card.revenue .card-header {
      background: linear-gradient(135deg, var(--revenue-color) 0%, #27ae60 100%);
    }

    .card.warning .card-header {
      background: linear-gradient(135deg, var(--warning-color) 0%, #d35400 100%);
    }

    .card.danger .card-header {
      background: linear-gradient(135deg, var(--danger-color) 0%, #c0392b 100%);
    }

    .card-title {
      font-size: 2.2rem;
      font-weight: 600;
      margin: 0;
    }

    .card-content {
      padding: 1.5rem;
      background-color: var(--card-bg);
    }

    .card-content p {
      margin: 0;
      font-size: 1.1rem;
      color: var(--text-color);
      font-weight: 500;
    }

    .icon {
      font-size: 2.5rem;
      opacity: 0.9;
      transition: transform 0.3s ease;
    }

    .card:hover .icon {
      transform: scale(1.1) rotate(10deg);
    }

    /* Chart Section Styles */
    .chart-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.5rem;
      padding: 0 1.5rem 1.5rem;
    }

    @media (max-width: 1200px) {
      .chart-row {
        grid-template-columns: 1fr;
      }
    }

    .chart-card {
      background: var(--card-bg);
      border-radius: 15px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      padding: 1.5rem;
      display: flex;
      flex-direction: column;
    }

    .chart-wrapper {
      position: relative;
      height: 300px;
      margin-top: 20px;
    }

    .chart-legend {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 20px;
      justify-content: center;
    }

    .legend-item {
      display: flex;
      align-items: center;
      font-size: 0.9rem;
      margin-right: 10px;
      background: #f8f9fa;
      padding: 5px 10px;
      border-radius: 20px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .legend-color {
      width: 15px;
      height: 15px;
      border-radius: 3px;
      margin-right: 8px;
      display: inline-block;
    }

    /* Recent Orders Table */
    .data-section {
      padding: 0 1.5rem 1.5rem;
    }

    .data-card {
      background: var(--card-bg);
      border-radius: 15px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      padding: 1.5rem;
    }

    .data-card h3 {
      margin-top: 0;
      color: var(--primary-color);
      border-bottom: 2px solid #eee;
      padding-bottom: 0.5rem;
    }

    .recent-orders table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }

    .recent-orders th, .recent-orders td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #eee;
    }

    .recent-orders th {
      background-color: var(--primary-color);
      color: white;
      font-weight: 500;
    }

    .recent-orders tr:hover {
      background-color: rgba(52, 152, 219, 0.05);
    }

    .status-badge-table {
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
      text-transform: capitalize;
      display: inline-block;
      min-width: 80px;
      text-align: center;
    }

    .status-pending {
      background-color: var(--pending-color);
      color: white;
    }

    .status-processing {
      background-color: var(--processing-color);
      color: white;
    }

    .status-completed {
      background-color: var(--success-color);
      color: white;
    }

    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 40px 20px;
      color: var(--text-color);
    }

    .empty-state i {
      font-size: 50px;
      color: #dfe6e9;
      margin-bottom: 15px;
    }

    .empty-state h3 {
      margin: 10px 0;
      color: var(--secondary-color);
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
      .stats-grid {
        grid-template-columns: 1fr 1fr;
        padding: 1rem;
        gap: 1rem;
      }
      
      .chart-row, .data-section {
        padding: 0 1rem 1rem;
      }
      
      .chart-card {
        padding: 1rem;
      }
      
      .chart-wrapper {
        height: 250px;
      }
      
      .recent-orders table {
        font-size: 0.9rem;
      }
      
      .recent-orders th, 
      .recent-orders td {
        padding: 8px 10px;
      }
    }

    @media (max-width: 576px) {
      .stats-grid {
        grid-template-columns: 1fr;
      }
      
      .card-header {
        padding: 1rem;
      }
      
      .card-title {
        font-size: 1.8rem;
      }
      
      .icon {
        font-size: 2rem;
      }
    }
  </style>
</head>
<body>
<?php include '../Dasboard/Navbar.php'; ?>

  <section style="display: flex; width: 100%">
   <?php include '../Dasboard/Sidebar.php'; ?>
    
    <main class="content">
    <div class="stats-grid">
      <!-- Total Categories -->
      <div class="card">
        <div class="card-header">
          <h2 class="card-title"><?php echo $categories_count; ?></h2>
          <i class="fas fa-th-large icon"></i>
        </div>
        <div class="card-content">
          <p>Total Categories</p>
        </div>
      </div>

      <!-- Total Products -->
      <div class="card">
        <div class="card-header">
          <h2 class="card-title"><?php echo $products_count; ?></h2>
          <i class="fas fa-pills icon"></i>
        </div>
        <div class="card-content">
          <p>Total Products</p>
        </div>
      </div>

      <!-- Low Stock Products -->
      <div class="card warning">
        <div class="card-header">
          <h2 class="card-title"><?php echo $low_stock_count; ?></h2>
          <i class="fas fa-exclamation-triangle icon"></i>
        </div>
        <div class="card-content">
          <p>Low Stock Products</p>
        </div>
      </div>

      <!-- Out of Stock Products -->
      <div class="card danger">
        <div class="card-header">
          <h2 class="card-title"><?php echo $out_of_stock_count; ?></h2>
          <i class="fas fa-times-circle icon"></i>
        </div>
        <div class="card-content">
          <p>Out of Stock Products</p>
        </div>
      </div>

      <!-- Total Pharmacists -->
      <div class="card">
        <div class="card-header">
          <h2 class="card-title"><?php echo $pharmacists_count; ?></h2>
          <i class="fas fa-user-md icon"></i>
        </div>
        <div class="card-content">
          <p>Total Pharmacists</p>
        </div>
      </div>

      <!-- Total Customers -->
      <div class="card">
        <div class="card-header">
          <h2 class="card-title"><?php echo $customers_count; ?></h2>
          <i class="fas fa-users icon"></i>
        </div>
        <div class="card-content">
          <p>Total Customers</p>
        </div>
      </div>

      <!-- Today's Orders -->
      <div class="card">
        <div class="card-header">
          <h2 class="card-title"><?php echo $today_orders_count; ?></h2>
          <i class="fas fa-shopping-cart icon"></i>
        </div>
        <div class="card-content">
          <p>Today's Orders</p>
        </div>
      </div>

      <!-- Pending Orders -->
      <div class="card pending">
        <div class="card-header">
          <h2 class="card-title"><?php echo $pending_orders_count; ?></h2>
          <i class="fas fa-clock icon"></i>
        </div>
        <div class="card-content">
          <p>Pending Orders</p>
        </div>
      </div>

      <!-- Processing Orders -->
      <div class="card processing">
        <div class="card-header">
          <h2 class="card-title"><?php echo $processing_orders_count; ?></h2>
          <i class="fas fa-sync-alt icon"></i>
        </div>
        <div class="card-content">
          <p>Processing Orders</p>
        </div>
      </div>

      <!-- Total Revenue -->
      <div class="card revenue">
        <div class="card-header">
          <h2 class="card-title">₹<?php echo number_format($total_revenue, 2); ?></h2>
          <i class="fas fa-rupee-sign icon"></i>
        </div>
        <div class="card-content">
          <p>Total Revenue</p>
        </div>
      </div>

      <!-- Completed Orders -->
      <div class="card completed">
        <div class="card-header">
          <h2 class="card-title"><?php echo $delivered_orders_count; ?></h2>
          <i class="fas fa-check-circle icon"></i>
        </div>
        <div class="card-content">
          <p>Completed Orders</p>
        </div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="chart-row">
      <!-- Top Categories Chart -->
      <div class="chart-card">
        <h3>Top Selling Categories</h3>
        <div class="chart-wrapper">
          <canvas id="categoriesChart"></canvas>
        </div>
        <div class="chart-legend" id="categoriesLegend"></div>
      </div>

      <!-- Revenue Chart -->
      <div class="chart-card">
        <h3>Monthly Revenue (Last 6 Months)</h3>
        <div class="chart-wrapper">
          <canvas id="revenueChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Recent Orders Section -->
    <div class="data-section">
      <div class="data-card recent-orders">
        <h3>Recent Orders</h3>
        <?php if (!empty($recent_orders)): ?>
          <table>
            <thead>
              <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recent_orders as $order): ?>
                <tr>
                  <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                  <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                  <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                  <td>
                    <span class="status-badge-table status-<?php echo $order['status']; ?>">
                      <?php echo htmlspecialchars($order['status']); ?>
                    </span>
                  </td>
                  <td><?php echo $order['order_date']; ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <div class="empty-state">
            <i class="fas fa-shopping-cart"></i>
            <h3>No Recent Orders</h3>
            <p>No orders have been placed recently</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
    </main>
  </section>
  <script src="../assets/js/dashboard.js"></script>
  <script>
    // Color generator function for charts
    function generateColors(count) {
      const colors = [];
      const baseColors = [
        '#3498db', '#2ecc71', '#f39c12', '#e74c3c', '#9b59b6', 
        '#1abc9c', '#d35400', '#34495e', '#16a085', '#c0392b'
      ];
      
      for (let i = 0; i < count; i++) {
        colors.push(baseColors[i % baseColors.length]);
      }
      return colors;
    }

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode(array_column($monthly_revenue, 'month')); ?>,
        datasets: [{
          label: 'Revenue (₹)',
          data: <?php echo json_encode(array_column($monthly_revenue, 'revenue')); ?>,
          backgroundColor: 'rgba(46, 204, 113, 0.7)',
          borderColor: 'rgba(46, 204, 113, 1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: 'Amount (₹)'
            }
          },
          x: {
            title: {
              display: true,
              text: 'Month'
            }
          }
        },
        plugins: {
          tooltip: {
            callbacks: {
              label: function(context) {
                return '₹' + context.raw.toLocaleString('en-IN', {minimumFractionDigits: 2});
              }
            }
          }
        }
      }
    });

    // Categories Doughnut Chart
    <?php if (!empty($top_categories)): ?>
      const categoriesData = {
        labels: <?php echo json_encode(array_column($top_categories, 'category_name')); ?>,
        datasets: [{
          data: <?php echo json_encode(array_column($top_categories, 'total_quantity')); ?>,
          backgroundColor: generateColors(<?php echo count($top_categories); ?>),
          borderWidth: 1
        }]
      };
      
      const categoriesCtx = document.getElementById('categoriesChart').getContext('2d');
      const categoriesChart = new Chart(categoriesCtx, {
        type: 'doughnut',
        data: categoriesData,
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  const label = context.label || '';
                  const value = context.raw || 0;
                  const total = context.dataset.data.reduce((a, b) => a + b, 0);
                  const percentage = Math.round((value / total) * 100);
                  return `${label}: ${value} sold (${percentage}%)`;
                }
              }
            }
          },
          cutout: '65%',
          animation: {
            animateScale: true,
            animateRotate: true
          }
        }
      });
      
      // Generate custom legend
      const legendContainer = document.getElementById('categoriesLegend');
      categoriesData.labels.forEach((label, i) => {
        const legendItem = document.createElement('div');
        legendItem.className = 'legend-item';
        legendItem.innerHTML = `
          <span class="legend-color" style="background-color: ${categoriesData.datasets[0].backgroundColor[i]}"></span>
          <span>${label}</span>
        `;
        legendContainer.appendChild(legendItem);
      });
    <?php else: ?>
      document.querySelector('.chart-card h3').insertAdjacentHTML('afterend', 
        '<div class="empty-state"><i class="fas fa-box-open"></i><h3>No Category Data</h3><p>Sales data will appear when orders are completed</p></div>');
    <?php endif; ?>
  </script>
</body>
</html>