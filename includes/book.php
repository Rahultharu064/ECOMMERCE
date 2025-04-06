<?php
session_start();
require 'config.php';

// if (!isset($_SESSION['user_id'])) {
//     header("Location:../frontend/ login.php");
//     exit();
// }

// Fetch available doctors
$doctors = [];
// try {
// //     $stmt = $pdo->query("SELECT d.id, u.first_name, u.last_name, d.specialization 
// //                          FROM doctors d 
// //                          JOIN users u ON d.user_id = u.id");
// //     $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
// // } catch (PDOException $e) {
// //     $error = "Error fetching doctors: " . $e->getMessage();
// // }

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $reason = $_POST['reason'];
    $patient_id = $_SESSION['user_id'];
    
    try {
        // Check if the selected time slot is available
        $stmt = $pdo->prepare("SELECT id FROM appointments 
                              WHERE doctor_id = :doctor_id 
                              AND appointment_date = :appointment_date 
                              AND appointment_time = :appointment_time
                              AND status != 'cancelled'");
        $stmt->execute([
            ':doctor_id' => $doctor_id,
            ':appointment_date' => $appointment_date,
            ':appointment_time' => $appointment_time
        ]);
        
        if ($stmt->fetch()) {
            $error = "The selected time slot is already booked. Please choose another time.";
        } else {
            // Book the appointment
            $stmt = $pdo->prepare("INSERT INTO appointments 
                                  (patient_id, doctor_id, appointment_date, appointment_time, reason, status) 
                                  VALUES (:patient_id, :doctor_id, :appointment_date, :appointment_time, :reason, 'pending')");
            $stmt->execute([
                ':patient_id' => $patient_id,
                ':doctor_id' => $doctor_id,
                ':appointment_date' => $appointment_date,
                ':appointment_time' => $appointment_time,
                ':reason' => $reason
            ]);
            
            $appointment_id = $pdo->lastInsertId();
            $_SESSION['appointment_id'] = $appointment_id;
            header("Location: confirmation.php");
            exit();
        }
    } catch (PDOException $e) {
        $error = "Error booking appointment: " . $e->getMessage();
    }
}

// Fetch available time slots for selected doctor (if any)
$available_slots = [];
if (!empty($_GET['doctor_id']) && !empty($_GET['date'])) {
    $doctor_id = $_GET['doctor_id'];
    $date = $_GET['date'];
    
    // Define working hours (9 AM to 5 PM)
    $start_time = strtotime('09:00:00');
    $end_time = strtotime('17:00:00');
    
    // Get booked slots for the selected doctor and date
    try {
        $stmt = $pdo->prepare("SELECT appointment_time FROM appointments 
                              WHERE doctor_id = :doctor_id 
                              AND appointment_date = :date
                              AND status != 'cancelled'");
        $stmt->execute([':doctor_id' => $doctor_id, ':date' => $date]);
        $booked_slots = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Generate available slots (every 30 minutes)
        for ($time = $start_time; $time <= $end_time; $time += 1800) { // 1800 seconds = 30 minutes
            $time_slot = date('H:i:s', $time);
            if (!in_array($time_slot, $booked_slots)) {
                $available_slots[] = $time_slot;
            }
        }
    } catch (PDOException $e) {
        $error = "Error fetching available slots: " . $e->getMessage();
    }
}
?>

<?php include 'header.php'; ?>

<div class="container" style="max-width: 800px; margin: 2rem auto;">
    <h2 style="color: #2065d1; margin-bottom: 1.5rem;">Book an Appointment</h2>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="post" action="book.php" style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label for="doctor_id" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Select Doctor</label>
            <select id="doctor_id" name="doctor_id" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px;">
                <option value="">-- Select a Doctor --</option>
                <?php foreach ($doctors as $doctor): ?>
                    <option value="<?php echo $doctor['id']; ?>" <?php echo (!empty($_POST['doctor_id']) && $_POST['doctor_id'] == $doctor['id']) ? 'selected' : ''; ?>>
                        Dr. <?php echo htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']); ?> - <?php echo htmlspecialchars($doctor['specialization']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label for="appointment_date" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Appointment Date</label>
            <input type="date" id="appointment_date" name="appointment_date" required 
                   min="<?php echo date('Y-m-d'); ?>" 
                   value="<?php echo !empty($_POST['appointment_date']) ? htmlspecialchars($_POST['appointment_date']) : ''; ?>"
                   style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px;">
        </div>
        
        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label for="appointment_time" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Appointment Time</label>
            <select id="appointment_time" name="appointment_time" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px;">
                <option value="">-- Select a Time --</option>
                <?php foreach ($available_slots as $slot): ?>
                    <option value="<?php echo $slot; ?>" <?php echo (!empty($_POST['appointment_time']) && $_POST['appointment_time'] == $slot) ? 'selected' : ''; ?>>
                        <?php echo date('g:i A', strtotime($slot)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label for="reason" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Reason for Appointment</label>
            <textarea id="reason" name="reason" rows="4" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px;"><?php echo !empty($_POST['reason']) ? htmlspecialchars($_POST['reason']) : ''; ?></textarea>
        </div>
        
        <button type="submit" style="background-color: #2065d1; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem; transition: background-color 0.3s;">Book Appointment</button>
        
        <a href="index.php" style="margin-left: 1rem; color: #2065d1; text-decoration: none;">Cancel</a>
    </form>
</div>

<script>
// Fetch available time slots when doctor or date changes
document.getElementById('doctor_id').addEventListener('change', fetchSlots);
document.getElementById('appointment_date').addEventListener('change', fetchSlots);

function fetchSlots() {
    const doctorId = document.getElementById('doctor_id').value;
    const date = document.getElementById('appointment_date').value;
    
    if (doctorId && date) {
        window.location.href = `book.php?doctor_id=${doctorId}&date=${date}`;
    }
}
</script>

<?php include 'footer.php'; ?>