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
    $orderby = "ORDER BY created_at DESC";
}

$searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']); // Search value


$searchQuery = "";
if (!empty($searchValue)) {
    $searchQuery = "AND (task_name LIKE '%$searchValue%')"; 
}

$filter_query = '';
if ($_SESSION['role'] != '1') {
    $downTheLineUser = getDownTheLineUser(); 
    $user_id = $_SESSION['ID'];
    $department = ($_SESSION['role'] == '2') ? $_SESSION['Department_id'] : getDepartmentData();
    $filter_query .= "AND (ticket_record.raised_by = '$user_id' OR ticket_record.assign_by IN ($downTheLineUser) OR ticket_record.assign_to IN ($downTheLineUser) OR (ticket_record.status = '1' AND ticket_record.department IN ($department)) OR (SELECT COUNT(id) FROM `ticket_history` WHERE ticket_history.ticket_id = ticket_record.id AND (assign_by IN ($downTheLineUser) OR assign_to IN ($downTheLineUser))) > 0)";
}


## Total number of records without filtering
$all_count = $conn->query("SELECT COUNT(id) as `allcount` FROM ticket_record WHERE ticket_record.task_name IS NOT NULL $filter_query");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering    
$filter_count = $conn->query("SELECT COUNT(id) as `filtered` FROM ticket_record WHERE ticket_record.task_name IS NOT NULL $filter_query $searchQuery");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

## Fetch Record
$tickets = $conn->query("SELECT * , DATE_FORMAT(created_at,'%d-%b-%Y') as `create_date` FROM ticket_record WHERE ticket_record.task_name IS NOT NULL $filter_query $searchQuery $orderby LIMIT $row , $rowperpage");
$data = [];
if ($tickets->num_rows > 0) {
    $i = 1;
    while($row = mysqli_fetch_assoc($tickets)) {
        $statusInfo = $conn->query("SELECT name , color FROM `ticket_status` WHERE id = '". $row['status'] ."'");
        $statusInfo = mysqli_fetch_assoc($statusInfo);
        $data[] = array(
            "ID" => $row["id"],
            "sqNo" => $i,
            "task_name" => $row['task_name'],
            "unique_id" => $row['unique_id'],
            "statusName" => $statusInfo['name'],
            "statusColor" => $statusInfo['color'],
            "create_date" => $row['create_date'],
        );
        $i++;
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

function getDownTheLineUser() : string {

    global $conn;
    $downTheLineUser = '';
    if( $_SESSION['role'] == '2') {
        if (isset($_SESSION['allChildId'])) {
            $downTheLineUserList = $_SESSION['allChildId'];
            if (!empty($downTheLineUserList)) {
                $downTheLineUser = implode(',',$downTheLineUserList);
            }
        }
    } elseif ($_SESSION['role'] == '3') {
        $searchQuery = '';
        if ($_SESSION['added_inside'] == '3') {
            $searchQuery .= ($_SESSION['Vertical_id'] == '1' || $_SESSION['Vertical_id'] == '2' || $_SESSION['Vertical_id'] == '3') ? " AND users.Vertical_id IN ('1','2','3')" : " AND users.Vertical_id = '".$_SESSION['Vertical_id']."'";
        }
        if ($_SESSION['added_inside'] == '3' || $_SESSION['added_inside'] == '2') {
            $searchQuery .= "AND users.Branch_id = '".$_SESSION['Branch_id']."'";
        }
        $searchQuery .= "AND users.Organization_id = '".$_SESSION['Organization_id']."'";
        $searchQuery .= "AND Designation.added_inside > '".$_SESSION['added_inside']."'";
        $delete_query = "users.Deleted_At IS NULL";
        $downTheLineUserList = $conn->query("SELECT GROUP_CONCAT(users.ID) AS 'user_ids' FROM `users` LEFT JOIN Designation ON Designation.ID = users.Designation_id WHERE $delete_query $searchQuery");
        $downTheLineUser = mysqli_fetch_column($downTheLineUserList);
        $currentUser = $_SESSION['ID'];
        $downTheLineUser = $currentUser.",". $downTheLineUser;
    }
    return $downTheLineUser;
}

function getDepartmentData() {

    global $conn;
    $user_id = $_SESSION['ID'];
    $searchQuery = '';
    $user_details = $conn->query("SELECT Designation.added_inside as `inside` FROM `users` LEFT JOIN Designation ON Designation.ID = users.Designation_id WHERE role = '3' AND users.ID = '$user_id'");
    $user_details = mysqli_fetch_assoc($user_details); 
    if ($user_details['inside'] == '3') {
        if ($_SESSION['Vertical_id'] == '1' || $_SESSION['Vertical_id'] == '2' || $_SESSION['Vertical_id'] == '3') {
            $searchQuery .= "Vertical_id IN ('1','2','3')";  
        } else {
            $searchQuery .= "Vertical_id = '".$_SESSION['Vertical_id']."'";
        }    
    } elseif ($user_details['inside'] == '2') {
        $searchQuery .= "JSON_SEARCH(branch_id,'all','". $_SESSION['Branch_id'] ."') IS NOT NULL";
    } else {
        $searchQuery .= "organization_id = '" .$_SESSION['Organization_id']. "'";
    }
    $department = $conn->query("SELECT GROUP_CONCAT(id) FROM `Department` WHERE $searchQuery");
    $department = mysqli_fetch_column($department);
    return $department;
}

?>