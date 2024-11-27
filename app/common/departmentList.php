<?php

require '../../includes/db-config.php';
session_start();

$department_list = getDepartmentList();
echo $department_list;

function getDepartmentList() {
    
    global $conn;    
    $searchQuery = '';
    if($_SESSION['role'] == '2' || $_SESSION['role'] == '3') {
        $searchQuery .= "AND organization_id = '".$_SESSION['Organization_id']."'";
    }
    if(isset($_REQUEST['organization_id']) && !empty($_REQUEST['organization_id']) && isset($_REQUEST['branch_id']) && !empty($_REQUEST['branch_id']) && !empty($_REQUEST['vertical_id']) && isset($_REQUEST['vertical_id'])) {
        $searchQuery .= " AND organization_id = '".$_REQUEST['organization_id']."' AND branch_id LIKE '%".$_REQUEST['branch_id']."%' AND vertical_id = '".$_REQUEST['vertical_id']."'";
    } 
    if (isset($_REQUEST['department_id']) && !empty($_REQUEST['department_id'])) {
        $searchQuery .= " AND ID = '".$_REQUEST['department_id']."'";
    }
    $delete_query = 'Deleted_At IS NULL';
    $option = '<option value="">Choose department</option>';
    $department = $conn->query("SELECT id , department_name FROM `Department` WHERE $delete_query $searchQuery");
    while($row = mysqli_fetch_assoc($department)) {
        if (isset($_REQUEST['department_id']) && !empty($_REQUEST['department_id']) && $row['id'] == $_REQUEST['department_id'] ) {
            $option .= '<option value = "'.$row['id'].'" selected >'.$row['department_name'].'</option>';
        } else {
            $option .= '<option value = "'.$row['id'].'">'.$row['department_name'].'</option>';
        }
    }
    return $option;
}

?>