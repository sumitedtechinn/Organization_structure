<?php
error_reporting(E_ALL);
require '../../includes/db-config.php';
session_start();

$result_list = [];

if(isset($_REQUEST['searchNotification'])) {

    $searchNotification = mysqli_real_escape_string($conn,$_REQUEST['searchNotification']);
    switch ($searchNotification) {
        case 'Ticket':
            $result_list['numOfTicketNotSeen'] = getNumberOfTicketNotSeen();   
            setDataInSession();
            break;
        default:
            $result_list['noResponse'] = "No Search Filed";           
    }
}

echo json_encode(['status'=>200]);

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


function setDataInSession() {

    global $result_list;

    $_SESSION['notificationCount'] = 0;
    $_SESSION['numOfTicketNotSeen'] = $result_list['numOfTicketNotSeen'];
    $_SESSION['notificationCount'] = ($_SESSION['numOfTicketNotSeen'] > 0) ? ++$_SESSION['notificationCount'] : $_SESSION['notificationCount'];
}