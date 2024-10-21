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
    $option = '<option value="">Select Organization</option>'; 
    $organization = $conn->query("SELECT id , organization_name FROM `organization` WHERE Deleted_At IS NULL $searchQuery");
    while($row = mysqli_fetch_assoc($organization)) {
        $option .= '<option value = "'.$row['id'].'" >'.$row['organization_name'].'</option>';
    }
    return $option;
}

function getBranch() {
    global $conn;
    $searchQuery = '';
    if($_SESSION['role'] == '2' || $_SESSION['role'] == '3') {
        $searchQuery .= "AND Branch.organization_id = '".$_SESSION['Organization_id']."'";
    }
    $option = '<option value="">Select Branch</option>'; 
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
    $option = '<option value="">Select Vertical</option>'; 
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
    $option = '<option value="">Select Department</option>'; 
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
    $option = '<option value="">Select Designation</option>'; 
    $designation = $conn->query("SELECT ID,designation_name FROM `Designation` WHERE Deleted_At IS NULL $searchQuery");
    while($row = mysqli_fetch_assoc($designation)) {
        $option .= '<option value = "'.$row['ID'].'" >'.$row['designation_name'].'</option>';
    }
    return $option;
}

?>