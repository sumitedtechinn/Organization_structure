<?php

require '../../includes/db-config.php';
session_start();

if(isset($_POST['department']) && isset($_POST['organization']) && isset($_POST['vertical']) && isset($_POST['branch'])) {

    $department = mysqli_real_escape_string($conn,$_REQUEST['department']);
    $organization = mysqli_real_escape_string($conn,$_REQUEST['organization']);
    $vertical = mysqli_real_escape_string($conn,$_REQUEST['vertical']);
    $branch = json_encode($_REQUEST['branch']);

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
            $image_path = $conn->query("SELECT logo FROM `Department` WHERE id ='".$_REQUEST['ID']."'");
            $path_name = mysqli_fetch_column($image_path);
        }
        $checkBranch = checkBranchUpdate();
        if ($checkBranch['status'] == 200) {
            $update_query = $conn->query("UPDATE `Department` SET `department_name`='$department',`organization_id`='$organization',`branch_id`='$branch',`vertical_id`='$vertical',`logo`='$path_name' WHERE `id` = '".$_REQUEST['ID']."'");
            showResponse($update_query,'updated');
        } else {
            echo json_encode($checkBranch);
        }
    } else {
        $checkColorOfNode = $conn->query("SELECT color FROM `Department` WHERE color IS NOT NULL LIMIT 1");
        if ($checkColorOfNode->num_rows > 0) {
            $checkColorOfNode = mysqli_fetch_column($checkColorOfNode);
            $insert_query = $conn->query("INSERT INTO `Department`(`department_name`,`organization_id`,`branch_id`,`vertical_id`,`logo`,`color`) VALUES('$department','$organization','$branch','$vertical','$path_name','$checkColorOfNode')");
            showResponse($insert_query,'inserted');
        } else {
            $insert_query = $conn->query("INSERT INTO `Department`(`department_name`,`organization_id`,`branch_id`,`vertical_id`,`logo`) VALUES('$department','$organization','$branch','$vertical','$path_name')");
            showResponse($insert_query,'inserted');
        }
        
    }
} else {
    showResponse(false,"Input field is empty");
}


function showResponse($response, $message = "Something went wrong!") {
    global $result;
    if ($response) {
        echo json_encode(['status' => 200, 'message' => "Department $message successfully!"]);
    } else {
        echo json_encode(['status' => 400, 'message' => $message]);
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

function checkBranchUpdate() {
    global $conn;
    $department_id = $_REQUEST['ID'];
    $check_department = $conn->query("SELECT CASE 
            WHEN EXISTS (SELECT users.ID FROM users WHERE users.Department_id = '$department_id') THEN 'users' 
            WHEN EXISTS (SELECT Vacancies.ID FROM Vacancies WHERE Vacancies.Department_id = '$department_id') THEN 'Vacancies' 
            WHEN EXISTS (SELECT Designation.ID FROM Designation WHERE Designation.department_id = '$department_id') THEN 'Designation' 
            ELSE 'Not Found' 
            END AS result");
    $check_department = mysqli_fetch_assoc($check_department);
    if ($check_department['result'] != 'Not Found') {
        $found_in = ucwords($check_department['result']);
        $current_branch_ids = $_POST['branch'];
        $db_branch_ids = $conn->query("SELECT branch_id FROM `Department` WHERE id = '$department_id'");
        $db_branch_ids = json_decode(mysqli_fetch_column($db_branch_ids),true);
        foreach($current_branch_ids as $key=>$value) {
            if(in_array($value,$db_branch_ids)) {
                $key = array_search($value,$db_branch_ids);
                unset($db_branch_ids[$key]);
            }
        }
        if(!empty($db_branch_ids)) {
            return ['status'=> 400 ,'title'=>"Sorry can't update Department" ,'message'=>"$found_in assign on this Department!"];
        } else {
            return ['status'=>200];    
        }
    } else {
        return ['status'=>200];
    }
}

?>