<?php
error_reporting( E_ALL );
require '../../includes/db-config.php';
session_start();

if(empty($_REQUEST)) {
    $data_field = file_get_contents('php://input'); // by this we get raw data
    $data_field = json_decode($data_field,true);
    $_REQUEST = $data_field;
}

$stepsLog = "";
$stepsLog .= date(DATE_ATOM) . " :: StoreAndupdateTicket Script Start \n\n";
$request_data = [];

if(isset($_REQUEST['task_name']) && isset($_REQUEST['ticket_category']) && isset($_REQUEST['ticket_priority']) && isset($_REQUEST['ticket_department']) && isset($_REQUEST['task_description'])) {
    
    $request_data['task_name'] = mysqli_real_escape_string($conn,$_REQUEST['task_name']);
    $request_data['status'] = '1';
    $request_data['category'] = mysqli_real_escape_string($conn,$_REQUEST['ticket_category']);
    $request_data['priority'] = mysqli_real_escape_string($conn,$_REQUEST['ticket_priority']);
    $request_data['department'] = mysqli_real_escape_string($conn,$_REQUEST['ticket_department']);
    $request_data['task_description'] = mysqli_real_escape_string($conn,$_REQUEST['task_description']);

    $request_data['attachment'] = null;
    if(isset($_FILES['attachment']) && !empty($_FILES['attachment']['name'])) {
        $image_name = $_FILES['attachment']['name'];
        $path_name = checkAndUploadImage($image_name);
        if(!$path_name) {
            saveLog(showResponse(false,'File must be image'));
            die;
        } else {
            $request_data['attachment'] = $path_name;
        }
    }

    if(!isset($_REQUEST['ticket_id'])) {
        insertTicket();   
    } else {
        updateTicket();
    }
} elseif (isset($_REQUEST['comment']) && isset($_REQUEST['method']) && isset($_REQUEST['ticket_id']) &&  $_REQUEST['method'] == "insertComment") {
    $comment = mysqli_real_escape_string($conn,$_REQUEST['comment']);
    $method = mysqli_real_escape_string($conn,$_REQUEST['method']);
    $attachments = [];
    if (isset($_FILES['attachment']) && !empty($_FILES['attachment']['name'][0])) {
        $file_length = count($_FILES['attachment']['name']);
        $arr_key = array_keys($_FILES['attachment']);
        foreach ($arr_key as $value) {
            $i= 0;
            while ($i < $file_length) {
                $attachments[$i][$value] = $_FILES['attachment'][$value][$i];
                $i++;
            }
        }
    }
    $ticket_id = mysqli_real_escape_string($conn,$_REQUEST['ticket_id']);
    call_user_func($method,$comment,$attachments,$ticket_id);
} elseif (isset($_REQUEST['selectedValue']) && isset($_REQUEST['method']) && isset($_REQUEST['ticket_id'])) {

    $selectedValue = mysqli_real_escape_string($conn,$_REQUEST['selectedValue']);
    $method = mysqli_real_escape_string($conn,$_REQUEST['method']);
    $ticket_id = mysqli_real_escape_string($conn,$_REQUEST['ticket_id']);
    call_user_func($method,$ticket_id,$selectedValue);
} elseif (isset($_REQUEST['deadline_date']) && isset($_REQUEST['ticket_id'])) {

    $deadline_date = mysqli_real_escape_string($conn,$_REQUEST['deadline_date']);
    $ticket_id = mysqli_real_escape_string($conn,$_REQUEST['ticket_id']);
    insertAndUpdateDeadLine($deadline_date,$ticket_id); 
} elseif (isset($_REQUEST['method']) && $_REQUEST['method'] == "checkUserIsAssignByUserOrNot" && isset($_REQUEST['ticket_id'])) {

    $ticket_id = mysqli_real_escape_string($conn,$_REQUEST['ticket_id']);
    $method = mysqli_real_escape_string($conn,$_REQUEST['method']);
    $method($ticket_id);
} else {
    $stepsLog .= date(DATE_ATOM) . " :: All required keys are not present \n\n";
    saveLog(showResponse(false,"All required keys are not present"));
}

function insertTicket() {

    global $conn;
    global $request_data;
    global $stepsLog;
    $stepsLog .= date(DATE_ATOM). " :: method inside the InsertTicket \n\n";
    $stepsLog .= date(DATE_ATOM). " :: requested data => " . json_encode($request_data) . "\n\n";
    try {
        $lastid = $conn->query("SELECT id FROM `ticket_record` ORDER BY id DESC LIMIT 1");
        if($lastid->num_rows > 0) {
            $lastid = mysqli_fetch_column($lastid);
        } else {
            $lastid = 1;
        }
        $unique_id = "ED-". $lastid ; // this id always generate like-: ED-1 (here 1 indicate id)
        $raised_by = null; // id of the person who is raised this ticket
        $ticket_create_person_name = null;
        $ticket_create_person_email = null;
        $ticket_create_person_contact = null;
        if (isset($_REQUEST['requestfrom']) && !empty($_REQUEST['requestfrom']) && $_REQUEST['requestfrom'] == 'edtech') {
            $raised_by = $_SESSION['ID'];
            $ticket_create_person_name = $_SESSION['Name'];
            $ticket_create_person_email = $_SESSION['Email'];
            $ticket_create_person_contact = $_SESSION['Mobile'];
        }
        $insert_query = "INSERT INTO `ticket_record`(`unique_id`, `task_name`, `task_description`,`requestfrom`, `raised_by`, `status`, `priority`, `category`, `department`, `create_person_name`, `create_person_number`, `create_person_email`, `attachment`) VALUES ('$unique_id','".$request_data['task_name']."','". $request_data['task_description'] ."','".$_REQUEST['requestfrom']."','$raised_by','".$request_data['status']."','" .$request_data['priority']. "','". $request_data['category'] ."','" .$request_data['department'] . "','$ticket_create_person_name','$ticket_create_person_contact','$ticket_create_person_email','" .$request_data['attachment'] . "')";
        $stepsLog .= date(DATE_ATOM) . " insert query => $insert_query \n\n";
        $insert = $conn->query($insert_query); 
        $last_id = $conn->insert_id;
        $insertTicketHistory_query = "INSERT INTO `ticket_history`(`ticket_id`, `updated_by`,`status`,`priority`, `category`, `department`) VALUES ('$last_id','$raised_by','". $request_data['status'] ."','". $request_data['priority'] ."','". $request_data['category'] ."','". $request_data['department'] ."')";
        $stepsLog .= date(DATE_ATOM) . " insert history query => $insertTicketHistory_query \n\n";
        $insertTicketHistory = $conn->query($insertTicketHistory_query);
        saveLog(showResponse($insert,"Ticket create"));
    } catch (Exception $e) {
        saveLog(showResponse(false,"Error : ".$e->getMessage()));       
    }
}

function updateTicket() {

}

function checkAndUploadImage($image_name) : bool|string {
    $extension = substr($image_name,strlen($image_name)-4,strlen($image_name));
    $allowed_extensions = array(".jpg","jpeg",".png",".gif",".pdf");
    if(in_array($extension,$allowed_extensions)) {
        move_uploaded_file($_FILES['attachment']['tmp_name'],'../../uploads/ticket_attachment/' . $image_name);
        return '../../uploads/ticket_attachment/' . $image_name;
    } else {
        return false;
    }
}

function checkAndUploadImageAndPdf($file) {
    $extension = substr($file['name'],strlen($file['name'])-4,strlen($file['name']));
    $allowed_extensions = array(".jpg","jpeg",".png",".gif",".pdf");
    if(in_array($extension,$allowed_extensions)) {
        move_uploaded_file($file['tmp_name'],'../../uploads/ticket_attachment/' . $file['name']);
        return '../../uploads/ticket_attachment/' . $file['name'];
    } else {
        return false;
    }
}

function insertComment($comment,$attachments,$ticket_id) {

    global $conn;
    global $stepsLog;
    $stepsLog .= date(DATE_ATOM). " :: method inside the InsertComment \n\n";
    $stepsLog .= date(DATE_ATOM). " :: requested data : comment => $comment and attachment => " . json_encode($attachments) . "\n\n";
    $store_attachment = [];
    if(!empty($attachments)) {
        foreach ($attachments as $value) {
            $path_name = checkAndUploadImageAndPdf($value);
            if(!$path_name) {
                saveLog(showResponse(false,'File must be image or pdf'));
                die;
            } else {
                $store_attachment[] = $path_name;
            }
        }
    }
    $stepsLog .= date(DATE_ATOM). " :: store_attachement => ". json_encode($store_attachment) ." \n\n";
    $user_id = $_SESSION['ID'];  // Change according to your session structure
    $user_name = $_SESSION['Name'];
    $user_image = $_SESSION['Photo'];
    $comment = $comment;
    $store_attachment = json_encode($store_attachment);
    $insert_comment_query = "INSERT INTO `ticket_comment`(`ticket_id`, `user_id`, `user_name`, `user_image`, `comment`, `attachment`) VALUES ('$ticket_id','$user_id','$user_name','$user_image','$comment','$store_attachment')";
    $insert_comment = $conn->query($insert_comment_query);
    $stepsLog .= date(DATE_ATOM) . " :: insert comment query =>  $insert_comment_query \n\n";
    $updateCommentNum = $conn->query("UPDATE ticket_record SET comment = (SELECT COUNT(id) FROM ticket_comment WHERE ticket_id = '$ticket_id') WHERE id = '$ticket_id'");
    saveLog(showResponse($insert_comment,"Comment send"));
}

function updateAssignToUser($ticket_id,$assignToUser_id) {

    global $stepsLog, $conn;
    $assignByUser_id = mysqli_real_escape_string($conn,$_SESSION['ID']);
    $stepsLog .= date(DATE_ATOM). " :: method inside the updateAssignToUser \n\n";
    $stepsLog .= date(DATE_ATOM). " :: requested data : ticket_id => $ticket_id and assignBy_user => $assignByUser_id , assignTo_user => $assignToUser_id \n\n";
    try {
        $updateTicketRecord_query = "UPDATE ticket_record SET assign_by = '$assignByUser_id' , assign_to = '$assignToUser_id' WHERE id = '$ticket_id'";
        $updateTicketRecord = $conn->query($updateTicketRecord_query);
        $stepsLog .= date(DATE_ATOM) . " :: updateTicketRecord_query => $updateTicketRecord_query \n\n";
        insertTicketHistory($ticket_id);
        saveLog(showResponse($updateTicketRecord,"User Assign"));
    } catch(Exception $e) {
        saveLog(showResponse(false,"Error : ". $e->getMessage()));    
    }
}

function updateDepartment($ticket_id,$department) {

    global $stepsLog, $conn;
    $stepsLog .= date(DATE_ATOM). " :: method inside the updateDepartment \n\n";
    $stepsLog .= date(DATE_ATOM). " :: requested data : ticket_id => $ticket_id and department => $department \n\n";
    try {
        $updateTicketRecord_query = "UPDATE ticket_record SET department = '$department' WHERE id = '$ticket_id'";
        $updateTicketRecord = $conn->query($updateTicketRecord_query);
        $stepsLog .= date(DATE_ATOM) . " :: updateTicketRecord_query => $updateTicketRecord_query \n\n";
        insertTicketHistory($ticket_id);
        saveLog(showResponse($updateTicketRecord,"Department Updated"));
    } catch(Exception $e) {
        saveLog(showResponse(false,"Error : ". $e->getMessage()));    
    }
}

function updateCategory($ticket_id,$category) {

    global $stepsLog, $conn;
    $stepsLog .= date(DATE_ATOM). " :: method inside the updateCategory \n\n";
    $stepsLog .= date(DATE_ATOM). " :: requested data : ticket_id => $ticket_id and category => $category \n\n";
    try {
        $updateTicketRecord_query = "UPDATE ticket_record SET category = '$category' WHERE id = '$ticket_id'";
        $updateTicketRecord = $conn->query($updateTicketRecord_query);
        $stepsLog .= date(DATE_ATOM) . " :: updateTicketRecord_query => $updateTicketRecord_query \n\n";
        insertTicketHistory($ticket_id);
        saveLog(showResponse($updateTicketRecord,"Category Updated"));
    } catch(Exception $e) {
        saveLog(showResponse(false,"Error : ". $e->getMessage()));    
    }
}

function updateStatus($ticket_id,$status) {

    global $stepsLog, $conn;
    $stepsLog .= date(DATE_ATOM). " :: method inside the updateStatus \n\n";
    $stepsLog .= date(DATE_ATOM). " :: requested data : ticket_id => $ticket_id and status => $status \n\n";
    try {
        $timer_query = "";
        if ($status == '4') {
            $timer_stop = date("Y-m-d");
            $timer_query = " , timer_stop = '$timer_stop'";
        }
        $updateTicketRecord_query = "UPDATE ticket_record SET status = '$status' $timer_query WHERE id = '$ticket_id'";
        $updateTicketRecord = $conn->query($updateTicketRecord_query);
        $stepsLog .= date(DATE_ATOM) . " :: updateTicketRecord_query => $updateTicketRecord_query \n\n";
        insertTicketHistory($ticket_id);
        saveLog(showResponse($updateTicketRecord,"Status Updated"));
    } catch(Exception $e) {
        saveLog(showResponse(false,"Error : ". $e->getMessage()));    
    }
}

function updatePriority($ticket_id,$priority) {

    global $stepsLog, $conn;
    $stepsLog .= date(DATE_ATOM). " :: method inside the updateCategory \n\n";
    $stepsLog .= date(DATE_ATOM). " :: requested data : ticket_id => $ticket_id and priority => $priority \n\n";
    try {
        $updateTicketRecord_query = "UPDATE ticket_record SET priority = '$priority' WHERE id = '$ticket_id'";
        $updateTicketRecord = $conn->query($updateTicketRecord_query);
        $stepsLog .= date(DATE_ATOM) . " :: updateTicketRecord_query => $updateTicketRecord_query \n\n";
        insertTicketHistory($ticket_id);
        saveLog(showResponse($updateTicketRecord,"Priority Updated"));
    } catch(Exception $e) {
        saveLog(showResponse(false,"Error : ". $e->getMessage()));    
    }
}


function insertAndUpdateDeadLine($deadline_date,$ticket_id) {

    global $stepsLog, $conn;
    $stepsLog .= date(DATE_ATOM). " :: method inside the insertAndUpdateDeadLine \n\n";
    $stepsLog .= date(DATE_ATOM). " :: requested data : ticket_id => $ticket_id and deadline_date => $deadline_date \n\n";
    try {
        $updateTicketRecord_query = "UPDATE ticket_record SET deadline_date = '$deadline_date' WHERE id = '$ticket_id'";
        $updateTicketRecord = $conn->query($updateTicketRecord_query);
        $stepsLog .= date(DATE_ATOM) . " :: updateTicketRecord_query => $updateTicketRecord_query \n\n";
        insertTicketHistory($ticket_id);
        saveLog(showResponse($updateTicketRecord,"DeadLine Updated"));
    } catch(Exception $e) {
        saveLog(showResponse(false,"Error : ". $e->getMessage()));    
    } 
}

/**
 * This function is for check that only Super admin and user who raised ticket are allow to close the ticket 
 */
function checkUserIsAssignByUserOrNot($ticket_id) {

    global $conn,$stepsLog;
    $stepsLog .= date(DATE_ATOM). " :: method inside the checkUserIsAssignByUserOrNot \n\n";
    $stepsLog .= date(DATE_ATOM). " :: requested data : ticket_id => $ticket_id \n\n";
    try {
        $user_id = mysqli_real_escape_string($conn,$_SESSION['ID']);
        $user_role = mysqli_real_escape_string($conn,$_SESSION['role']);
        if ($user_role == '1') {
            exit(saveLog(showResponse(true,"User allowed to close the Ticket")));    
        }
        $checkUser_query = "SELECT IF(raised_by = '$user_id','Yes','No') as `same_user` FROM `ticket_record` WHERE id = '$ticket_id'";
        $checkUser = $conn->query($checkUser_query);
        $checkUser = mysqli_fetch_column($checkUser);
        if($checkUser == 'Yes') {
            saveLog(showResponse(true,"User allowed to close the Ticket"));
        } else {
            saveLog(showResponse(false,"User who raised the ticket are allowed to close"));            
        }
    } catch(Exception $e) {
        saveLog(showResponse(false,"Error : ". $e->getMessage()));
    }
}

function insertTicketHistory($ticket_id) {
    
    global $conn,$stepsLog;
    $stepsLog .= date(DATE_ATOM). " :: method inside the insertTicketHistory \n\n";
    $stepsLog .= date(DATE_ATOM). " :: requested data : ticket_id => $ticket_id \n\n";
    try {
        $getUpdatedData_query = "SELECT assign_by , assign_to , status , priority , category , department ,deadline_date,timer_stop FROM `ticket_record` WHERE id = '$ticket_id'";
        $getUpdatedData = $conn->query($getUpdatedData_query);
        $stepsLog .= date(DATE_ATOM) . " :: getUpdatedData_query => $getUpdatedData_query \n\n";
        $getUpdatedData = mysqli_fetch_assoc($getUpdatedData);
        $assign_by = !empty($getUpdatedData['assign_by']) ? $getUpdatedData['assign_by'] : 0;
        $assign_to = !empty($getUpdatedData['assign_to']) ? $getUpdatedData['assign_to'] : 0;
        $insertTicketHistory_query = "INSERT INTO `ticket_history`(`ticket_id`, `updated_by`,`assign_by`,`assign_to`,`status`,`priority`, `category`, `department`,`deadline_date`,`timer_stop`) VALUES ('$ticket_id','" .$_SESSION['ID']. "','$assign_by','$assign_to','". $getUpdatedData['status'] ."','". $getUpdatedData['priority'] ."','". $getUpdatedData['category'] ."','". $getUpdatedData['department'] ."','" . $getUpdatedData['deadline_date'] . "','" . $getUpdatedData['timer_stop'] . "')";
        $stepsLog .= date(DATE_ATOM) . " :: insertTicketHistory_query => $insertTicketHistory_query \n\n";
        $insertTicketHistory = $conn->query($insertTicketHistory_query);
    } catch(Exception $e) {
        saveLog(showResponse(false,"Error : ". $e->getMessage()));
    }
}

function showResponse($response, $message = "Something went wrong!") {
    global $stepsLog;
    $result = ($response) ? ['status' => 200, 'message' => "$message successfully!"] : ['status' => 400, 'message' => $message]; 
    $stepsLog .= date(DATE_ATOM) . " :: respose => " . json_encode($result) . "\n\n";
    return $result;   
}

function saveLog($response) {
    global $stepsLog;
    $stepsLog .= " ============ End Of Script ================== \n\n";
    $pdf_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/ticket_log/';
    $fh = fopen($pdf_dir . 'createTicket_' . date('y-m-d') . '.log' , 'a');
    fwrite($fh,$stepsLog);
    fclose($fh);
    echo json_encode($response);
    exit;
}
?>