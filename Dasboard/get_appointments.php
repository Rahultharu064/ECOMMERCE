<?php
session_start();
require '../includes/config.php';
require '../includes/functions.php';

// Only allow admins
if (!is_logged_in() || $_SESSION['role'] !== 'admin') {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

$appointment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    // Get appointment details
    $stmt = $conn->prepare("SELECT 
        a.*,
        p.name AS patient_name, p.email AS patient_email, p.phone AS patient_phone,
        d.name AS doctor_name, doc.specialization, doc.consultation_fee,
        u1.name AS created_by_name, u2.name AS updated_by_name
        FROM appointments a
        JOIN users p ON a.patient_id = p.id
        JOIN users doc_user ON a.doctor_id = doc_user.id
        JOIN doctors doc ON doc_user.id = doc.user_id
        JOIN users d ON a.doctor_id = d.id
        LEFT JOIN users u1 ON a.created_by = u1.id
        LEFT JOIN users u2 ON a.updated_by = u2.id
        WHERE a.id = ?");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $appointment = $stmt->get_result()->fetch_assoc();

    if (!$appointment) {
        header("HTTP/1.1 404 Not Found");
        exit();
    }

    // Get appointment history
    $stmt = $conn->prepare("SELECT 
        h.*, u.name AS changed_by_name
        FROM appointment_history h
        JOIN users u ON h.changed_by = u.id
        WHERE h.appointment_id = ?
        ORDER BY h.changed_at DESC");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Format date and time
    $appointment_date = date('F j, Y', strtotime($appointment['appointment_date']));
    $appointment_time = date('g:i A', strtotime($appointment['appointment_time']));
    $created_at = date('M j, Y g:i A', strtotime($appointment['created_at']));
    $updated_at = $appointment['updated_at'] ? 
        date('M j, Y g:i A', strtotime($appointment['updated_at'])) : 'Never';
    
    // Generate status badge
    $status_badge = "<span class='status-badge {$appointment['status']}'>
        ".ucfirst($appointment['status'])."
    </span>";
    
    // Output HTML
    echo "<div class='appointment-details'>
        <div class='detail-header'>
            <h3>Appointment #{$appointment['id']}</h3>
            {$status_badge}
        </div>
        
        <div class='detail-section'>
            <h4>Patient Information</h4>
            <div class='detail-row'>
                <span class='detail-label'>Name:</span>
                <span class='detail-value'>{$appointment['patient_name']}</span>
            </div>
            <div class='detail-row'>
                <span class='detail-label'>Email:</span>
                <span class='detail-value'>{$appointment['patient_email']}</span>
            </div>
            <div class='detail-row'>
                <span class='detail-label'>Phone:</span>
                <span class='detail-value'>{$appointment['patient_phone']}</span>
            </div>
        </div>
        
        <div class='detail-section'>
            <h4>Doctor Information</h4>
            <div class='detail-row'>
                <span class='detail-label'>Doctor:</span>
                <span class='detail-value'>Dr. {$appointment['doctor_name']}</span>
            </div>
            <div class='detail-row'>
                <span class='detail-label'>Specialization:</span>
                <span class='detail-value'>{$appointment['specialization']}</span>
            </div>
            <div class='detail-row'>
                <span class='detail-label'>Consultation Fee:</span>
                <span class='detail-value'>$".number_format($appointment['consultation_fee'], 2)."</span>
            </div>
        </div>
        
        <div class='detail-section'>
            <h4>Appointment Details</h4>
            <div class='detail-row'>
                <span class='detail-label'>Date & Time:</span>
                <span class='detail-value'>{$appointment_date} at {$appointment_time}</span>
            </div>
            <div class='detail-row'>
                <span class='detail-label'>Reason:</span>
                <span class='detail-value'>{$appointment['reason']}</span>
            </div>
            <div class='detail-row'>
                <span class='detail-label'>Meeting Link:</span>
                <span class='detail-value'>
                    <a href='{$appointment['meeting_link']}' target='_blank'>{$appointment['meeting_link']}</a>
                </span>
            </div>
            <div class='detail-row'>
                <span class='detail-label'>Notes:</span>
                <span class='detail-value'>
                    <?php echo isset($appointment['notes']) ? htmlspecialchars($appointment['notes']) : 'None'; ?>
                </span>
            </div>
        </div>
        
        <div class='detail-section'>
            <h4>System Information</h4>
            <div class='detail-row'>
                <span class='detail-label'>Created By:</span>
                <span class='detail-value'>{$appointment['created_by_name']}</span>
            </div>
            <div class='detail-row'>
                <span class='detail-label'>Created At:</span>
                <span class='detail-value'>{$created_at}</span>
            </div>
            <div class='detail-row'>
                <span class='detail-label'>Last Updated By:</span>
                <span class='detail-value'>{$appointment['updated_by_name'] ?? 'None'}</span>
            </div>
            <div class='detail-row'>
                <span class='detail-label'>Last Updated At:</span>
                <span class='detail-value'>{$updated_at}</span>
            </div>
        </div>
        
        <div class='detail-section'>
            <h4>Status History</h4>
            <div class='history-list'>";
            
            if (!empty($history)) {
                foreach ($history as $entry) {
                    $changed_at = date('M j, Y g:i A', strtotime($entry['changed_at']));
                    $previous = $entry['previous_status'] ? ucfirst($entry['previous_status']) : 'N/A';
                    $new = ucfirst($entry['new_status']);
                    
                    echo "<div class='history-item'>
                        <div class='history-header'>
                            <span class='history-status-change'>
                                {$previous} → {$new}
                            </span>
                            <span class='history-date'>{$changed_at}</span>
                        </div>
                        <div class='history-details'>
                            <div><strong>Changed By:</strong> {$entry['changed_by_name']}</div>";
                    
                    if ($entry['change_reason']) {
                        echo "<div><strong>Reason:</strong> {$entry['change_reason']}</div>";
                    }
                    
                    echo "</div>
                    </div>";
                }
            } else {
                echo "<p>No history available for this appointment.</p>";
            }
            
            echo "</div>
        </div>
    </div>";

} catch (Exception $e) {
    header("HTTP/1.1 500 Internal Server Error");
    echo "<div class='alert alert-error'>Error fetching appointment details: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>