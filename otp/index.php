<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PharmaCare - OTP Login</title>
  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="pharma-icon">
        <i class="fas fa-prescription-bottle-alt"></i>
      </div>
      <h1>PharmaCare Login</h1>
      <form action="send_otp.php" method="POST">
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" required placeholder="your@email.com">
        </div>
        <button type="submit" class="btn">Send OTP</button>
      </form>
      <p class="security-note">
        <i class="fas fa-shield-alt"></i> Secure OTP authentication
      </p>
    </div>
    <div class="footer">
      <p>&copy; 2023 PharmaCare. All rights reserved.</p>
    </div>
  </div>
</body>
</html>