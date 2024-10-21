<?php

$branch_details = [];
include '../../includes/db-config.php';
session_start();

if(isset($_REQUEST['name']) && isset($_REQUEST['guard_name'])) {

    $name = mysqli_real_escape_string($conn,$_REQUEST['name']);
    $guard_name = mysqli_real_escape_string($conn,$_REQUEST['guard_name']);

    if(isset($_REQUEST['id'])) {
        $update_query = $conn->query("UPDATE roles SET name = '$name' , guard_name = '$guard_name' WHERE ID = '".$_REQUEST['id']."'");
        showResponse($update_query,'updated');
    } else {
        $insert_query = $conn->query("INSERT INTO roles (`name`,`guard_name`) VALUES ('$name','$guard_name')");
        showResponse($insert_query,'inserted');
    }
}

function showResponse($response, $message) {
    if ($response) {
        echo json_encode(['status' => 200, 'message' => "Role $message successfully!"]);
    } else {
        echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
    }
}

?>