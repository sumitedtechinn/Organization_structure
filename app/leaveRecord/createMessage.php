<?php 
include '../../includes/db-config.php';
$data_field = file_get_contents('php://input');
$data_field = json_decode($data_field,true);

$leave_details = $conn->query("SELECT leaveType.leaveName as `leave_type` , DATE_FORMAT(leave_record.start_date,'%d-%b-%Y') as `start_date` , DATE_FORMAT(leave_record.end_date,'%d-%b-%Y') as `end_date` , leave_record.mail_body as `leave_reason`  FROM `leave_record` LEFT JOIN leaveType ON leaveType.id = leave_record.leave_type WHERE leave_record.id = '". $data_field['leave_id'] ."'");
if($leave_details->num_rows > 0) {
    $leave_details = mysqli_fetch_assoc($leave_details);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Styled Leave Table</title>
    <style>
        table {
            width: 90%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            font-weight: bold;
            width: 20%;
        }

        td {
            color: #333;
        }

        tr:hover {
            background: #f1f1f1;
        }

        .request_button {
            margin: 4px 2px;
            display: block;
            height: 25px;
            padding: 10px 20px;
            text-align: center;
            border-radius: 16px;
            font-weight: bold;
            line-height: 25px;
        }
    </style>
</head>
<body>
<section>
    <p>Dear <b>Reporting Manager </b>,</p>
    <p>This is to inform you that <b> <?=$data_field['sender_name']?> </b> has submitted a leave request through the portal.</p>
    <p><b>Leave Details :</b></p>
    <table style="border: solid;">
        <tr><th>Leave Type</th><td><?=$leave_details['leave_type']?></td></tr>
        <tr><th>Leave From</th><td><?=$leave_details['start_date']?></td></tr>
        <tr><th>Leave To</th><td><?=$leave_details['end_date']?></td></tr>
        <tr><th>Leave Reason</th><td><?=$leave_details['leave_reason']?></td></tr>
    </table>
    <br>
    <p><b>Action Required : </b></p>
    <div style = "display:flex;gap:0.5rem;">
        <a href= "<?=$data_field['approval_url']?>" class = "button request_button" style = "background: #40ca58eb;color: white;">Approve</a>
        <a href= "<?=$data_field['reject_url']?>" class = "button request_button" style = "background: #e63333db;color: white;">DisApprove</a>
    </div>
    <br><br>
    <small><i>  *This is a system-generated email. No reply is required.</i></small>
</section>
</body>
</html>
