<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include '../../includes/db-config.php';
require '../../vendor/autoload.php';

$data_field = file_get_contents('php://input'); // by this we get raw data
$data_field = json_decode($data_field,true);

call_user_func($data_field['method'],$data_field);

function leaveRequestMail($data_field) {

    // echo "<pre>";
    // print_r($data_field);
    $receiver_name = $data_field['receiver_name'];
    $receiver_email = $data_field['receiver_email'];
    $sender_name = $data_field['user_name'];
    $mail_cc = null;
    if(isset($data_field['mail_cc']) && !empty($data_field['mail_cc'])) {
        $mail_cc = $data_field['mail_cc'];
    }
    // $sender_name = "Sumit Kumar";
    // $receiver_name = "Vikash";
    // $receiver_email = "sumitpathak901@gmail.com";

    $message = "Dear <b>Reporting Manager</b>,<br><br>This is to inform you that <b> {$sender_name} </b> has submitted a leave request through the portal.<br>Please log in to the portal to review the application.<br><br><small><i>  *This is a system-generated email. No reply is required.</i></small>";
    $subject = "Leave Request Submitted by {$sender_name}";
    sendMail($message,$sender_name,$receiver_email,$subject,$mail_cc);
}

function confirmLeaveMail($data_field) {

    // echo "<pre>";
    // print_r($data_field);
    $receiver_name = $data_field['receiver_name'];
    $receiver_email = $data_field['receiver_email'];
    $start_date = $data_field['start_date'];
    $end_date = $data_field['end_date'];
    $sender_name = "Edtech Innovate";
    $message = "";
    if ($data_field['status'] == 'approved') {
        $message = "Dear <b>{$receiver_name}</b>,<br><br>Your leave request for duration <b>{$start_date}<b> to <b>{$end_date}<b> has been <b>approved</b>.<br><br><small><i>  *This is a system-generated email. No reply is required.</i></small>";
    } else {
        $message = "Dear <b>{$receiver_name}</b>,<br><br>Your leave request for duration <b>{$start_date}<b> to <b>{$end_date}<b> has been <b>rejected</b>.<br><br><small><i>  *This is a system-generated email. No reply is required.</i></small>";
    }
    $subject = "Leave Request Status";
    sendMail($message,$sender_name,$receiver_email,$subject,null);
}

function sendMail($message,$sender_name,$receiver_email,$subject,$mail_cc) {

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
        // if(!is_null($mail_cc)) {
        //     $cc_email = explode(',',$mail_cc);
        //     foreach ($cc_email as $value) {
        //         $mail->addCC($value);        
        //     }
        // }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = strip_tags($message);

        $mail->send();
        echo json_encode(['status' => 200, 'message' => 'Mail send successfully']);
    } catch (Exception $e) {
        echo json_encode(['status' => 400, 'message' => 'Failed to send email. Mailer Error: ' . $e->getMessage()]);
    }
}
?>