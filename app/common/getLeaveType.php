<?php
## Database configuration
include '../../includes/db-config.php';
session_start();

$leaveTypeData = $conn->query("SELECT id,leaveName FROM leaveType WHERE Deleted_at IS NULL");
if($leaveTypeData->num_rows > 0) {
    $leaveTypeData = mysqli_fetch_all($leaveTypeData,MYSQLI_ASSOC);
    $leaveType = array_column($leaveTypeData,'leaveName','id');
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

?>