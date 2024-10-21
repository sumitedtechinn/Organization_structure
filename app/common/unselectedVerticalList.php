<?php

require '../../includes/db-config.php';
session_start();

$vertical = ''; $branch = '';

if (isset($_REQUEST['branch'])) {

    $branch_id = mysqli_real_escape_string($conn,$_REQUEST['branch']);
    $branch = "Branch_id LIKE '%$branch_id%'";
    $vertical = getVerticalList();
}
echo $vertical;

function getVerticalList() {
    global $conn;
    global $branch;
    $option = '<option value="">Select</option>';
    $vertical = $conn->query("SELECT * FROM `Vertical` WHERE $branch AND Deleted_At IS NULL");
    while ($row = mysqli_fetch_assoc($vertical)) {
        $option .= '<option value = "'.$row['ID'].'">'.$row['Vertical_name'].'</span></option>';
    }
    return $option;
}

?>