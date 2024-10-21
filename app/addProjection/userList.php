<?php

include '../../includes/db-config.php';
session_start();

if (isset($_REQUEST['projection_type'])) {
    $projection_type = mysqli_real_escape_string($conn,$_REQUEST['projection_type']);

    $user_id = '';
    if (isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id'])) {
        $user_id = mysqli_real_escape_string($conn,$_REQUEST['user_id']);
    }
    $projection_type_details = $conn->query("SELECT * FROM `Projection_type` WHERE ID = '$projection_type'");
    $projection_type_details = mysqli_fetch_assoc($projection_type_details);

    $users = $conn->query("SELECT users.* , Designation.code as `designation_code` FROM users LEFT JOIN Designation ON Designation.ID = users.Designation_id WHERE users.Organization_id = '".$projection_type_details['organization_id']."' AND users.Branch_id = '".$projection_type_details['branch_id']."' AND users.Vertical_id = '".$projection_type_details['vertical_id']."' AND users.Department_id = '".$projection_type_details['department_id']."'");
    
    $child_list = [];
    if (isset($_SESSION['allChildId'])) {
        $child_list = $_SESSION['allChildId'];
        unset($child_list[0]);
    }   

    $option = '<option value="">Select</option>';
    if ($users->num_rows > 0) {
        while($row = mysqli_fetch_assoc($users)) {
            if (!empty($child_list) || isset($_SESSION['allChildId'])) {
                // for user case 
                if (in_array($row['ID'],$child_list)) {
                    if (!empty($user_id) && $row['ID'] == $user_id) {
                        $option .= '<option value="'.$row['ID'].'" selected >'.$row['Name'].'('.$row['designation_code'].')</option>';
                    } else {
                        $option .= '<option value="'.$row['ID'].'">'.$row['Name'].'('.$row['designation_code'].')</option>';
                    }
                }
            } else {
                // For admin case any user can come
                if (!empty($user_id) && $row['ID'] == $user_id) {
                    $option .= '<option value="'.$row['ID'].'" selected >'.$row['Name'].'('.$row['designation_code'].')</option>';
                } else {
                    $option .= '<option value="'.$row['ID'].'">'.$row['Name'].'('.$row['designation_code'].')</option>';
                }
            }
        }
    }
}
echo $option;

?>