<?php 

## Database configuration
include '../../includes/db-config.php';
session_start();

## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$orderby = '';

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
    $searchQuery = "WHERE (name LIKE '%$searchValue%')"; 
}

## Total number of records without filtering
$all_count = $conn->query("SELECT COUNT(id) as `allcount` FROM ticket_category $searchQuery");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering    
$filter_count = $conn->query("SELECT COUNT(id) as `filtered` FROM ticket_category  $searchQuery");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

## Fetch Record
$tickets = $conn->query("SELECT * FROM ticket_category  $searchQuery $orderby LIMIT $row , $rowperpage");

$data = [];
if ($tickets->num_rows > 0) {
    $i = 1;
    while($row = mysqli_fetch_assoc($tickets)) {
        $departmentList = $conn->query("SELECT department_name FROM `Department` WHERE id = '".$row['department']."'");
        $departmentList = mysqli_fetch_column($departmentList);
        $data[] = array(
            "ID" => $row["id"],
            "sqNo" => $i,
            "name" => $row['name'],
            "department" => $departmentList,
            "created_at" => $row['created_at'],
        );
        $i++;
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