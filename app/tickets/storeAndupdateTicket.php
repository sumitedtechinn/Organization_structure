<?php
error_reporting( E_ALL );
require '../../includes/db-config.php';
require '../../app/mailSystem/MailJob.php';
require '../../app/mailSystem/CreateMailStructure.php';
session_start();

if(empty($_REQUEST)) {
    $data_field = file_get_contents('php://input'); // by this we get raw data
    $data_field = json_decode($data_field,true);
    $_REQUEST = $data_field;
}

$stepsLog = "";
$baseUrl = BASE_URL;
$stepsLog .= date(DATE_ATOM) . " :: StoreAndupdateTicket Script Start \n\n";
$request_data = [];
$mailjob = new MailJob();
$createMailStructure = new CreateMailStructure();

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
} elseif ( isset($_REQUEST['method']) && $_REQUEST['method'] == "checkUserStatusAsDevelopment" && isset($_REQUEST['assignTo'])) {

    $assign_to = mysqli_real_escape_string($conn,$_REQUEST['assignTo']);
    $method = mysqli_real_escape_string($conn,$_REQUEST['method']);
    $method($assign_to);
} elseif ( isset($_REQUEST['method']) && $_REQUEST['method'] == 'insertNotification' && isset($_REQUEST['ticket_id'])) {
    $ticket_id = mysqli_real_escape_string($conn,$_REQUEST['ticket_id']);
    $method = mysqli_real_escape_string($conn,$_REQUEST['method']);
    $method($ticket_id);
} elseif ( isset($_REQUEST['method']) && $_REQUEST['method'] == 'updateTicketStatusReviewToClose' && isset($_REQUEST['ticket_id'])) {
    $ticket_id = mysqli_real_escape_string($conn,$_REQUEST['ticket_id']);
    $method = mysqli_real_escape_string($conn,$_REQUEST['method']);
    $method($ticket_id);
} elseif ( isset($_REQUEST['method']) && $_REQUEST['method'] == "insertReopenTicketQuery" && isset($_REQUEST['message'])) {
    $query_message = mysqli_real_escape_string($conn,$_REQUEST['message']);
    $method = mysqli_real_escape_string($conn,$_REQUEST['method']);
    $ticket_id = mysqli_real_escape_string($conn,$_REQUEST['ticket_id']);
    $attachment = null;
    if(isset($_FILES['attachment']) && !empty($_FILES['attachment']['name'])) {
        $image_name = $_FILES['attachment']['name'];
        $path_name = checkAndUploadImage($image_name);
        if(!$path_name) {
            saveLog(showResponse(false,'File must be image'));
            die;
        } else {
            $attachment = $path_name;
        }
    }
    $method($ticket_id,$query_message,$attachment);
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
        $lastid_query = $conn->query("SELECT SUBSTRING_INDEX(unique_id, '-',-1) as `unique_id` FROM `ticket_record` WHERE unique_id LIKE '%ED%' ORDER BY id DESC LIMIT 1");
        if($lastid_query->num_rows > 0) {
            $lastid = mysqli_fetch_column($lastid_query);
            ++$lastid;
        } else {
            $lastid = 1;
        }
        $erpName = strtoupper($_REQUEST['requestfrom']);
        $unique_id = $erpName ."-". $lastid; // this id always generate like-: ED-1 (here 1 indicate id)
        $raised_by = null; // id of the person who is raised this ticket
        $ticket_create_person_name = null;
        $ticket_create_person_email = null;
        $ticket_create_person_contact = null;
        if (isset($_REQUEST['requestfrom']) && $_REQUEST['requestfrom'] == 'edtech') {
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

        // For Sending Mail
        $ticket_info_data = [
            "ticketQniqueId" => $unique_id , 
            "ticketSubject" => $request_data['task_name'] , 
            "ticketPriority" => $request_data['priority'] , 
            "raisedByPersonName" => $ticket_create_person_name,
            "createdTime" => date("d-M-Y h:i:s")
        ];
        $topMostHierarchyUser = getTopMostUserDetails($request_data['department']);
        if($topMostHierarchyUser['status'] == 400) {
            saveLog(showResponse(false,"Ticket create but mail not send"));    
        }

        $email_function = [
            "successfulTicketGenerationMessageForTicketRaisedPerson" => [
                "email" => $ticket_create_person_email ,
            ],
            "messageToDepartHeadForNewTicketCreate" => [
                "email" => $topMostHierarchyUser['usersEmail']
            ],
        ];
        
        $queueMailResponse = createEmailData($ticket_info_data,$email_function);
        $queueMailResponse = json_decode($queueMailResponse,true);
        if ($queueMailResponse['status'] == 400) {
            saveLog(showResponse(false,"Ticket create but mail not send"));
        }
        saveLog(showResponse($insert,"Ticket create"));
    } catch (Exception $e) {
        saveLog(showResponse(false,"Error : ".$e->getMessage()));       
    }
}

/**
 * Get the UpperHierarchy User Details 
 */
function getTopMostUserDetails($departmentId) : array {

    global $conn;
    global $stepsLog;
    $stepsLog .= date(DATE_ATOM). " :: method inside the getTopMostUserDetails \n\n";
    $stepsLog .= date(DATE_ATOM). " :: requested data => $departmentId \n\n";
    try {
        $department_details_query = "SELECT * FROM `Department` WHERE id = '$departmentId'";
        $stepsLog .= date(DATE_ATOM) . " :: Department details Query => $department_details_query \n\n";
        $department_details = $conn->query($department_details_query);
        $department_details = mysqli_fetch_assoc($department_details);
        $departmentUpperHierarchy_user_query = "SELECT Email FROM `users` WHERE Department_id = '$departmentId' AND Deleted_At IS NULL AND Hierarchy_value = (SELECT MIN(Hierarchy_value) FROM `users` WHERE Department_id = '$departmentId' AND Deleted_At IS NULL)";
        $stepsLog .= date(DATE_ATOM) . " :: DepartmentUpperHierarchy user Query => $departmentUpperHierarchy_user_query \n\n";
        $departmentUpperHierarchy_user = $conn->query($departmentUpperHierarchy_user_query);
        $departmentUpperHierarchy_user = mysqli_fetch_all($departmentUpperHierarchy_user,MYSQLI_ASSOC);
        $departmentUpperHierarchy_user = array_column($departmentUpperHierarchy_user,'Email');
        if ($department_details['vertical_id'] == '1' || $department_details['vertical_id'] == '2' || $department_details['vertical_id'] == '3') {
            $vertical = "'1','2','3'";  
        } else {
            $vertical = $department_details['vertical_id'];
        }    
        // Fetch users for different scopes in a single query
        $verticalUpperHierarchyUser = [];
        $query = "SELECT GROUP_CONCAT(CASE WHEN Vertical_id IN ($vertical) AND Department_id IS NULL THEN Email END) AS vertical_users FROM `users` WHERE Deleted_At IS NULL";
        $stepsLog .= date(DATE_ATOM) . " :: verticalUpperHierarchyUser Query => $query \n\n";
        $result = $conn->query($query);
        $users = mysqli_fetch_assoc($result);
        if (!empty($users['vertical_users'])) {
            $verticalUpperHierarchyUser = explode(',',$users['vertical_users']);
        }

        // Combine all user 
        $upperHierarchyUsers = array_merge($departmentUpperHierarchy_user,$verticalUpperHierarchyUser);
        $to = $upperHierarchyUsers[0];
        $cc = array_splice($upperHierarchyUsers,1);
        $usersEmail = [];
        $usersEmail['to'] = $to;
        $usersEmail['cc'] = implode(",",$cc);
    
        return ['status' => 200 , 'usersEmail' => $usersEmail];
    } catch (Exception $e) {
        $stepsLog .= date(DATE_ATOM) . " :: Errro => " . $e->getMessage() . " file => " . $e->getFile() . " on line => " . $e->getLine() .  " \n\n";
        return ['status'=>400,'message'=>"Error : ".$e->getMessage()];
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
        $updateTicketRecord_query = "UPDATE ticket_record SET assign_by = '$assignByUser_id' , assign_to = '$assignToUser_id' , reopenStatus = '0' WHERE id = '$ticket_id'";
        $updateTicketRecord = $conn->query($updateTicketRecord_query);
        $stepsLog .= date(DATE_ATOM) . " :: updateTicketRecord_query => $updateTicketRecord_query \n\n";
        insertTicketHistory($ticket_id);

        // get the ticket info 
        $ticketAllInformation = getTicketAllInformation($ticket_id);

        // check current ticket status
        $checkStatus_query = "SELECT IF(ticket_status.name LIKE '%assigned%','true','false') as `status` FROM `ticket_record` LEFT JOIN ticket_status ON ticket_status.id = ticket_record.status WHERE ticket_record.id = '$ticket_id'";
        $checkStatus = $conn->query($checkStatus_query);
        $checkStatus = mysqli_fetch_column($checkStatus);
        if($checkStatus == 'true') {
            $email_function = [
                "messageTicketAssignUser" => [
                    "email" => $ticketAllInformation['assignToUserEmail'] ,
                ],
                "messageToCreateUserForTicketAssignedDetails" => [
                    "email" => $ticketAllInformation['createPersonEmail']
                ],
            ];
        } else {
            $email_function = [
                "messageTicketAssignUser" => [
                    "email" => $ticketAllInformation['assignToUserEmail'] ,
                ],
            ];
        }
        $queueMailResponse = createEmailData($ticketAllInformation,$email_function);
        $queueMailResponse = json_decode($queueMailResponse,true);
        if ($queueMailResponse['status'] == 400) {
            saveLog(showResponse(false,"Ticket create but mail not send"));
        }
        saveLog(showResponse($updateTicketRecord,"User Assign"));
    } catch(Exception $e) {
        saveLog(showResponse(false,"Error : ". $e->getMessage()));    
    }
}

function getTicketAllAssignUserEmail($ticket_id) {

    global $conn;
    global $stepsLog, $conn;
    $stepsLog .= date(DATE_ATOM). " :: method inside the getTicketAllAssignUserEmail \n\n";
    $stepsLog .= date(DATE_ATOM). " :: requested data : ticket_id => $ticket_id \n\n";

    $allAssignUserEmail_query = "SELECT DISTINCT `email` FROM ( SELECT users.Email as `email` FROM ticket_history LEFT JOIN users ON ticket_history.assign_by = users.ID WHERE ticket_id = '$ticket_id' UNION SELECT users.Email as `email` FROM ticket_history LEFT JOIN users ON ticket_history.assign_to = users.ID WHERE ticket_history.ticket_id = '$ticket_id') as `combined`";
    $stepsLog .= date(DATE_ATOM) . " :: allAssignUserEmail_query => $allAssignUserEmail_query \n";
    $allAssignUserEmail = $conn->query($allAssignUserEmail_query);
    $emailData = [];
    while ($row = mysqli_fetch_assoc($allAssignUserEmail)) {
        if($allAssignUserEmail['person'] != '1') {
            $emailData[] = $row['email'];
        }
    }

    $to = $emailData[0];
    $cc = "";
    unset($emailData[0]);
    if(!empty($emailData)) {
        $cc = implode(',',$emailData);
    }
    return [$to,$cc];
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
            $timer_stop = date("Y-m-d h:i:s");
            $timer_query = " , timer_stop = '$timer_stop'";
        }
        $updateTicketRecord_query = "UPDATE ticket_record SET status = '$status' $timer_query WHERE id = '$ticket_id'";
        $updateTicketRecord = $conn->query($updateTicketRecord_query);
        $stepsLog .= date(DATE_ATOM) . " :: updateTicketRecord_query => $updateTicketRecord_query \n\n";
        insertTicketHistory($ticket_id);
        if($status == '4') {    
            // get the ticket info 
            $ticketAllInformation = getTicketAllInformation($ticket_id);

            $email_function = [
                "messageToCreateUserForReviewTicket" => [
                    "email" => $ticketAllInformation['createPersonEmail']
                ],
            ];

            $queueMailResponse = createEmailData($ticketAllInformation,$email_function);
            $queueMailResponse = json_decode($queueMailResponse,true);
            if ($queueMailResponse['status'] == 400) {
                saveLog(showResponse(false,"Status update but mail not send"));
            }
            saveLog(showResponse($updateTicketRecord,"Status Updated"));
        } elseif ($status == '5') {
            updateTicketStatusReviewToClose($ticket_id);
        } else {
            saveLog(showResponse($updateTicketRecord,"Status Updated"));
        }
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
        $timer_stop = !empty($getUpdatedData['timer_stop']) ? $getUpdatedData['timer_stop'] : null;
        
        $insertTicketHistory_query = "INSERT INTO `ticket_history` (`ticket_id`, `updated_by`, `assign_by`, `assign_to`, `status`, `priority`,`category`, `department`, `deadline_date`, `timer_stop`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertTicketHistory_query);

        // Properly bind `$timer_stop` to support `null`
        $stmt->bind_param("iiiiisssss", 
            $ticket_id,
            $_SESSION['ID'],
            $assign_by,
            $assign_to,
            $getUpdatedData['status'],
            $getUpdatedData['priority'],
            $getUpdatedData['category'],
            $getUpdatedData['department'],
            $getUpdatedData['deadline_date'],
            $timer_stop
        );

        if ($stmt->execute()) {
            $stepsLog .= date(DATE_ATOM) . " :: Insert into ticket_history successful \n\n";
        } else {
            throw new Exception("Insert into ticket_history failed: " . $stmt->error);
        }
    } catch(Exception $e) {
        saveLog(showResponse(false,"Error : ". $e->getMessage()));
    }
}

function checkUserStatusAsDevelopment($assign_to) {

    global $conn,$stepsLog;
    $stepsLog .= date(DATE_ATOM). " :: method inside the checkUserStatusAsDevelopment \n\n";
    $stepsLog .= date(DATE_ATOM). " :: requested data : assign_to => $assign_to \n\n";
    try {
        $checkUserStatus_query = "SELECT COUNT(ticket_record.id) FROM `ticket_record` LEFT JOIN ticket_status ON ticket_status.id = ticket_record.status LEFT JOIN ticket_category ON ticket_category.id = ticket_record.category WHERE ticket_record.assign_to = '$assign_to' AND ticket_category.multiple_assignation = '0' AND ticket_status.name NOT LIKE '%review%' AND ticket_status.name NOT LIKE '%close%'";
        $stepsLog .= date(DATE_ATOM) . " :: checkUserStatus_query => $checkUserStatus_query \n\n";
        $checkUserStatus = $conn->query($checkUserStatus_query);
        $checkUserStatus = mysqli_fetch_column($checkUserStatus);
        $stepsLog .= date(DATE_ATOM) . " :: checkUserStatus fetch Data => $checkUserStatus \n\n";
        if($checkUserStatus > 0) {
            saveLog(showResponse(false,"User Already Assign On New Development"));
        } else {
            saveLog(showResponse(true,"Assign User"));
        }
    } catch(Exception $e) {
        saveLog(showResponse(false,"Error : ". $e->getMessage()));
    }
}

function insertReopenTicketQuery($ticket_id,$query_message,$attachment) {
    global $conn,$stepsLog;
    $stepsLog .= date(DATE_ATOM). " :: method inside the insertReopenTicketQuery \n\n";
    $stepsLog .= date(DATE_ATOM). " :: requested data : ticket_id => $ticket_id , query_message => $query_message , attachment => $attachment \n\n";
    try {
        $checkTicketReopenStatus_query = "SELECT IF(reopenStatus = '1','open','close') as `status` , department , reopenQuery , reopenAttachment FROM ticket_record WHERE id = '$ticket_id'";
        $stepsLog .= date(DATE_ATOM) . " :: checkTicketReopenStatus_query => $checkTicketReopenStatus_query \n\n";
        $checkTicketReopenStatus = $conn->query($checkTicketReopenStatus_query);
        $checkTicketReopenStatus = mysqli_fetch_assoc($checkTicketReopenStatus);
        $stepsLog .= date(DATE_ATOM) . " :: resposne => " . json_encode($checkTicketReopenStatus) . "\n\n";
        $department = $checkTicketReopenStatus['department'];
        $reopenQuery = (!empty($checkTicketReopenStatus['reopenQuery']) && !is_null($checkTicketReopenStatus['reopenQuery'])) ? json_decode($checkTicketReopenStatus['reopenQuery'],true) : [];
        $reopenAttachment = (!empty($checkTicketReopenStatus['reopenAttachment']) && !is_null($checkTicketReopenStatus['reopenAttachment'])) ? json_decode($checkTicketReopenStatus['reopenAttachment'],true) : [];
        if($checkTicketReopenStatus['status'] == 'close') {
            $reopenQuery[] = $query_message;
            $reopenAttachment[] = (!is_null($attachment) || $attachment != '') ? $attachment : "No Attachment";
            $reopenQuery = json_encode($reopenQuery);
            $reopenAttachment = json_encode($reopenAttachment); 
            $updateReopenStatus_query = "UPDATE ticket_record SET reopenStatus = '1' , reopenQuery = '$reopenQuery' , reopenAttachment = '$reopenAttachment' WHERE id = '$ticket_id'";
            $stepsLog .= date(DATE_ATOM) . " :: updateReopenStatus_query =>  $updateReopenStatus_query \n\n";
            $updateReopenStatus = $conn->query($updateReopenStatus_query);
            if (!$updateReopenStatus) {
                saveLog(showResponse(false,"Something Went Wrong"));
            }

            $topMostHierarchyUser = getTopMostUserDetails($department);
            if($topMostHierarchyUser['status'] == 400) {
                saveLog(showResponse(false,$topMostHierarchyUser['message']));    
            }

            // get the ticket info 
            $ticketAllInformation = getTicketAllInformation($ticket_id);
            $ticketAllInformation['clientQueryMessage'] = $query_message;
            $email_function = [
                "messageToDepartmentHeadAboutTicketReopen" => [
                    "email" => $topMostHierarchyUser['usersEmail']
                ],
            ];
            $queueMailResponse = createEmailData($ticketAllInformation,$email_function);
            $queueMailResponse = json_decode($queueMailResponse,true);
            if ($queueMailResponse['status'] == 400) {
                saveLog(showResponse(false,"Re-open status update but mail not send"));
            }
            saveLog(showResponse($updateReopenStatus,"Re-open Request Send"));
        } else {
            saveLog(showResponse(false,"Re-open request already send"));
        }
    } catch(Exception $e) {
        saveLog(showResponse(false,"Error : ". $e->getMessage()));
    }
}

function updateTicketStatusReviewToClose($ticket_id) {
    
    global $conn;
    global $stepsLog;

    $stepsLog .= date(DATE_ATOM). " :: method inside the UpdateTicketStatusReviewToClose \n\n";
    try {
        $checkTicketStatus = $conn->query("SELECT `status` from `ticket_record` WHERE id = '$ticket_id'");
        $checkTicketStatus = mysqli_fetch_column($checkTicketStatus);
        if ($checkTicketStatus != '5') {
            $updateQuery = "UPDATE ticket_record SET status = '5' WHERE id = '$ticket_id'";
            $update = $conn->query($updateQuery);
            // insert ticket history
            insertTicketHistory($ticket_id);
            
            // get the ticket info 
            $ticketAllInformation = getTicketAllInformation($ticket_id);
            
            list($to,$cc) = getTicketAllAssignUserEmail($ticket_id);
            $email_queue['to'] = $to;
            $email_queue['cc'] = $cc; 
            $email_function = [
                "messageForTicketCloseConfirmation" => [
                    "email" => $email_queue,
                ]
            ];
            
            if (isset($_REQUEST['requestFromAutoClose'])) {
                $email_function["messageForAutoCloseTicket"] = [
                    "email" => $ticketAllInformation['createPersonEmail']
                ];
            }
            $queueMailResponse = createEmailData($ticketAllInformation,$email_function);
            $queueMailResponse = json_decode($queueMailResponse,true);
            if ($queueMailResponse['status'] == 400) {
                saveLog(showResponse(false,"Ticket create but mail not send"));
            }
            showResponse($update,"Status updated");
        } else {
            showResponse(false,"Status already updated");
        }
    } catch(Exception $e) {
        $stepsLog .= date(DATE_ATOM) . ": got exception => " . $e->getMessage() . " in file => " . $e->getFile() . " on line => " . $e->getLine() . " \n\n";
        showResponse(false,"Exception : " . $e->getMessage());
    }
}

function insertNotification($ticket_id) {

    global $conn,$stepsLog;
    $stepsLog .= date(DATE_ATOM). " :: method inside the insertNotification \n\n";
    $stepsLog .= date(DATE_ATOM). " :: requested data : assign_to => $ticket_id \n\n";
    try {
        $user_id = $_SESSION['ID'];
        $chechNotification_query = "SELECT id FROM `notifications` WHERE ticket_id = '$ticket_id' AND user_id = '$user_id'";
        $chechNotificationStatus = $conn->query($chechNotification_query);
        if($chechNotificationStatus->num_rows > 0) {
            saveLog(showResponse(true,"Notification already added"));
        } else {
            $insertNotification = $conn->query("INSERT INTO `notifications`(`ticket_id`, `user_id`) VALUES ('$ticket_id','$user_id')");
            $_SESSION['numOfTicketNotSeen'] = $_SESSION['numOfTicketNotSeen'] - 1;
            if ($_SESSION['numOfTicketNotSeen'] < 1) {
                $_SESSION['notificationCount'] -= 1;
            }
            saveLog(showResponse($insertNotification,"Notification added"));
        }
    } catch (Exception $e) {
        saveLog(showResponse(false,"Error : ". $e->getMessage()));
    }
}

/**
 * Use for create email queue and send data for redis push 
 */
function createEmailData($ticket_info_data,$email_function) {
    
    global $mailjob;
    global $stepsLog;
    global $baseUrl;
    global $createMailStructure;
    $stepsLog .= date(DATE_ATOM). " :: method inside the createEmailData \n\n";
    $stepsLog .= date(DATE_ATOM). " :: requested ticket_info_data => " . json_encode($ticket_info_data) . " email_function => " . json_encode($email_function) . " \n\n";

    $mail_queue = [];
    foreach ($email_function as $funcName => $emailData) {
        list($mailBody,$mailSubject,$to,$cc) = $createMailStructure->$funcName($ticket_info_data,$emailData['email'])->toArray();
        $stepsLog .= date(DATE_ATOM) . " :: Resposne received from => $funcName \n\n";
        $stepsLog .= date(DATE_ATOM) . " :: TO => $to \n\n cc => $cc \n\n subject => $mailSubject \n\n body => $mailBody \n\n";
        $mail_queue[] = $mailjob->setData($to,$cc,$mailSubject,$mailBody)->toArray();
    }   
    $stepsLog .= date(DATE_ATOM) . " :: mail_queue Data => " . json_encode($mail_queue) . "\n\n";
    $url = $baseUrl."/app/mailSystem/CreateMailQueue";
    try {
        $request = [];
        $request['data'] = $mail_queue;
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
        $stepsLog .= date(DATE_ATOM) . " :: Errro => " . $e->getMessage() . " file => " . $e->getFile() . " on line => " . $e->getLine() .  " \n\n";
        return json_encode(['status'=>400,'message'=>"Error : ".$e->getMessage()]);
    }
}

function getTicketAllInformation($ticket_id) {
    global $stepsLog, $conn;
    $stepsLog .= date(DATE_ATOM). " :: method inside the getTicketAllInformation \n\n";
    $stepsLog .= date(DATE_ATOM). " :: requested data : ticket_id => $ticket_id \n\n";

    $fetchTicketInfo_query = "SELECT ticket_record.id , ticket_record.unique_id , ticket_record.task_name , ticket_record.task_description , CASE WHEN ticket_record.priority = '1' THEN 'Low' WHEN ticket_record.priority = '2' THEN 'Medium' WHEN ticket_record.priority = '3' THEN 'High' END as `ticketPriority` , DATE_FORMAT(ticket_record.updated_at,'%d-%b-%Y %h:%i:%s') as `assignDate` , ticket_record.create_person_name as `createdPersonName` , assign_to_user.Name as `assignToUser` , assign_by_user.Name as `assignByUser` , ticket_record.create_person_email as `createPersonEmail` , assign_to_user.Email as `assignToUserEmail` FROM ticket_record LEFT JOIN users as `assign_to_user` ON ticket_record.assign_to = assign_to_user.ID LEFT JOIN users AS `assign_by_user` ON ticket_record.assign_by = assign_by_user.ID  WHERE ticket_record.id = '$ticket_id'";
    $stepsLog .= date(DATE_ATOM) . " :: fetch ticket info Query => $fetchTicketInfo_query \n\n";
    
    $fetchTicketInfo = $conn->query($fetchTicketInfo_query);
    $fetchTicketInfo = mysqli_fetch_assoc($fetchTicketInfo);
    return [
        "ticket_id" => $fetchTicketInfo['id'],
        "ticketQniqueId" => $fetchTicketInfo['unique_id'],
        "ticketSubject" => $fetchTicketInfo['task_name'] ,
        "ticketPriority" => $fetchTicketInfo['ticketPriority'] , 
        "assignDate" => $fetchTicketInfo['assignDate'] , 
        "createdPersonName" => $fetchTicketInfo['createdPersonName'] , 
        "assignToUser" => $fetchTicketInfo['assignToUser'] , 
        "assignByUser" => $fetchTicketInfo['assignByUser'] , 
        "createPersonEmail" => $fetchTicketInfo['createPersonEmail'],
        "assignToUserEmail" => $fetchTicketInfo['assignToUserEmail']
    ];
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
    deleteOldTicketLogs($pdf_dir,5);
    $fh = fopen($pdf_dir . 'createTicket_' . date('y-m-d') . '.log' , 'a');
    fwrite($fh,$stepsLog);
    fclose($fh);
    echo json_encode($response);
    exit;
}

function deleteOldTicketLogs($logDir, $daysOld = 5) {
    $maxFileAge = $daysOld * 86400; // 5 days in seconds
    if (!is_dir($logDir)) return;
    foreach (glob($logDir . '/createTicket_*.log') as $file) {
        if (is_file($file) && (time() - filemtime($file)) > $maxFileAge) {
            unlink($file);
        }
    }
}
?>