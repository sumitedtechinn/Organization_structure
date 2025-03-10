<?php

error_reporting( E_ALL );
require '../../includes/db-config.php';
session_start();

if(isset($_REQUEST['name']) && isset($_REQUEST['department']) && isset($_REQUEST['color'])) {

    $name = mysqli_real_escape_string($conn,$_REQUEST['name']);
    $color = mysqli_real_escape_string($conn,$_REQUEST['color']);
    $department = json_encode($_REQUEST['department']);

    if(!isset($_REQUEST['ID'])) {
        $insert_query = "INSERT INTO `ticket_status`( `name`, `department`, `color`) VALUES ('$name','$department','$color')";
        $insert = $conn->query($insert_query);
        echo showResponse($insert,"Status added");
    } else {
        $id = mysqli_real_escape_string($conn,$_REQUEST['ID']);
        $update_query = "UPDATE `ticket_status` SET `name`= '$name',`department` = '$department',`color` = '$color' WHERE id = '$id'";
        $update = $conn->query($update_query);
        echo showResponse($update,"Status Updated");
    }
}

function showResponse($response, $message = "Something went wrong!") {
    return ($response) ? json_encode(['status' => 200, 'message' => "$message successfully!"]) : json_encode(['status' => 400, 'message' => $message]);  
}

?>