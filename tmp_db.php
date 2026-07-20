<?php
$conn = mysqli_connect('localhost','root','','accounting');
if (!$conn) {
    echo 'connfail: ' . mysqli_connect_error();
    exit(1);
}
$res = mysqli_query($conn, 'SHOW TABLES');
while ($row = mysqli_fetch_row($res)) {
    echo $row[0] . "\n";
}
?>
