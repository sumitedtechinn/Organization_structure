<?php
## Database configuration
include '../../includes/db-config.php';
session_start();

$data_field = file_get_contents('php://input'); // by this we get raw data
$data_field = json_decode($data_field,true);
$result_list = [];
foreach ($data_field as $value) {
    $function_name = "get". ucfirst($value);
    $result_list[$value] = call_user_func($function_name);
}

echo json_encode($result_list);

function getDepartment() {
    global $conn;
    $option = '<option value="">Select Department</option>';
    $department = '';
    if( $_SESSION['role'] == '1' || ($_SESSION['role'] == '2' && $_SESSION['Department_id'] == '8')) { 
        $department = $conn->query("SELECT id,department_name FROM `Department` WHERE Deleted_At IS NULL");
    } elseif ($_SESSION['role'] == '3') {
        $searchQuery = '';
        if ($_SESSION['added_inside'] == '3') {
            $searchQuery .= ($_SESSION['Vertical_id'] == '1' || $_SESSION['Vertical_id'] == '2' || $_SESSION['Vertical_id'] == '3') ? " AND Department.vertical_id IN ('1','2','3')" : " AND Department.vertical_id = '".$_SESSION['Vertical_id']."'";
        }
        if ($_SESSION['added_inside'] == '3' || $_SESSION['added_inside'] == '2') {
            $searchQuery .= "AND Department.branch_id LIKE '%".$_SESSION['Branch_id']."%'";
        }
        $searchQuery .= "AND Department.organization_id = '".$_SESSION['Organization_id']."'";
        $department = $conn->query("SELECT id,department_name FROM `Department` WHERE Deleted_At IS NULL $searchQuery");
    }

    if( !empty($department) && $department->num_rows > 0) {
        while($row = mysqli_fetch_assoc($department)) {
            $option .= '<option value = "'.$row['id'].'" >'.$row['department_name'].'</option>';
        }
    }
    return $option;
}

function getUser() {
    global $conn;
    $option = '<option value="">Select User</option>';
    $downTheLineUser = getDownTheLineUser();
    $filterQuery = '';
    if ($downTheLineUser == 'No User Down the line') {
        return $option;
    } elseif (!empty($downTheLineUser)) {
        $filterQuery .= "AND ID IN ($downTheLineUser)";
    }
    $delete_query = "AND Deleted_At IS NULL";
    $users = $conn->query("SELECT ID , Name FROM `users` WHERE users.role != '1' $delete_query $filterQuery");
    while($row = mysqli_fetch_assoc($users)) {
        $option .= '<option value = "'.$row['ID'].'" >'.$row['Name'].'</option>';
    }
    return $option;
}

function getDownTheLineUser() : string {

    global $conn;
    $downTheLineUser = '';
    if( $_SESSION['role'] == '2') {
        if ( $_SESSION['Department_id'] != '8' && isset($_SESSION['allChildId'])) {
            $downTheLineUserList = $_SESSION['allChildId'];
            unset($downTheLineUserList[0]);
            if (!empty($downTheLineUserList)) {
                $downTheLineUser = implode(',',$downTheLineUserList);
            } else {
                $downTheLineUser = "No User Down the line";
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
    }
    return $downTheLineUser;
}

?>