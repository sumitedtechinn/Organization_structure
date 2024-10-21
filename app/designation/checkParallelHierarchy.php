<?php 

include '../../includes/db-config.php';
session_start();

if (isset($_REQUEST['department']) && isset($_REQUEST['parent_desigantion'])) {

    $department = mysqli_real_escape_string($conn,$_REQUEST['department']);
    list($parent_desigantion_id,$parent_hierarchy_value) = explode('_',mysqli_real_escape_string($conn,$_REQUEST['parent_desigantion']));
    $hierarchy = $parent_hierarchy_value+1;
    $checkChildDesignationAssingOrNot = $conn->query("SELECT ID as `desigantion` FROM `Designation` WHERE hierarchy_value = '$hierarchy' AND department_id = '".$department."' AND parent_id = '".$parent_desigantion_id."'");
    if($checkChildDesignationAssingOrNot->num_rows > 0 ) {
        echo json_encode(['status' => 400 , 'text' => 'Insert above the child', 'title' => 'Parent Has Already Child']);
    } else {
        echo json_encode(['status' => 200]);
    }
} elseif(isset($_REQUEST['branch']) && isset($_REQUEST['organization']) && isset($_REQUEST['parent_desigantion'])) {

    $branch = mysqli_real_escape_string($conn,$_REQUEST['branch']);
    $organization = mysqli_real_escape_string($conn,$_REQUEST['organization']);
    list($parent_desigantion_id,$parent_hierarchy_value) = explode('_',mysqli_real_escape_string($conn,$_REQUEST['parent_desigantion']));
    $hierarchy = $parent_hierarchy_value+1;
    $checkChildDesignationAssingOrNot = $conn->query("SELECT ID as `desigantion` FROM `Designation` WHERE hierarchy_value = '$hierarchy' AND branch_id = '".$branch."' AND organization_id = '".$organization."' AND parent_id = '".$parent_desigantion_id."'");
    if($checkChildDesignationAssingOrNot->num_rows > 0 ) {
        echo json_encode(['status' => 400 , 'text' => 'Insert above the child', 'title' => 'Parent Has Already Child']);
    } else {
        echo json_encode(['status' => 200]);
    }

} elseif(isset($_REQUEST['organization']) && isset($_REQUEST['parent_desigantion'])) {

    $organization = mysqli_real_escape_string($conn,$_REQUEST['organization']);
    list($parent_desigantion_id,$parent_hierarchy_value) = explode('_',mysqli_real_escape_string($conn,$_REQUEST['parent_desigantion']));
    $hierarchy = $parent_hierarchy_value+1;
    $checkChildDesignationAssingOrNot = $conn->query("SELECT ID as `desigantion` FROM `Designation` WHERE hierarchy_value = '$hierarchy' AND branch_id IS NULL AND organization_id = '".$organization."' AND parent_id = '".$parent_desigantion_id."'");
    if($checkChildDesignationAssingOrNot->num_rows > 0 ) {
        echo json_encode(['status' => 400 , 'text' => 'Insert above the child', 'title' => 'Parent Has Already Child']);
    } else {
        echo json_encode(['status' => 200]);
    }
}
?>