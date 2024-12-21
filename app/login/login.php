<?php
ini_set('display_errors', 1); 
require '../../includes/db-config.php';

if (isset($_POST['username']) && isset($_POST['password'])) {
    session_start();
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    if (empty($username) || empty($password)) {
        echo json_encode(['status' => 403, 'message' => 'Fields cannot be empty!']);
        session_destroy();
        exit;
    }
    $password = base64_encode($password);
    $check = $conn->query("SELECT users.* , organization.organization_name as `organization_name`,Branch.Branch_name as `branch_name` , Vertical.Vertical_name as `vertical_name` , Department.department_name as `department_name` FROM `users` LEFT JOIN organization ON organization.id = users.Organization_id LEFT JOIN Branch ON Branch.ID = users.Branch_id LEFT JOIN Vertical ON Vertical.ID = users.Vertical_id LEFT JOIN Department ON Department.id = users.Department_id WHERE users.Email = '$username' AND users.Password = '$password' limit 1");
    if($check->num_rows > 0) {   
        $row = mysqli_fetch_assoc($check);
        $_SESSION = $row;
        $permissions = $conn->query("SELECT permission_id FROM `role_has_permissions` WHERE role_id = '".$_SESSION['role']."'");
        $permissions_arr = [];
        while ($row = mysqli_fetch_assoc($permissions)) {
            $permissions_arr[] = $row['permission_id'];
        }
        $permissions_id = implode(',',$permissions_arr);
        $permission_pages = $conn->query("SELECT CONCAT(pages.Name,' ',Permission_type.Name) AS `permission` FROM `permission` LEFT JOIN pages ON pages.ID = permission.page LEFT JOIN Permission_type ON Permission_type.ID = permission.permission_type WHERE permission.ID IN (" . implode(',',$permissions_arr) . ")");
        $user_permission = [];
        while($row = mysqli_fetch_assoc($permission_pages)) {
            $user_permission[] = $row['permission'];
        }
        $_SESSION['previous_url'] = "";
        $_SESSION['current_url'] = "";
        $_SESSION['permission'] = $user_permission;

        // Set User child
        if($_SESSION['role'] == 2) {
            $allchild_ids = [];
            $allchild_ids[] = $_SESSION['ID'];
            $directChild = $conn->query("SELECT ID FROM `users` WHERE Assinged_Person_id = '".$_SESSION['ID']."' AND Deleted_At IS NULL");
            if($directChild->num_rows > 0) {
                while($row = mysqli_fetch_assoc($directChild)) {
                    $allchild_ids[] = $row['ID'];
                    fetchParentAllChild($row['ID']); 
                }
            }
            $_SESSION['allChildId'] = $allchild_ids;
            echo "<pre>";
            print_r($_SESSION);
            exit;
        }
        echo json_encode(['status' => 200, 'message' => 'Welcome!!!', 'url' => '\organization_structure\organization_layout']);
    } else {
        echo json_encode(['status' => 400, 'message' => 'Invalid credentials!']);
        session_destroy();
    }
} else {
    echo json_encode(['status' => 403, 'message' => 'Forbidden']);
    session_start();
    session_destroy();
}

function fetchParentAllChild($id) {

    global $conn;
    global $allchild_ids;
    $childId = $conn->query("SELECT ID FROM `users` WHERE Assinged_Person_id = '$id' AND Deleted_At IS NULL");
    if($childId->num_rows > 0) {
        while($row = mysqli_fetch_assoc($childId)) {
            $allchild_ids[] = $row['ID'];
            fetchParentAllChild($row['ID']);
        }
    }
}

?> 