<?php
$conn = mysqli_connect('localhost','root','','accounting');
if (!$conn) {
    echo 'connfail: ' . mysqli_connect_error();
    exit(1);
}
$tables = array('expense_categories','income_categories','expence','income');
foreach ($tables as $t) {
    $res = mysqli_query($conn, 'SHOW CREATE TABLE ' . $t);
    if ($res) {
        $row = mysqli_fetch_assoc($res);
        echo "\nTABLE $t:\n" . $row['Create Table'] . "\n";
    } else {
        echo "\nNO TABLE $t: " . mysqli_error($conn) . "\n";
    }
}
?>
