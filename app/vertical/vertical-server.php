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
    $orderby = "ORDER BY Vertical.ID ASC";
}

$searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']); // Search value

$searchQuery = "";
if (!empty($searchValue)) {
    $searchQuery = "AND (Vertical.Vertical_name LIKE '%$searchValue%')"; 
}

if($_SESSION['role'] == '2' || $_SESSION['role'] == '3') {
    $searchQuery .= "AND Vertical.organization_id = '".$_SESSION['Organization_id']."'";
}

if(isset($_POST['organizationfilter']) && !empty($_POST['organizationfilter'])) {
    $searchQuery .= "AND Vertical.organization_id = '".$_POST['organizationfilter']."'";
} 

if(isset($_POST['branchfilter']) && !empty($_POST['branchfilter'])) {
    $searchQuery .= "AND Vertical.Branch_id LIKE '%".$_POST['branchfilter']."%'";
} 

$delete_query = "";
if(isset($_POST['verticalType'])) {
    $delete_query = "Vertical.Deleted_At IS NOT NULL";
} else {
    $delete_query = "Vertical.Deleted_At IS NULL";
}

## Total number of records without filtering
$all_count = $conn->query("SELECT COUNT(ID) as `allcount` FROM Vertical WHERE $delete_query");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$filter_count = $conn->query("SELECT COUNT(ID) as `filtered` FROM Vertical WHERE $delete_query $searchQuery");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

## Fetch Record
$verticals = $conn->query("SELECT Vertical.ID as `ID`, Vertical.Vertical_name as `name` ,Vertical.Branch_id as `branch` , Vertical.image as `image`, organization.organization_name as `organization` FROM `Vertical` LEFT JOIN organization ON organization.id = Vertical.organization_id WHERE $delete_query $searchQuery $orderby LIMIT $row , $rowperpage");

$data = [];
if ($verticals->num_rows > 0 ) {
    while( $row = mysqli_fetch_assoc($verticals)) {
        $row['branch'] = json_decode($row['branch'],true);
        $branchs = $conn->query("SELECT Branch_name , IF(Deleted_At IS NULL,'No','Yes') as `branch_delete` FROM `Branch` WHERE ID IN (" .implode(',',$row['branch']). ")");
        $branchs = mysqli_fetch_all($branchs,MYSQLI_ASSOC);
        $branchs_name = '';
        foreach ($branchs as $value) {
            if ($value['branch_delete'] == 'Yes') {
                $branchs_name .= '<div class = "text-danger">'.$value['Branch_name'].'</div>';
            } else {
                $branchs_name .= '<div>'.$value['Branch_name'].'</div>';
            }
        }
        $data[] = array(
            'ID' => $row['ID'],
            'Vertical_name' => $row['name'],
            'organization' => $row['organization'],
            'Branch_name' => $branchs_name, 
            'image' => $row['image']
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