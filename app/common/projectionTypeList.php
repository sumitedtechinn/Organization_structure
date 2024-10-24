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
    $projection_type = $conn->query("SELECT Projection_type.* , Department.department_name as `department` FROM `Projection_type` LEFT JOIN Department ON Department.id = Projection_type.department_id WHERE Projection_type.Deleted_At IS NULL $searchQuery");
    while ($row = mysqli_fetch_assoc($projection_type)) {
        if (!empty($_REQUEST['projection_type']) && $row['ID'] == $_REQUEST['projection_type']) {
            $option .= '<option value = "'.$row['ID'].'" selected >'.$row['Name'].'('.$row['department'].')</option>';
        } else {
            $option .= '<option value = "'.$row['ID'].'">'.$row['Name'].'('.$row['department'].')</option>';
        }
    }
    echo $option;
}

?>