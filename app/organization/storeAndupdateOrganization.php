<?php

require '../../includes/db-config.php';
session_start();

if (isset($_POST['name']) && isset($_POST['start_date'])) {

    $organization = mysqli_real_escape_string($conn,$_POST['name']);
    $start_date = mysqli_real_escape_string($conn,$_POST['start_date']);

    $path_name = null;
    if(isset($_FILES['image']) && !empty($_FILES['image']['name'])) {
        $image_name = $_FILES['image']['name'];
        $path_name = checkAndUploadImage($image_name);
        if(!$path_name) {
            showResponse(false,'File must be image');
            die;
        }
    }

    if (isset($_REQUEST["ID"])) {
        if (is_null($path_name)) {
            $image_path = $conn->query("SELECT logo FROM `organization` WHERE id ='".$_REQUEST['ID']."'");
            $path_name = mysqli_fetch_column($image_path);
        }
        $update_query = $conn->query("UPDATE `organization` SET `organization_name`='$organization',`start_date`='$start_date',`logo`='$path_name' WHERE id = '".$_REQUEST['ID']."'");
        showResponse($update_query,'updated');
    } else {
        $checkColorOfNode = $conn->query("SELECT color FROM `organization` WHERE color IS NOT NULL LIMIT 1");
        if ($checkColorOfNode->num_rows > 0) {
            $checkColorOfNode = mysqli_fetch_column($checkColorOfNode);
            $insert_query = $conn->query("INSERT INTO `organization`(`organization_name`,`start_date`, `logo`, `color`) VALUES ('$organization','$start_date','$path_name','$checkColorOfNode')");
            showResponse($insert_query,'inserted');
        } else {
            $insert_query = $conn->query("INSERT INTO `organization`(`organization_name`,`start_date`,`logo`) VALUES ('$organization','$start_date','$path_name')");
            showResponse($insert_query,'inserted');
        }
    }
}

function showResponse($response, $message) {
    if ($response) {
        echo json_encode(['status' => 200, 'message' => "Organization $message successfully!"]);
    } else {
        echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
    }
}

function checkAndUploadImage($image_name) : bool|string {
    $extension = substr($image_name,strlen($image_name)-4,strlen($image_name));
    $allowed_extensions = array(".jpg","jpeg",".png",".gif");
    if(in_array($extension,$allowed_extensions)) {
        move_uploaded_file($_FILES['image']['tmp_name'],'../../uploads/usersImage/' . $image_name);
        return '../../uploads/usersImage/' . $image_name;
    } else {
        return false;
    }
}

?>