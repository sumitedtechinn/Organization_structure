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
    $orderby = "ORDER BY admission_details.$columnName $columnSortOrder";
} else {
    $orderby = "ORDER BY admission_details.id ASC";
}

$searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']); // Search value

$searchQuery = "";
if (!empty($searchValue)) {
    $searchQuery = "AND (Closure_details.center_name LIKE '%$searchValue%' OR users.Name LIKE '%$searchValue%')"; 
}

$projectionSearchQuery = '';

if(isset($_POST['organization_id']) && !empty($_POST['organization_id']) && $_POST['organization_id'] != 'None') {
    $projectionSearchQuery .= " AND organization_id = '".$_POST['organization_id']."'";
}

if(isset($_SESSION['role']) && $_SESSION['role'] == '3') {
    $projectionSearchQuery .= " AND organization_id = '".$_SESSION['Organization_id']."'";
}

if(isset($_POST['branch_id']) && !empty($_POST['branch_id']) && $_POST['branch_id'] != 'None') {
    $projectionSearchQuery .= " AND branch_id = '".$_POST['branch_id']."'";
}

if(isset($_POST['vertical_id']) && !empty($_POST['vertical_id']) && $_POST['vertical_id'] != 'None') {
    $projectionSearchQuery .= " AND vertical_id = '".$_POST['vertical_id']."'";
}

if(isset($_POST['department_id']) && !empty($_POST['department_id']) && $_POST['department_id'] != 'None') {
    $projectionSearchQuery .= " AND department_id = '".$_POST['department_id']."'";
}

if(isset($_POST['projectionType']) && !empty($_POST['projectionType']) && $_POST['projectionType'] != 'None') {
    $projectionSearchQuery .= " AND projectionType = '".$_POST['projectionType']."'";
}

if(isset($_POST['selected_projectionType']) && !empty($_POST['selected_projectionType'])) {
    $type = mysqli_real_escape_string($conn,$_POST['selected_projectionType']);
    $projection_type = $conn->query("SELECT GROUP_CONCAT(ID) as `ids` FROM `Projection_type` WHERE Name LIKE '%".$type."%' OR Name LIKE '%".ucfirst($type)."%'");
    $projection_type = mysqli_fetch_column($projection_type);
    $projectionSearchQuery .= " AND projectionType IN ($projection_type)";
}

if(isset($_POST['user']) && !empty($_POST['user']) && $_POST['user'] != 'None') {
    $projectionSearchQuery .= "AND user_id = '".$_POST['user']."'";
}

if(isset($_SESSION['role']) && $_SESSION['role'] == '2') {
    $projectionSearchQuery .= " AND user_id IN (".implode(',',$_SESSION['allChildId']).")";
}

if(isset($_POST['month']) && !empty($_POST['month']) && $_POST['month'] != 'None') {
    $projectionSearchQuery .= "AND month = '".$_POST['month']."'";
}

if(isset($_POST['year']) && !empty($_POST['year']) && $_POST['year'] != 'None') {
    $projectionSearchQuery .= "AND year = '".$_POST['year']."'";
}

$projectionDeleteQuery = "Deleted_At IS NULL";

$projections = $conn->query("SELECT ID FROM `Projection` WHERE $projectionDeleteQuery $projectionSearchQuery");
$projection_ids = []; $totalRecordwithFilter = 0; $totalRecords = 0;$data = [];
if ($projections->num_rows > 0) {
    while($projection = mysqli_fetch_assoc($projections)) {
        $projection_ids[] = $projection['ID'];
    }

    ## Total number of records without filtering
    $all_count = $conn->query("SELECT COUNT(admission_details.id) as `allcount` FROM admission_details WHERE admission_details.projection_id IN (".implode(',',$projection_ids).") AND Deleted_At IS NULL");
    $records = mysqli_fetch_assoc($all_count);
    $totalRecords = $records['allcount'];

    ## Total number of record with filtering
    $filter_count = $conn->query("SELECT COUNT(admission_details.id) as `filtered` FROM admission_details LEFT JOIN Closure_details ON Closure_details.id = admission_details.admission_by LEFT JOIN users ON users.ID = admission_details.user_id WHERE admission_details.projection_id IN (".implode(',',$projection_ids).") AND admission_details.Deleted_At IS NULL $searchQuery");
    $records = mysqli_fetch_assoc($filter_count);
    $totalRecordwithFilter = $records['filtered'];

    ## Fetch Record
    $admission_details = $conn->query("SELECT admission_details.* ,Projection_type.Name as `projection_type`, IF(admission_details.admission_by != 'self', Closure_details.center_name , CONCAT('Self','(',users.Name,')')) as `adm_by` FROM `admission_details` LEFT JOIN Closure_details ON Closure_details.id = admission_details.admission_by LEFT JOIN users ON users.ID = admission_details.user_id LEFT JOIN Projection_type ON Projection_type.ID = admission_details.projectionType WHERE admission_details.Deleted_At IS NULL AND admission_details.projection_id IN (".implode(',',$projection_ids).") $searchQuery $orderby LIMIT $row , $rowperpage");

    if ($admission_details->num_rows > 0 ) {
        while( $row = mysqli_fetch_assoc($admission_details)) {
            $createDate = date_format(date_create($row['created_at']),'d-M-Y');
            $deposit_amount = (is_null($row['deposit_amount']) || empty($row['deposit_amount'])) ?  '' : number_format($row['deposit_amount'],2,".",",");
            $received_amount = (is_null($row['amount']) || empty($row['amount'])) ?  '' : number_format($row['amount'],2,".",",");
            $data[] = array(
                'ID' => $row['id'],
                'adm_by' => $row['adm_by'],
                'projection_type' => $row['projection_type'], 
                'projection_id' => $row['projection_id'] , 
                'user_id' => $row['user_id'],
                'adm_number' => $row['numofadmission'],
                'adm_amount' => $received_amount,
                'deposit_amount' => $deposit_amount,
                'adm_date' => $createDate
            );
        }
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