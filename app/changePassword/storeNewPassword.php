<?php

require '../../includes/db-config.php';
session_start();

if(isset($_REQUEST['old_password']) && isset($_REQUEST['new_password']) && isset($_REQUEST['confirm_password'])) {
    
    $old_password = mysqli_real_escape_string($conn,$_REQUEST['old_password']);
    $new_password = mysqli_real_escape_string($conn,$_REQUEST['new_password']);
    $confirm_password = mysqli_real_escape_string($conn,$_REQUEST['confirm_password']);
    if($new_password != $confirm_password) {
        showResponse(false,"Confirm passowrd not matched");
    }
    $new_password = base64_encode($new_password);
    $old_password = base64_encode($old_password);
    $update_query = $conn->query("UPDATE `users` SET Password = '$new_password' WHERE role = '".$_SESSION['role']."' AND ID = '".$_SESSION['ID']."' AND Password = '$old_password'");
    if ($update_query) {
        showResponse(true,'updated');
    } else{
        showResponse(false);
    }   
}

function showResponse($response, $message = "Something went wrong!") {
    if ($response) {
        echo json_encode(['status' => 200, 'message' => "Password $message successfully!"]);
    } else {
        echo json_encode(['status' => 400, 'message' => $message]);
    }
}

?>