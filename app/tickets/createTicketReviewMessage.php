<?php 
$data_field = file_get_contents('php://input');
$data_field = json_decode($data_field,true);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
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
    <p>Hi <?=$data_field['createdPersonName']?>,</p>
    <div>Our support team has reviewed and worked on your ticket <strong><?=$data_field['ticketQniqueId']?></strong></div>.
    <div>We believe the issue has been resolved. Please take a moment to review the ticket.</div>
    <div>If the issue is resolved, you can go ahead and close the ticket. If not, feel free to re-open the Ticket.</div>
    <p><b>Action Required : </b></p>
    <div style = "display:flex;gap:0.5rem;">
        <a href= "<?=$data_field['close_url']?>" id="closeButton" class = "button request_button" style = "background: #40ca58eb;color: white;">Close</a>
        <a href= "<?=$data_field['reopen_url']?>" id="reopenButton" class = "button request_button" style = "background:rgba(234, 168, 36, 0.86);color: white;">Re-open</a>
    </div>
    <br><br>
    <small><i>  *This is a system-generated email. No reply is required.</i></small>
</section>

</body>
</html>