<?php 

## Database configuration
include '../../includes/db-config.php';
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

if (isset($columnSortOrder)) {
    $orderby = "ORDER BY $columnName $columnSortOrder";
} else {
    $orderby = "ORDER BY id ASC";
}

$searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']); // Search value

$searchQuery = "";
if (!empty($searchValue)) {
    $searchQuery = "AND leaveName LIKE '%$searchValue%'"; 
}

$delete_query = '';

if (isset($_POST['leaveType'])) {
    $delete_query .= "Deleted_At IS NOT NULL";
} else {
    $delete_query .= "Deleted_At IS NULL";
}

## Total number of records without filtering
$all_count = $conn->query("SELECT COUNT(ID) as `allcount` FROM leaveType WHERE $delete_query");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering    
$filter_count = $conn->query("SELECT COUNT(ID) as `filtered` FROM leaveType WHERE $delete_query $searchQuery");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

## Fetch Record
$leaveTypes = $conn->query("SELECT * FROM leaveType WHERE $delete_query $searchQuery $orderby LIMIT $row , $rowperpage");
if($leaveTypes->num_rows > 0) {
    $a = 1;
    while ($row = mysqli_fetch_assoc($leaveTypes)) {
        $data[] = array(
            'slno' => $a ,
            'ID' => $row['id'],
            'leaveName' => $row['leaveName'],
            'numOfLeave' => $row['numOfLeave'],
            'leaveCarryForward' => $row['leaveCarryForward']
        );
        $a++;
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