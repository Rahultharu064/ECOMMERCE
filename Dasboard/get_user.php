<?php
include "../includes/config.php";

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $sql = "SELECT * FROM users WHERE id = '$id'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);
    
    if ($user) {
        echo '
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h2>Edit User</h2>
        <div class="scrollable-container">
            <form id="editUserForm" action="../includes/edit_user.php" method="POST">
                <input type="hidden" name="id" value="'.$user['id'].'">
                
                <div class="form-group">
                    <label for="edit_name">Full Name</label>
                    <input type="text" id="edit_name" name="name" value="'.htmlspecialchars($user['name']).'" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_email">Email</label>
                    <input type="email" id="edit_email" name="email" value="'.htmlspecialchars($user['email']).'" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_phone">Phone</label>
                    <input type="text" id="edit_phone" name="phone" value="'.htmlspecialchars($user['phone']).'">
                </div>
                
                <div class="form-group">
                    <label for="edit_gender">Gender</label>
                    <select id="edit_gender" name="gender">
                        <option value="male" '.($user['gender'] == 'male' ? 'selected' : '').'>Male</option>
                        <option value="female" '.($user['gender'] == 'female' ? 'selected' : '').'>Female</option>
                        <option value="other" '.($user['gender'] == 'other' ? 'selected' : '').'>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit_dob">Date of Birth</label>
                    <input type="date" id="edit_dob" name="dob" value="'.$user['dob'].'">
                </div>
                
                <div class="form-group">
                    <label for="edit_address">Address</label>
                    <input type="text" id="edit_address" name="address" value="'.htmlspecialchars($user['address']).'">
                </div>
                
                <div class="form-group">
                    <label for="edit_province">Province</label>
                    <select id="edit_province" name="province">
                        <option value="koshi" '.($user['province'] == 'koshi' ? 'selected' : '').'>Koshi Province</option>
                        <option value="madhesh" '.($user['province'] == 'madhesh' ? 'selected' : '').'>Madhesh Province</option>
                        <option value="bagmati" '.($user['province'] == 'bagmati' ? 'selected' : '').'>Bagmati Province</option>
                        <option value="gandaki" '.($user['province'] == 'gandaki' ? 'selected' : '').'>Gandaki Province</option>
                        <option value="lumbini" '.($user['province'] == 'lumbini' ? 'selected' : '').'>Lumbini Province</option>
                        <option value="karnali" '.($user['province'] == 'karnali' ? 'selected' : '').'>Karnali Province</option>
                        <option value="sudurpaschim" '.($user['province'] == 'sudurpaschim' ? 'selected' : '').'>Sudurpaschim Province</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit_city">City/Municipality</label>
                    <input type="text" id="edit_city" name="city" value="'.htmlspecialchars($user['city']).'">
                </div>
                
                <div class="form-group">
                    <label for="edit_role">Role</label>
                    <select id="edit_role" name="role" required>
                        <option value="customer" '.($user['role'] == 'customer' ? 'selected' : '').'>Customer</option>
                        <option value="pharmacist" '.($user['role'] == 'pharmacist' ? 'selected' : '').'>Pharmacist</option>
                        <option value="admin" '.($user['role'] == 'admin' ? 'selected' : '').'>Admin</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit_status">Status</label>
                    <select id="edit_status" name="status" required>
                        <option value="active" '.($user['status'] == 'active' ? 'selected' : '').'>Active</option>
                        <option value="inactive" '.($user['status'] == 'inactive' ? 'selected' : '').'>Inactive</option>
                        <option value="suspended" '.($user['status'] == 'suspended' ? 'selected' : '').'>Suspended</option>
                    </select>
                </div>
                
                <button type="submit" class="btn-submit">Update User</button>
            </form>
        </div>';
    } else {
        echo '<p>User not found</p>';
    }
}
?><script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function closeEditModal() {
        $('#editUserModal').hide();
    }
</script>
<style>
    .scrollable-container {
        max-height: 400px; /* Adjust height as needed */
        overflow-y: auto;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }
</style>