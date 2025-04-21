<?php 

include('/home/edtechinnovate.com/organization.edtechinnovate.com/includes/db-config.php');
session_start();
 
$stepsLog = '';
$baseUrl = BASE_URL;
$stepsLog .= date(DATE_ATOM) . " :: Base URl Set => $baseUrl \n\n"; 
$arg1 = $argv[1] ?? null ;
if (!is_null($arg1)) {
    $function_name = $arg1;
    call_user_func($function_name);
} else {
    $stepsLog .= date(DATE_ATOM) . " :: CRON Run Date : " .date("d-m-Y")." \n\n";
    $stepsLog .= date(DATE_ATOM) . " :: No Argument received  \n\n";
    saveLog();
}

function userLeaveStatus() {

    global $conn;
    global $stepsLog;
    global $baseUrl;
    try {
        $stepsLog .= date(DATE_ATOM) . " :: CRON Run Date : " .date("d-m-Y")." \n\n";
        $getPendingLeaveRecordQuery = "SELECT leave_record.id as `leave_id` , users.Name as `name` , users.Email as `email` , DATE_FORMAT(leave_record.start_date,'%d-%b-%Y') as `start_date` , DATE_FORMAT(leave_record.end_date,'%d-%b-%Y') as `end_date` FROM `leave_record` LEFT JOIN users ON users.ID = leave_record.user_id WHERE leave_record.start_date < CURRENT_DATE() AND leave_record.status = '3' and users.Email IS NOT NULL";
        $stepsLog .= date(DATE_ATOM) . " :: getPendingLeaveRecordQuery => $getPendingLeaveRecordQuery \n\n";
        $getPendingLeaveRecord = $conn->query($getPendingLeaveRecordQuery);
        if ($getPendingLeaveRecord->num_rows > 0) {
            $getPendingLeaveRecord = mysqli_fetch_all($getPendingLeaveRecord,MYSQLI_ASSOC);
            $leaveRecordIds = array_column($getPendingLeaveRecord,'leave_id');
            //$stepsLog .= date(DATE_ATOM) . " :: pendingLeaveRecord Data => ". json_encode($getPendingLeaveRecord) ." \n\n";            
            foreach ($getPendingLeaveRecord as $key => $value) {
                $url = $baseUrl."/app/leaveRecord/sendLeaveMail.php";
                try {
                    $request = [];
                    $request['receiver_name'] = $value['name'];
                    $request['receiver_email'] = $value['email'];
                    $request['start_date'] = $value['start_date'];
                    $request['end_date'] = $value['end_date'];
                    $request['status'] = "dis_approved";
                    $request['method'] = "confirmLeaveMail";
                    $request = json_encode($request);
                    $opt = array(
                        'http' => array(
                            'method' => 'POST',
                            'header' => 'Content-Type: application/json',
                            'content' => $request , 
                            'timeout' => 60
                        )
                    );
                    $context = stream_context_create($opt);
                    $stepsLog .= date(DATE_ATOM) . " :: url => $url , request => $request \n\n";
                    $response = file_get_contents($url,false,$context);
                    $stepsLog .= date(DATE_ATOM) . " :: response => $response \n\n";
                } catch (Error $e) {
                    $stepsLog .= date(DATE_ATOM) . " :: error => ". $e->getMessage() ." , leave_id => ".$value['leave_id']."  \n\n";
                }
                try {
                    $updateQuery = "UPDATE leave_record SET `status` = '2' , `approved_by` = '1' WHERE id IN (".implode(',',$leaveRecordIds).")";
                    $stepsLog .= date(DATE_ATOM) . " :: updateQuery => ". $updateQuery ." \n\n";
                    $update = $conn->query($updateQuery);
                } catch (Error $e) {
                    $stepsLog .= date(DATE_ATOM) . " :: error => ". $e->getMessage() ." on file => " . $e->getFile() . " on line => " . $e->getLine() . " \n\n";
                }
            }
        } else {
            $stepsLog .= date(DATE_ATOM) . " :: No leave present for auto disapprove. \n\n";
        }
    } catch (Error $e) {
        $stepsLog .= date(DATE_ATOM) . " :: error => ". $e->getMessage() ." on file => " . $e->getFile() . " on line => " . $e->getLine() . " \n\n";
    } finally {
        saveLog();
    }
}

function saveLog() {
    global $stepsLog;
    $stepsLog .= " ============ End Of Script ================== \n\n";
    $pdf_dir = '/home/edtechinnovate.com/organization.edtechinnovate.com/uploads/auto_disapproveLog/';
    //deleteOldTicketLogs($pdf_dir);
    $fh = fopen($pdf_dir . 'CRON_log_' . date("F") . '.log' , 'a');
    fwrite($fh,$stepsLog);
    fclose($fh);
}


?>