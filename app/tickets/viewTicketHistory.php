<?php
## Database configuration
include '../../includes/db-config.php';
session_start();

$data_field = file_get_contents('php://input'); // by this we get raw data
$data_field = json_decode($data_field,true);

$ticket_history = [];
$ticket_history_style = '';
if(isset($data_field['ticket_id'])) {
    $id = mysqli_real_escape_string($conn,$data_field['ticket_id']);
    $ticket = $conn->query(getTicketHistoryInfoQuery($id));
    $ticket_history = mysqli_fetch_all($ticket,MYSQLI_ASSOC);
    if(count($ticket_history) >= 5) {
        $ticket_history_style = 'style="height: 500px;overflow-y: auto;"';
    }
}

function getTicketHistoryInfoQuery($ticket_id) : string {

    return "
    SELECT 
	ticket_record.unique_id , 
    ticket_history.assign_by , 
    ticket_history.assign_to,
    ticket_history.updated_by as `updateByUserId` , 
    users.Name as `updateByUserName` , 
    Designation.designation_name as `updateByUserDesignation` , 
    ticket_status.name as `statusName` , 
    ticket_history.status as `statusId` , 
    ticket_history.priority as `priorityId` , 
    IF ((ticket_history.deadline_date IS NULL OR ticket_history.deadline_date = '') , 'Not Set', ticket_history.deadline_date) as `deadline_date` , 
    IF ((ticket_history.deadline_date IS NULL OR ticket_history.deadline_date != '') , 'Timer Not Start' , IF ((ticket_history.timer_stop IS NULL OR ticket_history.timer_stop = '') , 'Timer Running' , ticket_history.timer_stop)) as `timer_stop`,
	CASE 
    	WHEN ticket_history.priority = '1' THEN 'Low' 
    	WHEN ticket_history.priority = '2' THEN 'Medium' 
    	WHEN ticket_history.priority = '3' THEN 'High' 
    	END AS `priority` ,
    ticket_category.name as `categoryName` , 
    ticket_history.category as `categoryId` ,
    Department.department_name ,
    ticket_history.department as `departmentId` , 
    CASE 
    WHEN TIMESTAMPDIFF(YEAR,ticket_history.created_at, NOW()) > 0 
        THEN CONCAT(TIMESTAMPDIFF(YEAR,ticket_history.created_at, NOW()), ' ', 
                    IF(TIMESTAMPDIFF(YEAR,ticket_history.created_at, NOW()) = 1, 'Year Ago', 'Years Ago'))
    WHEN TIMESTAMPDIFF(MONTH,ticket_history.created_at, NOW()) > 0 
        THEN CONCAT(TIMESTAMPDIFF(MONTH,ticket_history.created_at, NOW()), ' ', 
                    IF(TIMESTAMPDIFF(MONTH,ticket_history.created_at, NOW()) = 1, 'Month Ago', 'Months Ago'))
    WHEN FLOOR(TIMESTAMPDIFF(DAY,ticket_history.created_at, NOW()) / 7) > 0 
        THEN CONCAT(FLOOR(TIMESTAMPDIFF(DAY,ticket_history.created_at, NOW()) / 7), ' ', 
                    IF(FLOOR(TIMESTAMPDIFF(DAY,ticket_history.created_at, NOW()) / 7) = 1, 'Week Ago', 'Weeks Ago'))
    WHEN TIMESTAMPDIFF(DAY,ticket_history.created_at, NOW()) > 0 
        THEN CONCAT(TIMESTAMPDIFF(DAY,ticket_history.created_at, NOW()), ' ', 
                    IF(TIMESTAMPDIFF(DAY,ticket_history.created_at, NOW()) = 1, 'Day Ago', 'Days Ago'))
    WHEN TIMESTAMPDIFF(HOUR, ticket_history.created_at, NOW()) > 0 
        THEN CONCAT(TIMESTAMPDIFF(HOUR,ticket_history.created_at, NOW()), ' ', 
                    IF(TIMESTAMPDIFF(HOUR,ticket_history.created_at, NOW()) = 1, 'Hour Ago', 'Hours Ago'))
    WHEN TIMESTAMPDIFF(MINUTE,ticket_history.created_at, NOW()) > 0 
        THEN CONCAT(TIMESTAMPDIFF(MINUTE,ticket_history.created_at, NOW()), ' ', 
                    IF(TIMESTAMPDIFF(MINUTE,ticket_history.created_at, NOW()) = 1, 'Minute Ago', 'Minutes Ago'))
    ELSE 'Just Now'
    END AS `update`
    FROM `ticket_history`
    LEFT JOIN ticket_record ON ticket_record.id = ticket_history.ticket_id
    LEFT JOIN ticket_status ON ticket_status.id = ticket_history.status 
    LEFT JOIN ticket_category ON ticket_category.id = ticket_history.category
    LEFT JOIN users ON users.ID = ticket_history.updated_by
    LEFT JOIN Designation ON Designation.ID = users.Designation_id
    LEFT JOIN Department ON Department.id = ticket_history.department
    WHERE ticket_id = '$ticket_id' ORDER BY ticket_history.created_at DESC
    ";
}

function getAssignUserDetails($assignUserId) : array {

    global $conn;
    $assignUserDetails = [];
    if(!is_null($assignUserId) && $assignUserId != 0) {
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
?>

<!-- Modal -->
<div class="card-body">
    <div class="border p-4 rounded">
        <!-- Header Section -->
        <div class="card-title d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Ticket History</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" id="hide-modal" aria-label="Close"></button>
        </div>
        <hr />

        <!-- Table Header -->
        <div class="card bg-light">
            <div class="card-body">
                <div class="row fw-semibold" style="position: sticky; top: 0; z-index: 10;">
                    <div class="col-sm-2">Updated By</div>
                    <div class="col-sm-2">Assigned</div>
                    <div class="col-sm-2">Department</div>
                    <div class="col-sm-3">Information</div>
                    <div class="col-sm-2">Timer Details</div>
                    <div class="col-sm-1">Created At</div>
                </div>
            </div>
        </div>

        <!-- Ticket History Loop -->
        <div class="nav_course_h" <?=$ticket_history_style?>>
            <?php foreach ($ticket_history as $key => $value): 
                $assignToUser_details = getAssignUserDetails($value['assign_to']);
                $assignByUser_details = getAssignUserDetails($value['assign_by']);
            ?>
                <div class="card bg-light-subtle mb-2">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-2">
                                <div><?=$value['updateByUserName']?></div>
                                <div class="text-muted small"><?=$value['updateByUserDesignation']?></div>
                            </div>
                            <div class="col-sm-2">
                                <div><strong> By :</strong><?=$assignByUser_details['name']?></div>
                                <div><strong> To :</strong><?=$assignToUser_details['name']?></div>
                            </div>
                            <div class="col-sm-2">
                                <div><?=$value['department_name']?></div>
                            </div>
                            <div class="col-sm-3">
                                <div><strong>Category:</strong> <?=$value['categoryName']?></div>
                                <div><strong>Status:</strong> <?=$value['statusName']?></div>
                                <div><strong>Priority:</strong> <?=$value['priority']?></div>
                            </div>
                            <div class="col-sm-2">
                                <div><strong>DeadLine :</strong> <?=$value['deadline_date']?></div>
                                <div><strong>Timer Stop :</strong> <?=$value['timer_stop']?></div>
                            </div>
                            <div class="col-sm-1">
                                <div><?=$value['update']?></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
