<?php
include "../includes/config.php";

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $sql = "SELECT * FROM users WHERE id = '$id'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);
    
    if ($user) {
        echo '
        <span class="close" onclick="closeViewModal()">&times;</span>
        <h2>User Details</h2>
        
        <div class="user-details">
            <div class="detail-row">
                <span class="detail-label">ID:</span>
                <span class="detail-value">'.$user['id'].'</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Name:</span>
                <span class="detail-value">'.htmlspecialchars($user['name']).'</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Email:</span>
                <span class="detail-value">'.htmlspecialchars($user['email']).'</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Phone:</span>
                <span class="detail-value">'.htmlspecialchars($user['phone']).'</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Gender:</span>
                <span class="detail-value">'.ucfirst($user['gender']).'</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Date of Birth:</span>
                <span class="detail-value">'.$user['dob'].'</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Address:</span>
                <span class="detail-value">'.htmlspecialchars($user['address']).'</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Province:</span>
                <span class="detail-value">'.ucfirst($user['province']).'</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">City:</span>
                <span class="detail-value">'.htmlspecialchars($user['city']).'</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Role:</span>
                <span class="detail-value role-badge '.$user['role'].'">'.ucfirst($user['role']).'</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span class="detail-value status-badge '.$user['status'].'">'.ucfirst($user['status']).'</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Joined:</span>
                <span class="detail-value">'.date('M d, Y H:i', strtotime($user['created_at'])).'</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Last Updated:</span>
                <span class="detail-value">'.date('M d, Y H:i', strtotime($user['updated_at'])).'</span>
            </div>
        </div>';
    } else {
        echo '<p>User not found</p>';
    }
}
?>