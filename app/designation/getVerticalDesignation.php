<?php 

include '../../includes/db-config.php';
session_start();

$option = '';
if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'adminDesignation') {
    $option .= '<option value="">Select</div>';
} else {
    $option .= '<option value="">Select</div><option value = "0_0">Head</option>';
}

if(isset($_REQUEST['branch_id']) && isset($_REQUEST['vertical_id'])) {

    $branch_id = mysqli_real_escape_string($conn,$_REQUEST['branch_id']);
    $vertical_id = mysqli_real_escape_string($conn,$_REQUEST['vertical_id']);
    
    $parent_id  = '';
    if (isset($_REQUEST['hierarchy_value']) && !empty($_REQUEST['hierarchy_value'])) {
        $parent_hierarchy = intval($_REQUEST['hierarchy_value'])-1;
        $parent = $conn->query("SELECT ID FROM `Designation` where hierarchy_value = '$parent_hierarchy' AND vertical_id = '$vertical_id' AND added_inside = '3'"); 
        if($parent->num_rows > 0) {
            $parent_id = mysqli_fetch_column($parent);
        } else {
            $option .= '<option value="0_0" selected >Head</option>';
        }
    }

    $designation_id = '';
    if(isset($_REQUEST['designation_id'])) {
        $designation_id = mysqli_real_escape_string($conn,$_REQUEST['designation_id']);
    }
    
    $designation = $conn->query("SELECT ID , hierarchy_value , CONCAT(designation_name,'(',code,')') as `name` FROM Designation WHERE vertical_id = '$vertical_id' AND added_inside = '3'");
    if($designation->num_rows > 0) {
        while($row = mysqli_fetch_assoc($designation)) {
            if (!empty($parent_id) && $row['ID'] == $parent_id) {
                $option .= '<option value="'.$row['ID'].'_'.$row['hierarchy_value'].'" selected >'.$row['name'].'</option>';    
            } elseif ( !empty($designation_id) && $row['ID'] == $designation_id ){
                $option .= '<option value="'.$row['ID'].'_'.$row['hierarchy_value'].'" selected >'.$row['name'].'</option>';
            } else {
                $option .= '<option value="'.$row['ID'].'_'.$row['hierarchy_value'].'">'.$row['name'].'</option>';
            }
        }
    }
}

echo $option;
?>