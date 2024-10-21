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
    $orderby = "ORDER BY Designation.$columnName $columnSortOrder";
} else {
    $orderby = "ORDER BY Designation.hierarchy_value ASC";
}

$searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']); // Search value

$searchQuery = "";
if (!empty($searchValue)) {
    $searchQuery = "AND (Designation.designation_name LIKE '%$searchValue%' OR Designation.code LIKE '%$searchValue%')"; 
}

$delete_query = "";
if(isset($_POST['departmentType'])) {
    $delete_query = "Designation.Deleted_At IS NOT NULL";
} else {
    $delete_query = "Designation.Deleted_At IS NULL";
}

$designation_inside = '';
if(isset($_POST['departmentfilter']) && !empty($_POST['departmentfilter'])) {
    $searchQuery .= "AND Designation.department_id = '".$_POST['departmentfilter']."'";
    $designation_inside = 'department';
} elseif(isset($_POST['organizationfilter']) && !empty($_POST['organizationfilter'])) {
    $searchQuery .= "AND Designation.organization_id = '".$_POST['organizationfilter']."' AND Designation.branch_id IS NULL";
    $designation_inside = 'organization';
} elseif(isset($_POST['branchfilter']) && !empty($_POST['branchfilter'])) {
    $searchQuery .= "AND Designation.branch_id = '".$_POST['branchfilter']."'";
    $designation_inside = 'branch';
} else {
    $department = $conn->query("SELECT ID FROM `Designation` LIMIT 1");
    $department = mysqli_fetch_column($department);
    $searchQuery .= "AND Designation.department_id = '".$department."'";
    $designation_inside = 'department';
}

## Total number of records without filtering
$all_count = $conn->query("SELECT COUNT(ID) as `allcount` FROM Designation WHERE $delete_query");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$filter_count = $conn->query("SELECT COUNT(ID) as `filtered` FROM Designation WHERE $delete_query $searchQuery");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

## Fetch Record
$designation = $conn->query("SELECT Designation.* , Department.department_name as `department` , organization.organization_name as `organization` , Branch.Branch_name as `branch` FROM `Designation` LEFT JOIN Department ON Department.id = Designation.department_id LEFT JOIN organization ON organization.id = Designation.organization_id LEFT JOIN Branch ON Branch.ID = Designation.branch_id WHERE $delete_query $searchQuery $orderby LIMIT $row , $rowperpage");

$data = [];
if ($designation->num_rows > 0 ) {
    while( $row = mysqli_fetch_assoc($designation)) {
        $data[] = array(
            'ID' => $row['ID'],
            'designation_name' => $row['designation_name'],
            'code' => $row['code'],
            'hierarchy_value' => $row['hierarchy_value'], 
            'department' => $row['department'], 
            'organization' => $row['organization'],
            'branch' => $row['branch'] ,
            'desigantion_inside' => $designation_inside,
            'color' => $row['color']
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