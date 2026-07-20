<?php
$conn = mysqli_connect("localhost","root","","accounting");
if (!$conn) { echo "fail: " . mysqli_connect_error(); exit(1); }
$res = mysqli_query($conn, "SHOW COLUMNS FROM journal_entries LIKE 'source_type'");
if ($res && mysqli_num_rows($res) === 0) {
    $alter = "ALTER TABLE journal_entries ADD COLUMN source_type VARCHAR(20) NOT NULL DEFAULT '' AFTER description";
    if (!mysqli_query($conn, $alter)) {
        echo "alter_err: " . mysqli_error($conn);
        exit(1);
    }
    echo "added_source_type\n";
} else {
    echo "source_type_exists\n";
}
?>
