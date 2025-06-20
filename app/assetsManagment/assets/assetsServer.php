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

$orderby = (isset($columnSortOrder)) ? "ORDER BY assets.$columnName $columnSortOrder" : "ORDER BY assets.id ASC";
$searchValue = mysqli_real_escape_string($conn, $_POST['searchText']);

$searchQuery = !empty($searchValue) ? "AND (assets.brand_name LIKE '%$searchValue%' OR assets.model_number LIKE '%$searchValue%' OR assets.assets_code LIKE '%$searchValue%')" : "";

$searchQuery .= (isset($_POST['assetsCategory']) && !empty($_POST['assetsCategory'])) ? "AND assets.assets_category = '" .$_POST['assetsCategory']. "'" : "";

$searchQuery .= (isset($_POST['assetsStatus']) && !empty($_POST['assetsStatus'])) ? "AND assets.assets_status = '" .$_POST['assetsStatus']. "'" : "";

$searchQuery .= (isset($_POST['assetsUser']) && !empty($_POST['assetsUser'])) ? "AND assets.assets_assign_to = '" .$_POST['assetsUser']. "'" : "";

$delete_query = (isset($_POST['assetsType'])) ? "assets.Deleted_At IS NOT NULL" : "assets.Deleted_At IS NULL";

## Total number of records without filtering
$all_count = $conn->query("SELECT COUNT(ID) as `allcount` FROM assets WHERE $delete_query");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering    
$filter_count = $conn->query("SELECT COUNT(ID) as `filtered` FROM assets WHERE $delete_query $searchQuery");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

## Fetch Record
$assets = $conn->query("SELECT assets.* , assets_category.category_name , assets_status.status_name , IF(assets_assign_to IS NULL,'Not Assign',assets_assign_to) as `user_id` FROM assets LEFT JOIN assets_category ON assets_category.id = assets.assets_category LEFT JOIN assets_status ON assets_status.id = assets.assets_status WHERE $delete_query $searchQuery $orderby LIMIT $row , $rowperpage");
if($assets->num_rows > 0) {
    while ($row = mysqli_fetch_assoc($assets)) {
        if($row['user_id'] != 'Not Assign') {
            // Get User all details
            $user_id = $row['user_id'];
            $userDetails_query = "SELECT users.Name , Department.department_name , Designation.designation_name , users.Photo as `image` FROM `users` LEFT JOIN Department ON Department.id = users.Department_id LEFT JOIN Designation ON users.Designation_id = Designation.ID WHERE users.ID = '$user_id'";
            $userDetails = $conn->query($userDetails_query);
            $userDetails = mysqli_fetch_assoc($userDetails);
            $assets_assign_to = json_encode($userDetails);
        } else {
            $assets_assign_to = 'Not Assign';
        }  
        $data[] = array(
            'ID' => $row['id'],
            'brand_name' => $row['brand_name'],
            'model_number' => $row['model_number'],
            'assets_category_id' => $row['assets_category'],
            'assets_category_name' => $row['category_name'],
            'assets_code' => $row['assets_code'],
            'assets_status_id' => $row['assets_status'], 
            'assets_status_name' => $row['status_name'],
            'assets_assign_to' => $assets_assign_to ,
            'assets_description' => $row['assets_description']  
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