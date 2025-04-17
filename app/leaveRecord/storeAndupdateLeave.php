<?php 
include '../../includes/db-config.php';
session_start();

$stepsLog = '';
$data_field = file_get_contents('php://input'); // by this we get raw data
if (!empty($data_field)) {
    $data_field = json_decode($data_field,true);
    $_REQUEST['leave_id'] = $data_field['leave_id'];
    $_REQUEST['status'] = $data_field['status'];
    $_REQUEST['formType'] = $data_field['formType'];
}

$stepsLog .= date(DATE_ATOM) . " :: StoreAndUpate Script start \n\n";
$stepsLog .= date(DATE_ATOM) . " :: Request  => " . json_encode($_REQUEST) . "\n\n"; 

if (isset($_REQUEST['leave_id'])) {
    $details_query = "SELECT leave_record.id , leave_record.user_id , leave_record.supported_document , users.Name as `name` , leave_record.mail_to , leave_record.mail_cc FROM `leave_record` LEFT JOIN users ON users.ID = leave_record.user_id WHERE leave_record.id = '".$_REQUEST['leave_id']."'";
    $details = $conn->query($details_query);
    $stepsLog .= date(DATE_ATOM) . " :: details query => $details_query \n\n";
    $details = mysqli_fetch_assoc($details);
    $user_id = $details['user_id'];
    $user_name = $details['name'];
    $supported_document = $details['supported_document'];
    $mail_to = $details['mail_to'];
    $mail_cc = $details['mail_cc'];
    if(isset($_REQUEST['formType']) && $_REQUEST['formType'] == 'updateLeaveStatus') {
        $function_name = $_REQUEST['formType'];
        $function_args = mysqli_real_escape_string($conn,$_REQUEST['status']);
        call_user_func($function_name,$function_args,$_REQUEST['leave_id']);
    } else {
        updateLeave($user_id,$user_name,$supported_document,$mail_to,$mail_cc);
    }
} else {
    insertLeave();
}
saveLog();

function insertLeave() {
    global $conn , $stepsLog;
    $stepsLog .= date(DATE_ATOM) . " :: Inside the insertLeave Method \n\n"; 
    if (isset($_REQUEST['leave_type']) && isset($_REQUEST['start_date']) && isset($_REQUEST['end_date']) && isset($_REQUEST['mail_to']) && isset($_REQUEST['mail_subject']) && isset($_REQUEST['leave_reason'])) {
        $user_id = $_SESSION['ID'];
        $leave_type = mysqli_real_escape_string($conn,$_REQUEST['leave_type']);
        $start_date = mysqli_real_escape_string($conn,$_REQUEST['start_date']);
        $end_date = mysqli_real_escape_string($conn,$_REQUEST['end_date']);
        $mail_to = mysqli_real_escape_string($conn,$_REQUEST['mail_to']);
        $mail_subject = mysqli_real_escape_string($conn,$_REQUEST['mail_subject']);
        $mail_reason = mysqli_real_escape_string($conn,$_REQUEST['leave_reason']);
        $mail_cc = null;
        if (isset($_REQUEST['mail_cc'])) {
            $mail_cc = json_encode($_REQUEST['mail_cc']);
        }
    
        $path_name = null;
        if(isset($_FILES['supporting_document']) && !empty($_FILES['supporting_document']['name'])) {
            $image_name = $_FILES['supporting_document']['name'];
            $path_name = checkAndUploadImage($image_name);
            if(!$path_name) {
                $stepsLog .= date(DATE_ATOM) . " :: File must be Image or PDF \n\n";
                showResponse(false,'File must be Image or PDF');
                saveLog();
            }
        }
        
        if ($leave_type == '4') {
            $usedRestrictedLeave = usedRestrictedLeave();
            $d1 = strtotime($start_date);
            $d2 = strtotime($end_date);
            $diff = $d2 - $d1;
            $day = $diff / (60*60*24);
            
            if  (($day+$usedRestrictedLeave) >= 2 ) {
                $stepsLog .= date(DATE_ATOM) . " :: Only two restricted leave are allowed annually \n\n";
                showResponse(false,'Only two restricted leave are allowed annually');
                saveLog();
            }
        }

        if ($leave_type == '7') {
            echo "<pre>";
            echo "Inside the earned leave \n";
            $usedEarnedLeave = usedEarnedLeave();
            echo " usedEarnedLeave  => $usedEarnedLeave \n";
            $d1 = strtotime($start_date);
            $d2 = strtotime($end_date);
            $diff = $d2 - $d1;
            $day = $diff / (60*60*24);
            echo "NUmber of day => $day \n";
            if  (($day+$usedEarnedLeave) >= 6 ) {
                echo "came inside this \n";
                $stepsLog .= date(DATE_ATOM) . " :: Only Six Earned leave are allowed annually \n\n";
                showResponse(false,'Only Six Earned leave are allowed annually');
                saveLog();
            }
        }

        $user_name = $_SESSION['Name'];
        $insert = "INSERT INTO `leave_record`(`user_id`, `leave_type`, `start_date`, `end_date`, `mail_to`, `mail_cc`, `mail_subject`, `mail_body`, `supported_document`, `status`) VALUES ('$user_id','$leave_type','$start_date','$end_date','$mail_to','$mail_cc','$mail_subject','$mail_reason','$path_name','3')"; 
        $insert_query = $conn->query($insert);
        $stepsLog .= date(DATE_ATOM) . " :: insert query => $insert \n\n";
        if (!$insert_query) {
            $stepsLog .= date(DATE_ATOM) . " :: Inside Insert query false case";
            showResponse($insert_query,'Somthing Went Wrong');
            saveLog();
        }
        $leave_record_id = $conn->insert_id;
        $response = generateMail($user_name,$mail_to,$mail_cc,$leave_record_id);
        $stepsLog .= date(DATE_ATOM)  ." :: response came from generateMail => $response \n\n";
        $response = json_decode($response,true);
        if ($response['status'] == '200') {
            showResponse(true,'applied');
        } else {
            showResponse(false,$response['message']);
        }
    }
}

function updateLeave($user_id,$user_name,$supported_document,$mail_to,$mail_cc) {

    global $conn , $stepsLog;
    $stepsLog .= date(DATE_ATOM) . " :: Inside the updateLeave Method \n\n";
    if (isset($_REQUEST['leave_type']) && isset($_REQUEST['start_date']) && isset($_REQUEST['end_date']) && isset($_REQUEST['mail_subject']) && isset($_REQUEST['leave_reason'])) {
        $leave_type = mysqli_real_escape_string($conn,$_REQUEST['leave_type']);
        $start_date = mysqli_real_escape_string($conn,$_REQUEST['start_date']);
        $end_date = mysqli_real_escape_string($conn,$_REQUEST['end_date']);
        $mail_subject = mysqli_real_escape_string($conn,$_REQUEST['mail_subject']);
        $mail_reason = $_REQUEST['leave_reason'];
        $path_name = null;
        if(isset($_FILES['supporting_document']) && !empty($_FILES['supporting_document']['name'])) {
            $image_name = $_FILES['supporting_document']['name'];
            $path_name = checkAndUploadImage($image_name);
            if(!$path_name) {
                $stepsLog .= date(DATE_ATOM) . " :: File must be Image or PDF \n\n";
                showResponse(false,'File must be Image or PDF');
                saveLog();
            }
        }
        if (empty($path_name)) {
            $path_name = $supported_document;
        }
        
        $update = "UPDATE leave_record SET user_id = '$user_id' , leave_type = '$leave_type' , start_date = '$start_date' , end_date = '$end_date' , mail_subject = '$mail_subject' , mail_body = '$mail_reason' , supported_document = '$path_name' WHERE id = '".$_REQUEST['leave_id']."'";
        $update_query = $conn->query($update);
        if (!$update_query) {
            $stepsLog .= date(DATE_ATOM) . " :: Inside Update query false case";
            showResponse($update_query,"Somthing went wrong");
            saveLog();
        }
        $response = generateMail($user_name,$mail_to,$mail_cc,$_REQUEST['leave_id']);
        $stepsLog .= date(DATE_ATOM)  ." :: response came from generateMail => $response \n\n";
        $response = json_decode($response,true);
        if ($response['status'] == '200') {
            showResponse(true,'updated');
        } else {
            showResponse(false,$response['message']);
        }
    }
}

function updateLeaveStatus($status,$leave_id) {

    global $conn , $stepsLog;
    $stepsLog .= date(DATE_ATOM) . " :: Inside the updateLeaveStatus \n\n";
    $status_value = match($status){
        'approved' => '1',
        'dis_approved' => '2',
        'widthdraw' => '4'   
    };   
    $approved_by = isset($_SESSION['ID']) ? "approved_by = '" . $_SESSION['ID'] ."'" : "approved_by = SUBSTRING_INDEX(mail_to, '####', -1)";
    $update = "UPDATE leave_record SET status = '$status_value' , $approved_by WHERE id = '$leave_id'";
    $update_query = $conn->query($update);
    $stepsLog .= date(DATE_ATOM) . " :: update Query => $update \n\n";
    if(!$update_query) {
        $stepsLog .= date(DATE_ATOM) . " :: status Not Updated due to some error";
        showResponse($update_query,'status Not Updated due to some error');
        saveLog();
    }
    if ($status_value == '1' || $status_value == '2') {
        $response = sendConfirmMail($leave_id,$status);
        $stepsLog .= date(DATE_ATOM) . " :: response from sendConfirmMail => $response \n\n";
        $response = json_decode($response,true);
        if ($response['status'] == '200') {
            showResponse(true,'Status updated');
        } else {
            showResponse(false,$response['message']);
        }
    }
}

function showResponse($response, $message = 'Something went wrong!') {
    if ($response) {
        echo json_encode(['status' => 200, 'message' => "Leave $message successfully!"]);
    } else {
        echo json_encode(['status' => 400, 'message' => $message ]);
    }
}


function checkAndUploadImage($image_name) : bool|string {
    $extension = substr($image_name,strlen($image_name)-4,strlen($image_name));
    $allowed_extensions = array(".jpg","jpeg",".png",".gif",".pdf");
    if(in_array($extension,$allowed_extensions)) {
        move_uploaded_file($_FILES['supporting_document']['tmp_name'],'../../uploads/leave_document/' . $image_name);
        return '../../uploads/leave_document/' . $image_name;
    } else {
        return false;
    }
}

function generateMail($user_name,$mail_to,$mail_cc,$leave_id) {
    
    global $conn , $stepsLog;
    $stepsLog .= date(DATE_ATOM) . " :: Inside generateMail function \n\n";
    $mail_cc_list = [];
    if(!is_null($mail_cc)) {
        $mail_cc = json_decode($mail_cc,true);
        foreach ($mail_cc as $value) {
            $mail_cc_list[] = (explode('####',$value))[0];
        }
    }
    list($receiver_email,$receiver_id) = explode('####',$mail_to);
    $receiver_name = $conn->query("SELECT Name FROM `users` WHERE ID = '$receiver_id'");
    $receiver_name = mysqli_fetch_column($receiver_name);
    $url = "http://edtechstrucure.local/app/leaveRecord/sendLeaveMail";
    try {
        $request = [];
        $request['user_name'] = $user_name;
        $request['receiver_name'] = $receiver_name;
        $request['receiver_email'] = $receiver_email;
        $request['leave_id'] = $leave_id;
        $request['mail_cc'] = implode(',',$mail_cc_list);
        $request['method'] = "leaveRequestMail";
        $request = json_encode($request);
        $stepsLog .= date(DATE_ATOM) . "url => $url , request => $request \n\n";
        $opt = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => $request , 
                'timeout' => 60
            )
        );
        $context = stream_context_create($opt);
        $response = file_get_contents($url,false,$context);
        $stepsLog .= date(DATE_ATOM) . " :: response => $response \n\n"; 
        return $response;
    } catch (Error $e) {
        $stepsLog .= date(DATE_ATOM) . " :: Errro => " . $e->getMessage() . " \n\n";
        return json_encode(['status'=>400,'message'=>"Error : ".$e->getMessage()]);
    } 
}

function sendConfirmMail($leave_id,$status) {

    global $conn , $stepsLog;
    $stepsLog .= date(DATE_ATOM) . " :: Inside sendConfirmMail \n\n";
    $userInfo_query = "SELECT users.Name as `name` , users.Email as `email` , DATE_FORMAT(leave_record.start_date,'%d-%b-%Y') as `start_date` , DATE_FORMAT(leave_record.end_date,'%d-%b-%Y') as `end_date` FROM `leave_record` LEFT JOIN users ON users.ID = leave_record.user_id WHERE leave_record.id = '$leave_id'";
    $userInfo = $conn->query($userInfo_query);
    $stepsLog .= date(DATE_ATOM) . " :: userInfo query => $userInfo_query \n\n";
    $userInfo = mysqli_fetch_assoc($userInfo);
    $url = "http://edtechstrucure.local/app/leaveRecord/sendLeaveMail";
    try {
        $request = [];
        $request['receiver_name'] = $userInfo['name'];
        $request['receiver_email'] = $userInfo['email'];
        $request['start_date'] = $userInfo['start_date'];
        $request['end_date'] = $userInfo['end_date'];
        $request['status'] = $status;
        $request['method'] = "confirmLeaveMail";
        $request = json_encode($request);
        $stepsLog .= date(DATE_ATOM) . " :: url => $url , request => $request \n\n";
        $opt = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => $request , 
                'timeout' => 60
            )
        );
        $context = stream_context_create($opt);
        $response = file_get_contents($url,false,$context);
        $stepsLog .= date(DATE_ATOM) . " :: response => $response \n\n";
        return $response;
    } catch (Error $e) {
        $stepsLog .= date(DATE_ATOM) . " :: Errro => " . $e->getMessage() . " \n\n";
        return json_encode(['status'=>400,'message'=>"Error : ".$e->getMessage()]);
    }
}

function saveLog() {
    global $stepsLog;
    $stepsLog .= " ============ End Of Script ================== \n\n";
    $pdf_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/mail_response/';
    $fh = fopen($pdf_dir . 'storeAndUpdateLeave_' . date('y-m-d') . '.log' , 'a');
    fwrite($fh,$stepsLog);
    fclose($fh);
    exit;
}

function usedRestrictedLeave() {

    global $conn;
    $query = "SELECT SUM(CASE WHEN leave_type = '4' AND YEAR(start_date) = YEAR(CURRENT_DATE()) AND status = '1' THEN DATEDIFF(end_date,start_date)+1 ELSE 0 END) as `restricted_day_used` FROM leave_record WHERE user_id = '".$_SESSION['ID']."'";
    $restrictedLeave = $conn->query($query);
    $restrictedLeave = mysqli_fetch_column($restrictedLeave);
    return $restrictedLeave;     
}

function usedEarnedLeave() {

    global $conn;
    $query = "SELECT SUM(CASE WHEN leave_type = '7' AND YEAR(start_date) = YEAR(CURRENT_DATE()) AND (status = '1' OR status = '3') THEN DATEDIFF(end_date,start_date)+1 ELSE 0 END) as `earned_day_used` FROM leave_record WHERE user_id = '".$_SESSION['ID']."'";
    $earnedLeave = $conn->query($query);
    $earnedLeave = mysqli_fetch_column($earnedLeave);
    return $earnedLeave;     
}
?>