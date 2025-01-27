<?php

require '../../includes/db-config.php';
session_start();

if (isset($_REQUEST['projection_type'])) {

    $option = "<option value=''>Select Projection Type</option>";
    $searchQuery = '';
    if($_SESSION['role'] == '2') {
        $searchQuery = "AND Projection_type.organization_id = '".$_SESSION['Organization_id']."' and Projection_type.branch_id = '".$_SESSION['Branch_id']."' AND Projection_type.vertical_id = '".$_SESSION['Vertical_id']."' AND Projection_type.department_id = '".$_SESSION['Department_id']."'";
    } else if ($_SESSION['role'] == '3') {
        $searchQuery .= "AND Projection_type.organization_id = '".$_SESSION['Organization_id']."'";
    }
    $projection_type = $conn->query("SELECT Projection_type.* , Department.department_name as `department` , SUBSTRING_INDEX(Branch.Branch_name,',',1)  as `branch_name` FROM `Projection_type` LEFT JOIN Department ON Department.id = Projection_type.department_id LEFT JOIN Branch ON Branch.ID = Projection_type.branch_id WHERE Projection_type.Deleted_At IS NULL $searchQuery");
    while ($row = mysqli_fetch_assoc($projection_type)) {
        $department_name = $_SESSION['role'] == '2' ? '': '('.$row['department'].')('.$row['branch_name'].')';
        if(isset($_REQUEST['projectionType_form'])) {
            if (str_contains(strtolower($row['Name']),$_REQUEST['projectionType_form'])) {
                if (!empty($_REQUEST['projection_type']) && $row['ID'] == $_REQUEST['projection_type']) {
                    $option .= '<option value = "'.$row['ID'].'" selected >'.$row['Name'].$department_name.'</option>';
                } else {
                    $option .= '<option value = "'.$row['ID'].'">'.$row['Name'].$department_name.'</option>';
                }
            }
        } else {
            if (!empty($_REQUEST['projection_type']) && $row['ID'] == $_REQUEST['projection_type']) {
                $option .= '<option value = "'.$row['ID'].'" selected >'.$row['Name'].$department_name.'</option>';
            } else {
                $option .= '<option value = "'.$row['ID'].'">'.$row['Name'].$department_name.'</option>';
            }
        }
    }
    echo $option;
} elseif (isset($_REQUEST['organization_id']) && isset($_REQUEST['branch_id']) && isset($_REQUEST['vertical_id']) && isset($_REQUEST    ['department_id'])) {

    $organization_id = mysqli_real_escape_string($conn,$_REQUEST['organization_id']);
    $branch_id = mysqli_real_escape_string($conn,$_REQUEST['branch_id']);
    $vertical_id = mysqli_real_escape_string($conn,$_REQUEST['vertical_id']);
    $department_id = mysqli_real_escape_string($conn,$_REQUEST['department_id']);

    $option = '<option value = "">Select Projection Type</option>';
    $projection_type = $conn->query("SELECT ID,Name FROM `Projection_type` WHERE organization_id = '$organization_id' AND branch_id = '$branch_id' AND vertical_id = '$vertical_id' AND department_id = '$department_id' AND Deleted_At IS NULL");
    if($projection_type->num_rows > 0) {
        while($row = mysqli_fetch_assoc($projection_type)) {
            $option .= '<option value = "'.$row['ID'].'">'.$row['Name'].'</option>';
        }
    }
    echo $option;
}

?>