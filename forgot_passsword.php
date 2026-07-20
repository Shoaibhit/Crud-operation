<?php
session_start();

$conn = mysqli_connect("localhost","root","","user") or die("Connection Failed");

$error = '';

if (isset($_POST['send_otp'])) {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $safeEmail = mysqli_real_escape_string($conn, $email);
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$safeEmail'");

        if (mysqli_num_rows($check) > 0) {
            $otp = rand(100000, 999999);
            mysqli_query($conn, "UPDATE users SET otp='$otp' WHERE email='$safeEmail'");

            require 'mail.php';
            if (!sendOTP($email, $otp)) {
                $error = 'Failed to send verification code. Please try again later.';
            } else {
                $_SESSION['reset_email'] = $email;
                header('Location: verify_otp.php');
                exit();
            }
        } else {
            $error = 'Email not found. Use the email registered in your account.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Forgot Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous" />
</head>
<body style="background: radial-gradient(circle at top, #eef2ff, #f8f9ff 60%); min-height: 100vh;">
  <div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="card shadow-sm border-0 rounded-4 w-100" style="max-width: 480px;">
      <div class="card-body p-4 p-md-5">
        <h1 class="h4 mb-3 fw-bold text-center">Forgot Password</h1>
        <p class="text-center text-muted mb-4">Enter the email address for your account and we will send a verification code.</p>

        <?php if ($error): ?>
          <div class="alert alert-danger py-2" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form method="post" novalidate>
          <div class="mb-3">
            <label for="email" class="form-label">Registered Email</label>
            <input type="email" class="form-control form-control-lg rounded-3" id="email" name="email" placeholder="Enter your email" required>
          </div>
          <button type="submit" name="send_otp" class="btn btn-primary btn-lg w-100 rounded-3">Send Verification Code</button>
        </form>

        <div class="text-center mt-4">
          <a href="register.php" class="text-decoration-none">Back to login</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
