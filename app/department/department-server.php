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
    $orderby = "ORDER BY Department.$columnName $columnSortOrder";
} else {
    $orderby = "ORDER BY Department.id ASC";
}

$searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']); // Search value

$searchQuery = "";
if (!empty($searchValue)) {
    $searchQuery = " AND (Department.department_name LIKE '%$searchValue%')"; 
}

if($_SESSION['role'] == '2' || $_SESSION['role'] == '3') {
    $searchQuery .= "AND Department.organization_id = '".$_SESSION['Organization_id']."'";
}

if (!empty($_POST['organizationFilter'])) {
    $searchQuery .= " AND Department.organization_id = '".$_POST['organizationFilter']."'";
} 

if (!empty($_POST['branchFilter'])) {
    $searchQuery .= " AND Department.branch_id like '%".$_POST['branchFilter']."%'";
}

if (!empty($_POST['verticalFilter'])) {
    $searchQuery .= " AND Department.vertical_id = '".$_POST['verticalFilter']."'";
}

$delete_query = "";
if(isset($_POST['departmentType'])) {
    $delete_query = "Department.Deleted_At IS NOT NULL";
} else {
    $delete_query = "Department.Deleted_At IS NULL";
}


## Total number of records without filtering
$all_count = $conn->query("SELECT COUNT(Department.id) as `allcount` FROM Department WHERE $delete_query");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$filter_count = $conn->query("SELECT COUNT(Department.id) as `filtered` FROM Department WHERE $delete_query $searchQuery");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

## Fetch Record
$department = $conn->query("SELECT Department.id as `ID`,Department.department_name as `department` , organization.organization_name as `organization` , Department.branch_id as `branch` , Vertical.Vertical_name as `vertical` , Department.logo as `logo` FROM Department LEFT JOIN organization ON organization.id = Department.organization_id LEFT JOIN Vertical ON Vertical.ID = Department.vertical_id WHERE $delete_query $searchQuery $orderby LIMIT $row , $rowperpage");

$data = [];
if ($department->num_rows > 0) {
    while($row = mysqli_fetch_assoc($department)) {
        $branch_ids = json_decode($row['branch'],true);
        $branchs = $conn->query("SELECT Branch_name FROM `Branch` WHERE ID IN (".implode(',',$branch_ids).")");
        $branchs = mysqli_fetch_all($branchs,MYSQLI_ASSOC);
        $branchs_name = '';
        foreach ($branchs as $value) {
            $branchs_name .= '<div>'.$value['Branch_name'].'</div>';
        }
        $data[] = array(
            "ID" => $row["ID"],
            "department" => $row['department'], 
            "organization" => $row['organization'],  
            "branch" => $branchs_name, 
            "vertical" => $row['vertical'], 
            "logo" => $row['logo']
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