<?php

require '../../includes/db-config.php';
session_start();

if (isset($_REQUEST['id']) && isset($_REQUEST['table'])) {

    $id = mysqli_real_escape_string($conn,$_REQUEST['id']);
    $table = mysqli_real_escape_string($conn,$_REQUEST['table']);
    
    if ($table == "users") {
        $response = $conn->query("UPDATE $table SET Deleted_At = CURRENT_TIMESTAMP , Assinged_Person_id = NULL WHERE ID = '$id'");
        showResponse($response,$table);    
    } else if ( $table == 'Designation') {
        updateHierarchyBeforeDeleteDesigantion();
        $response = $conn->query("DELETE FROM $table WHERE ID = '$id'");
        showResponse($response,$table);
    } else {
        $response = $conn->query("UPDATE $table SET Deleted_At = CURRENT_TIMESTAMP WHERE ID = '$id'");
        showResponse($response,$table);
    }
}

function updateHierarchyBeforeDeleteDesigantion(){
    global $conn;
    $current_designation_details = $conn->query("SELECT id,hierarchy_value,department_id,organization_id,branch_id,parent_id FROM Designation WHERE ID = '".$_REQUEST['id']."'");
    $current_designation_details = mysqli_fetch_assoc($current_designation_details);
    $id = $current_designation_details['id'];
    $department_id = $current_designation_details['department_id'];
    $branch_id = $current_designation_details['branch_id'];
    $organization_id = $current_designation_details['organization_id'];
    $hierarchy_value = $current_designation_details['hierarchy_value'];
    $parent_id = $current_designation_details['parent_id'];

    $where_clause = '';
    if (is_null($organization_id) && is_null($branch_id)) {
        $where_clause =  "AND department_id = '$department_id'";
    } elseif (is_null($department_id) && is_null($branch_id)) {
        $where_clause =  "AND branch_id IS NULL AND organization_id = '$organization_id'";
    } else {
        $where_clause =  "AND branch_id = '$branch_id' AND organization_id = '$organization_id'";
    }
    $check_upper_hierarchy = $conn->query("SELECT ID FROM Designation WHERE hierarchy_value > '$hierarchy_value' $where_clause AND parent_id = '$id'");
        
    if($check_upper_hierarchy->num_rows > 0) {
        $update_query = $conn->query("UPDATE Designation SET hierarchy_value = '$hierarchy_value', parent_id = '$parent_id' where parent_id = '$id' $where_clause");
        $child_hierarchy = intval($hierarchy_value) + 1 ; 
        checkAndUpdateBelowHierarchy($check_upper_hierarchy,$where_clause,$child_hierarchy);
    }
}

function checkAndUpdateBelowHierarchy($ids_list,$where_clause,$hierarchy_value) {

    global $conn;
    while($row = mysqli_fetch_assoc($ids_list)) {
        $check_upper_hierarchy = $conn->query("SELECT ID FROM Designation WHERE parent_id = '".$row['ID']."' $where_clause");
        if($check_upper_hierarchy->num_rows > 0) {
            $update_query = $conn->query("UPDATE Designation SET hierarchy_value = '$hierarchy_value' where parent_id = '".$row['ID']."' $where_clause");
            $child_hierarchy = intval($hierarchy_value) + 1;
            checkAndUpdateBelowHierarchy($check_upper_hierarchy,$where_clause,$child_hierarchy);
        }
    }
}

function showResponse($response, $message = 'Something went wrong!') {
    if ($response) {
        echo json_encode(['status' => 200, 'message' => "$message removed successfully!"]);
    } else {
        echo json_encode(['status' => 400, 'message' => $message ]);
    }
}

?>