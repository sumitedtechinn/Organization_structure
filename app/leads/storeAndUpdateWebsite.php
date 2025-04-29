<?php

require '../../includes/db-config.php';

if(isset($_REQUEST['websiteName']) && isset($_REQUEST['websiteUrl'])) {

    $websiteName = mysqli_real_escape_string($conn,$_REQUEST['websiteName']);
    $websiteUrl = mysqli_real_escape_string($conn,$_REQUEST['websiteUrl']);

    $query = ""; $message = "";
    if (isset($_REQUEST['ID'])) {
        $query = "UPDATE `websiteList` SET `websiteName`='$websiteName',`websiteUrl`='$websiteUrl'WHERE `id` = '$id'";
        $message = "Updated";
    } else {
        $query = "INSERT INTO `websiteList`(`websiteName`, `websiteUrl`) VALUES ('$websiteName','$websiteUrl')";
        $message = "Insert";
    }
    $response = $conn->query($query);
    showResponse($response,"Website $message");
}

function showResponse($response, $message = "Something went wrong!") {
    if ($response) {
        echo json_encode(['status' => 200, 'message' => "$message successfully!"]);
    } else {
        echo json_encode(['status' => 400, 'message' => $message]);
    }
}

?>