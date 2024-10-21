<?php

require '../../includes/db-config.php';
session_start();

$vertical = '';
$verticals_id = '';
if (isset($_REQUEST['branch']) && isset($_REQUEST['organization_id'])) {

    $organization_id = mysqli_real_escape_string($conn,$_REQUEST['organization_id']);
    $branches = [];
    if (is_array($_REQUEST['branch'])) {
        foreach ($_REQUEST['branch'] as $key => $value) {
            $branches[] = "Branch_id LIKE '%$value%'";    
        }
    } else {
        $branches[] = "Branch_id LIKE '%".$_REQUEST['branch']."%'";
    }
    if (isset($_REQUEST['vertical_id']) && !empty($_REQUEST['vertical_id'])) {
        $verticals_id = $_REQUEST['vertical_id'];
    }
    $vertical = getVerticalList($organization_id,$branches);
}

echo $vertical;

function getVerticalList($organization_id,$branches) {
    global $conn;
    global $verticals_id;
    $option = '<option value="">Select</option>';
    $vertical = $conn->query("SELECT * FROM `Vertical` WHERE organization_id = '$organization_id' AND ( ".implode(' OR ' ,$branches)." ) AND Deleted_At IS NULL");
    while ($row = mysqli_fetch_assoc($vertical)) {
        if(!empty($verticals_id) && $row['ID'] == $verticals_id ) {
            $option .= '<option value = "'.$row['ID'].'" selected>'.$row['Vertical_name'].'</span></option>';
        } else {
            $option .= '<option value = "'.$row['ID'].'">'.$row['Vertical_name'].'</span></option>';
        }
    }
    return $option;
}

?>