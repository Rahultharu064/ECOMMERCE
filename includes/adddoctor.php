\
<?php
include '../includes/config.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first = $_POST['first_name'];
    $last = $_POST['last_name'];
    $spec = $_POST['specialization'];

    $sql = "INSERT INTO doctors (first_name, last_name, specialization) VALUES ('$first', '$last', '$spec')";
    if (mysqli_query($conn, $sql)) {
        header("Location: viewdoctor.php?msg=Doctor added successfully");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>


<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Doctor</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container mt-5">
    <div class="card">
        <h2 class="mb-4">Add New Doctor</h2>
        <form method="POST" action="adddoctor.php">
            <div class="mb-3">
                <label>First Name</label>
                <input type="text" name="first_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Last Name</label>
                <input type="text" name="last_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Specialization</label>
                <input type="text" name="specialization" class="form-control" required>
            </div>
            <button class="btn btn-primary" type="submit">Add Doctor</button>
            <a href="index.php" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>
</body>
</html>
