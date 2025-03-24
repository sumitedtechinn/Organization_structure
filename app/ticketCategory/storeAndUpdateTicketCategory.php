<?php

error_reporting( E_ALL );
require '../../includes/db-config.php';
session_start();

if(isset($_REQUEST['name']) && isset($_REQUEST['department']) && isset($_REQUEST['multiple_assignation'])) {

    $name = mysqli_real_escape_string($conn,$_REQUEST['name']);
    $department = mysqli_real_escape_string($conn,$_REQUEST['department']);
    $multiple_assignation = (int)$_REQUEST['multiple_assignation'];

    if(!isset($_REQUEST['ID'])) {
        $insert_query = "INSERT INTO `ticket_category`(`name`, `department`,`multiple_assignation`) VALUES ('$name','$department','$multiple_assignation')";
        $insert = $conn->query($insert_query);
        echo showResponse($insert,"Category added");
    } else {
        $id = mysqli_real_escape_string($conn,$_REQUEST['ID']);
        $update_query = "UPDATE `ticket_category` SET `name`= '$name',`department` = '$department' , `multiple_assignation` = $multiple_assignation WHERE id = '$id'";
        $update = $conn->query($update_query);
        echo showResponse($update,"Category Updated");
    }
}

function showResponse($response, $message = "Something went wrong!") {
    return ($response) ? json_encode(['status' => 200, 'message' => "$message successfully!"]) : json_encode(['status' => 400, 'message' => $message]);  
}

?>