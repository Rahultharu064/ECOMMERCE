<?php
session_start();
require '../includes/config.php';
require '../includes/function.php';

// Only allow admins
// if (!is_logged_in() || $_SESSION['role'] !== 'admin') {
//     header("Location: ../login.php");
//     exit();
// }

$error = '';

// Fetch patients and doctors
$patients = [];
$doctors = [];

try {
    // Get active patients
    $stmt = $conn->prepare("SELECT id, name FROM users 
                           WHERE role = 'patient' AND status = 'active' 
                           ORDER BY name");
    $stmt->execute();
    $patients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Get active doctors with their specializations
    $stmt = $conn->prepare("SELECT u.id, u.name, d.specialization 
                           FROM users u
                           JOIN doctors d ON u.id = d.user_id
                           WHERE u.role = 'doctor' AND u.status = 'active'
                           ORDER BY u.name");
    $stmt->execute();
    $doctors = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $error = "Error fetching data: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = clean_input($_POST['patient_id']);
    $doctor_id = clean_input($_POST['doctor_id']);
    $appointment_date = clean_input($_POST['appointment_date']);
    $appointment_time = clean_input($_POST['appointment_time']);
    $reason = clean_input($_POST['reason']);
    $notes = clean_input($_POST['notes'] ?? '');
    $meeting_link = clean_input($_POST['meeting_link'] ?? '');

    try {
        // Check availability
        $stmt = $conn->prepare("SELECT id FROM appointments 
                              WHERE doctor_id = ? 
                              AND appointment_date = ? 
                              AND appointment_time = ?
                              AND status NOT IN ('cancelled', 'no_show')");
        $stmt->bind_param("iss", $doctor_id, $appointment_date, $appointment_time);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "The selected time slot is already booked. Please choose another time.";
        } else {
            // Generate meeting link if not provided
            if (empty($meeting_link)) {
                $meeting_id = bin2hex(random_bytes(8));
                $meeting_link = "https://meet.pharmacare.com/" . $meeting_id;
            }
            
            // Create appointment
            $stmt = $conn->prepare("INSERT INTO appointments 
                                  (patient_id, doctor_id, appointment_date, appointment_time, 
                                   reason, notes, meeting_link, created_by) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisssssi", 
                $patient_id, $doctor_id, $appointment_date, $appointment_time,
                $reason, $notes, $meeting_link, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $appointment_id = $stmt->insert_id;
                
                // Log appointment creation
                $stmt = $conn->prepare("INSERT INTO appointment_history 
                                      (appointment_id, changed_by, previous_status, new_status) 
                                      VALUES (?, ?, NULL, 'pending')");
                $stmt->bind_param("ii", $appointment_id, $_SESSION['user_id']);
                $stmt->execute();
                
                $_SESSION['success'] = "Appointment created successfully";
                header("Location: appointments.php");
                exit();
            } else {
                $error = "Failed to create appointment. Please try again.";
            }
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch available time slots if doctor and date selected
$available_slots = [];
if (!empty($_GET['doctor_id']) && !empty($_GET['date'])) {
    $doctor_id = (int)$_GET['doctor_id'];
    $date = clean_input($_GET['date']);
    
    try {
        // Get doctor's working hours
        $stmt = $conn->prepare("SELECT working_hours FROM doctors WHERE user_id = ?");
        $stmt->bind_param("i", $doctor_id);
        $stmt->execute();
        $doctor = $stmt->get_result()->fetch_assoc();
        
        $working_hours = $doctor['working_hours'] ?? '09:00:00-17:00:00';
        list($start_time, $end_time) = explode('-', $working_hours);
        
        $start_time = strtotime($start_time);
        $end_time = strtotime($end_time);
        
        // Get booked slots
        $stmt = $conn->prepare("SELECT appointment_time FROM appointments 
                              WHERE doctor_id = ? 
                              AND appointment_date = ?
                              AND status NOT IN ('cancelled', 'no_show')");
        $stmt->bind_param("is", $doctor_id, $date);
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
    <title>Add Appointment | Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <?php include '../Dasboard/Navbar.php'; ?>
    
    <div class="admin-container">
        <?php include '../Dasboard/sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="content-header">
                <h1>Add New Appointment</h1>
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
                            <label for="patient_id">Patient</label>
                            <select id="patient_id" name="patient_id" required>
                                <option value="">-- Select Patient --</option>
                                <?php foreach ($patients as $patient): ?>
                                    <option value="<?= $patient['id'] ?>" 
                                        <?= (!empty($_POST['patient_id']) && $_POST['patient_id'] == $patient['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($patient['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="doctor_id">Doctor</label>
                            <select id="doctor_id" name="doctor_id" required>
                                <option value="">-- Select Doctor --</option>
                                <?php foreach ($doctors as $doctor): ?>
                                    <option value="<?= $doctor['id'] ?>" 
                                        <?= (!empty($_POST['doctor_id']) && $_POST['doctor_id'] == $doctor['id']) ? 'selected' : '' ?>
                                        <?= (!empty($_GET['doctor_id']) && $_GET['doctor_id'] == $doctor['id']) ? 'selected' : '' ?>>
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
                                   value="<?= !empty($_POST['appointment_date']) ? htmlspecialchars($_POST['appointment_date']) : 
                                          (!empty($_GET['date']) ? htmlspecialchars($_GET['date']) : '') ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="appointment_time">Time</label>
                            <select id="appointment_time" name="appointment_time" required>
                                <option value="">-- Select Time --</option>
                                <?php foreach ($available_slots as $slot): ?>
                                    <option value="<?= $slot ?>" 
                                        <?= (!empty($_POST['appointment_time']) && $_POST['appointment_time'] == $slot) ? 'selected' : '' ?>>
                                        <?= date('g:i A', strtotime($slot)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="reason">Reason</label>
                        <textarea id="reason" name="reason" rows="3" required><?= 
                            !empty($_POST['reason']) ? htmlspecialchars($_POST['reason']) : '' ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="meeting_link">Meeting Link</label>
                            <input type="url" id="meeting_link" name="meeting_link" 
                                   value="<?= !empty($_POST['meeting_link']) ? htmlspecialchars($_POST['meeting_link']) : '' ?>" 
                                   placeholder="Leave blank to auto-generate">
                        </div>
                        
                        <div class="form-group">
                            <label for="notes">Notes (Internal)</label>
                            <textarea id="notes" name="notes" rows="2"><?= 
                                !empty($_POST['notes']) ? htmlspecialchars($_POST['notes']) : '' ?></textarea>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-calendar-plus"></i> Create Appointment
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
        
        if (doctorId && date) {
            window.location.href = `add_appointment.php?doctor_id=${doctorId}&date=${date}`;
        }
    }
    
    // Set minimum date to today
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('appointment_date').min = new Date().toISOString().split('T')[0];
    });
    </script>
</body>
</html>