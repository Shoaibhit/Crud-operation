<?php
session_start();

$conn = mysqli_connect("localhost","root","","user") or die("Connection Failed");

if (!isset($_SESSION['reset_email'])) {
    header('Location: forgot_passsword.php');
    exit();
}

$email = $_SESSION['reset_email'];
$error = '';

if (isset($_POST['verify'])) {
    $otp = trim($_POST['otp']);

    if ($otp === '') {
        $error = 'Please enter the OTP sent to your email.';
    } else {
        $safeOtp = mysqli_real_escape_string($conn, $otp);
        $safeEmail = mysqli_real_escape_string($conn, $email);
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$safeEmail' AND otp='$safeOtp'");

        if (mysqli_num_rows($check) > 0) {
            $_SESSION['allow_reset'] = true;
            header('Location: reset_password.php');
            exit();
        } else {
            $error = 'Invalid code. Please check the OTP and try again.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Verify OTP</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous" />
</head>
<body style="background: linear-gradient(135deg, #f4f7ff 0%, #eef6ff 100%); min-height: 100vh;">
  <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card shadow-sm rounded-4 border-0 w-100" style="max-width: 480px;">
      <div class="card-body p-4 p-md-5">
        <h1 class="h4 mb-3 fw-bold text-center">Verify Your Code</h1>
        <p class="text-center text-muted mb-4">Enter the 6-digit code from your email.</p>

        <?php if ($error): ?>
          <div class="alert alert-danger py-2" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form method="post" novalidate>
          <div class="mb-3">
            <label for="otp" class="form-label">Verification Code</label>
            <input type="text" class="form-control form-control-lg rounded-3" id="otp" name="otp" placeholder="Enter code" required>
          </div>
          <button type="submit" id="verifyButton" name="verify" class="btn btn-primary btn-lg w-100 rounded-3">Verify Code</button>
        </form>

        <div class="text-center mt-4">
          <a href="forgot_passsword.php" class="text-decoration-none">Change email</a>
        </div>
      </div>
    </div>
  </div>

</body>
</html>
