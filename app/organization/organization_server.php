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
    $orderby = "ORDER BY start_date ASC";
}

$searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']); // Search value

$searchQuery = "";
if (!empty($searchValue)) {
    $searchQuery = "AND (organization.organization_name LIKE '%$searchValue%')"; 
}

$delete_query = '';

if (isset($_POST['organizationtype'])) {
    $delete_query .= "Deleted_at IS NOT NULL";
} else {
    $delete_query .= "Deleted_at IS NULL";
}

if($_SESSION['role'] == '2' || $_SESSION['role'] == '3') {
    $searchQuery .= "AND id = '".$_SESSION['Organization_id']."'";
}

## Total number of records without filtering
$all_count = $conn->query("SELECT COUNT(ID) as `allcount` FROM organization WHERE $delete_query $searchQuery");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$filter_count = $conn->query("SELECT COUNT(ID) as `filtered` FROM organization WHERE $delete_query $searchQuery");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

## Fetch Record
$organization = $conn->query("SELECT * FROM organization WHERE $delete_query $searchQuery $orderby LIMIT $row , $rowperpage");

$data = [];
if ($organization->num_rows > 0) {
    while($row = mysqli_fetch_assoc($organization)) {
        $start_date = date_format(date_create($row["start_date"]),'d-M-Y');
        $organizationHead_info = checkOrganizationUser($row['id']);
        if(!$organizationHead_info) {
            $organizationHead_info = "Not Assigned Head";
        }
        $data[] = array(
            "ID" => $row["id"], 
            "organization_name" => $row["organization_name"],
            "organization_head" => $organizationHead_info,  
            "Start_date" => $start_date,
            "logo" => $row['logo'],
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

function checkOrganizationUser($id) {

    global $conn;
    $user_info = [];
    $checkDesignation = $conn->query("SELECT id , CONCAT(designation_name,'(',code,')') as `designation`,color FROM `Designation` WHERE organization_id = '$id' AND branch_id IS NULL AND department_id IS NULL");
    if($checkDesignation->num_rows > 0 ) {
        $i = 0;
        while($row = mysqli_fetch_assoc($checkDesignation)) {
            $checkUser = $conn->query("SELECT Name FROM `users` WHERE Department_id IS NULL AND Branch_id IS NULL AND Vertical_id IS NULL AND Designation_id = '".$row['id']."' AND Organization_id = '$id' AND Deleted_At IS NULL");
            $user_name = [];
            if($checkUser->num_rows > 0) {
                $j = 0;
                while($user = mysqli_fetch_assoc($checkUser)) {
                    $user_name[$j] = $user['Name'];
                    $j++;
                }
            }
            $user_info[$i]['user_name'] = $user_name;
            $user_info[$i]['designation'] = $row['designation'];
            $user_info[$i]['color'] = $row['color'];
            $i++; 
        }
        return $user_info;
    } else {
        return false;
    }
}

?>