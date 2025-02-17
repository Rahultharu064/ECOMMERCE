<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://unpkg.com/lucide@latest">
</head>
<body>
<?php include '../Dasboard/Navbar.php'; ?>
    <section style="display: flex; width: 100%">
     <?php include '../Dasboard/Sidebar.php'; ?>
      <!-- //// main co -->
      <main class="content">
        <div class="stats-grid">
          <!-- Total Categories -->
          <!-- <?php include '../Dasboard/Navbar.php'; ?>
          <?php include '../Dasboard/Sidebar.php'; ?> -->
          <div class="card">
            <div class="card-header">
              <h2 class="card-title">9</h2>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                class="icon"
              >
                <path
                  d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"
                />
              </svg>
            </div>
            <div class="card-content">
              <p>Total Categories</p>
            </div>
          </div>

          <!-- Total Books -->
          <div class="card">
            <div class="card-header">
              <h2 class="card-title">16</h2>
              <i data-lucide="book" class="icon"></i>
            </div>
            <div class="card-content">
              <p>Total Books</p>
            </div>
          </div>

          <!-- Total Admins -->
          <div class="card">
            <div class="card-header">
              <h2 class="card-title">4</h2>
              <i data-lucide="users" class="icon"></i>
            </div>
            <div class="card-content">
              <p>Total Admins</p>
            </div>
          </div>

          <!-- Total Customers -->
          <div class="card">
            <div class="card-header">
              <h2 class="card-title">6</h2>
              <i data-lucide="users" class="icon"></i>
            </div>
            <div class="card-content">
              <p>Total Customers</p>
            </div>
          </div>

          <!-- Today's Orders -->
          <div class="card">
            <div class="card-header">
              <h2 class="card-title">0</h2>
              <i data-lucide="shopping-cart" class="icon"></i>
            </div>
            <div class="card-content">
              <p>Today's Orders</p>
            </div>
          </div>

          <!-- Today's Revenue -->
          <div class="card">
            <div class="card-header">
              <h2 class="card-title">Rs. 0</h2>
              <i data-lucide="dollar-sign" class="icon"></i>
            </div>
            <div class="card-content">
              <p>Today's Revenue</p>
            </div>
          </div>

          <!-- Total Revenue -->
          <div class="card">
            <div class="card-header">
              <h2 class="card-title">Rs. 26,655</h2>
              <i data-lucide="dollar-sign" class="icon"></i>
            </div>
            <div class="card-content">
              <p>Total Revenue</p>
            </div>
          </div>

          <!-- Total Orders -->
          <div class="card">
            <div class="card-header">
              <h2 class="card-title">15</h2>
              <i data-lucide="shopping-cart" class="icon"></i>
            </div>
            <div class="card-content">
              <p>Total Orders</p>
            </div>
          </div>

          <!-- Total Delivered Orders -->
          <div class="card">
            <div class="card-header">
              <h2 class="card-title">8</h2>
              <i data-lucide="package" class="icon"></i>
            </div>
            <div class="card-content">
              <p>Total Delivered Orders</p>
            </div>
          </div>

          <!-- Units of Book Sold -->
          <div class="card">
            <div class="card-header">
              <h2 class="card-title">50</h2>
              <i data-lucide="book" class="icon"></i>
            </div>
            <div class="card-content">
              <p>Units of Book Sold</p>
            </div>
          </div>

          <!-- Popular Book -->
          <div class="card popular-book-card">
            <div class="card-header">
              <div>
                <h2 class="card-title">Seto Dharti</h2>
                <p class="card-content">23 Units Sold</p>
                <p class="card-content">Popular Book</p>
              </div>
              <img src="shap1.jpg" alt="Book Cover" />
            </div>
          </div>
        </div>
      </main>
    </section>
    <script src ="../assets/js/dashboard.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</body>
</html>