<?php
require 'auth.php';
header('Content-Type: application/json');
require 'connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$name = trim($_POST['name'] ?? '');
if ($name === '') {
    echo json_encode(['success' => false, 'message' => 'Name is required']);
    exit;
}

$checkSql = "SELECT id, name FROM income_categories WHERE LOWER(TRIM(name)) = LOWER(TRIM(?)) LIMIT 1";
$stmt = mysqli_prepare($conn, $checkSql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 's', $name);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_bind_result($stmt, $existingId, $existingName);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => true, 'id' => $existingId, 'name' => $existingName]);
        exit;
    }
    mysqli_stmt_close($stmt);
}

$insertSql = "INSERT INTO income_categories (name) VALUES (?)";
$stmt = mysqli_prepare($conn, $insertSql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . mysqli_error($conn)]);
    exit;
}
mysqli_stmt_bind_param($stmt, 's', $name);
if (mysqli_stmt_execute($stmt)) {
    $id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => true, 'id' => $id, 'name' => $name]);
    exit;
} else {
    $err = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Insert failed: ' . $err]);
    exit;
}
