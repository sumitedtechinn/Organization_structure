<?php 

require '../../includes/db-config.php';
session_start();

if (isset($_REQUEST['name']) && isset($_REQUEST['user_email']) && isset($_REQUEST['contact_number']) && isset($_REQUEST['country_code']) && isset($_REQUEST['doj']) && isset($_FILES['image']) && isset($_REQUEST['pin_code']) && isset($_REQUEST['country']) && isset($_REQUEST['state']) && isset($_REQUEST['city']) && isset($_REQUEST['address'])) {

    $user_name = mysqli_real_escape_string($conn,$_REQUEST['name']);
    $email = mysqli_real_escape_string($conn,$_REQUEST['user_email']);
    $contact = mysqli_real_escape_string($conn,$_REQUEST['contact_number']);
    $country_code = mysqli_real_escape_string($conn,$_REQUEST['country_code']);
    $doj = mysqli_real_escape_string($conn,$_REQUEST['doj']);
    $pin_code = mysqli_real_escape_string($conn,$_REQUEST['pin_code']);
    $country = mysqli_real_escape_string($conn,$_REQUEST['country']);
    $state = mysqli_real_escape_string($conn,$_REQUEST['state']);
    $city = mysqli_real_escape_string($conn,$_REQUEST['city']);
    $address = mysqli_real_escape_string($conn,$_REQUEST['address']);
    $role_id = mysqli_real_escape_string($conn,$_REQUEST['role']);
    $image_name = $_FILES['image']['name'];
    $password = str_replace("-","",$doj);
    $password = base64_encode($password);

    $path_name = null;
    if(isset($_FILES['image']) && !empty($_FILES['image']['name'])) {
        $image_name = $_FILES['image']['name'];
        $path_name = checkAndUploadImage($image_name);
        if(!$path_name) {
            showResponse(false,'File must be image');
            die;
        }
    }
    if (isset($_REQUEST['ID'])) {
        if (is_null($path_name)) {
            $image_path = $conn->query("SELECT Photo FROM users WHERE ID ='".$_REQUEST['ID']."'");
            $path_name = mysqli_fetch_column($image_path);
            if (empty($path_name)) {
                $path_name = "/../../assets/images/sample_user.jpeg";
            }
        }
        $update_query = $conn->query("UPDATE users SET Name= '$user_name' , Email= '$email' , Mobile = '$contact' , Country_code = '$country_code' , DOJ = '$doj',Password = '$password' , Address = '$address' , Country = '$country' , State = '$state' , City = '$city' , Photo = '$path_name' , Updated_On = CURRENT_TIMESTAMP WHERE ID = '".$_REQUEST['ID']."'");
        showResponse($update_query,'Updated');
    } else {
        if (is_null($path_name)) {
            $path_name = "/../../assets/images/sample_user.jpeg";
        }
        $insert_query = $conn->query("INSERT INTO `users`(`Name`, `role`, `Email`, `Mobile`, `Country_code`, `DOJ`, `Department_id`, `Designation_id`, `Hierarchy_value`, `Vertical_id`, `Branch_id`, `Organization_id`, `Assinged_Person_id`, `Password`, `Address`, `Pincode`, `State`, `City`, `Country`, `Photo`) VALUES ('$user_name','$role_id','$email','$contact','$country_code','$doj',null,null,null,null,null,null,null,'$password','$address','$pin_code','$state','$city','$country','$path_name')");
        showResponse($insert_query,'inserted');
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

function showResponse($response, $message = "Something went wrong!") {
    if ($response) {
        echo json_encode(['status' => 200, 'message' => "User $message successfully!"]);
    } else {
        echo json_encode(['status' => 400, 'message' => $message]);
    }
}

?>
