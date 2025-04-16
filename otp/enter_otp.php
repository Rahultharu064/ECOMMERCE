


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PharmaCare - Verify OTP</title>
  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="pharma-icon">
        <i class="fas fa-lock"></i>
      </div>
      <h2>Verify OTP</h2>
      <p>We've sent a 6-digit code to <?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'your email'; ?></p>
      
      <?php if (isset($_SESSION['error'])): ?>
        <div class="error message">
          <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
      <?php endif; ?>
      
      <form action="verify_otp.php" method="POST">
        <div class="otp-container">
          <?php for ($i = 1; $i <= 6; $i++): ?>
            <input type="text" name="otp<?php echo $i; ?>" class="otp-input" maxlength="1" pattern="\d" required
                   value="<?php echo isset($_POST['otp'.$i]) ? htmlspecialchars($_POST['otp'.$i]) : ''; ?>">
          <?php endfor; ?>
        </div>
        <button type="submit" class="btn">Verify OTP</button>
      </form>
      
      <p class="security-note">
        <i class="fas fa-clock"></i> OTP expires in 5 minutes
      </p>
    </div>
  </div>

  <script>
    // Auto-focus and move between OTP inputs
    const inputs = document.querySelectorAll('.otp-input');
    inputs.forEach((input, index) => {
      // Focus first empty input on load
      if (index === 0 || (inputs[index - 1].value && !input.value)) {
        input.focus();
      }
      
      input.addEventListener('input', () => {
        if (input.value.length === 1) {
          if (index < inputs.length - 1) {
            inputs[index + 1].focus();
          }
        }
      });
      
      input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && input.value.length === 0) {
          if (index > 0) {
            inputs[index - 1].focus();
          }
        }
      });
    });
  </script>
</body>
</html>