<?php
## Database configuration
include '../../includes/db-config.php';
session_start();

$data_field = file_get_contents('php://input'); // by this we get raw data
$data_field = json_decode($data_field,true);
$result_list = [];
foreach ($data_field as $value) {
    $function_name = "get". ucfirst($value['name']);
    $select_value = $value['value'];
    $result_list[$value['name']] = call_user_func($function_name,$select_value);
}

echo json_encode($result_list);

function getStatus($selected_status) {

    global $conn;
    $option = "<option value=''>Choose Status</option>";
    list($department,$status) = explode("_",$selected_status);
    $searchQuery = "WHERE JSON_SEARCH(department,'all','$department') IS NOT NULL";
    $allStatus = $conn->query("SELECT id , name FROM `ticket_status` $searchQuery");
    $allStatus = mysqli_fetch_all($allStatus,MYSQLI_ASSOC);
    $allStatus = array_column($allStatus,'name','id');
    if(empty($status)) {
        $status = '1';
    }
    $option .= createOption($allStatus,$status);
    return $option;
}

function getCategory($selected_category) : string {

    global $conn;
    $option = "<option value=''>Choose Category</option>";
    list($department,$category) = explode("_",$selected_category);
    $searchQuery = "WHERE department = '$department'";
    $allCategory = $conn->query("SELECT id , name FROM `ticket_category` $searchQuery");
    $allCategory = mysqli_fetch_all($allCategory,MYSQLI_ASSOC);
    $allCategory = array_column($allCategory,'name','id');
    $option .= createOption($allCategory,$category);    
    return $option;
}

function getPriority($selected_priority) : string {

    $option = "<option value=''>Choose Priority</option>";
    $allPriority = ['1' => "Low" , '2'=>"Medium" , '3'=> "High"];
    $option .= createOption($allPriority,$selected_priority);
    return $option;
}

function getDepartment($selected_department) : string {
    
    global $conn;    
    $searchQuery = '';
    if($_SESSION['role'] == '2' || $_SESSION['role'] == '3') {
        $searchQuery .= "AND organization_id = '".$_SESSION['Organization_id']."'";
    }
    $searchQuery .= "AND branch_id LIKE '%1%'";
    $delete_query = 'Deleted_At IS NULL';
    $option = '<option value="">Choose department</option>';
    $allDepartment = $conn->query("SELECT id , department_name FROM `Department` WHERE $delete_query $searchQuery");
    $allDepartment = mysqli_fetch_all($allDepartment,MYSQLI_ASSOC);
    $allDepartment = array_column($allDepartment,'department_name','id');
    $option .= createOption($allDepartment,$selected_department);    
    return $option;
}

function getAssignTo($selected_params) {
    global $conn;    
    list($department,$assignTo) = explode('_',$selected_params);
    $searchQuery = '';
    $searchQuery .= "AND Branch_id = '1' AND Organization_id = '1'";
    $searchQuery .= "AND Department_id = '$department'";
    $delete_query = 'Deleted_At IS NULL';
    $option = '<option value="">Choose User</option>';
    $departmentUser = $conn->query("SELECT ID , Name FROM `users` WHERE $delete_query $searchQuery ORDER BY Hierarchy_value ASC");
    $departmentUser = mysqli_fetch_all($departmentUser,MYSQLI_ASSOC);
    $departmentUser = array_column($departmentUser,'Name','ID');
    $option .= createOption($departmentUser,$assignTo);    
    return $option;
}

function getTicketStatusDepartment($selected_params) {

    global $conn;
    $searchQuery = '';
    if($_SESSION['role'] == '2' || $_SESSION['role'] == '3') {
        $searchQuery .= "AND organization_id = '".$_SESSION['Organization_id']."'";
    }
    $searchQuery .= "AND branch_id LIKE '%1%'";
    $delete_query = 'Deleted_At IS NULL';
    $option = '<option value="">Choose department</option>';
    $allDepartment = $conn->query("SELECT id , department_name FROM `Department` WHERE $delete_query $searchQuery");
    $allDepartment = mysqli_fetch_all($allDepartment,MYSQLI_ASSOC);
    $allDepartment = array_column($allDepartment,'department_name','id');
    $option .= createMultiSelectOption($allDepartment,$selected_params);    
    return $option;
}

function createOption($optionData,$selected) : string {
    $option = "";
    foreach ($optionData as $id => $name) {
        $isSelected = ($id == $selected) ? "selected" : "";
        $option .= "<option value='$id' $isSelected>$name</option>";
    }
    return $option;
}

function createMultiSelectOption($optionData,$selected) {
    $option = "";
    $selected = explode(',',$selected);
    foreach ($optionData as $id => $name) {
        $isSelected = (in_array($id,$selected)) ? "selected" : "";
        $option .= "<option value='$id' $isSelected>$name</option>";
    }
    return $option;
}

?>