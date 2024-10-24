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
    $orderby = "ORDER BY permission.$columnName $columnSortOrder";
} else {
    $orderby = "ORDER BY permission.ID ASC";
}

$searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']); // Search value

$searchQuery = "";
if(!empty($_POST['apply_page_filter']))  {
    $searchQuery .= " AND permission.page = '".$_POST['apply_page_filter']."'";
}

if (!empty($_POST['permission_type_filter'])) {
    $searchQuery .= " AND permission.permission_type = '".$_POST['permission_type_filter']."'";
}

$delete_query = '';
if(isset($_POST['deleteType'])) {
    $delete_query = 'Deleted_At IS NOT NULL';
} else {
    $delete_query = 'Deleted_At IS NULL';
}
## Total number of records without filtering
$all_count = $conn->query("SELECT COUNT(ID) as `allcount` FROM permission WHERE $delete_query $searchQuery");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$filter_count = $conn->query("SELECT COUNT(ID) as `filtered` FROM permission WHERE $delete_query $searchQuery");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

## Fetch Record
$permission_list = $conn->query("SELECT permission.ID as `ID`, Permission_type.Name as `type`, pages.Name as `page`, permission.Created_at as `created` FROM `permission` LEFT JOIN Permission_type ON Permission_type.ID = permission.permission_type LEFT JOIN pages ON pages.ID = permission.page WHERE $delete_query $searchQuery $orderby LIMIT $row , $rowperpage");

$data = [];
if ($permission_list->num_rows > 0 ) {
    while( $row = mysqli_fetch_assoc($permission_list)) {
        $role_assigned = $conn->query("SELECT roles.name as `role` FROM `role_has_permissions` LEFT JOIN roles ON roles.ID = role_has_permissions.role_id WHERE permission_id = '".$row['ID']."'");
        $role_name = [];
        if($role_assigned->num_rows > 0) {
            while($role = mysqli_fetch_assoc($role_assigned)) {
                $role_name[] = $role['role'];
            }    
        }
        $role_assign = '';
        if (!empty($role_name)) {
            $role_assign = implode(',', $role_name);
        }
        $data[] = array(
            'ID' => $row['ID'],
            'type' => $row['type'],
            'page' => $row['page'],
            'created' => $row['created'],
            'role_assign' => $role_assign
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