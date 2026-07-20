<?php
session_start();

$conn = mysqli_connect("localhost","root","","user") or die("Connection Failed");

if (!isset($_SESSION['reset_email']) || !isset($_SESSION['allow_reset'])) {
    header('Location: forgot_passsword.php');
    exit();
}

$email = $_SESSION['reset_email'];
$error = '';
$success = '';

if (isset($_POST['reset_password'])) {
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);

    if ($password === '') {
        $error = 'Please enter a new password.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match. Please check both fields.';
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $safePassword = mysqli_real_escape_string($conn, $passwordHash);
        $safeEmail = mysqli_real_escape_string($conn, $email);

        mysqli_query($conn, "UPDATE users SET password='$safePassword', otp='' WHERE email='$safeEmail'");

        unset($_SESSION['reset_email']);
        unset($_SESSION['allow_reset']);

        $success = 'Your password has been reset successfully. <a href="register.php" class="text-decoration-none">Login now</a>.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Reset Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous" />
</head>
<body style="background: radial-gradient(circle at top left, #ffffff, #eef4ff 70%); min-height: 100vh;">
  <div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="card shadow-sm border-0 rounded-4 w-100" style="max-width: 520px;">
      <div class="card-body p-4 p-md-5">
        <h1 class="h4 mb-3 fw-bold text-center">Reset Your Password</h1>
        <p class="text-center text-muted mb-4">Set a new password for <strong><?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></strong>.</p>

        <?php if ($error): ?>
          <div class="alert alert-danger py-2" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php elseif ($success): ?>
          <div class="alert alert-success py-2" role="alert"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (!$success): ?>
          <form method="post" novalidate>
            <div class="mb-3">
              <label for="password" class="form-label">New Password</label>
              <input type="password" class="form-control form-control-lg rounded-3" id="password" name="password" placeholder="Enter new password" required>
            </div>
            <div class="mb-3">
              <label for="confirm_password" class="form-label">Confirm New Password</label>
              <input type="password" class="form-control form-control-lg rounded-3" id="confirm_password" name="confirm_password" placeholder="Repeat new password" required>
            </div>
            <button type="submit" name="reset_password" class="btn btn-primary btn-lg w-100 rounded-3">Save Password</button>
          </form>
        <?php endif; ?>

        <div class="text-center mt-4">
          <a href="register.php" class="text-decoration-none">Back to login</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
