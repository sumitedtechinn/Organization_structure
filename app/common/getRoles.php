<?php

## Database configuration
include '../../includes/db-config.php';
session_start();


$option = '<option value ="">Select Role</option>';
$role = $conn->query("SELECT ID,name FROM `roles` WHERE ID != 1");
if ($role->num_rows > 0) {
    while($row = mysqli_fetch_assoc($role)) {
        if(isset($_REQUEST['role_id']) && !empty($_REQUEST['role_id']) && $row['ID'] == $_REQUEST['role_id']) {
            $option .= '<option value = "'.$row['ID'].'" selected>'.$row['name'].'</option>';
        } else {
            $option .= '<option value = "'.$row['ID'].'">'.$row['name'].'</option>';
        }
    }
}

echo $option;

?>