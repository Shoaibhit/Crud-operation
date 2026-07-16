<?php
header('Content-Type: application/json');
require 'connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$code = trim($_POST['account_code'] ?? '');
$name = trim($_POST['name'] ?? '');
$type = trim($_POST['account_type'] ?? '');

if ($code === '' || $name === '' || $type === '') {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

$checkSql = "SELECT id FROM accounts WHERE LOWER(TRIM(account_code)) = LOWER(TRIM(?)) LIMIT 1";
$stmt = mysqli_prepare($conn, $checkSql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 's', $code);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => false, 'message' => 'Account code already exists']);
        exit;
    }
    mysqli_stmt_close($stmt);
}

$insertSql = "INSERT INTO accounts (account_code, name, account_type, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
$stmt = mysqli_prepare($conn, $insertSql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . mysqli_error($conn)]);
    exit;
}
mysqli_stmt_bind_param($stmt, 'sss', $code, $name, $type);
if (mysqli_stmt_execute($stmt)) {
    $id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => true, 'id' => $id]);
    exit;
} else {
    $err = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Insert failed: ' . $err]);
    exit;
}
