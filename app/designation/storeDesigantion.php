<?php 

include '../../includes/db-config.php';
session_start();

// echo "<pre>";
// print_r($_REQUEST);
// exit;

if (isset($_REQUEST['designation']) && isset($_REQUEST['designation_code']) && isset($_REQUEST['parent_desigantion']) && isset($_REQUEST['node_colour']) && isset($_REQUEST['designation_addIn'])) {

    $designation = mysqli_real_escape_string($conn,$_REQUEST['designation']);
    $designation_code = mysqli_real_escape_string($conn,$_REQUEST['designation_code']);
    list($parent_desigantion_id,$parent_hierarchy_value) = explode('_',mysqli_real_escape_string($conn,$_REQUEST['parent_desigantion']));
    $node_colour = mysqli_real_escape_string($conn,$_REQUEST['node_colour']);
    $designation_addIn = mysqli_real_escape_string($conn,$_REQUEST['designation_addIn']);
    $added_inside = mysqli_real_escape_string($conn,$_REQUEST['added_inside']);

    $hierarchy_value = intval($parent_hierarchy_value) + 1;
    $where_clause = '';$insert_query = '';
    if(isset($_REQUEST['department_id']) && ($added_inside == '4')) {
        $department = mysqli_real_escape_string($conn,$_REQUEST['department_id']);
        $where_clause =  "AND department_id = '$department' AND added_inside = '$added_inside'";
        $insert_query = "INSERT INTO `Designation`(`designation_name`,`code`,`hierarchy_value`,`department_id`, `parent_id`, `added_inside`, `color`) VALUES ('$designation','$designation_code','$hierarchy_value','$department','$parent_desigantion_id','$added_inside','$node_colour')";
    } elseif(isset($_REQUEST['vertical_id']) && $added_inside = '3') {
        $vertical = mysqli_real_escape_string($conn,$_REQUEST['vertical_id']);
        $where_clause =  "AND vertical_id = '$vertical' AND added_inside = '$added_inside'";
        $insert_query = "INSERT INTO `Designation`(`designation_name`, `code`, `hierarchy_value`, `vertical_id`, `parent_id`, `added_inside`, `color`) VALUES ('$designation','$designation_code','$hierarchy_value','$vertical','$parent_desigantion_id','$added_inside','$node_colour')";
    } elseif(isset($_REQUEST['organization_id']) && isset($_REQUEST['branch_id'])) {
        $organization = mysqli_real_escape_string($conn,$_REQUEST['organization_id']);
        $branch = mysqli_real_escape_string($conn,$_REQUEST['branch_id']);
        $where_clause =  "AND branch_id = '$branch' AND organization_id = '$organization' AND added_inside = '$added_inside'";
        $insert_query = "INSERT INTO `Designation`(`designation_name`, `code`, `hierarchy_value`, `branch_id`, `organization_id`, `parent_id`, `added_inside`, `color`) VALUES ('$designation','$designation_code','$hierarchy_value','$branch','$organization','$parent_desigantion_id','$added_inside','$node_colour')";
    } elseif (isset($_REQUEST['organization_id'])) {
        $organization = mysqli_real_escape_string($conn,$_REQUEST['organization_id']);
        $where_clause =  "AND organization_id = '$organization' AND added_inside = '$added_inside'";
        $insert_query = "INSERT INTO `Designation`(`designation_name`, `code`, `hierarchy_value`, `organization_id`, `parent_id`, `added_inside`, `color`) VALUES ('$designation','$designation_code','$hierarchy_value','$organization','$parent_desigantion_id','$added_inside','$node_colour')";
    }
    
    $insert_designation = $conn->query($insert_query);
    $last_inserted_id = $conn->insert_id;

    $upper_hierarchy_value_data = $conn->query("SELECT ID FROM Designation WHERE hierarchy_value >= '$hierarchy_value' $where_clause AND ID != '$last_inserted_id' AND parent_id = '$parent_desigantion_id'");

    if($upper_hierarchy_value_data->num_rows > 0 && $designation_addIn == "Add in above") {
        $child_hierarchy = intval($hierarchy_value) + 1;
        $update_child = $conn->query("UPDATE Designation SET hierarchy_value = '$child_hierarchy', parent_id = '$last_inserted_id' WHERE hierarchy_value = '$hierarchy_value' $where_clause AND ID != '$last_inserted_id' AND parent_id = '$parent_desigantion_id'");
        checkAndUpdateBelowHierarchy($upper_hierarchy_value_data,$where_clause,$child_hierarchy);
    }
    showResponse($insert_designation,'inserted');
    
} elseif (isset($_REQUEST['designation']) && isset($_REQUEST['designation_code']) && isset($_REQUEST['node_colour'])) {
    $designation = mysqli_real_escape_string($conn,$_REQUEST['designation']);
    $designation_code = mysqli_real_escape_string($conn,$_REQUEST['designation_code']);
    $node_colour = mysqli_real_escape_string($conn,$_REQUEST['node_colour']);

    $update_query = $conn->query("UPDATE `Designation` SET `designation_name`='$designation',`code`='$designation_code',`color`='$node_colour' WHERE `ID`='".$_REQUEST['ID']."'");
    showResponse($update_query,'updated');
}

function checkAndUpdateBelowHierarchy($ids_list,$where_clause,$hierarchy_value) {
    global $conn;
    while($row = mysqli_fetch_assoc($ids_list)) {
        $check_upper_hierarchy = $conn->query("SELECT ID FROM Designation WHERE parent_id = '".$row['ID']."' $where_clause ");
        if($check_upper_hierarchy->num_rows > 0) {
            $child_hierarchy = intval($hierarchy_value) + 1;
            $update_query = $conn->query("UPDATE Designation SET hierarchy_value = '$hierarchy_value' where parent_id = '".$row['ID']."' $where_clause");
            checkAndUpdateBelowHierarchy($check_upper_hierarchy,$where_clause,$child_hierarchy);
        }
    }
}

function showResponse($response, $message = 'Something went wrong!') {
    if ($response) {
        echo json_encode(['status' => 200, 'message' => "Designation $message successfully!"]);
    } else {
        echo json_encode(['status' => 400, 'message' => $message]);
    }
}

?>