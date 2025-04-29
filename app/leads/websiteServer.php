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

$orderby = (isset($columnSortOrder)) ? "ORDER BY $columnName $columnSortOrder" : "ORDER BY id ASC";
$searchValue = $_REQUEST['searchtext'] ?? "";

$searchQuery = "";
if (!empty($searchValue)) {
    $searchQuery = "WHERE (websiteName LIKE '%$searchValue%' OR websiteUrl LIKE '%$searchValue%')"; 
}

## Total number of records without filtering
$all_count = $conn->query("SELECT COUNT(ID) as `allcount` FROM websiteList");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering    
$filter_count = $conn->query("SELECT COUNT(ID) as `filtered` FROM websiteList $searchQuery");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

## Fetch Record
$websiteLists = $conn->query("SELECT * FROM websiteList $searchQuery $orderby LIMIT $row , $rowperpage");

$data = [];
$i = 1;
if ($websiteLists->num_rows > 0) {
    while($row = mysqli_fetch_assoc($websiteLists)) {
        $data[] = array(
            "ID" => $row["id"], 
            "websiteName" => $row["websiteName"],
            "websiteUrl" => $row["websiteUrl"],
            "rowCount" => $i
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