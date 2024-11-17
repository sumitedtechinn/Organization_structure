<?php

require '../../includes/db-config.php';
session_start();

if(isset($_REQUEST['admission_center']) && isset($_REQUEST['numOfAdmission']) && isset($_REQUEST['admission_amount']) && isset($_REQUEST['id'])) {

    $admission_by = mysqli_real_escape_string($conn,$_REQUEST['admission_center']);
    $numOfAdmission = mysqli_real_escape_string($conn,$_REQUEST['numOfAdmission']);
    $admission_amount = mysqli_real_escape_string($conn,$_REQUEST['admission_amount']);
    $id = mysqli_real_escape_string($conn,$_REQUEST['id']); 

    $update = $conn->query("UPDATE admission_details SET admission_by = '$admission_by' , numofadmission = '$numOfAdmission' , amount = '$admission_amount' WHERE id = '$id'");

    showResponse($update,'Updated');
}

function showResponse($response, $message = "Something went wrong!") {
    if ($response) {
        echo json_encode(['status' => 200, 'message' => "Admission  details $message successfully!"]);
    } else {
        echo json_encode(['status' => 400, 'message' => $message]);
    }
}
?>