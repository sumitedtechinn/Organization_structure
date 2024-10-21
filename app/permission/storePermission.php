<?php

require '../../includes/db-config.php';
session_start();

if ( isset($_REQUEST['permission_type']) && isset($_REQUEST['apply_page'])) {

    $apply_page = mysqli_real_escape_string($conn,$_REQUEST['apply_page']);
    if(is_array($_REQUEST['permission_type'])) {
        $permission_type = $_REQUEST['permission_type'];
        $permision_name = [];
        foreach ($permission_type as $value) {
            $result = checkPermission($apply_page,$value);
            if($result != 'not found') {
                $permision_name[] = $result;
            }
        }
        if(!empty($permision_name)) {
            $text = "Permission already exits for " . implode(',', $permision_name);
            showResponse(false,'Duplicate Permissions',$text);
            die;
        }
    } else {
        $permission_type = mysqli_real_escape_string($conn,$_REQUEST['permission_type']);
        $permision_name = '';
        $result = checkPermission($apply_page,$permission_type);
        if($result != 'not found') {
            $permision_name = $result;
        }
        if(!empty($permision_name)) {
            $text = "Permission already exits for " . $permision_name;
            showResponse(false,'Duplicate Permissions',$text);
            die;
        }
    }

    if(isset($_REQUEST['id'])) {
        $update_query = $conn->query("UPDATE permission SET permission_type = '$permission_type' , page = '$apply_page' , Updated_at = CURRENT_TIMESTAMP WHERE ID = '".$_REQUEST['id']."'");
        showResponse($update_query,'updated');
    } else {
        $insert_query = "INSERT INTO `permission`(`permission_type`,`page`) VALUES";
        $query_values = [];
        foreach($permission_type as $id) {
            $query_values[] = "('$id','$apply_page')";
        }
        $insert = $conn->query($insert_query . implode(',',$query_values));
        showResponse($insert,'inserted');
    }
}

function checkPermission($apply_page,$permission_type) {
    global $conn;
    $check = $conn->query("SELECT COUNT(permission.ID) as `count` , Permission_type.Name as `name` FROM `permission` LEFT JOIN Permission_type ON Permission_type.ID = permission.permission_type WHERE permission.page = '$apply_page' AND permission.permission_type = '$permission_type' AND Deleted_at IS NULL");
    $check_data = mysqli_fetch_assoc($check);
    if($check_data['count'] > 0 ) {
        return $check_data['name'];
    } else {
        return "not found";
    }
}

function showResponse($response, $message = "Something went wrong!" , $text = null) {
    if ($response) {
        echo json_encode(['status' => 200, 'message' => "Permission $message successfully!"]);
    } else {
        echo json_encode(['status' => 400, 'message' => $message , 'text' => $text]);
    }
}

?>