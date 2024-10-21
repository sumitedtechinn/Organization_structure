<?php

require '../../includes/db-config.php';
session_start();

if (isset($_REQUEST['name']) && isset($_REQUEST['permissions']) && isset($_REQUEST['id'])) {

    $role_name = mysqli_real_escape_string($conn,$_REQUEST['name']);
    $current_permissions = $_REQUEST['permissions'];
    $role_id = $_REQUEST['id'];

    $previous_permission = $conn->query("SELECT * FROM `role_has_permissions` WHERE role_id = '$role_id'");
    if( $previous_permission->num_rows > 0) {
        $previous_permission_list = []; $inserted_data = []; $deleted_data = [];
        while($row = mysqli_fetch_assoc($previous_permission)) {
            $previous_permission_list['previous_'.$row['permission_id']] = $row['permission_id'];
        }
        foreach ($current_permissions as $key => $value) {
            if(in_array($value,$previous_permission_list)) {
                unset($previous_permission_list['previous_'.$value]);
            } else {
                $inserted_data[] = $value;
            }
        }
        $deleted_data = $previous_permission_list;
        if(!empty($deleted_data)) {
            $deleted_query = deletePermission($deleted_data,$role_id);
            if(!$deleted_query) {
                showResponse($deleted_query);
                die;
            }
        }
        if(!empty($inserted_data)) {
            $insert_permission = insertPermission($inserted_data,$role_id);
            if(!$insert_permission) {
                showResponse($insert_permission);
                die;
            }
        }
        showResponse(true,'Updated');
    } else {
        $insert_permission = insertPermission($current_permissions,$role_id);
        showResponse($insert_permission,'Updated');
    }
}

function deletePermission($deleted_data,$role_id) {
    global $conn;
    $deleted_query = $conn->query("DELETE FROM role_has_permissions WHERE permission_id IN (".implode(',',$deleted_data).") AND role_id = '$role_id'");
    return $deleted_query;
}

function insertPermission($permission,$role) {
    global $conn;
    $insert_query = "INSERT INTO `role_has_permissions`(`permission_id`, `role_id`) VALUES";
    $query_values = [];
    foreach($permission as $id) {
        $query_values[] = "('$id','$role')";
    }
    $insert_permission = $conn->query($insert_query . implode(',',$query_values));
    return $insert_permission;
}

function showResponse($response, $message=null) {
    if ($response) {
        echo json_encode(['status' => 200, 'message' => "Permission $message successfully!"]);
    } else {
        echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
    }
}

?>