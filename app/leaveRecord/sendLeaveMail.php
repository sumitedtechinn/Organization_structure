<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include '../../includes/db-config.php';
require '../../vendor/autoload.php';

$data_field = file_get_contents('php://input'); // by this we get raw data
$data_field = json_decode($data_field,true);

// echo "<pre>";
// print_r($data_field);
// $receiver_name = $data_field['receiver_name'];
// $receiver_email = $data_field['receiver_email'];
// $sender_name = $data_field['user_name'];

$sender_name = "Sumit Kumar";
$receiver_name = "Vikash";
$receiver_email = "sumitpathak901@gmail.com";

$message = "Dear <b>Reporting Manager</b>,<br><br>This is to inform you that <b> {$sender_name} </b> has submitted a leave request through the portal.<br>Please log in to the portal to review the application.<br><br><small><i>  *This is a system-generated email. No reply is required.</i></small>";

$mail = new PHPMailer(true);

try {

    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'no-reply@edtechinnovate.com';
    $mail->Password   = 'ypjpmutmkhitbfgn';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('noreply@yourdomain.com',$sender_name);
    $mail->addAddress($receiver_email);

    // $mail->addCC('hr@edtechinnovate.com');
    // if(!empty($data_field['mail_cc'])) {
    //     $cc_email = explode(',',$data_field['mail_cc']);
    //     foreach ($cc_email as $value) {
    //         $mail->addCC($value);        
    //     }
    // }

    $mail->isHTML(true);
    $mail->Subject = "Leave Request Submitted by {$sender_name}";
    $mail->Body    = $message;
    $mail->AltBody = strip_tags($message);

    $mail->send();
    echo json_encode(['status' => 200, 'message' => 'Mail send successfully']);
} catch (Exception $e) {
    echo json_encode(['status' => 400, 'message' => 'Failed to send email. Mailer Error: ' . $e->getMessage()]);
}
?>