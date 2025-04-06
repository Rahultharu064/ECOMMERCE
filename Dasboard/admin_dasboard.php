<?php
session_start();
include "../includes/config.php";

// Check if admin is logged in
// if (!isset($_SESSION['user_email']) || $_SESSION['user_role'] !== 'admin') {
//     header("Location: ../frontend/Homepage.php");
//     exit();
// }

// Get all users with default status if not set
$sql = "SELECT id, name, email, phone, gender, dob, address, province, city, 
               role, IFNULL(status, 'active') as status, created_at, updated_at 
        FROM users ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
$users = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - User Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        /* Admin Dashboard Styles */
        .admin-container {
            display: flex;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-content {
            flex: 1;
            background: #f5f6fa;
            margin-left: 250px; /* Match sidebar width */
            padding-top: 60px; /* For fixed header */
        }

        .content {
            padding: 25px;
        }

        .action-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .btn-add {
            background: #27ae60;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-add:hover {
            background: #2ecc71;
        }

        .search-bar {
            display: flex;
        }

        .search-bar input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px 0 0 4px;
            width: 250px;
        }

        .search-bar button {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }

        .table-container {
            background: #fff;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background: #f8f9fa;
            font-weight: 600;
        }

        table tr:hover {
            background: #f8f9fa;
        }

        .role-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .role-badge.admin {
            background: #e74c3c;
            color: white;
        }

        .role-badge.pharmacist {
            background: #3498db;
            color: white;
        }

        .role-badge.customer {
            background: #2ecc71;
            color: white;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-badge.active {
            background: #2ecc71;
            color: white;
        }

        .status-badge.inactive {
            background: #f39c12;
            color: white;
        }

        .status-badge.suspended {
            background: #e74c3c;
            color: white;
        }

        .actions {
            display: flex;
            gap: 5px;
        }

        .actions button {
            border: none;
            padding: 5px 8px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-edit {
            background: #3498db;
            color: white;
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
        }

        .btn-view {
            background: #9b59b6;
            color: white;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 5px;
        }

        .pagination button, .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background: #fff;
            cursor: pointer;
        }

        .pagination span.active {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border-radius: 5px;
            width: 50%;
            max-width: 600px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .btn-submit {
            background: #27ae60;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background: #2ecc71;
        }
    </style>
</head>
<body>
   <?php include '../Dasboard/Navbar.php'; ?>
   <?php include '../Dasboard/Sidebar.php'; ?>

   <!-- Main Content -->
   <div class="main-content">
       <div class="content">
           <!-- Add New User Button -->
           <div class="action-bar">
               <button class="btn-add" onclick="openAddUserModal()">
                   <i class="fas fa-plus"></i> Add New User
               </button>
               <div class="search-bar">
                   <input type="text" id="searchInput" placeholder="Search users...">
                   <button><i class="fas fa-search"></i></button>
               </div>
           </div>

           <!-- Users Table -->
           <div class="table-container">
               <table>
                   <thead>
                       <tr>
                           <th>ID</th>
                           <th>Name</th>
                           <th>Email</th>
                           <th>Phone</th>
                           <th>Role</th>
                           <th>Status</th>
                           <th>Joined</th>
                           <th>Actions</th>
                       </tr>
                   </thead>
                   <tbody>
                       <?php foreach ($users as $user): ?>
                       <tr>
                           <td><?php echo $user['id']; ?></td>
                           <td><?php echo htmlspecialchars($user['name']); ?></td>
                           <td><?php echo htmlspecialchars($user['email']); ?></td>
                           <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                           <td>
                               <span class="role-badge <?php echo $user['role']; ?>">
                                   <?php echo ucfirst($user['role']); ?>
                               </span>
                           </td>
                           <td>
                               <span class="status-badge <?php echo $user['status']; ?>">
                                   <?php echo ucfirst($user['status']); ?>
                               </span>
                           </td>
                           <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                           <td class="actions">
                               <button class="btn-edit" onclick="openEditModal(<?php echo $user['id']; ?>)">
                                   <i class="fas fa-edit"></i>
                               </button>
                               <button class="btn-delete" onclick="confirmDelete(<?php echo $user['id']; ?>)">
                                   <i class="fas fa-trash"></i>
                               </button>
                               <button class="btn-view" onclick="viewUser(<?php echo $user['id']; ?>)">
                                   <i class="fas fa-eye"></i>
                               </button>
                           </td>
                       </tr>
                       <?php endforeach; ?>
                   </tbody>
               </table>
           </div>

           <!-- Pagination -->
           <div class="pagination">
               <button><i class="fas fa-angle-left"></i></button>
               <span class="active">1</span>
               <span>2</span>
               <span>3</span>
               <button><i class="fas fa-angle-right"></i></button>
           </div>
       </div>
   </div>

   <!-- Add User Modal -->
   <div id="addUserModal" class="modal">
       <div class="modal-content">
           <span class="close" onclick="closeAddUserModal()">&times;</span>
           <h2>Add New User</h2>
           <form id="addUserForm" action="../includes/add_user.php" method="POST">
               <div class="form-group">
                   <label for="add_name">Full Name</label>
                   <input type="text" id="add_name" name="name" required>
               </div>
               
               <div class="form-group">
                   <label for="add_email">Email</label>
                   <input type="email" id="add_email" name="email" required>
               </div>
               
               <div class="form-group">
                   <label for="add_password">Password</label>
                   <input type="password" id="add_password" name="password" required>
               </div>
               
               <div class="form-group">
                   <label for="add_role">Role</label>
                   <select id="add_role" name="role" required>
                       <option value="customer">Customer</option>
                       <option value="pharmacist">Pharmacist</option>
                      
                   </select>
               </div>
               
               <div class="form-group">
                   <label for="add_status">Status</label>
                   <select id="add_status" name="status" required>
                       <option value="active">Active</option>
                       <option value="inactive">Inactive</option>
                       <option value="suspended">Suspended</option>
                   </select>
               </div>
               
               <button type="submit" class="btn-submit">Add User</button>
           </form>
       </div>
   </div>

   <!-- Edit User Modal -->
   <div id="editUserModal" class="modal">
       <div class="modal-content">
           <!-- Content will be loaded via AJAX -->
       </div>
   </div>

   <!-- View User Modal -->
   <div id="viewUserModal" class="modal">
       <div class="modal-content">
           <!-- Content will be loaded via AJAX -->
       </div>
   </div>

   <script>
       // Open Add User Modal
       function openAddUserModal() {
           document.getElementById('addUserModal').style.display = 'block';
       }

       // Close Add User Modal
       function closeAddUserModal() {
           document.getElementById('addUserModal').style.display = 'none';
       }

       // Open Edit Modal with user data
       function openEditModal(userId) {
           fetch(`../Dasboard/get_user.php?id=${userId}`)
               .then(response => response.text())
               .then(data => {
                   document.getElementById('editUserModal').querySelector('.modal-content').innerHTML = data;
                   document.getElementById('editUserModal').style.display = 'block';
               })
               .catch(error => console.error('Error:', error));
       }

       // Close Edit Modal
       function closeEditModal() {
           document.getElementById('editUserModal').style.display = 'none';
       }

       // View User Details
       function viewUser(userId) {
           fetch(`../Dasboard/view_user.php?id=${userId}`)
               .then(response => response.text())
               .then(data => {
                   document.getElementById('viewUserModal').querySelector('.modal-content').innerHTML = data;
                   document.getElementById('viewUserModal').style.display = 'block';
               })
               .catch(error => console.error('Error:', error));
       }

       // Close View Modal
       function closeViewModal() {
           document.getElementById('viewUserModal').style.display = 'none';
       }

       // Confirm before deleting user
       function confirmDelete(userId) {
           if (confirm('Are you sure you want to delete this user?')) {
               window.location.href = `../Dasboard/delete_user.php?id=${userId}`;
           }
       }

       // Search functionality
       document.getElementById('searchInput').addEventListener('keyup', function() {
           const searchValue = this.value.toLowerCase();
           const rows = document.querySelectorAll('table tbody tr');
           
           rows.forEach(row => {
               const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
               const email = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
               if (name.includes(searchValue) || email.includes(searchValue)) {
                   row.style.display = '';
               } else {
                   row.style.display = 'none';
               }
           });
       });

       // Close modals when clicking outside
       window.onclick = function(event) {
           const modals = ['addUserModal', 'editUserModal', 'viewUserModal'];
           modals.forEach(modalId => {
               const modal = document.getElementById(modalId);
               if (event.target == modal) {
                   modal.style.display = 'none';
               }
           });
       }
   </script>
</body>
</html>