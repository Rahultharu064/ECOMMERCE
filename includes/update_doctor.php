<?php
include 'config.php';

$id = $_POST['id'];
$first = $_POST['first_name'];
$last = $_POST['last_name'];
$spec = $_POST['specialization'];

$sql = "UPDATE doctors SET first_name='$first', last_name='$last', specialization='$spec' WHERE id=$id";

if (mysqli_query($conn, $sql)) {
    header("Location: index.php?msg=Doctor updated successfully");
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
