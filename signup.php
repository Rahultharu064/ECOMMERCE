<?php 
include "includes/config.php";

// Ensure the config.php file sets up the $conn variable
if (!isset($conn)) {
    die("Database connection not established.");
}

if(isset($_POST['submit'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $address = $_POST['address'];
    $province = $_POST['province'];
    $city = $_POST['city'];
    $role = isset($_POST['role']) ? $_POST['role'] : 'customer'; // Default to customer if not set
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    
    // Verify passwords match before hashing
    if ($password !== $confirmPassword) {
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (name, email, phone, gender, dob, address, province, city, role, password) 
                VALUES ('$name', '$email', '$phone', '$gender','$dob','$address','$province','$city','$role','$password')";
        
        if(mysqli_query($conn, $sql)){
            // Start session and store user data
            session_start();
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = $role;
            
            // Redirect based on role
            if ($role === 'pharmacist') {
                echo "<script>window.location.href = './Dasboard/dasboard.php'</script>";
            } else {
                echo "<script>window.location.href = './frontend/Homepage.php'</script>";
            }
        } else {
            if (mysqli_errno($conn) == 1062) {
                echo "<script>alert('Email already exists!');</script>";
            } else {
                echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
            }
        }
    }
}
?>








<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaCare - Sign Up</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="./assets/css/login.css">
</head>
<body>
    <div class="container">
        <h2>Create Your PharmaCare Account</h2>
        <form id="signupForm" method="POST">

            <div class="form-group">
                <label for="name">Full Name</label>
                <i class="fas fa-user"></i>
                <input type="text" id="name" name="name" placeholder="Enter your full name">
                <span class="error" id="nameError"></span>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <i class="fas fa-envelope"></i>
                <input type="email" id="email" name="email" placeholder="Enter your email">
                <span class="error" id="emailError"></span>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" placeholder="Create password">
                <span class="error" id="passwordError"></span>
            </div>

            <div class="form-group">
                <label for="confirmPassword">Confirm Password</label>
                <i class="fas fa-lock"></i>
                <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm password">
                <span class="error" id="confirmPasswordError"></span>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <i class="fas fa-phone"></i>
                <input type="tel" id="phone" name="phone" placeholder="Enter your phone number">
                <span class="error" id="phoneError"></span>
            </div>

            <div class="form-group">
                <label for="gender">Gender</label>
                <select id="gender" name="gender">
                    <option value="">Select</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
                <span class="error" id="genderError"></span>
            </div>

            <div class="form-group">
                <label for="dob">Date of Birth</label>
                <i class="fas fa-calendar"></i>
                <input type="date" name="dob" id="dob">
                <span class="error" id="dobError"></span>
            </div>
            <div class="form-group">
            <label for="address">Address</label>
            <i class="fas fa-map-marker-alt"></i>
            <input type="text" id="address" name="address" placeholder="Enter street address">
            <span class="error" id="addressError"></span>
        </div>

        <div class="form-group">
            <label for="province">Province</label>
            <select id="province" name="province">
                <option value="">Select Province</option>
                <option value="koshi">Koshi Province</option>
                <option value="madhesh">Madhesh Province</option>
                <option value="bagmati">Bagmati Province</option>
                <option value="gandaki">Gandaki Province</option>
                <option value="lumbini">Lumbini Province</option>
                <option value="karnali">Karnali Province</option>
                <option value="sudurpaschim">Sudurpaschim Province</option>
            </select>
            <span class="error" id="provinceError"></span>
        </div>

        <div class="form-group">
            <label for="city">City/Municipality</label>
            <i class="fas fa-city"></i>
            <input type="text" id="city" name="city" placeholder="Enter city/municipality">
            <span class="error" id="cityError"></span>
        </div>

            <div class="form-group">
                <label for="role" name="role">Role</label>
                <select id="role" name="role">

                    <option value="">Select Role</option>
                    <option value="customer">Customer</option>
                    <option value="pharmacist">Pharmacist</option>
                </select>
                <span class="error" id="roleError"></span>
            </div>

            <button type="submit" name="submit">Create Account</button>

            <div class="social-login">
                <button type="button" class="social-btn google-btn">
                    <i class="fab fa-google"></i>
                    Google
                </button>
                <button type="button" class="social-btn facebook-btn">
                    <i class="fab fa-facebook-f"></i>
                    Facebook
                </button>
            </div>
        </form>
    </div>

<script src="./assets/js/login.js"></script>

    

   
    </script>
</body>
</html>
