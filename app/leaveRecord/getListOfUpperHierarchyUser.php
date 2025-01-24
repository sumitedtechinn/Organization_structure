<?php

include '../../includes/db-config.php';
session_start();

$user_list = [];

if($_REQUEST['user_id']) {
    $user_id = mysqli_real_escape_string($conn,$_REQUEST['user_id']);
    $user_details = $conn->query("SELECT users.* , Designation.added_inside as `inside` FROM `users` LEFT JOIN Designation ON users.Designation_id = Designation.ID WHERE users.ID = '$user_id'");
    $user_details = mysqli_fetch_assoc($user_details);
    getUpperHierarchyUser($user_details);
    if ($user_details['role'] == '2') {
        // get all the upper hierarchy user of that department 
        checkUserPresentOnVerticalLevel($user_details);
        checkUserPresentOnBranchLevel($user_details);
        checkUserPresentOnOrganizationLevel($user_details);
    } elseif ($user_details['role'] == '3') {
        if ($user_details['inside'] == '3') {
            checkUserPresentOnBranchLevel($user_details);
            checkUserPresentOnOrganizationLevel($user_details);
        } elseif ($user_details['inside'] == '2') {
            checkUserPresentOnOrganizationLevel($user_details);
        }
    }
    echo makeoptionTag();
}


function getUpperHierarchyUser($user_details) {

    global $user_list;
    global $conn;
    $searchQuery = '';
    if($user_details['role'] == '2') {
        $searchQuery .= "AND Department_id = '".$user_details['Department_id']."'";
        $searchQuery .= "AND Vertical_id = '".$user_details['Vertical_id']."'";
        $searchQuery .= "AND Branch_id = '".$user_details['Branch_id']."'";
        $searchQuery .= "AND Organization_id = '".$user_details['Organization_id']."'";
    } elseif ($user_details['role'] == '3') {
        $searchQuery .= "AND Department_id IS NULL";    
        $searchQuery .= ($user_details['inside'] >= '3') ? "AND Vertical_id = '".$user_details['Vertical_id']."'" : "AND Vertical_id IS NULL";
        $searchQuery .= ($user_details['inside'] >= '2') ? "AND Branch_id = '".$user_details['Branch_id']."'" : "AND Branch_id IS NULL";
        $searchQuery .= ($user_details['inside'] >= '1') ? "AND Organization_id = '".$user_details['Organization_id']."'" : "AND Organization_id IS NULL";
    }
    $delete_query = "Deleted_At IS NULL";
    $upperHierarchyUsers = $conn->query("SELECT `ID`, `Name` , `Email` FROM `users`  WHERE $delete_query $searchQuery AND Hierarchy_value < '".$user_details['Hierarchy_value']."'");
    if ($upperHierarchyUsers->num_rows > 0) {
        while ($upperHierarchyUser = mysqli_fetch_assoc($upperHierarchyUsers)) {
            $user_list[$upperHierarchyUser['ID']] = array(
                'name' => $upperHierarchyUser['Name'] , 
                'email' => $upperHierarchyUser['Email']
            );
        }
    }
}

function checkUserPresentOnVerticalLevel($user_details) {
    
    global $user_list;
    global $conn;
    $searchQuery = '';
    if ( $user_details['Vertical_id'] == '1' || $user_details['Vertical_id'] == '2' || $user_details['Vertical_id'] == '3') {
        $searchQuery .= "AND Vertical_id IN ('1','2','3')";  
    } else {
        $searchQuery .= "AND Vertical_id = '".$user_details['Vertical_id']."'";
    }
    $searchQuery .= "AND Branch_id = '".$user_details['Branch_id']."'";
    $searchQuery .= "AND Organization_id = '".$user_details['Organization_id']."'";
    $userVerticalLevels = $conn->query("SELECT `ID` , `Name` , `Email` FROM `users` WHERE role = '3' AND Department_id IS NULL $searchQuery");
    if ($userVerticalLevels->num_rows > 0) {
        while($userVerticalLevel = mysqli_fetch_assoc($userVerticalLevels)) {
            $user_list[$userVerticalLevel['ID']] = array(
                'name' => $userVerticalLevel['Name'] , 
                'email' => $userVerticalLevel['Email']
            );
        }
    }
}

function checkUserPresentOnBranchLevel($user_details) {

    global $user_list;
    global $conn;
    $searchQuery = '';
    $searchQuery .= "AND Branch_id = '".$user_details['Branch_id']."'";
    $searchQuery .= "AND Organization_id = '".$user_details['Organization_id']."'";
    $userBranchLevels = $conn->query("SELECT `ID` , `Name` , `Email` FROM `users` WHERE role = '3' AND Department_id IS NULL AND Vertical_id IS NULL $searchQuery");
    if( $userBranchLevels->num_rows > 0) {
        while( $userBranchLevel = mysqli_fetch_assoc($userBranchLevels)) {
            $user_list[$userBranchLevel['ID']] = array(
                'name' => $userBranchLevel['Name'] , 
                'email' => $userBranchLevel['Email']
            );
        }
    }
}

function checkUserPresentOnOrganizationLevel($user_details) {

    global $user_list;
    global $conn;
    $searchQuery = '';
    $searchQuery .= "AND Organization_id = '".$user_details['Organization_id']."'";
    $userOrganizationLevels = $conn->query("SELECT `ID` , `Name` , `Email` FROM `users` WHERE role = '3' AND Department_id IS NULL AND Vertical_id IS NULL AND Branch_id IS NULL $searchQuery");
    if( $userOrganizationLevels->num_rows > 0) {
        while( $userOrganizationLevel = mysqli_fetch_assoc($userOrganizationLevels)) {
            $user_list[$userOrganizationLevel['ID']] = array(
                'name' => $userOrganizationLevel['Name'] , 
                'email' => $userOrganizationLevel['Email']
            );
        }
    }
}

function makeoptionTag() : string {

    global $user_list;
    $option = '<option value="">Select</option>';
    if ( isset($_REQUEST['type']) && $_REQUEST['type'] == 'mail_to') {
        $selected_id = (explode('####',$_REQUEST['mail_to']))[1];
        foreach ($user_list as $id => $value) {
            if ($id == $selected_id) {
                $option .= '<option value = "'.$value['email'].'####'.$id.'" selected >'.$value['name'].'</option>';
            } else {
                $option .= '<option value = "'.$value['email'].'####'.$id.'">'.$value['name'].'</option>';
            }
        }
    } elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == 'mail_cc') {
        $notSelect_id = (explode('####',$_REQUEST['mail_toUser']))[1];
        $match_ids = [];
        if (isset($_REQUEST['mail_cc']) && !empty($_REQUEST['mail_cc'])) {
            $selected_ids = json_decode($_REQUEST['mail_cc'],true);
            $i = 0;
            foreach ($selected_ids as  $value) {
                $match_ids[$i] = (explode('####',$value))[1];
                $i++;
            }
        }
        foreach ($user_list as $id => $value) {
            if ($id == $notSelect_id) {
                continue;
            } elseif (in_array($id,$match_ids)) {
                $option .= '<option value = "'.$value['email'].'####'.$id.'" selected >'.$value['name'].'</option>';
            } else {
                $option .= '<option value = "'.$value['email'].'####'.$id.'">'.$value['name'].'</option>';
            }
        }
    }
    return $option;    
}

?>