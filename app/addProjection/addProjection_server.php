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
    $orderby = "ORDER BY Projection.$columnName $columnSortOrder";
} else {
    $orderby = "ORDER BY Projection.month ASC";
}

$searchQuery = "";
// On user login there down the line hierarchy all user projection will show
if ($_SESSION['role'] == 2) {
    //$vertical = json_decode($_SESSION['Vertical_id'],true);
    $searchQuery .= " AND Projection.user_id IN (".implode(',',$_SESSION['allChildId']).")";
}

if ($_SESSION['role'] == '3') {
    $searchQuery .= "AND Projection.organization_id= '".$_SESSION['Organization_id']."'";
}

if(isset($_POST['organizationFilter']) && !empty($_POST['organizationFilter'])) {
    $searchQuery .= "AND Projection.organization_id= '".$_POST['organizationFilter']."'";
}

if(isset($_POST['branchFilter']) && !empty($_POST['branchFilter'])) {
    $searchQuery .= "AND Projection.branch_id = '".$_POST['branchFilter']."'";
}

if(isset($_POST['verticalFilter']) && !empty($_POST['verticalFilter'])) {
    $searchQuery .= "AND Projection.vertical_id = '".$_POST['verticalFilter']."'";
}

if(isset($_POST['departmentFilter']) && !empty($_POST['departmentFilter'])) {
    $searchQuery .= "AND Projection.department_id = '".$_POST['departmentFilter']."'";
}

if(isset($_POST['selected_year']) && !empty($_POST['selected_year'])) {
    $searchQuery .= "AND Projection.year = '".$_POST['selected_year']."'";
}

if (isset($_POST['selected_month']) && !empty($_POST['selected_month'])) {
    if ($_POST['selected_month'] != 13) {
        $searchQuery .= " AND Projection.month = " . $_POST['selected_month'];
    }
}

if (isset($_POST['selected_user']) && !empty($_POST['selected_user'])) {
    $searchQuery .= " AND Projection.user_id = '".$_POST['selected_user']."'";
}

if (isset($_POST['selected_projection_type']) && !empty($_POST['selected_projection_type'])) {
    $searchQuery .= " AND Projection.projectionType = '".$_POST['selected_projection_type']."'";
}

$delete_query = "";
if (isset($_POST['projectionType'])) {
    $delete_query = "Projection.Deleted_At IS NOT NULL";
} else {
    $delete_query = "Projection.Deleted_At IS NULL";
}

## Total number of records without filtering
$all_count = $conn->query("SELECT COUNT(ID) as `allcount` FROM Projection WHERE $delete_query $searchQuery");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$filter_count = $conn->query("SELECT COUNT(ID) as `filtered` FROM Projection WHERE $delete_query $searchQuery");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

## Fetch Record
$projection = $conn->query("SELECT Projection.* , Projection_type.Name as `projection_type` , IF(Projection_type.Deleted_At IS NULL , 'No' , 'Yes') as `projection_type_delete` , organization.organization_name as `organization` , IF(organization.Deleted_At IS NULL , 'No' , 'Yes') as `organization_delete` , Branch.Branch_name as `branch` , IF( Branch.Deleted_At IS NULL , 'No' , 'Yes') AS `branch_delete` ,  Vertical.Vertical_name as `vertical` ,IF( Vertical.Deleted_At IS NULL ,'No','Yes') as `vertical_delete`, Department.department_name as `department` , IF(Department.Deleted_At IS NULL , 'No' , 'Yes') as `department_delete` , CONCAT(Designation.designation_name,'(',Designation.code,')') as `designation` , IF(Designation.Deleted_At IS NULL , 'No' , 'Yes') as `designation_delete`, users.Name as `user`, users.Photo as `user_image` , IF( users.Deleted_At IS NULL , 'No' , 'Yes') as `user_delete` FROM `Projection` LEFT JOIN Projection_type ON Projection_type.ID = Projection.projectionType LEFT JOIN organization ON organization.id = Projection.organization_id LEFT JOIN Branch ON Branch.ID = Projection.branch_id LEFT JOIN Vertical ON Vertical.ID = Projection.vertical_id LEFT JOIN Department ON Department.id = Projection.department_id LEFT JOIN Designation ON Designation.ID = Projection.designation_id LEFT JOIN users ON users.ID = Projection.user_id WHERE $delete_query $searchQuery $orderby LIMIT $row , $rowperpage");

$data = [];
if ($projection->num_rows > 0 ) {
    while( $row = mysqli_fetch_assoc($projection)) {
        $numOfClosureComplete = $conn->query("SELECT COUNT(ID) FROM Closure_details WHERE Projection_id = '".$row['ID']."' and doc_closed IS NOT NULL AND Deleted_At IS NULL");
        $numOfClosureComplete = intval(mysqli_fetch_column($numOfClosureComplete));
        $numOfClosurePending = $conn->query("SELECT COUNT(ID) FROM Closure_details WHERE Projection_id = '".$row['ID']."' and doc_closed IS NULL AND Deleted_At IS NULL");
        $numOfClosurePending = intval(mysqli_fetch_column($numOfClosurePending));
        $month_arr = ['1' => 'January','2' => 'February' , '3' => 'March' , '4' => 'April' , '5' => 'May' , '6' => 'June' , '7' => 'July' , '8' => 'August' , '9' => 'September' , '10' => 'October' , '11' => 'November' , '12' => 'December'];
        $month = $month_arr[$row['month']];
        $data[] = array(
            'ID' => $row['ID'],
            'projection_type' => $row['projection_type'],
            'projection_type_delete' => $row['projection_type_delete'],
            'organization' => $row['organization'],
            'organization_id' => $row['organization_id'],
            'organization_delete' => $row['organization_delete'],  
            'branch' => $row['branch'],
            'branch_id' => $row['branch_id'],
            'branch_delete' => $row['branch_delete'],
            'vertical_id' => $row['vertical_id'],
            'vertical' => $row['vertical'],
            'vertical_delete' => $row['vertical_delete'],
            'department' => $row['department'],
            'department_id' => $row['department_id'],
            'department_delete' => $row['department_delete'],
            'designation' => $row['designation'],
            'designation_id' => $row['designation_id'],
            'designation_delete' => $row['designation_delete'],
            'user_id' => $row['user_id'],
            'user' => $row['user'],
            'user_image' => $row['user_image'],
            'user_delete' => $row['user_delete'],
            'numOfClosure' => $row['numOfClosure'],
            'numOfClosureComplete' => $numOfClosureComplete,
            'numOfClosurePending' => $numOfClosurePending,
            'month' => $month ,
            'year' => $row['year']
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