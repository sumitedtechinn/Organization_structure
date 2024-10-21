<?php

require '../../includes/db-config.php';
session_start();

$searchQuery = '';
if($_SESSION['role'] == '2' || $_SESSION['role'] == '3') {
    $searchQuery .= "AND id = '".$_SESSION['Organization_id']."'";
}
$delete_query = 'Deleted_At IS NULL';
$option = '<option value ="">Select Organization</option>';
$organization = $conn->query("SELECT id , organization_name FROM organization where $delete_query $searchQuery");
if ($organization->num_rows > 0) {
    while($row = mysqli_fetch_assoc($organization)) {
        if(isset($_REQUEST['organization_id']) && !empty($_REQUEST['organization_id']) && $row['id'] == $_REQUEST['organization_id']) {
            $option .= '<option value = "'.$row['id'].'" selected>'.$row['organization_name'].'</option>';
        } else {
            $option .= '<option value = "'.$row['id'].'">'.$row['organization_name'].'</option>';
        }
    }
}

echo $option;
?>