<?php
include 'config.php';

// Fetch doctors from the doctors table
$doctorQuery = "SELECT id, first_name, last_name FROM doctors";
$doctorResult = mysqli_query($conn, $doctorQuery);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form submission handling
    $doctor_id = $_POST['doctor_id'];
    $patient_name = mysqli_real_escape_string($conn, $_POST['patient_name']);
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];

    if (empty($doctor_id) || empty($patient_name) || empty($appointment_date) || empty($appointment_time)) {
        $error = "All fields are required!";
    } else {
        $insertQuery = "INSERT INTO appointments (doctor_id, patient_name, appointment_date, appointment_time) 
                        VALUES ('$doctor_id', '$patient_name', '$appointment_date', '$appointment_time')";
        
        if (mysqli_query($conn, $insertQuery)) {
            $success = "Appointment booked successfully!";
           
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Appointment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../assets/css/pharmacy.css">
   <style>
    /* General Styles */
:root {
    --primary-color: #3498db;
    --secondary-color: #2980b9;
    --accent-color: #e74c3c;
    --light-color: #f8f9fa;
    --dark-color: #343a40;
    --success-color: #2ecc71;
    --danger-color: #e74c3c;
    --border-radius: 8px;
    --box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    --transition: all 0.3s ease;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f5f7fa;
    color: #333;
    line-height: 1.6;
    padding-bottom: 50px;
}

.container {
    max-width: 800px;
    margin: 260px auto;
    padding: 0 15px;
}

/* Card Styling */
.card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 30px;
    margin-top: 30px;
    border: none;
    transition: var(--transition);
}

.card:hover {
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.card h2 {
    color: var(--dark-color);
    text-align: center;
    margin-bottom: 25px;
    font-weight: 600;
    position: relative;
    padding-bottom: 10px;
}

.card h2:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background: var(--primary-color);
}

/* Form Elements */
.form-control {
    height: 45px;
    border-radius: var(--border-radius);
    border: 1px solid #ddd;
    padding: 10px 15px;
    font-size: 16px;
    transition: var(--transition);
}

.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
}

textarea.form-control {
    height: auto;
    min-height: 120px;
}

label {
    font-weight: 500;
    margin-bottom: 8px;
    display: block;
    color: #555;
}

/* Button Styling */
.btn {
    padding: 10px 20px;
    border-radius: var(--border-radius);
    font-weight: 500;
    transition: var(--transition);
    font-size: 16px;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background-color: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background-color: var(--secondary-color);
    transform: translateY(-2px);
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #5a6268;
    transform: translateY(-2px);
}

/* Alert Messages */
.alert {
    padding: 12px 15px;
    border-radius: var(--border-radius);
    margin-bottom: 20px;
    font-size: 15px;
}

.alert-danger {
    background-color: #f8d7da;
    color: var(--danger-color);
    border: 1px solid #f5c6cb;
}

.alert-success {
    background-color: #d4edda;
    color: var(--success-color);
    border: 1px solid #c3e6cb;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .card {
        padding: 20px;
    }
    
    .card h2 {
        font-size: 24px;
    }
    
    .form-control {
        height: 42px;
        font-size: 15px;
    }
    
    .btn {
        padding: 8px 16px;
        font-size: 15px;
    }
}

@media (max-width: 576px) {
    .card {
        margin-top: 20px;
        padding: 15px;
    }
    
    .card h2 {
        font-size: 22px;
        margin-bottom: 20px;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
}

/* Form Layout Enhancements */
.mb-3 {
    margin-bottom: 1.5rem !important;
}

/* Button Group Styling */
.form-group-buttons {
    display: flex;
    gap: 10px;
    margin-top: 25px;
}

/* Date and Time Input Styling */
input[type="date"]::-webkit-calendar-picker-indicator,
input[type="time"]::-webkit-calendar-picker-indicator {
    cursor: pointer;
    opacity: 0.7;
    filter: invert(0.5);
}

input[type="date"]:focus::-webkit-calendar-picker-indicator,
input[type="time"]:focus::-webkit-calendar-picker-indicator {
    opacity: 1;
}

/* Select Dropdown Styling */
select.form-control {
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 16px;
    padding-right: 30px;
}

/* Animation for Form Elements */
.form-control, .btn, .alert {
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
   </style>
</head>
<body>
    <?php
    include '../includes/header.php';
    ?>
<div class="container mt-5">
    <div class="card">
        <h2 class="mb-4">Book an Appointment</h2>

        <!-- Display error or success messages -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="book.php">
            <!-- Doctor selection dropdown -->
            <div class="mb-3">
                <label for="doctor_id">Select Doctor</label>
                <select name="doctor_id" id="doctor_id" class="form-control" required>
                    <option value="">Select a doctor</option>
                    <?php while ($doctor = mysqli_fetch_assoc($doctorResult)): ?>
                        <option value="<?= $doctor['id'] ?>">
                            <?= $doctor['first_name'] . ' ' . $doctor['last_name'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Patient Name input -->
            <div class="mb-3">
                <label for="patient_name">Patient Name</label>
                <input type="text" name="patient_name" id="patient_name" class="form-control" required>
            </div>

            <!-- Appointment Date input -->
            <div class="mb-3">
                <label for="appointment_date">Appointment Date</label>
                <input type="date" name="appointment_date" id="appointment_date" class="form-control" required>
            </div>

            <!-- Appointment Time input -->
            <div class="mb-3">
                <label for="appointment_time">Appointment Time</label>
                <input type="time" name="appointment_time" id="appointment_time" class="form-control" required>
            </div>

            <!-- Submit button -->
            <button type="submit" class="btn btn-primary">Book Appointment</button>
            <a href="book.php" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>
    
        <?php include '../includes/footer.php'; ?>
        
</body>
</html>
