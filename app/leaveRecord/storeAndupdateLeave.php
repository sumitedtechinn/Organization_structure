<?php 
include '../../includes/db-config.php';
session_start();

if (isset($_REQUEST['leave_id'])) {
    $details = $conn->query("SELECT leave_record.id , leave_record.user_id , leave_record.supported_document , users.Name as `name` , leave_record.mail_to , leave_record.mail_cc FROM `leave_record` LEFT JOIN users ON users.ID = leave_record.user_id WHERE leave_record.id = '".$_REQUEST['leave_id']."'");
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

function insertLeave() {
    global $conn;
    if (isset($_REQUEST['leave_type']) && isset($_REQUEST['start_date']) && isset($_REQUEST['end_date']) && isset($_REQUEST['mail_to']) && isset($_REQUEST['mail_subject']) && isset($_REQUEST['leave_reason'])) {
        $user_id = $_SESSION['ID'];
        $leave_type = mysqli_real_escape_string($conn,$_REQUEST['leave_type']);
        $start_date = mysqli_real_escape_string($conn,$_REQUEST['start_date']);
        $end_date = mysqli_real_escape_string($conn,$_REQUEST['end_date']);
        $mail_to = mysqli_real_escape_string($conn,$_REQUEST['mail_to']);
        $mail_subject = mysqli_real_escape_string($conn,$_REQUEST['mail_subject']);
        $mail_reason = $_REQUEST['leave_reason'];
        $mail_cc = null;
        if (isset($_REQUEST['mail_cc'])) {
            $mail_cc = json_encode($_REQUEST['mail_cc']);
        }
    
        $path_name = null;
        if(isset($_FILES['supporting_document']) && !empty($_FILES['supporting_document']['name'])) {
            $image_name = $_FILES['supporting_document']['name'];
            $path_name = checkAndUploadImage($image_name);
            if(!$path_name) {
                showResponse(false,'File must be Image or PDF');
                die;
            }
        }
        
        $user_name = $_SESSION['Name'];

        $response = generateMail($user_name,$mail_to,$mail_cc);
        $response = json_decode($response,true);
        if ($response['status'] == '200') {
            $insert_query = $conn->query("INSERT INTO `leave_record`(`user_id`, `leave_type`, `start_date`, `end_date`, `mail_to`, `mail_cc`, `mail_subject`, `mail_body`, `supported_document`, `status`) VALUES ('$user_id','$leave_type','$start_date','$end_date','$mail_to','$mail_cc','$mail_subject','$mail_reason','$path_name','3')");
            showResponse($insert_query,'applied');
        }
    }
}

function updateLeave($user_id,$user_name,$supported_document,$mail_to,$mail_cc) {

    global $conn;
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
                showResponse(false,'File must be Image or PDF');
                die;
            }
        }
        if (empty($path_name)) {
            $path_name = $supported_document;
        }
        
        $response = generateMail($user_name,$mail_to,$mail_cc);
        $response = json_decode($response,true);
        if($response['status'] == '200') {
            $update_query = $conn->query("UPDATE leave_record SET user_id = '$user_id' , leave_type = '$leave_type' , start_date = '$start_date' , end_date = '$end_date' , mail_subject = '$mail_subject' , mail_body = '$mail_reason' , supported_document = '$path_name' WHERE id = '".$_REQUEST['leave_id']."'");
            showResponse($update_query,'updated');
        }
    }
}

function updateLeaveStatus($status,$leave_id) {

    global $conn;
    $status_value = match($status){
        'approved' => '1',
        'dis_approved' => '2',
        'widthdraw' => '4'   
    };   
    $approved_by = $_SESSION['ID'];
    $update_query = $conn->query("UPDATE leave_record SET status = '$status_value' , approved_by = '$approved_by' WHERE id = '$leave_id'");
    showResponse($update_query,'status Updated'); 
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

function generateMail($user_name,$mail_to,$mail_cc) {
    global $conn;

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
        $request['mail_cc'] = implode(',',$mail_cc_list);
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
        $response = file_get_contents($url,false,$context);
        return $response;
    } catch (Error $e) {
        showResponse(false,$e->getMessage());
        die;
    } 
}
?>