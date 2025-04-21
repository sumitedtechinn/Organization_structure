<?php 

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

## Database configuration
include '../../includes/db-config.php';
require '../../app/mailSystem/MailJob.php';
require '../../app/mailSystem/CreateMailStructure.php';


$obj = new StoreAndUpdateTicketApi();
$obj->conn = $conn;
$obj->mailjob = new MailJob();
$obj->createMailStructure = new CreateMailStructure();
$obj->request = $_REQUEST;

try {
    if(!empty($obj->request)) {
        $obj->stepsLog = date(DATE_ATOM) . ": request received => " . json_encode($obj->request) . " \n\n";
        $functionName = $obj->request['requestMethod'];
        $obj->$functionName();
    } else {
        $obj->showResponse(false,"Empty reqeust found");
    }
} catch (Exception $e) {
    $obj->stepsLog = date(DATE_ATOM) . ": got exception => " . $e->getMessage() . " in file => " . $e->getFile() . " on line => " . $e->getLine();
    $obj->showResponse(false,"Exception : " . $e->getMessage());
} catch (Error $e) {
    $obj->stepsLog = date(DATE_ATOM) . ": got error => " . $e->getMessage() . " in file => " . $e->getFile() . " on line => " . $e->getLine();
    $obj->showResponse(false,"Error : " . $e->getMessage());
} finally {
    $obj->saveLog();
}

class StoreAndUpdateTicketApi {

    public $conn; 
    public $stepsLog; 
    public $request;
    public $finalRes;
    public $baseUrl = BASE_URL;
    public $mailjob;
    public $createMailStructure;

    public function insertTicket() {

        if(isset($this->request['ticket_heading']) && isset($this->request['ticket_category']) && isset($this->request['requestfrom']) && isset($this->request['ticket_description']) && isset($this->request['priority']) && isset($this->request['status']) && isset($this->request['raised_by']) && isset($this->request['create_person_name']) && isset($this->request['create_person_number'])) {

            try {
                $this->request['attachment'] = null;
                // for check attachment file
                if(isset($_FILES['attachment']) && !empty($_FILES['attachment']['name'])) {
                    $image_name = $_FILES['attachment']['name'];
                    $path_name = $this->checkAndUploadImage($image_name);
                    if(!$path_name) {
                        $this->showResponse(false,'File must be image');
                        $this->saveLog();
                    } else {
                        $this->request['attachment'] = $path_name;
                    }
                }

                $erp_name = strtoupper($this->request['requestfrom']);
                // Get the last inserted unique id 
                $lastid_query = "SELECT SUBSTRING_INDEX(unique_id, '-',-1) as `unique_id` FROM `ticket_record` WHERE unique_id LIKE '%$erp_name%' ORDER BY id DESC LIMIT 1";
                $exceute_query = $this->conn->query($lastid_query);
                if($exceute_query->num_rows > 0) {
                    $lastid = mysqli_fetch_column($exceute_query);
                    ++$lastid;
                } else {
                    $lastid = 1;
                }
                $unique_id = $erp_name .'-'. $lastid;
                list($ticket_category,$department) = explode("##",$this->request['ticket_category']);
                
                // Insert new ticket in the DB 
                $insert_query = "INSERT INTO `ticket_record`(`unique_id`, `task_name`, `task_description`, `requestfrom`, `raised_by`, `status`, `priority`, `category`, `department`, `create_person_name`, `create_person_number`, `create_person_email`, `attachment`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";

                $insert_param = [
                    $unique_id, 
                    $this->request['ticket_heading'], 
                    $this->request['ticket_description'] ,
                    $this->request['requestfrom'] , 
                    $this->request['raised_by'],
                    $this->request['status'],
                    $this->request['priority'],
                    $ticket_category ,
                    $department , 
                    $this->request['create_person_name'],
                    $this->request['create_person_number'],
                    $this->request['create_person_email'] ,
                    $this->request['attachment']
                ];

                $this->stepsLog .=  date(DATE_ATOM) . " :: INSERT query => $insert_query \n\n";
                $this->stepsLog .=  date(DATE_ATOM) . " :: INSERT param => " . json_encode($insert_param) . " \n\n";
                $stmt = $this->conn->prepare($insert_query);
                $stmt->bind_param("sssssiiiissss", ...$insert_param);

                if ($stmt->execute()) {
                    $this->stepsLog .= date(DATE_ATOM) . " :: Insert into ticket successful \n\n";
                } else {
                    throw new Exception("Insert into ticket failed: " . $stmt->error);
                }

                // Insert ticket details in history 
                $last_id = $this->conn->insert_id;
                $insertTicketHistory_query = "INSERT INTO `ticket_history` (`ticket_id`, `updated_by`, `status`, `priority`, `category`, `department`) VALUES (?,?,?,?,?,?)";
                $this->stepsLog .= date(DATE_ATOM) . " :: insertTicketHistory_query => $insertTicketHistory_query \n\n";
                $insertTicketHistory = $this->conn->prepare($insertTicketHistory_query);

                if ($insertTicketHistory === false) {
                    throw new Exception("Prepare failed: " . $this->conn->error);
                }

                $insert_historyParam = [
                    $last_id, 
                    $this->request['raised_by'],
                    $this->request['status'],
                    $this->request['priority'], 
                    $ticket_category ,
                    $department ,
                ];

                $this->stepsLog .= date(DATE_ATOM) . " :: insertTicketHistory_param => " .json_encode($insert_historyParam).  " \n\n";
                $insertTicketHistory->bind_param("iiiiii", ...$insert_historyParam);

                if ($insertTicketHistory->execute()) {
                    $this->stepsLog .= date(DATE_ATOM) . " :: Ticket history is inserted successful \n\n";
                } else {
                    throw new Exception("Insert into ticket failed: " . $insertTicketHistory->error);
                }

                // For Sending Mail
                $ticket_info_data = [
                    "ticketQniqueId" => $unique_id, 
                    "ticketSubject" => $this->request['ticket_heading'], 
                    "ticketPriority" => $this->request['priority'], 
                    "raisedByPersonName" => $this->request['create_person_name'],
                    "createdTime" => date("d-M-Y h:i:s")
                ];
                $topMostHierarchyUser = $this->getTopMostUserDetails($department);
                if($topMostHierarchyUser['status'] == 400) {
                    $this->showResponse(false,"Ticket create but mail not send");   
                    $this->saveLog(); 
                }

                $email_function = [
                    "successfulTicketGenerationMessageForTicketRaisedPerson" => [
                        "email" => $this->request['create_person_email'] ,
                    ],
                    "messageToDepartHeadForNewTicketCreate" => [
                        "email" => $topMostHierarchyUser['usersEmail']
                    ],
                ];
                
                $queueMailResponse = $this->createEmailData($ticket_info_data,$email_function);
                $queueMailResponse = json_decode($queueMailResponse,true);
                if ($queueMailResponse['status'] == 400) {
                    $this->showResponse(false,"Ticket create but mail not send");
                    $this->saveLog();
                }

                $this->showResponse(true,"Ticket create");
            } catch (Exception $e) {
                $this->stepsLog = date(DATE_ATOM) . ": got exception => " . $e->getMessage() . " in file => " . $e->getFile() . " on line => " . $e->getLine();
                $this->showResponse(false,"Exception : " . $e->getMessage());
            }
        }
    }

    public function checkAndUploadImage($image_name) : bool|string {
        $extension = substr($image_name,strlen($image_name)-4,strlen($image_name));
        $allowed_extensions = array(".jpg","jpeg",".png",".gif",".pdf");
        if(in_array($extension,$allowed_extensions)) {
            move_uploaded_file($_FILES['attachment']['tmp_name'],'../../uploads/ticket_attachment/' . $image_name);
            return '../../uploads/ticket_attachment/' . $image_name;
        } else {
            return false;
        }
    }

    public function checkAndUploadImageAndPdf($file) {
        $extension = substr($file['name'],strlen($file['name'])-4,strlen($file['name']));
        $allowed_extensions = array(".jpg","jpeg",".png",".gif",".pdf");
        if(in_array($extension,$allowed_extensions)) {
            move_uploaded_file($file['tmp_name'],'../../uploads/ticket_attachment/' . $file['name']);
            return '../../uploads/ticket_attachment/' . $file['name'];
        } else {
            return false;
        }
    }
    

    public function insertComment() {

        $this->stepsLog .= date(DATE_ATOM). " :: method inside the InsertComment \n\n";

        try {
            $comment = mysqli_real_escape_string($this->conn,$this->request['comment']);
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
            $ticket_id = mysqli_real_escape_string($this->conn,$this->request['ticket_id']);
            $user_id = mysqli_real_escape_string($this->conn,$this->request['user_id']);
            $user_name = mysqli_real_escape_string($this->conn,$this->request['user_name']);
            $user_image = mysqli_real_escape_string($this->conn,$this->request['user_image']);
            $store_attachment = [];
            if(!empty($attachments)) {
                foreach ($attachments as $value) {
                    $path_name = $this->checkAndUploadImageAndPdf($value);
                    if(!$path_name) {
                        $this->showResponse(false,'File must be image or PDF');
                        $this->saveLog();
                    } else {
                        $store_attachment[] = $path_name;
                    }
                }
            }
            $this->stepsLog .= date(DATE_ATOM). " :: store_attachement => ". json_encode($store_attachment) ." \n\n";
            $comment = $comment;
            $store_attachment = json_encode($store_attachment);

            $insert_comment_query = "INSERT INTO `ticket_comment`(`ticket_id`, `user_id`, `user_name`, `user_image`, `comment`, `attachment`) VALUES (?,?,?,?,?,?)";

            $this->stepsLog .= date(DATE_ATOM) . " :: INSERT comment query => $insert_comment_query \n\n";
            $insert_comment = $this->conn->prepare($insert_comment_query);

            $insert_comment_param = [
                $ticket_id ,
                $user_id , 
                $user_name , 
                $user_image , 
                $comment , 
                $store_attachment
            ];

            $this->stepsLog .= date(DATE_ATOM) . " :: insert comment param =>  " . json_encode($insert_comment_param). " \n\n";
            $insert_comment->bind_param("isssss", ...$insert_comment_param);

            if ($insert_comment->execute()) {
                $this->stepsLog .= date(DATE_ATOM) . " :: Comment inserted successful \n\n";
            } else {
                throw new Exception("Insert into ticket failed: " . $insert_comment->error);
            }

            $this->showResponse(true,"Comment send");
            
        } catch (Exception $e) {
            $this->stepsLog = date(DATE_ATOM) . ": got exception => " . $e->getMessage() . " in file => " . $e->getFile() . " on line => " . $e->getLine() . " \n\n";
            $this->showResponse(false,"Exception : " . $e->getMessage());
        }
    }

    /**
     * Get the UpperHierarchy User Details 
     */
    public function getTopMostUserDetails($departmentId) : array {

        $this->stepsLog .= date(DATE_ATOM). " :: method inside the getTopMostUserDetails \n\n";
        $this->stepsLog .= date(DATE_ATOM). " :: requested data => $departmentId \n\n";
        try {
            $department_details_query = "SELECT * FROM `Department` WHERE id = '$departmentId'";
            $this->stepsLog .= date(DATE_ATOM) . " :: Department details Query => $department_details_query \n\n";
            $department_details = $this->conn->query($department_details_query);
            $department_details = mysqli_fetch_assoc($department_details);
            $departmentUpperHierarchy_user_query = "SELECT Email FROM `users` WHERE Department_id = '$departmentId' AND Deleted_At IS NULL AND Hierarchy_value = (SELECT MIN(Hierarchy_value) FROM `users` WHERE Department_id = '$departmentId' AND Deleted_At IS NULL)";
            $this->stepsLog .= date(DATE_ATOM) . " :: DepartmentUpperHierarchy user Query => $departmentUpperHierarchy_user_query \n\n";
            $departmentUpperHierarchy_user = $this->conn->query($departmentUpperHierarchy_user_query);
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
            $this->stepsLog .= date(DATE_ATOM) . " :: verticalUpperHierarchyUser Query => $query \n\n";
            $result = $this->conn->query($query);
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
            $this->stepsLog .= date(DATE_ATOM) . " :: Errro => " . $e->getMessage() . " file => " . $e->getFile() . " on line => " . $e->getLine() .  " \n\n";
            return ['status'=>400,'message'=>"Error : ".$e->getMessage()];
        }
    }

    /**
     * Use for create email queue and send data for redis push 
     */
    public function createEmailData($ticket_info_data,$email_function) {
        
        $this->stepsLog .= date(DATE_ATOM). " :: method inside the createEmailData \n\n";
        $this->stepsLog .= date(DATE_ATOM). " :: requested ticket_info_data => " . json_encode($ticket_info_data) . " email_function => " . json_encode($email_function) . " \n\n";

        $mail_queue = [];
        foreach ($email_function as $funcName => $emailData) {
            list($mailBody,$mailSubject,$to,$cc) = $this->createMailStructure->$funcName($ticket_info_data,$emailData['email'])->toArray();
            $this->stepsLog .= date(DATE_ATOM) . " :: Resposne received from => $funcName \n\n";
            $this->stepsLog .= date(DATE_ATOM) . " :: TO => $to \n\n cc => $cc \n\n subject => $mailSubject \n\n body => $mailBody \n\n";
            $mail_queue[] = $this->mailjob->setData($to,$cc,$mailSubject,$mailBody)->toArray();
        }   
        $this->stepsLog .= date(DATE_ATOM) . " :: mail_queue Data => " . json_encode($mail_queue) . "\n\n";
        $url = $this->baseUrl."/app/mailSystem/CreateMailQueue";
        try {
            $request = [];
            $request['data'] = $mail_queue;
            $request = json_encode($request);
            $this->stepsLog .= date(DATE_ATOM) . " :: url => $url , request => $request \n\n";
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
            $this->stepsLog .= date(DATE_ATOM) . " :: response => $response \n\n";
            return $response;
        } catch (Error $e) {
            $this->stepsLog .= date(DATE_ATOM) . " :: Errro => " . $e->getMessage() . " file => " . $e->getFile() . " on line => " . $e->getLine() .  " \n\n";
            return json_encode(['status'=>400,'message'=>"Error : ".$e->getMessage()]);
        }
    }

    public function showResponse($response, $message = "Something went wrong!") {

        $this->finalRes = ($response) ? ['status' => 200, 'message' => "$message successfully!"] : ['status' => 400, 'message' => $message]; 
        $this->stepsLog .= date(DATE_ATOM) . " :: respose => " . json_encode($this->finalRes) . "\n\n";
    }

    public function saveLog() {
        
        $this->stepsLog .= " ============ End Of Script ================== \n\n";
        $pdf_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/ticket_log/';
        $this->deleteOldTicketLogs($pdf_dir,5);
        $fh = fopen($pdf_dir . 'createTicketAPI_' . date('y-m-d') . '.log' , 'a');
        fwrite($fh,$this->stepsLog);
        fclose($fh);
        echo json_encode($this->finalRes);
        exit;
    }

    public function deleteOldTicketLogs($logDir, $daysOld = 5) {
        $maxFileAge = $daysOld * 86400; // 5 days in seconds
        if (!is_dir($logDir)) return;
        foreach (glob($logDir . '/createTicketAPI_*.log') as $file) {
            if (is_file($file) && (time() - filemtime($file)) > $maxFileAge) {
                unlink($file);
            }
        }
    }
}

?>