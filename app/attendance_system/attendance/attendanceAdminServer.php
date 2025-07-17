<?php 

## Database configuration
include '../../../includes/db-config.php';
session_start();

## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$orderby = '';
$data = [];

if (isset($_POST['order'])) {
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name 
    $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
}

$orderby = (isset($columnSortOrder)) ? "ORDER BY attendance_setting.$columnName $columnSortOrder" : "ORDER BY attendance_setting.id ASC"; 
$searchValue = mysqli_real_escape_string($conn, $_POST['searchText']);
$searchQuery = (!empty($searchValue)) ? "AND (users.Name LIKE '%$searchValue%')" : "";
$filter_query = '';
if (isset($_REQUEST['month']) && isset($_REQUEST['year'])) {
    $month = mysqli_real_escape_string($conn,$_REQUEST['month']);
    $month = 5;
    $year = mysqli_real_escape_string($conn,$_REQUEST['year']);
    $filter_query = "MONTH(attendance_date) = '$month' AND YEAR(attendance_date) = '$year'";
}

## Total number of records without filtering
$all_count = $conn->query("SELECT COUNT(id) FROM `attendance` WHERE $filter_query GROUP BY user_id");
$totalRecords = $all_count->num_rows;

## Total number of record with filtering    
$filter_count = $conn->query("SELECT COUNT(attendance.id) FROM `attendance` LEFT JOIN users ON users.ID = attendance.user_id WHERE $filter_query $searchQuery GROUP BY attendance.user_id");
$totalRecordwithFilter = $filter_count->num_rows;

## Fetch Record
$fetchAttendance = $conn->query("SELECT attendance.* , users.Name as `user_name`, users.Photo as `user_image` ,  attendance_status.name as `status_name` FROM `attendance` LEFT JOIN users ON users.ID = attendance.user_id LEFT JOIN attendance_status ON attendance_status.id = attendance.status WHERE $filter_query $searchQuery ORDER BY attendance.user_id , attendance.attendance_date");
$groupAttendanceByUser = [];
if($fetchAttendance->num_rows > 0) {
    while ($row = mysqli_fetch_assoc($fetchAttendance)) {
        $groupAttendanceByUser[$row['user_id']][] = $row; 
    }
}

if(!empty($groupAttendanceByUser)) {
    $data = fetchAllAttendanceData($groupAttendanceByUser);
}

$filter_data = array_slice(array_values($data),$row,$rowperpage);

## Response
$response = array(
    "draw" => intval($draw),
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    "aaData" => $filter_data
);

echo json_encode($response);

function fetchAllAttendanceData($groupAttendanceByUser) : array {
    $getAllUserData = array_reduce(array_keys($groupAttendanceByUser), function($acc,$user_id) {
        for ($i=1; $i <= 31; $i++) { 
            $acc[$user_id][$i] = null;
        }
        return $acc;
    } , []);

    foreach ($groupAttendanceByUser as $user_id => $value) {
        foreach ($value as $key => $attendance) {
            $month_date = date('j',strtotime($attendance['attendance_date']));
            if(array_key_exists($month_date,$getAllUserData[$user_id])) {
                $getAllUserData[$user_id][$month_date]  = formatAttendanceData($attendance);   
            }
        }
        $getAllUserData[$user_id]['user_name'] = $value[0]['user_name'];
        $getAllUserData[$user_id]['user_image'] = $value[0]['user_image'];
    }

    return $getAllUserData;
}

function formatAttendanceData($attendance_details) : string {
    $attendance_id = $attendance_details['id'];
    $user_id = $attendance_details['user_id'];
    $in_time = formatTime($attendance_details['in_time']);
    $out_time = formatTime($attendance_details['out_time']);
    $status = $attendance_details['status_name'];
    return "attendance_id=>$attendance_id@@@@user_id=>$user_id@@@@in_time=>$in_time@@@@out_time=>$out_time@@@@status=>$status";
}

function formatTime($time) :string {
    return implode(":",array_filter(explode(":",$time) , fn($value,$key) => ($key == 2) ? false : true ,ARRAY_FILTER_USE_BOTH));
}
?>