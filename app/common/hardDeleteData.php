<?php

require '../../includes/db-config.php';
session_start();

if (isset($_REQUEST['id']) && isset($_REQUEST['table'])) {

    $id = mysqli_real_escape_string($conn,$_REQUEST['id']);
    $table = mysqli_real_escape_string($conn,$_REQUEST['table']);
    $response = $conn->query("DELETE FROM $table WHERE ID = '$id'");
    if ($response) {
        echo json_encode(['status' => 200, 'message' => "$table removed successfully!"]);
    } else {
        echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
    }
}

?>