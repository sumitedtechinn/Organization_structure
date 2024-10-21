<?php

require '../../includes/db-config.php';
session_start();

if (isset($_REQUEST['closure_id']) && isset($_REQUEST['center_name']) && isset($_REQUEST['center_email']) && isset($_REQUEST['contact_number'])) {
    
    $closure_id = mysqli_real_escape_string($conn,$_REQUEST['closure_id']);
    $center_name = mysqli_real_escape_string($conn,$_REQUEST['center_name']);
    $center_email = mysqli_real_escape_string($conn,$_REQUEST['center_email']);
    $contact = mysqli_real_escape_string($conn,$_REQUEST['contact_number']);
    $country_code = mysqli_real_escape_string($conn,$_REQUEST['country_code']);
    $projection_type = mysqli_real_escape_string($conn,$_REQUEST['projection_type']);


    $update_query = $conn->query("UPDATE `Closure_details` SET `center_name`='$center_name',`center_email`='$center_email',`contact`='$contact',`country_code`='$country_code',`projectionType`='$country_code', Updated_At = CURRENT_TIMESTAMP WHERE ID = '$closure_id'");
    showResponse($update_query,'updated');
}

function showResponse($response, $message) {
    if ($response) {
        echo json_encode(['status' => 200, 'message' => "Closure $message successfully!"]);
    } else {
        echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
    }
}

?>