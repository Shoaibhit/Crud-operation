<?php
header('Content-Type: application/json');
require 'connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$code = trim($_POST['account_code'] ?? '');
$name = trim($_POST['name'] ?? '');
$type = trim($_POST['account_type'] ?? '');

if ($id <= 0 || $code === '' || $name === '' || $type === '') {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

$checkSql = "SELECT id FROM accounts WHERE LOWER(TRIM(account_code)) = LOWER(TRIM(?)) AND id <> ? LIMIT 1";
$stmt = mysqli_prepare($conn, $checkSql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'si', $code, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => false, 'message' => 'Account code already exists']);
        exit;
    }
    mysqli_stmt_close($stmt);
}

$updateSql = "UPDATE accounts SET account_code = ?, name = ?, account_type = ?, updated_at = NOW() WHERE id = ?";
$stmt = mysqli_prepare($conn, $updateSql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . mysqli_error($conn)]);
    exit;
}
mysqli_stmt_bind_param($stmt, 'sssi', $code, $name, $type, $id);
if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => true]);
    exit;
} else {
    $err = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Update failed: ' . $err]);
    exit;
}
