<?php
## Database configuration
include '../../includes/db-config.php';
session_start();

$leaveTypeData = $conn->query("SELECT id,leaveName FROM leaveType WHERE Deleted_at IS NULL");
if($leaveTypeData->num_rows > 0) {
    $leaveTypeData = mysqli_fetch_all($leaveTypeData,MYSQLI_ASSOC);
    $leaveType = array_column($leaveTypeData,'leaveName','id');
    if(removeRestrictedLeave()) {
        unset($leaveType['4']);
    }
    if(removeEarnedLeave()) {
        unset($leaveType['7']);
    }
    echo makeoptionTag($leaveType);
}

function makeoptionTag($leaveType) : string {
    global $conn;
    $option = '<option value="">Select Leave</option>';
    $selected_id = '';
    if(isset($_REQUEST['leavetype_id']) && !empty($_REQUEST['leavetype_id'])) {
        $selected_id = mysqli_real_escape_string($conn,$_REQUEST['leavetype_id']);
    }
    foreach ($leaveType as $id => $name) { 
        $option .= (!empty($selected_id) && $selected_id == $id) ? '<option value = "'.$id.'" selected >'.$name.'</option>' : '<option value = "'.$id.'">'.$name.'</option>';
    }
    return $option;
}

function removeRestrictedLeave() : bool {

    global $conn;
    $query = "SELECT SUM(CASE WHEN leave_type = '4' AND YEAR(start_date) = YEAR(CURRENT_DATE()) AND status = '1' THEN DATEDIFF(end_date,start_date)+1 ELSE 0 END) as `restricted_day_used` FROM leave_record WHERE user_id = '".$_SESSION['ID']."'";
    $restrictedLeave = $conn->query($query);
    $restrictedLeave = mysqli_fetch_column($restrictedLeave);
    return ($restrictedLeave >= 2 ) ? true : false;     
}

function removeEarnedLeave() : bool {

    global $conn;
    $query = "SELECT SUM(CASE WHEN leave_type = '7' AND YEAR(start_date) = YEAR(CURRENT_DATE()) AND status = '1' THEN DATEDIFF(end_date,start_date)+1 ELSE 0 END) as `restricted_day_used` FROM leave_record WHERE user_id = '".$_SESSION['ID']."'";
    $restrictedLeave = $conn->query($query);
    $restrictedLeave = mysqli_fetch_column($restrictedLeave);
    return ($restrictedLeave >= 6 ) ? true : false;     
}

?>