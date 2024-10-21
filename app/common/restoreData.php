<?php

require '../../includes/db-config.php';
session_start();

if (isset($_REQUEST['id']) && isset($_REQUEST['table'])) {

    $id = mysqli_real_escape_string($conn,$_REQUEST['id']);
    $table = mysqli_real_escape_string($conn,$_REQUEST['table']);

    if ($table == 'Designation') {
        $update_hierarchy_response = updateHierarchyBeforeRestoreDesigantion();
        if($update_hierarchy_response) {
            $response = $conn->query("UPDATE $table SET Deleted_At = null WHERE ID = '$id'");
            showResponse($response,$table);    
        } else {
            showResponse($update_hierarchy_response,"Lower Designation hierarchy not updated");
        }
    } else {
        $response = $conn->query("UPDATE $table SET Deleted_At = null WHERE ID = '$id'");
        showResponse($response,$table);    
    }
}

function updateHierarchyBeforeRestoreDesigantion() {
    global $conn;

    $current_designation_details = $conn->query("SELECT hierarchy_value,department_id FROM Designation WHERE ID = '".$_REQUEST['id']."'");
    $current_designation_details = mysqli_fetch_assoc($current_designation_details);

    $upper_hierarchy_value_data = $conn->query("SELECT ID,hierarchy_value FROM Designation WHERE hierarchy_value >= '".$current_designation_details['hierarchy_value']."' AND department_id = '".$current_designation_details['department_id']."' AND ID != '".$_REQUEST['id']."'");
        
    if($upper_hierarchy_value_data->num_rows > 0) {
        $update_query = "UPDATE Designation SET hierarchy_value = CASE ID";
        $ids_list = [];
        while($row = mysqli_fetch_assoc($upper_hierarchy_value_data)) {
            $ids_list[] = $row['ID'];
            $updated_hierarchy_value = $row['hierarchy_value']+1;
            $update_query .= " WHEN '".$row['ID']."' THEN '$updated_hierarchy_value'";
        }
        $update_query .= "ELSE hierarchy_value END WHERE ID IN (" .implode(',',$ids_list). ") AND department_id = '".$current_designation_details['department_id']."'";
        $update_hierarchy_value = $conn->query($update_query);

        return $update_hierarchy_value;
    }
}

function showResponse($response, $message = 'Something went wrong!') {
    if ($response) {
        echo json_encode(['status' => 200, 'message' => "$message restore successfully!"]);
    } else {
        echo json_encode(['status' => 400, 'message' => $message ]);
    }
}

?>