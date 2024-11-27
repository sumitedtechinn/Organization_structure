<?php 

include '../../includes/db-config.php';
session_start();

if(isset($_REQUEST['selected_insideType'])) {
    $selected_insideType = mysqli_real_escape_string($conn,$_REQUEST['selected_insideType']);
    $added_inside = mysqli_real_escape_string($conn,$_REQUEST['selected_insideTypeId']);
    switch ($selected_insideType) {
    case "organization" :
        checkParallelHierarcyForOrganization($added_inside);
        break;
    case "branch" : 
        checkParallelHierarcyForBranch($added_inside);
        break;
    case "vertical" : 
        checkParallelHierarcyForVertical($added_inside);
        break;
    case "department" : 
        checkParallelHierarcyForDepartment($added_inside);
        break;
    }
}


function checkParallelHierarcyForOrganization($added_inside) {
    global $conn;
    $organization = mysqli_real_escape_string($conn,$_REQUEST['organization_id']);
    list($parent_desigantion_id,$parent_hierarchy_value) = explode('_',mysqli_real_escape_string($conn,$_REQUEST['parent_desigantion']));
    $hierarchy = $parent_hierarchy_value+1;
    $checkChildDesignationAssingOrNot = $conn->query("SELECT ID as `desigantion` FROM `Designation` WHERE hierarchy_value = '$hierarchy' AND branch_id IS NULL AND organization_id = '$organization' AND parent_id = '$parent_desigantion_id' AND added_inside = '$added_inside'");
    showResponse($checkChildDesignationAssingOrNot);
}

function checkParallelHierarcyForBranch($added_inside) {
    global $conn;
    $branch = mysqli_real_escape_string($conn,$_REQUEST['branch_id']);
    $organization = mysqli_real_escape_string($conn,$_REQUEST['organization_id']);
    list($parent_desigantion_id,$parent_hierarchy_value) = explode('_',mysqli_real_escape_string($conn,$_REQUEST['parent_desigantion']));
    $hierarchy = $parent_hierarchy_value+1;
    $checkChildDesignationAssingOrNot = $conn->query("SELECT ID as `desigantion` FROM `Designation` WHERE hierarchy_value = '$hierarchy' AND branch_id = '$branch' AND organization_id = '$organization' AND parent_id = '$parent_desigantion_id' AND added_inside = '$added_inside'");
    showResponse($checkChildDesignationAssingOrNot);
}

function checkParallelHierarcyForVertical($added_inside) {
    global $conn;
    $vertical = mysqli_real_escape_string($conn,$_REQUEST['vertical_id']);
    list($parent_desigantion_id,$parent_hierarchy_value) = explode('_',mysqli_real_escape_string($conn,$_REQUEST['parent_desigantion']));
    $hierarchy = $parent_hierarchy_value+1;
    $checkChildDesignationAssingOrNot = $conn->query("SELECT ID as `desigantion` FROM `Designation` WHERE hierarchy_value = '$hierarchy' AND parent_id = '$parent_desigantion_id' AND vertical_id = '$vertical' AND added_inside = '$added_inside'");
    showResponse($checkChildDesignationAssingOrNot);
}

function checkParallelHierarcyForDepartment($added_inside) {
    global $conn;
    $department = mysqli_real_escape_string($conn,$_REQUEST['department_id']);
    list($parent_desigantion_id,$parent_hierarchy_value) = explode('_',mysqli_real_escape_string($conn,$_REQUEST['parent_desigantion']));
    $hierarchy = $parent_hierarchy_value+1;
    $checkChildDesignationAssingOrNot = $conn->query("SELECT ID as `desigantion` FROM `Designation` WHERE hierarchy_value = '$hierarchy' AND parent_id = '$parent_desigantion_id' AND department_id = '$department' AND added_inside = '$added_inside'");
    showResponse($checkChildDesignationAssingOrNot);
} 

function showResponse($checkDesignation) {
    if($checkDesignation->num_rows > 0 ) {
        echo json_encode(['status' => 400 , 'text' => 'Insert above the child', 'title' => 'Parent Has Already Child']);
    } else {
        echo json_encode(['status' => 200]);
    }
}
?>