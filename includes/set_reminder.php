<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $med_name = $_POST['med_name'];
    $reminder_time = $_POST['reminder_time'];
    $reminder_date = $_POST['reminder_date'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO reminders (user_id, medication_name, reminder_time, reminder_date, status) 
                              VALUES (:user_id, :med_name, :reminder_time, :reminder_date, 'active')");
        $stmt->execute([
            ':user_id' => $user_id,
            ':med_name' => $med_name,
            ':reminder_time' => $reminder_time,
            ':reminder_date' => $reminder_date
        ]);
        
        $_SESSION['success_message'] = "Reminder set successfully!";
        header("Location: dashboard.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error setting reminder: " . $e->getMessage();
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<div id="reminderModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:#fff; padding:20px; border-radius:10px; box-shadow:0 0 20px rgba(0,0,0,0.2); z-index:1000;">
        <h3>Set Medication Reminder</h3>
        <form action="set_reminder.php" method="post">
            <label for="med_name">Medication Name:</label>
            <input type="text" id="med_name" name="med_name" required style="width:100%; padding:8px; margin:8px 0; border:1px solid #ddd; border-radius:4px;">
            
            <label for="reminder_time">Time:</label>
            <input type="time" id="reminder_time" name="reminder_time" required style="width:100%; padding:8px; margin:8px 0; border:1px solid #ddd; border-radius:4px;">
            
            <label for="reminder_date">Date:</label>
            <input type="date" id="reminder_date" name="reminder_date" required style="width:100%; padding:8px; margin:8px 0; border:1px solid #ddd; border-radius:4px;">
            
            <div style="display:flex; justify-content:space-between; margin-top:15px;">
                <button type="button" onclick="document.getElementById('reminderModal').style.display='none'" style="background:#f44336;">Cancel</button>
                <button type="submit" style="background:#4CAF50; color:white;">Set Reminder</button>
            </div>
        </form>
    </div>
    <script>
        document.getElementById('reminderModal').style.display = 'block';
        window.onclick = function(event) {
            if (event.target == document.getElementById('reminderModal')) {
                document.getElementById('reminderModal').style.display = "none";
            }
        }
    </script>
</body>
</html>

