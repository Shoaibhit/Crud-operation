<?php
require 'connection.php';

if (!isset($_GET['id'])) {
    die("Invalid Request");
}

$id = (int)$_GET['id'];

$stmt = mysqli_prepare($conn, "DELETE FROM accounts WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    header("Location: charts_of_accounts2.php?msg=deleted");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}

mysqli_stmt_close($stmt);
?>