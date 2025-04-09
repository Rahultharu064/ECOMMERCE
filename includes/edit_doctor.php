<?php
include 'config.php';
$id = $_GET['id'];
$doctor = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM doctors WHERE id=$id"));
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Doctor</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container mt-5">
    <div class="card">
        <h2 class="mb-4">Edit Doctor</h2>
        <form method="POST" action="update_doctor.php">
            <input type="hidden" name="id" value="<?= $doctor['id'] ?>">
            <div class="mb-3">
                <label>First Name</label>
                <input type="text" name="first_name" class="form-control" value="<?= $doctor['first_name'] ?>" required>
            </div>
            <div class="mb-3">
                <label>Last Name</label>
                <input type="text" name="last_name" class="form-control" value="<?= $doctor['last_name'] ?>" required>
            </div>
            <div class="mb-3">
                <label>Specialization</label>
                <input type="text" name="specialization" class="form-control" value="<?= $doctor['specialization'] ?>" required>
            </div>
            <button class="btn btn-primary" type="submit">Update</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
</body>
</html>
