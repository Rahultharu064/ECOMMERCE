<?php
session_start();
require '../includes/config.php';
require '../includes/functions.php';

// Only allow admins
if (!is_logged_in() || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$appointment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

// Fetch appointment details
try {
    $stmt = $conn->prepare("SELECT 
        a.*, p.name AS patient_name, d.name AS doctor_name, doc.specialization
        FROM appointments a
        JOIN users p ON a.patient_id = p.id
        JOIN users d ON a.doctor_id = d.id
        JOIN doctors doc ON d.id = doc.user_id
        WHERE a.id = ?");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $appointment = $stmt->get_result()->fetch_assoc();

    if (!$appointment) {
        header("Location: appointments.php");
        exit();
    }
} catch (Exception $e) {
    $error = "Error fetching appointment: " . $e->getMessage();
}

// Fetch available doctors
$doctors = [];
try {
    $stmt = $conn->prepare("SELECT u.id, u.name, d.specialization 
                           FROM users u
                           JOIN doctors d ON u.id = d.user_id
                           WHERE u.role = 'doctor' AND u.status = 'active'");
    $stmt->execute();
    $doctors = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $error = "Error fetching doctors: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = clean_input($_POST['doctor_id']);
    $appointment_date = clean_input($_POST['appointment_date']);
    $appointment_time = clean_input($_POST['appointment_time']);
    $reason = clean_input($_POST['reason']);
    $notes = clean_input($_POST['notes']);
    $status = clean_input($_POST['status']);
    $meeting_link = clean_input($_POST['meeting_link']);

    try {
        // Begin transaction
        $conn->begin_transaction();

        // Get current status
        $stmt = $conn->prepare("SELECT status FROM appointments WHERE id = ?");
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();
        $current_status = $stmt->get_result()->fetch_assoc()['status'];

        // Update appointment
        $stmt = $conn->prepare("UPDATE appointments 
                              SET doctor_id = ?, appointment_date = ?, appointment_time = ?,
                                  reason = ?, notes = ?, status = ?, meeting_link = ?,
                                  updated_by = ?, updated_at = NOW()
                              WHERE id = ?");
        $stmt->bind_param("issssssii", 
            $doctor_id, $appointment_date, $appointment_time,
            $reason, $notes, $status, $meeting_link,
            $_SESSION['user_id'], $appointment_id
        );
        $stmt->execute();

        // Log status change if it changed
        if ($current_status !== $status) {
            $stmt = $conn->prepare("INSERT INTO appointment_history 
                                  (appointment_id, changed_by, previous_status, new_status) 
                                  VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", 
                $appointment_id, $_SESSION['user_id'], $current_status, $status);
            $stmt->execute();
        }

        // Commit transaction
        $conn->commit();

        $_SESSION['success'] = "Appointment updated successfully";
        header("Location: appointments.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error updating appointment: " . $e->getMessage();
    }
}

// Fetch available time slots for selected doctor and date
$available_slots = [];
if (!empty($appointment['doctor_id']) && !empty($appointment['appointment_date'])) {
    try {
        // Get doctor's working hours
        $stmt = $conn->prepare("SELECT working_hours FROM doctors WHERE user_id = ?");
        $stmt->bind_param("i", $appointment['doctor_id']);
        $stmt->execute();
        $doctor = $stmt->get_result()->fetch_assoc();
        
        $working_hours = $doctor['working_hours'] ?? '09:00:00-17:00:00';
        list($start_time, $end_time) = explode('-', $working_hours);
        
        $start_time = strtotime($start_time);
        $end_time = strtotime($end_time);
        
        // Get booked slots excluding current appointment
        $stmt = $conn->prepare("SELECT appointment_time FROM appointments 
                              WHERE doctor_id = ? 
                              AND appointment_date = ?
                              AND id != ?
                              AND status NOT IN ('cancelled', 'no_show')");
        $stmt->bind_param("isi", 
            $appointment['doctor_id'], $appointment['appointment_date'], $appointment_id);
        $stmt->execute();
        $booked_slots = $stmt->get_result()->fetch_all(MYSQLI_COLUMN, 0);
        
        // Generate available slots (every 30 minutes)
        for ($time = $start_time; $time <= $end_time; $time += 1800) {
            $time_slot = date('H:i:s', $time);
            if (!in_array($time_slot, $booked_slots)) {
                $available_slots[] = $time_slot;
            }
        }
    } catch (Exception $e) {
        $error = "Error fetching available slots: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Appointment | Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="content-header">
                <h1>Edit Appointment</h1>
                <a href="appointments.php" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Appointments
                </a>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            
            <div class="card">
                <form method="POST" class="appointment-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="patient_name">Patient</label>
                            <input type="text" id="patient_name" value="<?= htmlspecialchars($appointment['patient_name']) ?>" readonly>
                            <input type="hidden" name="patient_id" value="<?= $appointment['patient_id'] ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="doctor_id">Doctor</label>
                            <select id="doctor_id" name="doctor_id" required>
                                <option value="">-- Select Doctor --</option>
                                <?php foreach ($doctors as $doctor): ?>
                                    <option value="<?= $doctor['id'] ?>" 
                                        <?= ($doctor['id'] == $appointment['doctor_id']) ? 'selected' : '' ?>>
                                        Dr. <?= htmlspecialchars($doctor['name']) ?> - <?= htmlspecialchars($doctor['specialization']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="appointment_date">Date</label>
                            <input type="date" id="appointment_date" name="appointment_date" 
                                   value="<?= htmlspecialchars($appointment['appointment_date']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="appointment_time">Time</label>
                            <select id="appointment_time" name="appointment_time" required>
                                <option value="">-- Select Time --</option>
                                <?php foreach ($available_slots as $slot): ?>
                                    <option value="<?= $slot ?>" 
                                        <?= ($slot == $appointment['appointment_time']) ? 'selected' : '' ?>>
                                        <?= date('g:i A', strtotime($slot)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="reason">Reason</label>
                        <textarea id="reason" name="reason" rows="3" required><?= 
                            htmlspecialchars($appointment['reason']) ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" required>
                                <option value="pending" <?= $appointment['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="confirmed" <?= $appointment['status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                <option value="completed" <?= $appointment['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="cancelled" <?= $appointment['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="meeting_link">Meeting Link</label>
                            <input type="url" id="meeting_link" name="meeting_link" 
                                   value="<?= htmlspecialchars($appointment['meeting_link']) ?>" 
                                   placeholder="https://meet.example.com/your-meeting-id">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Notes (Internal)</label>
                        <textarea id="notes" name="notes" rows="2"><?= 
                            htmlspecialchars($appointment['notes'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="appointments.php" class="btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
    // Update time slots when doctor or date changes
    document.getElementById('doctor_id').addEventListener('change', updateSlots);
    document.getElementById('appointment_date').addEventListener('change', updateSlots);
    
    function updateSlots() {
        const doctorId = document.getElementById('doctor_id').value;
        const date = document.getElementById('appointment_date').value;
        const appointmentId = <?= $appointment_id ?>;
        
        if (doctorId && date) {
            window.location.href = `edit_appointment.php?id=${appointmentId}&doctor_id=${doctorId}&date=${date}`;
        }
    }
    </script>
</body>
</html>