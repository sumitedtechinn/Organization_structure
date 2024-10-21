<?php

require '../../includes/db-config.php';
session_start();

if (isset($_REQUEST['department_id']) && isset($_REQUEST['organization_id']) && empty($_REQUEST['organization_id'])) {
    $organization = $conn->query("SELECT organization_id FROM `Department` WHERE id = '".$_REQUEST['department_id']."'");
    $organization = mysqli_fetch_column($organization);
    $_REQUEST['organization_id'] = $organization;
}

$branchs = '';$branch_list = '';$department_branch_ids = [];
if (isset($_REQUEST['organization_id'])) {
    $organization_id = mysqli_real_escape_string($conn,$_REQUEST['organization_id']);

    if (isset($_REQUEST['department_id'])) {
        $branch = $conn->query("SELECT branch_id FROM Department WHERE id = '".$_REQUEST['department_id']."'");
        $branch = mysqli_fetch_column($branch);
        $department_branch_ids = json_decode($branch,true);
    }

    if(isset($_REQUEST['branch_id']) && !empty($_REQUEST['branch_id'])) {
        if(is_array($_REQUEST['branch_id'])) {
            $branchs = $_REQUEST['branch_id'];
        } else {
            $branchs = $_REQUEST['branch_id'];
        }
    }

    if (isset($_REQUEST['department_id'])) {
        $branch_list = getDepartmentBranches($organization_id);
    } else {
        $branch_list = getBranchList($organization_id);
    }
}

echo $branch_list;

function getBranchList($organization_id) {
    global $conn;
    global $branchs;
    $option = '<option value="">Select</option>';
    $branch = $conn->query("SELECT ID, Branch_name FROM Branch WHERE Deleted_At IS NULL AND organization_id = '$organization_id'");
    while ($row = mysqli_fetch_assoc($branch)) {
        if (!empty($branchs) && is_array($branchs) && in_array($row['ID'],$branchs)) {
            $option .= '<option value = "'.$row['ID'].'" selected >'.$row['Branch_name'].'</option>'; 
        } elseif(!empty($branchs) && !is_array($branchs) && $row['ID'] == $branchs) {
            $option .= '<option value = "'.$row['ID'].'" selected >'.$row['Branch_name'].'</option>';
        } else {
            $option .= '<option value = "'.$row['ID'].'">'.$row['Branch_name'].'</option>';
        }
    }
    return $option;
}

function getDepartmentBranches() {

    global $conn;
    global $department_branch_ids; 
    global $branchs;
    $option = '<option value="">Select</option>';
    $branch = $conn->query("SELECT ID, Branch_name FROM Branch WHERE Deleted_At IS NULL AND ID IN (".implode(',',$department_branch_ids).")");
    while ($row = mysqli_fetch_assoc($branch)) {
        if (!empty($branchs) && is_array($branchs) && in_array($row['ID'],$branchs)) {
            $option .= '<option value = "'.$row['ID'].'" selected >'.$row['Branch_name'].'</option>'; 
        } elseif(!empty($branchs) && !is_array($branchs) && $row['ID'] == $branchs) {
            $option .= '<option value = "'.$row['ID'].'" selected >'.$row['Branch_name'].'</option>';
        } else {
            $option .= '<option value = "'.$row['ID'].'">'.$row['Branch_name'].'</option>';
        }
    }
    return $option;
}

?>