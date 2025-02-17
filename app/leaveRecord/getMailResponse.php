<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

include '../../includes/db-config.php';
require '../../vendor/autoload.php';

$stepsLog = '';
$key = "edtechLeave";
$jwt =  $_REQUEST['token'];
$response = checkAllCases();
$stepsLog .= date(DATE_ATOM) ." :: response get from getAllCases method => " . json_encode($response) . "\n\n";  
sendResponse();

function checkAllCases() {
    global $stepsLog;
    $stepsLog .= date(DATE_ATOM) . " :: Script Start -> Inside checkAllCases function \n\n";
    try {
        $decoded_data = decodeJWTToken();
        if(isset($decoded_data['status'])) {
            return $decoded_data;
        }
        $expireTimeResponse = checkExpireDateAndTime($decoded_data);
        if($expireTimeResponse['status'] == 400) {
            return $expireTimeResponse;
        }
        $leave_id = $decoded_data['leave_id'];
        $checkActionPerformOrNot = checkActionPerformOrNot($leave_id);
        if($checkActionPerformOrNot['status'] == 400) {
            return $checkActionPerformOrNot;
        }
        $action = ($decoded_data['action'] == 'approve') ? 'approved' : 'dis_approved';
        // // Update the data in data base
        // $updateStatus = updateLeaveStatus($leave_id,$decoded_data['action']);
        // if ($updateStatus['status'] == 400) {
        //     return $updateStatus;
        // }
        return showResponse(true,"Please Wait..","Please login to portal for see all details.",$leave_id,$action);
    } catch (Exception $e) {
        return showResponse(false,"Error","Error : "+$e->getMessage());
    }
}

function decodeJWTToken() : array {
    global $jwt , $key , $stepsLog;
    $stepsLog .= date(DATE_ATOM) . " :: Inside the decodeJWTToken \n\n";
    try {
        $data = JWT::decode($jwt, new Key($key, 'HS256'));
        $stepsLog .= date(DATE_ATOM) . " :: Data came in token => ". json_encode((array)$data) ." \n\n";
        return (array) $data;
    } catch (Exception $e) {
        $data = showResponse(false,"Token is invalid","Error : ".$e->getMessage());
    }
    return $data;
}

function checkExpireDateAndTime($decoded_data) : array {
    
    global $stepsLog;
    $stepsLog .= date(DATE_ATOM) . " :: Inside the checkExpireDateAndTime \n\n";

    $tokenExpireDateAndTime = date("d-m-Y H:i:s", $decoded_data['exp']);
    $currentDateAndTime = date("d-m-Y H:i:s");
    $stepsLog .= date(DATE_ATOM) . " :: expireDateAndTime => $tokenExpireDateAndTime and currentTime => $currentDateAndTime \n\n";
    $expireTimestamp = strtotime($tokenExpireDateAndTime);
    $currentTimestamp = strtotime($currentDateAndTime);
    $timeDifference =  $expireTimestamp - $currentTimestamp;
    $stepsLog .= date(DATE_ATOM) . " :: timeDifference between current and expire => $timeDifference \n\n";
    if($timeDifference < 0) {
        return showResponse(false,"Token Expire","Please login to portal for see all details.");
    } else {
        return ['status'=>200];
    }
}

function checkActionPerformOrNot($leave_id) : array {

    global $conn , $stepsLog;
    $stepsLog .= date(DATE_ATOM) . " :: Inside the checkActionPerformOrNot \n\n";
    $checkActionQuery = "SELECT IF(status=3,'Not Perform','Action Perform') as `status` FROM `leave_record` WHERE id = '$leave_id'";
    $stepsLog .= date(DATE_ATOM) . " :: checkActionQuery => $checkActionQuery \n\n";
    $checkAction = $conn->query($checkActionQuery);
    $checkAction = mysqli_fetch_column($checkAction);
    $stepsLog .= date(DATE_ATOM) . " :: output from query => $checkAction \n\n";
    if($checkAction == "Action Perform") {
        return showResponse(false,"Action already perform","Please login to portal for see all details.");
    } else {
        return ['status'=>200];
    }
}

// function updateLeaveStatus($leave_id,$action) {

//     global $conn , $stepsLog;
//     $stepsLog .= date(DATE_ATOM) . " :: Inside the updateLeaveStatus data came as param leave_id => $leave_id , action => $action \n\n";
//     $status = ($action == 'approve') ? '1' : '2';
//     $updateStatusQuery = "UPDATE leave_record SET status = '$status' , approved_by = SUBSTRING_INDEX(mail_to, '####', -1) WHERE id = '$leave_id'"; 
//     $stepsLog .= date(DATE_ATOM) . " :: updateStatusQuery => $updateStatusQuery  \n\n";
//     $updateStatus = $conn->query($updateStatusQuery);
//     if($updateStatus) {
//         return ['status'=>200];
//     } else {
//         return showResponse(false,"Error in Update Query","Please login to portal for see all details.");
//     }
// }

function sendResponse() {
    global $response, $stepsLog ;
    $stepsLog .= date(DATE_ATOM) . " :: Inside the sendResponse \n\n";
    $url = "http://edtechstrucure.local/app/leaveRecord/sendMailResponse";
    try {
        $request = [];
        $request['title'] = $response['title'];
        $request['message'] = $response['message'];
        $request['status'] = $response['status'];
        $request['leave_id'] = $response['leave_id'];
        $request['action'] = $response['action'];
        $request = base64_encode(json_encode($request));
        $stepsLog .= date(DATE_ATOM) ." :: request => " .json_encode($request). " \n\n";
        $stepsLog .= date(DATE_ATOM) ." :: url => $url \n\n";
        saveLog();
        setcookie("data",$request, time() + 3600, "/");
        header("Location: $url");
        exit();
    } catch (Error $e) {
        return json_encode(showResponse(false,"Error",$e->getMessage()));
    }
}

function showResponse($response,$title,$messsage,$leave_id=null,$action=null) : array {
    return ($response) ? ['status'=>200,'title'=>$title,'message'=>$messsage,'leave_id' => $leave_id,'action'=>$action] : ['status'=>400,'title'=>$title,'message'=>$messsage,'leave_id' => $leave_id,'action'=>$action]; 
}


function saveLog() {
    global $stepsLog;
    $stepsLog .= " ============ End Of Script ================== \n\n";
    $pdf_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/mail_response/';
    $fh = fopen($pdf_dir . 'mailLog_' . date('y-m-d') . '.log' , 'a');
    fwrite($fh,$stepsLog);
    fclose($fh);
}

?>