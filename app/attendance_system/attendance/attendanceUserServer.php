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

// $orderby = (isset($columnSortOrder)) ? "ORDER BY attendance_setting.$columnName $columnSortOrder" : "ORDER BY attendance_setting.id ASC"; 
// $searchValue = mysqli_real_escape_string($conn, $_POST['searchText']);
// $searchQuery = (!empty($searchValue)) ? "AND (users.Name LIKE '%$searchValue%')" : "";
$filter_query = '';
if (isset($_REQUEST['year'])) {
    $year = mysqli_real_escape_string($conn,$_REQUEST['year']);
    $filter_query = " YEAR(attendance.attendance_date) = '$year' AND attendance.user_id = '{$_SESSION['ID']}'";
}

## Total number of records without filtering
$totalRecords = 12;

## Total number of record with filtering    
$totalRecordwithFilter = 12;

## Fetch Record
$fetchAttendance = $conn->query("SELECT attendance.* , users.Name as `user_name`,  attendance_status.name as `status_name` FROM `attendance` LEFT JOIN users ON users.ID = attendance.user_id LEFT JOIN attendance_status ON attendance_status.id = attendance.status WHERE $filter_query ORDER BY attendance.attendance_date");
if($fetchAttendance->num_rows > 0) {
    $fetchAttendance = mysqli_fetch_all($fetchAttendance,MYSQLI_ASSOC);
    $data = fetchAllAttendanceData($fetchAttendance);
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

function fetchAllAttendanceData($groupAttendanceByMonth) : array {
    $allMonthArr = getAllMonthArr();
    foreach ($groupAttendanceByMonth as $attendance) {
        $month = date('F',strtotime($attendance['attendance_date']));
        $month_date = date('j',strtotime($attendance['attendance_date']));
        if(array_key_exists($month_date,$allMonthArr[$month])) {
            $allMonthArr[$month][$month_date]  = formatAttendanceData($attendance);   
        }
    }
    return $allMonthArr;
}

function getAllMonthArr() : array {
    $allMonthArr = [];
    $year = date('Y');
    for ($i=1; $i <= 12; $i++) { 
        $month = date('F',mktime(0,0,0,$i,1,$year));
        $allMonthArr[$month] = allDayArr($month);
    }
    return $allMonthArr;
}


function allDayArr($month) : array {
    $arr = [];
    $arr['month'] = $month;
    for ($i=1; $i <= 31 ; $i++) { 
        $arr[$i] = null;
    }
    return $arr;
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