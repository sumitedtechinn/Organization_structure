<?php

require '../../includes/db-config.php';
session_start();

if (isset($_REQUEST['color']) && isset($_REQUEST['table'])) {

    $color = mysqli_real_escape_string($conn,$_REQUEST['color']);
    $table = mysqli_real_escape_string($conn,$_REQUEST['table']);
    
    $update_color = $conn->query("UPDATE $table SET color = '$color'");
    showResponse($update_color,$table);
}

function showResponse($response, $message = 'Something went wrong!') {
    if ($response) {
        echo json_encode(['status' => 200, 'message' => "$message updated successfully!"]);
    } else {
        echo json_encode(['status' => 400, 'message' => $message ]);
    }
}

?>