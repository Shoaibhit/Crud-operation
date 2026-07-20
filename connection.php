<?php
$conn = mysqli_connect("localhost", "root", "", "accounting");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Ensure opening_balance exists for account records.
$result = mysqli_query($conn, "SHOW COLUMNS FROM accounts LIKE 'opening_balance'");
if ($result && mysqli_num_rows($result) === 0) {
    mysqli_query($conn, "ALTER TABLE accounts ADD COLUMN opening_balance DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER account_type");
}

?>
