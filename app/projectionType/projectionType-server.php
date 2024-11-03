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
    $orderby = "ORDER BY Projection_type.$columnName $columnSortOrder";
} else {
    $orderby = "ORDER BY Projection_type.ID ASC";
}

$searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']); // Search value

$searchQuery = "";
if (!empty($searchValue)) {
    $searchQuery = "AND Name LIKE '%$searchValue%'"; 
}

if (isset($_POST['selectOrganization']) && !empty($_POST['selectOrganization'])) {
    $searchQuery .= "AND Projection_type.Organization_id ='".$_POST['selectOrganization']."'";
}

if (isset($_POST['selectBranch']) && !empty($_POST['selectBranch'])) {
    $searchQuery .= " AND Projection_type.Branch_id = '".$_POST['selectBranch']."'";
}

if (isset($_POST['selectVertical']) && !empty($_POST['selectVertical'])) {
    $searchQuery .= "AND Projection_type.Vertical_id = '".$_POST['selectVertical']."'";
}

if (isset($_POST['selectDepartment']) && !empty($_POST['selectDepartment'])) {
    $searchQuery .= "AND Projection_type.Department_id ='".$_POST['selectDepartment']."'";
}

if (isset($_SESSION['role']) && $_SESSION['role'] == '2') {
    $searchQuery .= "AND Projection_type.Organization_id = '".$_SESSION['Organization_id']."'";
    $searchQuery .= " AND Projection_type.Branch_id = '".$_SESSION['Branch_id']."'";
    $searchQuery .= "AND Projection_type.Vertical_id = '".$_SESSION['Vertical_id']."'";
    $searchQuery .= "AND Projection_type.Department_id ='".$_SESSION['Department_id']."'";
}

$delete_query = '';
if (isset($_POST['projectionType'])) {
    $delete_query = "Projection_type.Deleted_At IS NOT NULL";
} else {
    $delete_query = "Projection_type.Deleted_At IS NULL";
}

## Total number of records without filtering
$all_count = $conn->query("SELECT COUNT(Projection_type.ID) as `allcount` FROM Projection_type WHERE $delete_query $searchQuery");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$filter_count = $conn->query("SELECT COUNT(Projection_type.ID) as `filtered` FROM Projection_type WHERE $delete_query $searchQuery");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

## Fetch Record
$projection_type = $conn->query("SELECT Projection_type.*, organization.organization_name as `organization` , Branch.Branch_name as `branch` , Vertical.Vertical_name AS `vertical` , Department.department_name as `department` FROM `Projection_type` LEFT JOIN organization ON organization.id = Projection_type.organization_id LEFT JOIN Branch ON Branch.ID = Projection_type.branch_id LEFT JOIN Vertical ON Vertical.ID = Projection_type.vertical_id LEFT JOIN Department ON Department.id = Projection_type.department_id WHERE $delete_query $searchQuery $orderby LIMIT $row , $rowperpage");

$data = [];
if ($projection_type->num_rows > 0 ) {
    $slno = 1 ;
    while( $row = mysqli_fetch_assoc($projection_type)) {
        $data[] = array(
            'ID' => $row['ID'],
            'projection_type' => $row['Name'],
            'slno' => $slno ,
            'organization' => $row['organization'] , 
            "organization_id" => $row['organization_id'] , 
            "branch" => $row['branch'] ,
            "branch_id" => $row['branch_id'] ,
            "vertical" => $row['vertical'] , 
            "vertical_id" => $row['vertical_id'] , 
            "department" => $row['department'] , 
            "department_id" => $row['department_id'],
            "created_at" => $row['Created_At']
        );
        $slno++;
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