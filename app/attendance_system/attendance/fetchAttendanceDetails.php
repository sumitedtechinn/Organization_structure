<?php

## Database configuration
include '../../../includes/db-config.php';
session_start();

$data_field = file_get_contents('php://input'); // by this we get raw data
if (!empty($data_field)) {
    $data_field = json_decode($data_field,true);
    $_REQUEST = array_merge($_REQUEST,$data_field);
}

$finalRes = [];

if (isset($_REQUEST['method']) && $_REQUEST['method'] == 'fetchAttendanceDetails') {
    $id = mysqli_real_escape_string($conn,$_REQUEST['id']);
    $finalRes = fetchAttendanceDetails($id);
}

echo json_encode($finalRes);

function fetchAttendanceDetails($id)  : array {
    global $conn;

    try {
        $fetchAttendanceDetails_query = "SELECT attendance.attendance_date , attendance.in_time , attendance.out_time , attendance.work_duration , attendance.late_by , attendance.early_by ,  attendance.punch_in_record , attendance.description , attendance_status.name as `status` FROM `attendance` LEFT JOIN attendance_status ON attendance_status.id = attendance.status WHERE attendance.id = '$id'";
        $fetchAttendanceDetails = $conn->query($fetchAttendanceDetails_query);
        $fetchAttendanceDetails = mysqli_fetch_assoc($fetchAttendanceDetails);
        return ['status' => 200, 'message' => json_encode($fetchAttendanceDetails)];
    } catch (Exception $e) {
        return sendResponse(false, 'Exception : ' . $e->getMessage() . " on  line : ". $e->getLine());        
    }
} 

function sendResponse($response,$message = "Something Went Wrong") {
    return ($response) ? ['status' => 200 , 'message' => "Setting $message successfully"] : ['status' => 400 , 'message' => $message];
}
?>