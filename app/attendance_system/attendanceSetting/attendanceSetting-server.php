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
$searchQuery = (!empty($searchValue)) ? "AND (organization.organization_name LIKE '%$searchValue%')" : "";
$delete_query = (isset($_POST['attendanceSetting'])) ? "attendance_setting.Deleted_At IS NOT NULL"  : "attendance_setting.Deleted_At IS NULL";

## Total number of records without filtering
$all_count = $conn->query("SELECT COUNT(ID) as `allcount` FROM attendance_setting WHERE $delete_query");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering    
$filter_count = $conn->query("SELECT COUNT(attendance_setting.ID) as `filtered` FROM attendance_setting LEFT JOIN organization ON organization.id = attendance_setting.id WHERE $delete_query $searchQuery");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

## Fetch Record
$attendanceSetting = $conn->query("SELECT attendance_setting.* , organization.organization_name FROM attendance_setting LEFT JOIN organization ON organization.id = attendance_setting.id WHERE $delete_query $searchQuery $orderby LIMIT $row , $rowperpage");
if($attendanceSetting->num_rows > 0) {
    while ($row = mysqli_fetch_assoc($attendanceSetting)) {
        $holidayCount = json_decode($row['holiday'],true);
        $data[] = array(
            'ID' => $row['id'] , 
            'organization_name' => $row['organization_name'],
            'in_time' => $row['in_time'],
            'out_time' => $row['out_time'],
            'relaxation_time' => $row['relaxation_time'],
            'week_off' => $row['week_off'],
            "holidayList" => count($holidayCount)
        );
    }
}

## Response
$response = array(
    "draw" => intval($draw),
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    "aaData" => $data
);

echo json_encode($response);

?>