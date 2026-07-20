<?php
if (php_sapi_name() !== 'cli') {
    exit('Background sender must run from CLI.');
}

if ($argc !== 3) {
    exit('Usage: php send_otp_background.php <email> <otp>');
}

$email = $argv[1];
$otp = $argv[2];

require __DIR__ . '/mail.php';

sendOTP($email, $otp);
