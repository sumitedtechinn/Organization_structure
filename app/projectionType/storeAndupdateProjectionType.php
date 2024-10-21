<?php

require '../../includes/db-config.php';
session_start();

if (isset($_REQUEST['name']) && isset($_REQUEST['branch']) && isset($_REQUEST['department'])) {
    $projection_type_name = mysqli_real_escape_string($conn,$_REQUEST['name']);
    $branch = mysqli_real_escape_string($conn,$_REQUEST['branch']);
    $department = mysqli_real_escape_string($conn,$_REQUEST['department']);

    $department_details = $conn->query("SELECT organization_id , vertical_id  FROM `Department` WHERE id = '$department'");
    $department_details = mysqli_fetch_assoc($department_details);

    if (isset($_REQUEST['id'])) {
        $update_query = $conn->query("UPDATE `Projection_type` SET `Name`='$projection_type_name' , `organization_id` = '".$department_details['organization_id']."' , `branch_id` = '$branch' , `vertical_id` = '".$department_details['vertical_id']."' , `department_id` = '$department' WHERE ID = ". $_REQUEST['id']);
        showResponse($update_query,'Updated');
    } else {
        $insert_query = $conn->query("INSERT INTO `Projection_type`(`Name`,`organization_id`,`branch_id`,`vertical_id`,`department_id`) VALUES ('$projection_type_name','".$department_details['organization_id']."','$branch','".$department_details['vertical_id']."','$department')");
        showResponse($insert_query,'inserted');
    }
}

function showResponse($response, $message) {
    if ($response) {
        echo json_encode(['status' => 200, 'message' => "Projection type $message successfully!"]);
    } else {
        echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
    }
}

?>