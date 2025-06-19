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

function getOrganization() {
    global $conn;
    $searchQuery = '';
    if($_SESSION['role'] == '2' || $_SESSION['role'] == '3') {
        $searchQuery .= "AND id = '".$_SESSION['Organization_id']."'";
    }
    $option = '<option value="">Choose Organization</option>'; 
    $organization = $conn->query("SELECT id , organization_name FROM `organization` WHERE Deleted_At IS NULL $searchQuery");
    while($row = mysqli_fetch_assoc($organization)) {
        if($_SESSION['role'] == '3' && $_SESSION['Organization_id'] == $row['id']) {
            $option .= '<option value = "'.$row['id'].'" selected>'.$row['organization_name'].'</option>';
        } else {
            $option .= '<option value = "'.$row['id'].'" >'.$row['organization_name'].'</option>';    
        }
    }
    return $option;
}

function getBranch() {
    global $conn;
    $searchQuery = '';
    if($_SESSION['role'] == '2' || $_SESSION['role'] == '3') {
        $searchQuery .= "AND Branch.organization_id = '".$_SESSION['Organization_id']."'";
    }
    $option = '<option value="">Choose Branch</option>'; 
    $branch = $conn->query("SELECT ID , Branch_name FROM `Branch` WHERE Deleted_At IS NULL $searchQuery");
    while($row = mysqli_fetch_assoc($branch)) {
        $option .= '<option value = "'.$row['ID'].'" >'.$row['Branch_name'].'</option>';
    }
    return $option;
}

function getVertical() {
    global $conn;
    $searchQuery = '';
    if($_SESSION['role'] == '2' || $_SESSION['role'] == '3') {
        $searchQuery .= "AND Vertical.organization_id = '".$_SESSION['Organization_id']."'";
    }
    $option = '<option value="">Choose Vertical</option>'; 
    $vertical = $conn->query("SELECT ID , Vertical_name FROM `Vertical` WHERE Deleted_At IS NULL $searchQuery");
    while($row = mysqli_fetch_assoc($vertical)) {
        $option .= '<option value = "'.$row['ID'].'" >'.$row['Vertical_name'].'</option>';
    }
    return $option;
}

function getDepartment() {
    global $conn;
    $searchQuery = '';
    if($_SESSION['role'] == '2' || $_SESSION['role'] == '3') {
        $searchQuery .= "AND Department.organization_id = '".$_SESSION['Organization_id']."'";
    }
    $option = '<option value="">Choose Department</option>'; 
    $department = $conn->query("SELECT id,department_name FROM `Department` WHERE Deleted_At IS NULL $searchQuery");
    while($row = mysqli_fetch_assoc($department)) {
        $option .= '<option value = "'.$row['id'].'" >'.$row['department_name'].'</option>';
    }
    return $option;
}

function getDesignation() {
    global $conn;
    $searchQuery = '';
    if($_SESSION['role'] == '2' || $_SESSION['role'] == '3') {
        $searchQuery .= "AND Designation.organization_id = '".$_SESSION['Organization_id']."'";
    }
    $option = '<option value="">Choose Designation</option>'; 
    $designation = $conn->query("SELECT ID,designation_name FROM `Designation` WHERE Deleted_At IS NULL $searchQuery");
    while($row = mysqli_fetch_assoc($designation)) {
        $option .= '<option value = "'.$row['ID'].'" >'.$row['designation_name'].'</option>';
    }
    return $option;
}

/**
 * On Super Admin Login 
 * -- Get all the user list for those projection are generated
 * On Admin login
 * -- Get all the user list for those projection are genrated and belong to there organization
 * On User login
 * -- Get user list for those projection are genrated and came under him.
 */

function getProjectionUser() {
    global $conn;
    $searchQuery = '';
    if($_SESSION['role'] == '2' || $_SESSION['role'] == '3') {
        $searchQuery .= "AND users.Organization_id = '".$_SESSION['Organization_id']."'";
    }
    if(isset($_SESSION['allChildId'])) {
        $searchQuery .= "AND users.ID IN (".implode(',',$_SESSION['allChildId']).")";
    }
    $option = '<option value="">Select User</option>';
    $delete_query = "users.Deleted_At IS NULL";
    $users = $conn->query("SELECT DISTINCT users.Name as `name`,users.ID as `id` FROM users JOIN Projection ON users.ID = Projection.user_id WHERE $delete_query $searchQuery");
    while ($row = mysqli_fetch_assoc($users)) {
        $option .= '<option value = "'.$row['id'].'">'.$row['name'].'</option>';
    }
    return $option;
}

/**
 * On Super Admin Login 
 * -- Get all the projection type list for those projection are generated
 * On Admin login
 * -- Get all the projection type list for those projection are genrated and belong to there organization
 * On User login
 * -- Get projection type list for those projection are genrated and belong to there department , vertical and organization.
 */
function getProjectionType() {
    global $conn;
    $searchQuery = '';
    if($_SESSION['role'] == '3') {
        $searchQuery .= "AND Projection_type.organization_id = '".$_SESSION['Organization_id']."'";
    }
    if($_SESSION['role'] == '2') {
        $searchQuery .= "AND Projection_type.organization_id = '".$_SESSION['Organization_id']."' AND Projection_type.branch_id = '".$_SESSION['Branch_id']."' AND Projection_type.vertical_id = '".$_SESSION['Vertical_id']."' AND Projection_type.department_id = '".$_SESSION['Department_id']."'";
    }
    $delete_query = "Projection_type.Deleted_At IS NULL";
    $option = '<option value="">Select Projection Type</option>';
    $projection_type = $conn->query("SELECT DISTINCT Projection_type.Name as `name`, Department.department_name as `department`, Projection_type.ID as `id` FROM Projection_type JOIN Projection ON Projection_type.ID = Projection.projectionType LEFT JOIN Department ON Department.id = Projection_type.department_id WHERE $delete_query $searchQuery");
    while ($row = mysqli_fetch_assoc($projection_type)) {
        $option .= '<option value = "'.$row['id'].'">'.$row['name']."(".$row['department'].")".'</option>';
    }
    return $option;
}
?>