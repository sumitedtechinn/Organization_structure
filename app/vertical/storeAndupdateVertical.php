<?php

require '../../includes/db-config.php';
session_start();

if(isset($_POST['name']) && isset($_POST['organization']) && isset($_POST['branch'])) {

    $vertical_name = mysqli_real_escape_string($conn,$_POST['name']);
    $organization = mysqli_real_escape_string($conn,$_POST['organization']);
    $branch_id = [];
    if (is_array($_POST['branch'])) {
        $branch_id = json_encode($_POST['branch']);
    } else {
        $branch_id = mysqli_real_escape_string($conn,$_POST['branch']);
    }
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
            $image_path = $conn->query("SELECT image FROM Vertical WHERE ID ='".$_REQUEST['ID']."'");
            $path_name = mysqli_fetch_column($image_path);
        }
        $checkBranch = checkBranchUpdate();
        if ($checkBranch['status'] == 200) {
            $update_query = $conn->query("UPDATE Vertical SET Vertical.Vertical_name='$vertical_name', Vertical.organization_id = '$organization',Vertical.Branch_id= '$branch_id' ,Vertical.image = '$path_name'  WHERE Vertical.ID ='".$_REQUEST['ID']."'");
            showResponse($update_query,'updated');
        } else {
            echo json_encode($checkBranch);
        }
    } else {
        $checkColorOfNode = $conn->query("SELECT color FROM `Vertical` WHERE color IS NOT NULL LIMIT 1");
        if ($checkColorOfNode->num_rows > 0) {
            $checkColorOfNode = mysqli_fetch_column($checkColorOfNode);
            $insert_query = $conn->query("INSERT INTO `Vertical` (`Vertical_name`,`organization_id`,`Branch_id`,`image`,`color`) VALUES('$vertical_name','$organization','$branch_id','$path_name','$checkColorOfNode')");   
            showResponse($insert_query,'inserted');
        } else {
            $insert_query = $conn->query("INSERT INTO `Vertical` (`Vertical_name`,`organization_id`,`Branch_id`,`image`) VALUES('$vertical_name','$organization','$branch_id','$path_name')");   
            showResponse($insert_query,'inserted');
        }
    }
} else {
    showResponse(false,"Please fill all the input fields");
}

function showResponse($response, $message = "Something went wrong!") {
    if ($response) {
        echo json_encode(['status' => 200, 'message' => "Verticals $message successfully!"]);
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
    $check_department = $conn->query("SELECT COUNT(id) FROM `Department` WHERE vertical_id = '".$_REQUEST['ID']."'");
    if (mysqli_fetch_column($check_department) > 0) {
        $current_branch_ids = $_POST['branch'];
        $db_branch_ids = $conn->query("SELECT Branch_id FROM `Vertical` WHERE ID = '".$_REQUEST['ID']."'");
        $db_branch_ids = json_decode(mysqli_fetch_column($db_branch_ids),true);
        foreach($current_branch_ids as $key=>$value) {
            if(in_array($value,$db_branch_ids)) {
                $key = array_search($value,$db_branch_ids);
                unset($db_branch_ids[$key]);
            }
        }
        if(!empty($db_branch_ids)) {
            return ['status' => 400 , 'message' => "Department assign on this vertical" , 'title' => "Sorry can't update the vertical"];
        } else {
            return ['status'=>200];    
        }
    } else {
        return ['status'=>200];
    }
}
?>