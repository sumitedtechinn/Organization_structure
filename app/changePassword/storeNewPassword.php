<?php

require '../../includes/db-config.php';
session_start();

if(isset($_REQUEST['password'])) {
    $new_password = mysqli_real_escape_string($conn,$_REQUEST['password']);
    $new_password = base64_encode($new_password);

    $update_query = $conn->query("UPDATE `users` SET Password = '$new_password' WHERE role = '".$_SESSION['role']."' AND ID = '".$_SESSION['ID']."'");
    showResponse($update_query,'updated');    
}

function showResponse($response, $message) {
    if ($response) {
        echo json_encode(['status' => 200, 'message' => "Password $message successfully!"]);
    } else {
        echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
    }
}

?>