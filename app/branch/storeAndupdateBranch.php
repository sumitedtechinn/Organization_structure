<?php

require '../../includes/db-config.php';
session_start();

if(isset($_POST['name']) && isset($_POST['contact_number']) && isset($_REQUEST['country_code']) && isset($_POST['start_date']) && isset($_POST['pin_code']) && isset($_REQUEST['organization']) &&  isset($_POST['country']) && isset($_POST['state']) && isset($_POST['city']) && isset($_POST['address'])) {

    $branch_name = mysqli_real_escape_string($conn,$_POST['name']);
    $organization = mysqli_real_escape_string($conn,$_REQUEST['organization']);
    $contact = mysqli_real_escape_string($conn,$_POST['contact_number']);
    $country_code = mysqli_real_escape_string($conn,$_POST['country_code']);
    $start_date = mysqli_real_escape_string($conn,$_POST['start_date']);
    $pin_code = mysqli_real_escape_string($conn,$_POST['pin_code']);
    $country = mysqli_real_escape_string($conn,$_POST['country']);
    $state = mysqli_real_escape_string($conn,$_POST['state']);
    $city = mysqli_real_escape_string($conn,$_POST['city']);
    $address = mysqli_real_escape_string($conn,$_POST['address']);

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
            $image_path = $conn->query("SELECT image FROM Branch WHERE ID ='".$_REQUEST['ID']."'");
            $path_name = mysqli_fetch_column($image_path);
        }
        $update_query = $conn->query("UPDATE Branch SET Branch.Branch_name = '$branch_name', Branch.organization_id = '$organization', Branch.Contact = '$contact' , Branch.Country_code = '$country_code', Branch.Start_date = '$start_date' , Branch.Pin_code = '$pin_code' , Branch.Country = '$country' , Branch.State = '$state' , Branch.City = '$city' , Branch.Address = '$address' , Branch.image = '$path_name' WHERE Branch.ID = '".$_REQUEST['ID']."'");
        showResponse($update_query,'updated');
    } else {
        $checkColorOfNode = $conn->query("SELECT color FROM `Branch` WHERE color IS NOT NULL LIMIT 1");
        if ($checkColorOfNode->num_rows > 0) {
            $checkColorOfNode = mysqli_fetch_column($checkColorOfNode);
            $insert_query = $conn->query("INSERT INTO `Branch`(`Branch_name`,`organization_id`,`Contact`,`Country_code`,`Start_date`,`Pin_code`,`Country`,`State`,`city`,`Address`,`image`,`color`) VALUES ('$branch_name','$organization',$contact,$country_code,'$start_date',$pin_code,'$country','$state','$city','$address','$path_name','$checkColorOfNode')");
            showResponse($insert_query,'inserted');
        } else {
            $insert_query = $conn->query("INSERT INTO `Branch`(`Branch_name`,`organization_id`,`Contact`,`Country_code`,`Start_date`,`Pin_code`,`Country`,`State`,`city`,`Address`,`image`) VALUES ('$branch_name','$organization',$contact,$country_code,'$start_date',$pin_code,'$country','$state','$city','$address','$path_name')");
            showResponse($insert_query,'inserted');
        }
    }
}

function showResponse($response, $message) {
    if ($response) {
        echo json_encode(['status' => 200, 'message' => "Branch $message successfully!"]);
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