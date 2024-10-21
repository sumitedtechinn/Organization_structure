<?php

require '../../includes/db-config.php';
session_start();

if (isset($_REQUEST['id'])) {
    if (isset($_REQUEST['numOfClosure'])) {
        $numOfClosure = mysqli_real_escape_string($conn,$_REQUEST['numOfClosure']);
        
        $numOfClosureCompleted = $conn->query("SELECT COUNT(ID) FROM Closure_details WHERE Projection_id = '".$_REQUEST['id']."' and doc_closed IS NOT NULL AND Deleted_At IS NULL");
        $numOfClosureCompleted = mysqli_fetch_column($numOfClosureCompleted);
        if($numOfClosure-$numOfClosureCompleted >= 0) {
            $update_query = $conn->query("UPDATE `Projection` SET `numOfClosure`='$numOfClosure' WHERE ID = '".$_REQUEST['id']."'");
            showResponse($update_query,'updated');
        } else {
            showResponse(false,"Number of closure is too less");
        }
    }
} else {
    if (isset($_REQUEST['projection_type']) && isset($_REQUEST['user']) && isset($_REQUEST['numOfClosure']) && isset($_REQUEST['month'])) {
    
        $projection_type = mysqli_real_escape_string($conn,$_REQUEST['projection_type']);
        $user = mysqli_real_escape_string($conn,$_REQUEST['user']);
        $numOfClosure = mysqli_real_escape_string($conn,$_REQUEST['numOfClosure']);
        $month = mysqli_real_escape_string($conn,$_REQUEST['month']);

        $projection_type_details = $conn->query("SELECT * FROM `Projection_type` WHERE ID = '$projection_type'");
        $projection_type_details = mysqli_fetch_assoc($projection_type_details);

        $designation_id = $conn->query("SELECT Designation_id FROM users WHERE ID = '$user'");
        $designation_id = mysqli_fetch_column($designation_id);

        $check = $conn->query("SELECT COUNT(ID) FROM Projection WHERE projectionType = '$projection_type' AND user_id = '$user' AND month = '$month'");
        $check_duplicate = mysqli_fetch_column($check);

        if($check_duplicate == 0 ) {
            $insert_query = $conn->query("INSERT INTO `Projection`(`projectionType`,`organization_id`,`branch_id`,`vertical_id`, `department_id`,`designation_id`,`user_id`,`numOfClosure`,`month`) VALUES ('$projection_type','".$projection_type_details['organization_id']."','".$projection_type_details['branch_id']."','".$projection_type_details['vertical_id']."','".$projection_type_details['department_id']."','$designation_id','$user','$numOfClosure','$month')");
            showResponse($insert_query,'inserted');
        } else {
            showResponse(false,"Duplicate");
        }
    }     
}


function showResponse($response, $message = "Something went wrong!") {
    if ($response) {
        echo json_encode(['status' => 200, 'message' => "Projection $message successfully!"]);
    } else {
        echo json_encode(['status' => 400, 'message' => $message]);
    }
}

?>