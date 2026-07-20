<?php
require 'auth.php';
header('Content-Type: application/json');
require 'connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$code = trim($_POST['account_code'] ?? '');
$name = trim($_POST['name'] ?? '');
$type = trim($_POST['account_type'] ?? '');
$opening_balance = trim($_POST['opening_balance'] ?? '0');

if ($code === '' || $name === '' || $type === '') {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if (!is_numeric($opening_balance)) {
    echo json_encode(['success' => false, 'message' => 'Opening balance must be a number']);
    exit;
}
$opening_balance = number_format((float)$opening_balance, 2, '.', '');

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

$insertSql = "INSERT INTO accounts (account_code, name, account_type, opening_balance, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())";
$stmt = mysqli_prepare($conn, $insertSql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . mysqli_error($conn)]);
    exit;
}
mysqli_stmt_bind_param($stmt, 'sssd', $code, $name, $type, $opening_balance);
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
