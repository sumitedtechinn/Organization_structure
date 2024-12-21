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
    $orderby = "ORDER BY Closure_details.$columnName $columnSortOrder";
} else {
    $orderby = "ORDER BY Closure_details.ID ASC";
}

$searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']); // Search value

$searchQuery = "";
if (!empty($searchValue)) {
    $searchQuery = "AND (Closure_details.center_name LIKE '%$searchValue%' OR Closure_details.center_email LIKE '%$searchValue%')"; 
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
    if ($_POST['month'] != 13) {
        $projectionSearchQuery .= " AND month = '".$_POST['month']."'";
    }
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
    $all_count = $conn->query("SELECT COUNT(id) as `allcount` FROM Closure_details WHERE Closure_details.Projection_id IN (".implode(',',$projection_ids).") AND Deleted_At IS NULL");
    $records = mysqli_fetch_assoc($all_count);
    $totalRecords = $records['allcount'];

    ## Total number of record with filtering
    $filter_count = $conn->query("SELECT COUNT(id) as `filtered` FROM Closure_details WHERE Closure_details.Projection_id IN (".implode(',',$projection_ids).") AND Deleted_At IS NULL $searchQuery");
    $records = mysqli_fetch_assoc($filter_count);
    $totalRecordwithFilter = $records['filtered'];

    ## Fetch Record
    $closure_details = $conn->query("SELECT Closure_details.* , Projection_type.Name as `projection_type` FROM Closure_details LEFT JOIN Projection_type ON Projection_type.ID = Closure_details.projectionType WHERE Closure_details.Projection_id IN (".implode(',',$projection_ids).") AND Closure_details.Deleted_At IS NULL $searchQuery $orderby LIMIT $row , $rowperpage");

    if ($closure_details->num_rows > 0 ) {
        while( $row = mysqli_fetch_assoc($closure_details)) {
            $status = ""; $last_updated_date = '';
            $doc_received_status = is_null($row['doc_received']) ?  false : true;
            if($doc_received_status) {
                $doc_colse_status = is_null($row['doc_closed']) ? false : true;
                if($doc_colse_status) {
                    $status = "Deal Closed";
                    $last_updated_date = $row['doc_closed'];
                } else {
                    $status = "Doc Received";
                    $last_updated_date = $row['doc_received'];    
                }
            } else {
                $status = "Doc Prepare";
                $last_updated_date = $row['doc_prepare'];
            }
            $last_updated_date = date_format(date_create($last_updated_date),'d-M-Y');
            $authorization_amount = empty($row['amount']) ? 'None' : number_format($row['amount'],2,'.',',');
            $data[] = array(
                'ID' => $row['id'],
                'center_name' => $row['center_name'],
                'center_email' => $row['center_email'],
                'contact' => $row['contact'],
                'country_code' => $row['country_code'],
                'projection_type' => $row['projection_type'], 
                'projection_id' => $row['projection_id'] , 
                'user_id' => $row['user_id'],
                'authorization_amount' => $authorization_amount,
                'doc_status' => $status , 
                'last_update_date' => $last_updated_date
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