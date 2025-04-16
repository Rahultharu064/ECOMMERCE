<?php
include 'config.php';

// Admin check (optional)
session_start();
// if (!isset($_SESSION['customer']) || $_SESSION['user']['role'] !== 'pharmacist') {
//     header("Location: ../frontend/login.php");
//     exit();
// }

// Fetch appointments with doctor details
$appointmentQuery = "
    SELECT a.id, a.patient_name, a.appointment_date, a.appointment_time, a.status, 
           d.first_name AS doctor_first_name, d.last_name AS doctor_last_name
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.id
    ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$appointmentsResult = mysqli_query($conn, $appointmentQuery);

// Check if query was successful
if (!$appointmentsResult) {
    die("Query failed: " . mysqli_error($conn));
}

// Handle status update (approve or cancel appointment)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve'])) {
        $appointment_id = $_POST['appointment_id'];
        $updateStatusQuery = "UPDATE appointments SET status = 'approved' WHERE id = '$appointment_id'";
        mysqli_query($conn, $updateStatusQuery);
    } elseif (isset($_POST['cancel'])) {
        $appointment_id = $_POST['appointment_id'];
        $updateStatusQuery = "UPDATE appointments SET status = 'cancelled' WHERE id = '$appointment_id'";
        mysqli_query($conn, $updateStatusQuery);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Manage Appointments</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <?php include '../Dasboard/Navbar.php'; ?>
    <?php include '../Dasboard/Sidebar.php'; ?>
    
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Appointments</h1>
        <a href="../Dasboard/dasboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Appointments</h2>
        </div>
        <div class="card-body">
            <!-- Appointment Table -->
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Doctor</th>
                        <th>Patient Name</th>
                        <th>Appointment Date</th>
                        <th>Appointment Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($appointment = mysqli_fetch_assoc($appointmentsResult)): ?>
                        <tr>
                            <td><?php echo $appointment['id']; ?></td>
                            <td>Dr.<?php echo $appointment['doctor_first_name'] . ' ' . $appointment['doctor_last_name']; ?></td>
                            <td><?php echo $appointment['patient_name']; ?></td>
                            <td><?php echo $appointment['appointment_date']; ?></td>
                            <td><?php echo $appointment['appointment_time']; ?></td>
                            <td>
                                <?php echo ucfirst($appointment['status']); ?>
                            </td>
                            <td>
                                <?php if ($appointment['status'] == 'pending'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                        <button type="submit" name="approve" class="btn btn-success btn-sm">Approve</button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                        <button type="submit" name="cancel" class="btn btn-danger btn-sm">Cancel</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">No action</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
