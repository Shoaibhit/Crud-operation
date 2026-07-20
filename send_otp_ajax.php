<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');

$conn = mysqli_connect("localhost", "root", "", "user");
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit();
}

if (!isset($_SESSION['reset_email'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Please start again.']);
    exit();
}

$email = trim($_SESSION['reset_email']);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid session email.']);
    exit();
}

$safeEmail = mysqli_real_escape_string($conn, $email);
$result = mysqli_query($conn, "SELECT id FROM users WHERE email='$safeEmail'");
if (!$result || mysqli_num_rows($result) === 0) {
    echo json_encode(['success' => false, 'message' => 'Email not found. Please start again.']);
    exit();
}

$otp = rand(100000, 999999);
mysqli_query($conn, "UPDATE users SET otp='$otp' WHERE email='$safeEmail'");

require 'mail.php';
$sent = sendOTP($email, $otp);

if ($sent) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send OTP email. Check server mail settings.']);
}
