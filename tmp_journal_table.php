<?php
$conn = mysqli_connect("localhost","root","","accounting");
if (!$conn) { echo "fail: " . mysqli_connect_error(); exit(1); }
$res = mysqli_query($conn, "SHOW TABLES LIKE 'journal_entries'");
if ($res && mysqli_num_rows($res) === 0) {
    $sql = "CREATE TABLE `journal_entries` (
        `id` int NOT NULL AUTO_INCREMENT,
        `reference_no` varchar(30) NOT NULL,
        `entry_date` date NOT NULL,
        `debit_account` varchar(255) NOT NULL,
        `credit_account` varchar(255) NOT NULL,
        `amount` decimal(10,2) NOT NULL,
        `description` text NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `reference_no` (`reference_no`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    if (!mysqli_query($conn, $sql)) {
        echo "create_err: " . mysqli_error($conn);
        exit(1);
    }
    echo "created\n";
} else {
    echo "exists\n";
}
?>
