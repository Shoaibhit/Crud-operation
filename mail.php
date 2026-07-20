<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

function sendOTP($email, $otp)
{

    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();

        $mail->Host = 'sandbox.smtp.mailtrap.io';

        $mail->SMTPAuth = true;

        $mail->Username = '2368f21beaf713';

        $mail->Password = '37fce0cc549651';

        $mail->Port = 2525;

        $mail->setFrom('admin@test.com', 'Accounting System');

        $mail->addAddress($email);

        $mail->isHTML(true);

        $mail->Subject = "Password Change Verification";

        $mail->Body = "
        <h2>Password Change Verification</h2>

        <p>Your verification code is:</p>

        <h1>$otp</h1>

        <p>This code is valid for 5 minutes.</p>
        ";

        $mail->send();

        return true;

    } catch (Exception $e) {

        return false;

    }

}