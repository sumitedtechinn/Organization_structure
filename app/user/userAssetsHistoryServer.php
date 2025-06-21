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

$orderby = (isset($columnSortOrder)) ? "ORDER BY assets_history.$columnName $columnSortOrder" : "ORDER BY assets_history.id ASC";
$searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']);

$searchQuery = !empty($searchValue) ? "AND (assets.brand_name LIKE '%$searchValue%' OR assets.model_number LIKE '%$searchValue%' OR assets.assets_code LIKE '%$searchValue%' OR assets_category.category_name LIKE '%$searchValue%')" : "";

$filterQuery = (isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id'])) ? "assets_history.user_id = '" . $_REQUEST['user_id'] . "'" : "" ; 

## Total number of records without filtering
$all_count = $conn->query("SELECT COUNT(assets_history.id) as `allcount` FROM assets_history WHERE $filterQuery");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering    
$filter_count = $conn->query("SELECT COUNT(assets_history.id) as `filtered` FROM assets_history LEFT JOIN assets ON assets.id = assets_history.asset_id LEFT JOIN assets_category ON assets_category.id = assets.assets_category WHERE $filterQuery $searchQuery");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

## Fetch Record
$assets_history = $conn->query("SELECT assets.brand_name , assets.model_number , assets.assets_code , assets_category.category_name , DATE_FORMAT(assets_history.assigned_on,'%d-%b-%Y') as `assigned_on`  , DATE_FORMAT( assets_history.return_on,'%d-%b-%Y') as `return_on` FROM assets_history LEFT JOIN assets ON assets.id = assets_history.asset_id LEFT JOIN assets_category ON assets_category.id = assets.assets_category WHERE $filterQuery $searchQuery $orderby LIMIT $row , $rowperpage");
if($assets_history->num_rows > 0) {
    $a= 1;
    while ($row = mysqli_fetch_assoc($assets_history)) {  
        $data[] = array(
            'slno' => $a,
            'brand_name' => $row['brand_name'],
            'model_number' => $row['model_number'],
            'assets_code' => $row['assets_code'],
            'category_name' => $row['category_name'],
            'assigned_on' => $row['assigned_on'],
            'return_on' => $row['return_on']
        );
        ++$a;
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