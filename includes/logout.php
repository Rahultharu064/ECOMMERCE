<?php
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Redirect to the login page or homepage
header("Location:../frontend/login.php");
exit();
?>