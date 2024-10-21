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
    $orderby = "ORDER BY ID ASC";
}

$searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']); // Search value

$searchQuery = "";
if (!empty($searchValue)) {
    $searchQuery = "AND (name LIKE '%$searchValue%' OR guard_name LIKE '%$searchValue%')"; 
}

$delete_query = '';

if (isset($_POST['roleType']) && $_POST['roleType'] == 'deleteRole' ) {
    $delete_query .= "Deleted_At IS NOT NULL";
} else {
    $delete_query .= "Deleted_At IS NULL";
}

## Total number of records without filtering
$all_count = $conn->query("SELECT COUNT(ID) as `allcount` FROM roles WHERE $delete_query");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$filter_count = $conn->query("SELECT COUNT(ID) as `filtered` FROM roles WHERE $delete_query $searchQuery");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

## Fetch Record
$roles = $conn->query("SELECT * FROM roles WHERE $delete_query $searchQuery $orderby LIMIT $row , $rowperpage");

$data = [];
if ($roles->num_rows > 0) {
    while($row = mysqli_fetch_assoc($roles)) {
        $data[] = array(
            "ID" => $row["ID"], 
            "name" => $row["name"],
            "guard_name" => $row["guard_name"], 
            "created_at" => $row["created_at"], 
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