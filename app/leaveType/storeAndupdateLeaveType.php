<?php

require '../../includes/db-config.php';
session_start();

if (isset($_REQUEST['leave_name']) && isset($_REQUEST['numofleave']) && isset($_REQUEST['leave_carryforward'])) {

    $leaveName = mysqli_real_escape_string($conn,$_REQUEST['leave_name']);
    $numofleave = mysqli_real_escape_string($conn,$_REQUEST['numofleave']);
    $leaveCarryForward = mysqli_real_escape_string($conn,$_REQUEST['leave_carryforward']);

    if (isset($_REQUEST['id'])) {
        $update = $conn->query("UPDATE leaveType SET leaveName = '$leaveName' , numOfLeave = '$numofleave' , leaveCarryForward = '$leaveCarryForward' WHERE id = '".$_REQUEST['id']."'");
        showResponse($update,'Updated');
    } else {
        $insert = $conn->query("INSERT INTO leaveType (`leaveName`, `numOfLeave`, `leaveCarryForward`) VALUES ('$leaveName','$numofleave','$leaveCarryForward')");
        showResponse($insert,'added');
    }
}

function showResponse($response, $message = "Something went wrong!") {
    if ($response) {
        echo json_encode(['status' => 200, 'message' => "Leave type $message successfully!"]);
    } else {
        echo json_encode(['status' => 400, 'message' => $message]);
    }
}
?>