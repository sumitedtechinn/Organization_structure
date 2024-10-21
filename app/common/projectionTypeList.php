<?php

require '../../includes/db-config.php';
session_start();

if (isset($_REQUEST['projection_type'])) {

    $option = "<option value=''>Select Projection Type</option>";
    if($_SESSION['role'] == '2') {
        $projection_type = $conn->query("SELECT * FROM `Projection_type` WHERE organization_id = '".$_SESSION['Organization_id']."' and branch_id = '".$_SESSION['Branch_id']."' AND vertical_id = '".$_SESSION['Vertical_id']."' AND department_id = '".$_SESSION['Department_id']."' AND Deleted_At IS NULL");
    } else {
        $projection_type = $conn->query("SELECT ID,Name FROM `Projection_type` WHERE Deleted_At IS NULL");
    }
    while ($row = mysqli_fetch_assoc($projection_type)) {
        if (!empty($_REQUEST['projection_type']) && $row['ID'] == $_REQUEST['projection_type']) {
            $option .= '<option value = "'.$row['ID'].'" selected >'.$row['Name'].'</option>';
        } else {
            $option .= '<option value = "'.$row['ID'].'">'.$row['Name'].'</option>';
        }
    }
    echo $option;
}

?>