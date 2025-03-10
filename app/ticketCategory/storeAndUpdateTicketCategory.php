<?php

error_reporting( E_ALL );
require '../../includes/db-config.php';
session_start();

if(isset($_REQUEST['name']) && isset($_REQUEST['department'])) {

    $name = mysqli_real_escape_string($conn,$_REQUEST['name']);
    $department = mysqli_real_escape_string($conn,$_REQUEST['department']);

    if(!isset($_REQUEST['ID'])) {
        $insert_query = "INSERT INTO `ticket_category`(`name`, `department`) VALUES ('$name','$department')";
        $insert = $conn->query($insert_query);
        echo showResponse($insert,"Category added");
    } else {
        $id = mysqli_real_escape_string($conn,$_REQUEST['ID']);
        $update_query = "UPDATE `ticket_category` SET `name`= '$name',`department` = '$department' WHERE id = '$id'";
        $update = $conn->query($update_query);
        echo showResponse($update,"Category Updated");
    }
}

function showResponse($response, $message = "Something went wrong!") {
    return ($response) ? json_encode(['status' => 200, 'message' => "$message successfully!"]) : json_encode(['status' => 400, 'message' => $message]);  
}

?>