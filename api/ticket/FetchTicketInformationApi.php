<?php 

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

## Database configuration
include '../../includes/db-config.php';

$obj = new FetchTicketInformationApi();
$obj->conn = $conn;
$obj->request = file_get_contents('php://input');

try {
    if(!empty($obj->request)) {
        $obj->request = json_decode($obj->request,true);
        $functionName = $obj->request['method'];
        $obj->$functionName($obj->request['id']);
    } else {
        $obj->showResponse(false,"Empty Request Data");
    }
} catch (Exception $e) {
    $obj->showResponse(false,": got exception => " . $e->getMessage() . " in file => " . $e->getFile() . " on line => " . $e->getLine());
}

class FetchTicketInformationApi {

    public $conn; 
    public $request;
    public $finalRes;

    public function getTicketInfo($id) {

        try {
            $select_query = "
            SELECT 
            ticket_record.id as `id` ,
            ticket_record.unique_id as `unique_id` , 
            ticket_record.task_name as `task_name` , 
            ticket_record.task_description , 
            ticket_record.status as `status_value` ,
            ticket_status.name as `status` ,
            ticket_record.raised_by as `raised_by` , 
            IF(ticket_record.assign_by IS NULL , 'Not Assign',ticket_record.assign_by) as `assign_by`,  
            IF(ticket_record.assign_to IS NULL , 'Not Assign',ticket_record.assign_to) as `assign_to`,
            ticket_record.category as `category`, 
            ticket_category.name as `category_name`,
            ticket_record.department as `department`, 
            Department.department_name as `department_name`,
            ticket_record.create_person_name, 
            ticket_record.create_person_number,
            ticket_record.create_person_email,
            ticket_record.priority as `priority_value`,
            IF(ticket_record.deadline_date IS NOT NULL,ticket_record.deadline_date,'deadLine not set') AS `deadline_date`,
            CASE 
            WHEN ticket_record.priority = '1' THEN 'Low' 
            WHEN ticket_record.priority = '2' THEN 'Medium' 
            WHEN ticket_record.priority = '3' THEN 'High' 
            END AS `priority` ,
            CASE 
            WHEN TIMESTAMPDIFF(YEAR,ticket_record.updated_at, NOW()) > 0 
                THEN CONCAT(TIMESTAMPDIFF(YEAR,ticket_record.updated_at, NOW()), ' ', 
                            IF(TIMESTAMPDIFF(YEAR,ticket_record.updated_at, NOW()) = 1, 'Year Ago', 'Years Ago'))
            WHEN TIMESTAMPDIFF(MONTH,ticket_record.updated_at, NOW()) > 0 
                THEN CONCAT(TIMESTAMPDIFF(MONTH,ticket_record.updated_at, NOW()), ' ', 
                            IF(TIMESTAMPDIFF(MONTH,ticket_record.updated_at, NOW()) = 1, 'Month Ago', 'Months Ago'))
            WHEN FLOOR(TIMESTAMPDIFF(DAY,ticket_record.updated_at, NOW()) / 7) > 0 
                THEN CONCAT(FLOOR(TIMESTAMPDIFF(DAY,ticket_record.updated_at, NOW()) / 7), ' ', 
                            IF(FLOOR(TIMESTAMPDIFF(DAY,ticket_record.updated_at, NOW()) / 7) = 1, 'Week Ago', 'Weeks Ago'))
            WHEN TIMESTAMPDIFF(DAY,ticket_record.updated_at, NOW()) > 0 
                THEN CONCAT(TIMESTAMPDIFF(DAY,ticket_record.updated_at, NOW()), ' ', 
                            IF(TIMESTAMPDIFF(DAY,ticket_record.updated_at, NOW()) = 1, 'Day Ago', 'Days Ago'))
            WHEN TIMESTAMPDIFF(HOUR, ticket_record.updated_at, NOW()) > 0 
                THEN CONCAT(TIMESTAMPDIFF(HOUR,ticket_record.updated_at, NOW()), ' ', 
                            IF(TIMESTAMPDIFF(HOUR,ticket_record.updated_at, NOW()) = 1, 'Hour Ago', 'Hours Ago'))
            WHEN TIMESTAMPDIFF(MINUTE,ticket_record.updated_at, NOW()) > 0 
                THEN CONCAT(TIMESTAMPDIFF(MINUTE,ticket_record.updated_at, NOW()), ' ', 
                            IF(TIMESTAMPDIFF(MINUTE,ticket_record.updated_at, NOW()) = 1, 'Minute Ago', 'Minutes Ago'))
            ELSE 'Just Now'
            END AS `update`,
            ticket_record.attachment 
            FROM `ticket_record` 
            LEFT JOIN ticket_status ON ticket_status.id = ticket_record.status 
            LEFT JOIN ticket_category ON ticket_category.id = ticket_record.category 
            LEFT JOIN Department ON Department.id = ticket_record.department 
            WHERE ticket_record.id = '$id'
            ";

            $ticketInfo = $this->conn->query($select_query);
            $ticketInfo = mysqli_fetch_assoc($ticketInfo);   

            $priority_color = match ($ticketInfo['priority']) {
                'Low' => 'badge-success',
                'Medium' => 'badge-warning',
                'High' => 'badge-danger'
            };
            $ticketInfo['priority_color'] = $priority_color;
            
            $this->showResponse(true,"Ticket Fetch ",$ticketInfo);
        } catch (Exception $ex) {
            $this->showResponse(false,"Exception : " . $ex->getMessage());
        }
    }
    
    function checkComments($ticket_id) {
    
        try{
            $select_query = "
            SELECT * , CASE 
            WHEN TIMESTAMPDIFF(YEAR, created_at, NOW()) > 0 
                THEN CONCAT(TIMESTAMPDIFF(YEAR, created_at, NOW()), ' ', 
                            IF(TIMESTAMPDIFF(YEAR, created_at, NOW()) = 1, 'Year Ago', 'Years Ago'))
            WHEN TIMESTAMPDIFF(MONTH, created_at, NOW()) > 0 
                THEN CONCAT(TIMESTAMPDIFF(MONTH, created_at, NOW()), ' ', 
                            IF(TIMESTAMPDIFF(MONTH, created_at, NOW()) = 1, 'Month Ago', 'Months Ago'))
            WHEN FLOOR(TIMESTAMPDIFF(DAY, created_at, NOW()) / 7) > 0 
                THEN CONCAT(FLOOR(TIMESTAMPDIFF(DAY, created_at, NOW()) / 7), ' ', 
                            IF(FLOOR(TIMESTAMPDIFF(DAY, created_at, NOW()) / 7) = 1, 'Week Ago', 'Weeks Ago'))
            WHEN TIMESTAMPDIFF(DAY, created_at, NOW()) > 0 
                THEN CONCAT(TIMESTAMPDIFF(DAY, created_at, NOW()), ' ', 
                            IF(TIMESTAMPDIFF(DAY, created_at, NOW()) = 1, 'Day Ago', 'Days Ago'))
            WHEN TIMESTAMPDIFF(HOUR, created_at, NOW()) > 0 
                THEN CONCAT(TIMESTAMPDIFF(HOUR, created_at, NOW()), ' ', 
                            IF(TIMESTAMPDIFF(HOUR, created_at, NOW()) = 1, 'Hour Ago', 'Hours Ago'))
            WHEN TIMESTAMPDIFF(MINUTE, created_at, NOW()) > 0 
                THEN CONCAT(TIMESTAMPDIFF(MINUTE, created_at, NOW()), ' ', 
                            IF(TIMESTAMPDIFF(MINUTE, created_at, NOW()) = 1, 'Minute Ago', 'Minutes Ago'))
            ELSE 'Just Now'
            END AS time_ago
            FROM ticket_comment
            WHERE ticket_id = '$ticket_id'
            ORDER BY id DESC
            ";
            
            $comments_data = $this->conn->query($select_query);
            $comment = [];
            if ($comments_data->num_rows > 0) {
                $comment = mysqli_fetch_all($comments_data,MYSQLI_ASSOC);
            }

            $this->showResponse(true,"Ticket Commnet ",$comment);
        } catch (Exception $e) {
            $this->showResponse(false,"Exception : " . $e->getMessage());   
        }

    }
    
    public function getAssignUserDetails($assignUserId) {
       
        $assignUserDetails = [];
        try {
            if(!is_null($assignUserId)) {
                $getUserDetails_query = "SELECT users.ID , users.Name , users.Photo , Designation.designation_name as `designation` , users.Email as `email` FROM users LEFT JOIN Designation ON Designation.ID = users.Designation_id WHERE users.ID = '$assignUserId'";
                $getUserDetails = $this->conn->query($getUserDetails_query);
                $getUserDetails = mysqli_fetch_assoc($getUserDetails);
                $assignUserDetails['name'] = $getUserDetails['Name'];
                $assignUserDetails['image'] = $getUserDetails['Photo'];
                $assignUserDetails['email'] = $getUserDetails['email'];
                $assignUserDetails['designation'] = $getUserDetails['designation'];
            } else {
                $assignUserDetails['name'] = 'Not Assing';
                $assignUserDetails['image'] = '/../../assets/images/sample_user.jpeg';
                $assignUserDetails['designation'] = 'None';
                $assignUserDetails['email'] = "xxx123@gmail.com"; 
            }   
            $this->showResponse(true,"User details" ,$assignUserDetails);
        } catch (Exception $e) {
            $this->showResponse(false,"Exception : " . $e->getMessage());
        }
    }

    public function showResponse($response, $message = "Something went wrong!",$data = []) {

        $this->finalRes = ($response) ? ['status' => 200, 'message' => "$message successfully!" , 'data' => $data] : ['status' => 400, 'message' => $message ,  'data' => $data]; 
        echo json_encode($this->finalRes);
    }
    
}
?>