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
    $check = $conn->query("SELECT users.* , organization.organization_name as `organization_name`,Branch.Branch_name as `branch_name` , Vertical.Vertical_name as `vertical_name` , Department.department_name as `department_name` , Designation.added_inside as `added_inside` FROM `users` LEFT JOIN organization ON organization.id = users.Organization_id LEFT JOIN Branch ON Branch.ID = users.Branch_id LEFT JOIN Vertical ON Vertical.ID = users.Vertical_id LEFT JOIN Department ON Department.id = users.Department_id LEFT JOIN Designation ON users.Designation_id = Designation.ID WHERE users.Email = '$username' AND users.Password = '$password' limit 1");
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
        }

        //for notification
        $_SESSION['notificationCount'] = 0;
        $_SESSION['numOfTicketNotSeen'] = getNumberOfTicketNotSeen();
        $_SESSION['notificationCount'] = ($_SESSION['numOfTicketNotSeen'] > 0) ? ++$_SESSION['notificationCount'] : $_SESSION['notificationCount'];
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

function getNumberOfTicketNotSeen() {

    global $conn;
    $filter_query = '';
    $user_id = $_SESSION['ID'];
    if ($_SESSION['role'] != '1') {
        $downTheLineUser = getDownTheLineUser(); 
        $department = ($_SESSION['role'] == '2') ? $_SESSION['Department_id'] : getDepartmentData();
        $filter_query .= "AND (ticket_record.raised_by = '$user_id' OR ticket_record.assign_by IN ($downTheLineUser) OR ticket_record.assign_to IN ($downTheLineUser) OR (ticket_record.status = '1' AND ticket_record.department IN ($department)) OR (SELECT COUNT(id) FROM `ticket_history` WHERE ticket_history.ticket_id = ticket_record.id AND (assign_by IN ($downTheLineUser) OR assign_to IN ($downTheLineUser))) > 0)";
    }
    $filter_query .= "AND IF(notifications.ticket_id = ticket_record.id and notifications.user_id = '$user_id',false,true)";
    ## Fetch Record
    $numOfTicketNotSeen_query = "SELECT count(ticket_record.id) as `numofticketrecord` FROM ticket_record LEFT JOIN notifications ON notifications.ticket_id = ticket_record.id AND notifications.user_id = '$user_id' WHERE ticket_record.task_name IS NOT NULL $filter_query";
    $numOfTicketNotSeen = $conn->query($numOfTicketNotSeen_query);
    $numOfTicketNotSeen = mysqli_fetch_column($numOfTicketNotSeen);
    return $numOfTicketNotSeen;
}

function getDownTheLineUser() : string {

    global $conn;
    $downTheLineUser = '';
    if( $_SESSION['role'] == '2') {
        if (isset($_SESSION['allChildId'])) {
            $downTheLineUserList = $_SESSION['allChildId'];
            if (!empty($downTheLineUserList)) {
                $downTheLineUser = implode(',',$downTheLineUserList);
            }
        }
    } elseif ($_SESSION['role'] == '3') {
        $searchQuery = '';
        if ($_SESSION['added_inside'] == '3') {
            $searchQuery .= ($_SESSION['Vertical_id'] == '1' || $_SESSION['Vertical_id'] == '2' || $_SESSION['Vertical_id'] == '3') ? " AND users.Vertical_id IN ('1','2','3')" : " AND users.Vertical_id = '".$_SESSION['Vertical_id']."'";
        }
        if ($_SESSION['added_inside'] == '3' || $_SESSION['added_inside'] == '2') {
            $searchQuery .= "AND users.Branch_id = '".$_SESSION['Branch_id']."'";
        }
        $searchQuery .= "AND users.Organization_id = '".$_SESSION['Organization_id']."'";
        $searchQuery .= "AND Designation.added_inside > '".$_SESSION['added_inside']."'";
        $delete_query = "users.Deleted_At IS NULL";
        $downTheLineUserList = $conn->query("SELECT GROUP_CONCAT(users.ID) AS 'user_ids' FROM `users` LEFT JOIN Designation ON Designation.ID = users.Designation_id WHERE $delete_query $searchQuery");
        $downTheLineUser = mysqli_fetch_column($downTheLineUserList);
        $currentUser = $_SESSION['ID'];
        $downTheLineUser = $currentUser.",". $downTheLineUser;
    }
    return $downTheLineUser;
}

function getDepartmentData() {

    global $conn;
    $user_id = $_SESSION['ID'];
    $searchQuery = '';
    $user_details = $conn->query("SELECT Designation.added_inside as `inside` FROM `users` LEFT JOIN Designation ON Designation.ID = users.Designation_id WHERE role = '3' AND users.ID = '$user_id'");
    $user_details = mysqli_fetch_assoc($user_details); 
    if ($user_details['inside'] == '3') {
        if ($_SESSION['Vertical_id'] == '1' || $_SESSION['Vertical_id'] == '2' || $_SESSION['Vertical_id'] == '3') {
            $searchQuery .= "Vertical_id IN ('1','2','3')";  
        } else {
            $searchQuery .= "Vertical_id = '".$_SESSION['Vertical_id']."'";
        }    
    } elseif ($user_details['inside'] == '2') {
        $searchQuery .= "JSON_SEARCH(branch_id,'all','". $_SESSION['Branch_id'] ."') IS NOT NULL";
    } else {
        $searchQuery .= "organization_id = '" .$_SESSION['Organization_id']. "'";
    }
    $department = $conn->query("SELECT GROUP_CONCAT(id) FROM `Department` WHERE $searchQuery");
    $department = mysqli_fetch_column($department);
    return $department;
}

?> 