<?php
## Database configuration
include '../../includes/db-config.php';
session_start();

$data_field = file_get_contents('php://input'); // by this we get raw data
$data_field = json_decode($data_field,true);

$ticket_details = [];
$assignToUser_details = [];
$assignByUser_details = [];
$validationForNewStatus = '';
$validationOnCategoryAndDepartment = '';
if(isset($data_field['id'])) {
    $id = mysqli_real_escape_string($conn,$data_field['id']);
    $ticket = $conn->query(getTicketData($id));
    $ticket_details = mysqli_fetch_assoc($ticket);
    // validation if ticket status is New
    if ($ticket_details['status_value'] == '1' && $_SESSION['role'] != '1') {
        $validationForNewStatus = "disabled";
        $current_user = $_SESSION['ID'];
        $all_user_ids = explode(",",getTopMostUserDetails($ticket_details['department']));
        if (in_array($current_user,$all_user_ids)) {
            $validationForNewStatus = "";
        }
    }
    // if status change from new to any other then 
    if ($ticket_details['status_value'] != '1' && $_SESSION['role'] != '1') {
        $validationOnCategoryAndDepartment = 'disabled';
    }
    $priority_color = match($ticket_details['priority']) {
        'Low' => "bg-success",
        'Medium' => "bg-warning",
        'High' => "bg-danger"
    };
    $numOfComment = 0;
    $comments = checkComments($id);
    $ticket_comment_style = '';
    if(!empty($comments)) {   
        $numOfComment = count($comments);
        if($numOfComment > 2) {
            $ticket_comment_style = 'style="height: 500px;overflow-y: auto;"';
        }
    }
    $assignToUser_details = getAssignUserDetails($ticket_details['assign_to']);
    $assignByUser_details = getAssignUserDetails($ticket_details['assign_by']);
}

function getTicketData($id) {

    $select_query = "
    SELECT 
	ticket_record.unique_id as `unique_id` , 
    ticket_record.task_name as `task_name` , 
    ticket_record.task_description , 
    ticket_record.status as `status_value` ,
    ticket_status.name as `status` ,
    ticket_record.raised_by as `raised_by` , 
    ticket_record.assign_by ,
    ticket_record.assign_to ,
    ticket_record.category as `category`, 
    ticket_category.name as `category_name`,
    ticket_record.department as `department`, 
    department.department_name as `department_name`,
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
    return $select_query;
}

function checkComments($ticket_id) {

    global $conn;

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
    
    $comments_data = $conn->query($select_query);
    $comment = [];
    if ($comments_data->num_rows > 0) {
        $comment = mysqli_fetch_all($comments_data,MYSQLI_ASSOC);
    }
    return $comment;
}

function getAssignUserDetails($assignUserId) : array {

    global $conn;
    $assignUserDetails = [];
    if(!is_null($assignUserId)) {
        $getUserDetails_query = "SELECT users.ID , users.Name , users.Photo , Designation.designation_name as `designation` FROM users LEFT JOIN Designation ON Designation.ID = users.Designation_id WHERE users.ID = '$assignUserId'";
        $getUserDetails = $conn->query($getUserDetails_query);
        $getUserDetails = mysqli_fetch_assoc($getUserDetails);
        $assignUserDetails['name'] = $getUserDetails['Name'];
        $assignUserDetails['image'] = $getUserDetails['Photo'];
        $assignUserDetails['designation'] = $getUserDetails['designation'];
    } else {
        $assignUserDetails['name'] = 'Not Assing';
        $assignUserDetails['image'] = '/../../assets/images/sample_user.jpeg';
        $assignUserDetails['designation'] = 'None';
    }
    return $assignUserDetails;
}

function getFileName($file_path) {
    $arr = explode("/",$file_path);
    $file_name = end($arr);
    return $file_name;
}

function getTopMostUserDetails($departmentId) {

    global $conn;
    $department_details = $conn->query("SELECT * FROM `Department` WHERE id = '$departmentId'");
    $department_details = mysqli_fetch_assoc($department_details);
    $departmentUpperHierarchy_user = $conn->query("SELECT GROUP_CONCAT(ID) FROM `users` WHERE Department_id = '$departmentId' AND Deleted_At IS NULL AND Hierarchy_value = (SELECT MIN(Hierarchy_value) FROM `users` WHERE Department_id = '$departmentId' AND Deleted_At IS NULL)");
    $departmentUpperHierarchy_user = mysqli_fetch_column($departmentUpperHierarchy_user);
    if ($department_details['vertical_id'] == '1' || $department_details['vertical_id'] == '2' || $department_details['vertical_id'] == '3') {
        $vertical = "'1','2','3'";  
    } else {
        $vertical =  $_SESSION['Vertical_id'];
    }    
    // Fetch users for different scopes in a single query
    $query = "
        SELECT 
            GROUP_CONCAT(CASE WHEN Vertical_id IN ($vertical) AND Department_id IS NULL THEN ID END) AS vertical_users,
            GROUP_CONCAT(CASE WHEN Vertical_id IS NULL AND Department_id IS NULL AND Branch_id IN (" . implode(',', json_decode($department_details['branch_id'], true)) . ") THEN ID END) AS branch_users,
            GROUP_CONCAT(CASE WHEN Vertical_id IS NULL AND Department_id IS NULL AND Branch_id IS NULL AND Organization_id = '" . $department_details['organization_id'] . "' THEN ID END) AS organization_users
        FROM `users`
        WHERE Deleted_At IS NULL;
    ";
    $result = $conn->query($query);
    $users = mysqli_fetch_assoc($result);

    // Combine all user IDs
    $all_users = array_filter([
        $departmentUpperHierarchy_user,
        $users['vertical_users'],
        $users['branch_users'],
        $users['organization_users']
    ]);

    $all_user_ids = implode(',', $all_users);
    $all_user_ids = '1,'. $all_user_ids;
    return $all_user_ids;
}
?>