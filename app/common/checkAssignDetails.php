<?php

$branch_details = [];
include '../../includes/db-config.php';
session_start();

$assing_response = '';

if (isset($_REQUEST['id']) && isset($_REQUEST['search'])) {

    if( $_REQUEST['search'] == 'Branch') {
        $assing_response = checkAssignBranch();
    } else if ($_REQUEST['search'] == 'Vertical') {
        $assing_response = checkVerticalAssign();
    } elseif ($_REQUEST['search'] == 'organization') {
        $assing_response = checkAssignOrganization();
    } elseif ($_REQUEST['search'] == 'Designation') {
        $assing_response = checkAssignDesignation();
    } elseif ($_REQUEST['search'] == 'Department') {
        $assing_response = checkAssignDepartment();
    } elseif ($_REQUEST['search'] == 'users') {
        $assing_response = checkAssignUser();
    } elseif ($_REQUEST['search'] == 'user_role') {
        $assing_response = checkUserAssignOrganization();
    } elseif ($_REQUEST['search'] == 'Projection_type') {
        $assing_response = checkAssignProjectionType(); 
    } elseif ($_REQUEST['search'] == 'ticket_status') {
        $assing_response = checkAssignTicketStatus();
    } elseif ($_REQUEST['search'] == 'ticket_category') {
        $assing_response = checkAssignTicketCategory();
    }
}

echo $assing_response;

function checkAssignOrganization() {

    global $conn;
    $check_branch = $conn->query("SELECT COUNT(ID) FROM `Branch` WHERE organization_id = '".$_REQUEST['id']."' AND Deleted_At IS NULL");
    if (mysqli_fetch_column($check_branch) > 0) {
        return (json_encode(['status'=> 400,'title'=>"Organization can't delete",'text'=>"Branchs assign to this!"]));
    }
    return json_encode(['status' => 200]);
}

function checkAssignBranch() {

    global $conn;
    $check_vertical = $conn->query("SELECT COUNT(ID) FROM `Vertical` WHERE Branch_id LIKE '%".$_REQUEST['id']."%' AND Deleted_At IS NULL");
    if (mysqli_fetch_column($check_vertical) > 0) {
        if (isset($_REQUEST['type']) && isset($_REQUEST['organization_id'])) {
            $currentSelectedOrganization_id = $_REQUEST['organization_id'];
            $assignOrganization_id = $conn->query("SELECT organization_id FROM `Branch` WHERE ID = '".$_REQUEST['id']."'");
            $assignOrganization_id = mysqli_fetch_column($assignOrganization_id);
            if ($currentSelectedOrganization_id == $assignOrganization_id) {
                return json_encode(['status' => 200]);    
            } else {
                return (json_encode(['status'=> 400 ,'title'=>"Sorry can't update Organization" ,'text'=>"Verticals assign to this!",'previous' => $assignOrganization_id]));
            }
        } else {
            return (json_encode(['status'=> 400 ,'title'=>"Branch can't delete" , 'text'=>"Verticals assign to this!"]));
        }
    }
    return json_encode(['status' => 200]);
}

function checkVerticalAssign() {

    global $conn;
    $check_department = $conn->query("SELECT COUNT(id) FROM `Department` WHERE vertical_id = '".$_REQUEST['id']."' AND Deleted_At IS NULL");
    if (mysqli_fetch_column($check_department) > 0) {
        if(isset($_REQUEST['type']) == "update_organization" && isset($_REQUEST['organization_id'])) {
            $currentSelectedOrganization_id = $_REQUEST['organization_id'];
            $assignOrganization_id = $conn->query("SELECT organization_id FROM `Vertical` WHERE ID = '".$_REQUEST['id']."'");
            $assignOrganization_id = mysqli_fetch_column($assignOrganization_id);
            if ($currentSelectedOrganization_id == $assignOrganization_id) {
                return json_encode(['status' => 200 , 'message' => "same organization"]);    
            } else {
                return (json_encode(['status'=> 400 ,'title'=>"Sorry can't update Organization" ,'text'=>"Department assign on this vertical",'previous' => $assignOrganization_id]));
            }
        } else {
            return json_encode(['status'=> 400 , 'title'=>"Vertical can't delete" , 'text'=>"Department assign to this!"]);
        }
    } else {
        if(isset($_REQUEST['type']) && isset($_REQUEST['organization_id'])) {
            $currentSelectedOrganization_id = $_REQUEST['organization_id'];
            $assignOrganization_id = $conn->query("SELECT organization_id FROM `Vertical` WHERE ID = '".$_REQUEST['id']."'");
            $assignOrganization_id = mysqli_fetch_column($assignOrganization_id);
            if ($currentSelectedOrganization_id == $assignOrganization_id) {
                return json_encode(['status' => 200 , 'message' => "same organization"]);    
            } else {
                return json_encode(['status' => 200 , 'message' => "vertical not mapped"]);
            }
        } else {
            return json_encode(['status' => 200]);
        }
    }
}

function checkAssignDepartment() {
    global $conn;
    $department_id = $_REQUEST['id'];
    $check_department = $conn->query("SELECT CASE 
            WHEN EXISTS (SELECT users.ID FROM users WHERE users.Department_id = '$department_id') THEN 'users' 
            WHEN EXISTS (SELECT Vacancies.ID FROM Vacancies WHERE Vacancies.Department_id = '$department_id') THEN 'Vacancies' 
            WHEN EXISTS (SELECT Designation.ID FROM Designation WHERE Designation.department_id = '$department_id') THEN 'Designation' 
            ELSE 'Not Found' 
            END AS result");
    $check_department = mysqli_fetch_assoc($check_department);
    if ($check_department['result'] != 'Not Found') {
        $found_in = ucwords($check_department['result']);
        if(isset($_REQUEST['type']) && $_REQUEST['type'] == "update_organization" && isset($_REQUEST['organization_id'])) {
            $currentSelectedOrganization_id = $_REQUEST['organization_id'];
            $assignOrganization_id = $conn->query("SELECT organization_id FROM `Department` WHERE ID = '$department_id'");
            $assignOrganization_id = mysqli_fetch_column($assignOrganization_id);
            if ($currentSelectedOrganization_id == $assignOrganization_id) {
                return json_encode(['status' => 200 , 'message' => "same organization"]);    
            } else {
                return (json_encode(['status'=> 400 ,'title'=>"Sorry can't update Organization" ,'text'=>"$found_in assign on this Department!",'previous' => $assignOrganization_id]));
            }
        } elseif ( isset($_REQUEST['type']) && $_REQUEST['type'] == "update_vertical" && isset($_REQUEST['vertical_id'])) {
            $currentVertical_id = $_REQUEST['vertical_id'];
            $assignVertical_id = $conn->query("SELECT vertical_id FROM `Department` WHERE ID = '$department_id'");
            $assignVertical_id = mysqli_fetch_column($assignVertical_id);
            if($currentVertical_id == $assignVertical_id) {
                return json_encode(['status' => 200 , 'message' => "same vertical"]);
            } else {
                return (json_encode(['status'=> 400 ,'title'=>"Sorry can't update vertical" ,'text'=>"$found_in assign on this Department!",'previous' => $assignVertical_id]));
            }
        } else {
            return json_encode(['status'=> 400 , 'title'=>"Department can't delete" , 'text'=>"$found_in assign on this Department!"]);
        }
    } else {
        if(isset($_REQUEST['type']) && isset($_REQUEST['organization_id'])) {
            $currentSelectedOrganization_id = $_REQUEST['organization_id'];
            $assignOrganization_id = $conn->query("SELECT organization_id FROM `Department` WHERE ID = '$department_id'");
            $assignOrganization_id = mysqli_fetch_column($assignOrganization_id);
            if ($currentSelectedOrganization_id == $assignOrganization_id) {
                return json_encode(['status' => 200 , 'message' => "same organization"]);    
            } else {
                return json_encode(['status' => 200 , 'message' => "Department not mapped"]);
            }
        } elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == "update_vertical" && isset($_REQUEST['vertical_id'])) {
            $currentVertical_id = $_REQUEST['vertical_id'];
            $assignVertical_id = $conn->query("SELECT vertical_id FROM `Department` WHERE ID = '$department_id'");
            $assignVertical_id = mysqli_fetch_column($assignVertical_id);
            if($currentVertical_id == $assignVertical_id) {
                return json_encode(['status' => 200 , 'message' => "same vertical"]);
            } else {
                return json_encode(['status' => 200 , 'message' => "Department not mapped"]);
            }
        } else {
            return json_encode(['status' => 200]);
        }
    }
}

function checkAssignDesignation() {

    global $conn;
    $designation_id = mysqli_real_escape_string($conn,$_REQUEST['id']);
    $check_designation = $conn->query("SELECT CASE 
    WHEN EXISTS (SELECT users.ID FROM users WHERE users.Designation_id = '$designation_id') THEN 'users' 
    WHEN EXISTS (SELECT Vacancies.ID FROM Vacancies WHERE Vacancies.Designation_id = '$designation_id') THEN 'Vacancies'  
    ELSE 'Not Found' 
    END AS result");
    $check_designation = mysqli_fetch_assoc($check_designation);
    if ($check_designation['result'] != 'Not Found') {
        $found_in = ucwords($check_designation['result']);
        return json_encode(['status'=> 400 , 'title'=>"Designation can't delete", 'text'=>"$found_in assign on this Designation!"]);
    } else {
        return json_encode(['status'=>200]);
    }
}

function checkAssignUser(){

    global $conn;
    $user_id = mysqli_real_escape_string($conn,$_REQUEST['id']);
    $check_user = $conn->query("SELECT COUNT(ID) FROM `users` WHERE Assinged_Person_id = '$user_id'");
    if (mysqli_fetch_column($check_user) > 0) {
        if( isset($_REQUEST['type']) &&  $_REQUEST['type'] == "update_branch" && isset($_REQUEST['branch_id'])) {
            $currentBranch_id = $_REQUEST['branch_id'];
            $assignBranch_id = $conn->query("SELECT Branch_id FROM users WHERE ID = '$user_id'");
            $assignBranch_id = mysqli_fetch_column($assignBranch_id);
            if ($currentBranch_id == $assignBranch_id) {
                return json_encode(['status' => 200 , 'message' => "same branch"]);    
            } else {
                return (json_encode(['status'=> 400 ,'title'=>"Sorry can't update Branch" ,'text'=>"User assign as parent!",'previous' => $assignBranch_id]));
            }
        } elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == "update_vertical" && isset($_REQUEST['vertical_id'])) {
            $currentVertical_id = $_REQUEST['vertical_id'];
            $assignVertical_id = $conn->query("SELECT Vertical_id FROM users WHERE ID = '$user_id'");
            $assignVertical_id = mysqli_fetch_column($assignVertical_id); 
            if ($currentVertical_id == $assignVertical_id) {
                return json_encode(['status' => 200 , 'message' => "same vertical"]);    
            } else {
                return (json_encode(['status'=> 400 ,'title'=>"Sorry can't update Vertical" ,'text'=>"User assign as parent!",'previous' => $assignVertical_id]));
            }
        } elseif ( isset($_REQUEST['type']) && $_REQUEST['type'] == "update_designation" && isset($_REQUEST['designation_id'])) {
            $currentDesignation = explode("_",$_REQUEST['designation_id']);
            $currentDesignation_id = $currentDesignation[0];
            $assign_id = $conn->query("SELECT Designation_id,Hierarchy_value FROM users WHERE ID = '$user_id'");
            $assign_id = mysqli_fetch_assoc($assign_id);
            $assignDesgnation_id = $assign_id['Designation_id'];
            $assignHierarchy_value = $assign_id['Hierarchy_value'];
            if($currentDesignation_id == $assignDesgnation_id) {
                return json_encode(['status' => 200 , 'message' => "same designation"]);
            } else {
                return (json_encode(['status'=> 400 ,'title'=>"Sorry can't update Designation" ,'text'=>"User assign as parent!",'previous' => $assignDesgnation_id .'_' . $assignHierarchy_value]));
            }
        } elseif ( isset($_REQUEST['type']) && $_REQUEST['type'] == "update_department" && isset($_REQUEST['department_id'])) {
            $currentDepartment_id = $_REQUEST['department_id'];
            $assignDepartment_id = $conn->query("SELECT Department_id FROM users WHERE ID = '$user_id'");
            $assignDepartment_id = mysqli_fetch_column($assignDepartment_id);
            if ($currentDepartment_id == $assignDepartment_id) {
                return json_encode(['status' => 200 , 'message' => "same department"]);
            } else {
                return (json_encode(['status'=> 400 ,'title'=>"Sorry can't update Department" ,'text'=>"User assign as parent!",'previous' => $assignDepartment_id]));
            }
        }  elseif ( isset($_REQUEST['type']) && $_REQUEST['type'] == "update_reporting" && isset($_REQUEST['parent_id'])) {
            $currentParent_id = mysqli_real_escape_string($conn,$_REQUEST['parent_id']);
            $assignParent_id = $conn->query("SELECT Assinged_Person_id FROM users WHERE ID = '$user_id'");
            $assignParent_id = mysqli_fetch_column($assignParent_id);
            if ($currentParent_id == $assignParent_id) {
                return json_encode(['status' => 200 , 'message' => "same reporting"]);
            } else {
                return (json_encode(['status'=> 400 ,'title'=>"Sorry can't update Assign Person" ,'text'=>"User assign as parent!",'previous' => $assignParent_id]));
            }
        } elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == "update_organization" && isset($_REQUEST['organization_id'])) {
            $currentOrganization_id = $_REQUEST['organization_id'];
            $assignOrganization_id = $conn->query("SELECT Organization_id FROM users WHERE ID = '$user_id'");
            $assignOrganization_id = mysqli_fetch_column($assignOrganization_id);
            if ($currentOrganization_id == $assignOrganization_id) {
                return json_encode(['status' => 200 , 'message' => "same organization"]);
            } else {
                return (json_encode(['status'=> 400 ,'title'=>"Sorry can't update Organization" ,'text'=>"User assign as parent!",'previous' => $assignOrganization_id]));
            }
        } else {
            return (json_encode(['status'=> 400,'title'=>"User can't delete",'text'=>"Users assign as parent!"]));
        }
    } else {
        if( isset($_REQUEST['type']) && $_REQUEST['type'] == "update_branch" && isset($_REQUEST['branch_id'])) {
            $currentBranch_id = $_REQUEST['branch_id'];
            $assignBranch_id = $conn->query("SELECT Branch_id FROM users WHERE ID = '$user_id'");
            $assignBranch_id = mysqli_fetch_column($assignBranch_id);
            if ($currentBranch_id == $assignBranch_id) {
                return json_encode(['status' => 200 , 'message' => "same branch"]);    
            } else {
                return json_encode(['status' => 200 , 'message' => "User not assign as parent"]);
            }
        } elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == "update_vertical" && isset($_REQUEST['vertical_id'])) {
            $currentVertical_id = $_REQUEST['vertical_id'];
            $assignVertical_id = $conn->query("SELECT Vertical_id FROM users WHERE ID = '$user_id'");
            $assignVertical_id = mysqli_fetch_column($assignVertical_id);
            if ($currentVertical_id == $assignVertical_id) {
                return json_encode(['status' => 200 , 'message' => "same vertical"]);    
            } else {
                return json_encode(['status' => 200 , 'message' => "User not assign as parent"]);
            }
        } elseif ( isset($_REQUEST['type']) && $_REQUEST['type'] == "update_designation" && isset($_REQUEST['designation_id'])) {
            list($currentDesignation_id,$hierarchy_value) = explode("_",mysqli_real_escape_string($conn,$_REQUEST['designation_id']));
            $assignDesgnation_id = $conn->query("SELECT Designation_id FROM users WHERE ID = '$user_id'");
            $assignDesgnation_id = mysqli_fetch_column($assignDesgnation_id);
            if($currentDesignation_id == $assignDesgnation_id) {
                return json_encode(['status' => 200 , 'message' => "same designation"]);
            } else {
                return (json_encode(['status'=> 200 ,'message' => "User not assign as parent"]));
            }
        } elseif ( isset($_REQUEST['type']) && $_REQUEST['type'] == "update_department" && isset($_REQUEST['department_id'])) {
            $currentDepartment_id = $_REQUEST['department_id'];
            $assignDepartment_id = $conn->query("SELECT Department_id FROM users WHERE ID = '$user_id'");
            $assignDepartment_id = mysqli_fetch_column($assignDepartment_id);
            if ($currentDepartment_id == $assignDepartment_id) {
                return json_encode(['status' => 200 , 'message' => "same department"]);
            } else {
                return json_encode(['status' => 200 , 'message' => "User not assign as parent"]);
            }
        } elseif ( isset($_REQUEST['type']) && $_REQUEST['type'] == "update_reporting" && isset($_REQUEST['parent_id'])) {
            $currentParent_id = mysqli_real_escape_string($conn,$_REQUEST['parent_id']);
            $assignParent_id = $conn->query("SELECT Assinged_Person_id FROM users WHERE ID = '$user_id'");
            $assignParent_id = mysqli_fetch_column($assignParent_id);
            if ($currentParent_id == $assignParent_id) {
                return json_encode(['status' => 200 , 'message' => "same reporting"]);
            } else {
                return (json_encode(['status'=> 200 ,'message' => "User not assign as parent"]));
            }
        } elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == "update_organization" && isset($_REQUEST['organization_id'])) {
            $currentOrganization_id = $_REQUEST['organization_id'];
            $assignOrganization_id = $conn->query("SELECT Organization_id FROM users WHERE ID = '$user_id'");
            $assignOrganization_id = mysqli_fetch_column($assignOrganization_id);
            if ($currentOrganization_id == $assignOrganization_id) {
                return json_encode(['status' => 200 , 'message' => "same organization"]);
            } else {
                return (json_encode(['status'=> 200 ,'message' => "User not assign as parent"]));
            }
        } else {
            return json_encode(['status' => 200]);
        }
    }
}

function checkUserAssignOrganization() {
    global $conn; 
    $user_id = mysqli_real_escape_string($conn,$_REQUEST['id']);
    $role_id = mysqli_real_escape_string($conn,$_REQUEST['role_id']);
    $checkUserOrganizationAssign = $conn->query("SELECT IF(Organization_id IS NOT NULL , 'Yes' , 'No') as `user_assign` , role FROM users WHERE ID = '$user_id'");
    $checkUserOrganizationAssign = mysqli_fetch_all($checkUserOrganizationAssign,MYSQLI_ASSOC);
    if($checkUserOrganizationAssign[0]['user_assign'] == 'Yes') {
        if($checkUserOrganizationAssign[0]['role'] == $role_id) {
            return json_encode(['status' => 200 , 'message' => 'same role']);
        } else {
            return json_encode(['status' => 400 , 'text' => 'Organization info assign to user' , 'title' => "Sorry..role not update" , "previous" => $checkUserOrganizationAssign[0]['role']]);
        }
    } else {
        return json_encode(['status'=> 200 , 'message' => 'user role can be change']);
    }
}

/**
 * Task -1 
 * Check that given projectionntype id any projection are generated or not 
 * If not genereate then pass status = 200 if generate then pass status 400
 */
function checkAssignProjectionType() {

    global $conn; 
    $projection_type_id = mysqli_real_escape_string($conn,$_REQUEST['id']);
    $checkProjectionType = $conn->query("SELECT * FROM `Projection` WHERE projectionType = '$projection_type_id' AND Deleted_At IS NULL");
    if($checkProjectionType->num_rows > 0 ) {
        if( isset($_REQUEST['type']) && $_REQUEST['type'] == "update_branch" && isset($_REQUEST['branch_id'])) {
            $currentBranch_id = $_REQUEST['branch_id'];
            $assignBranch_id = $conn->query("SELECT branch_id FROM Projection_type WHERE ID = '$projection_type_id'");
            $assignBranch_id = mysqli_fetch_column($assignBranch_id);
            if ($currentBranch_id == $assignBranch_id) {
                return json_encode(['status' => 200 , 'message' => "same branch"]);    
            } else {
                return (json_encode(['status'=> 400 ,'title'=>"Sorry can't update Branch" ,'text'=>"Projection genrated on this projection type",'previous' => $assignBranch_id]));
            }
        } elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == "update_department" && isset($_REQUEST['department_id'])) {
            $currentDepartment_id = $_REQUEST['department_id'];
            $assignDepartment_id = $conn->query("SELECT department_id FROM Projection_type WHERE ID = '$projection_type_id'");
            $assignDepartment_id = mysqli_fetch_column($assignDepartment_id);
            if ($currentDepartment_id == $assignDepartment_id) {
                return json_encode(['status' => 200 , 'message' => "same department"]);
            } else {
                return (json_encode(['status'=> 400 ,'title'=>"Sorry can't update Department" ,'text'=>"Projection genrated on this projection type",'previous' => $assignDepartment_id]));
            }
        } else {
            return (json_encode(['status'=> 400,'title'=>"Projection type can't delete",'text'=>"Projection generated on this projection type"]));
        }
    } else {
        if( isset($_REQUEST['type']) && $_REQUEST['type'] == "update_branch" && isset($_REQUEST['branch_id'])) {
            $currentBranch_id = $_REQUEST['branch_id'];
            $assignBranch_id = $conn->query("SELECT branch_id FROM Projection_type WHERE ID = '$projection_type_id'");
            $assignBranch_id = mysqli_fetch_column($assignBranch_id);
            if ($currentBranch_id == $assignBranch_id) {
                return json_encode(['status' => 200 , 'message' => "same branch"]);    
            } else {
                return json_encode(['status' => 200 , 'message' => "projection not generated"]);
            }
        } elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == "update_department" && isset($_REQUEST['department_id'])) {
            $currentDepartment_id = $_REQUEST['department_id'];
            $assignDepartment_id = $conn->query("SELECT department_id FROM Projection_type WHERE ID = '$projection_type_id'");
            $assignDepartment_id = mysqli_fetch_column($assignDepartment_id);
            if ($currentDepartment_id == $assignDepartment_id) {
                return json_encode(['status' => 200 , 'message' => "same department"]);
            } else {
                return json_encode(['status' => 200 , 'message' => "User not assign as parent"]);
            }
        } else {
            return json_encode(['status' => 200]);
        }
        
    }
}
 
/**
 * for delete 
 * 1) Only allow if that ticket status not present in any ticket_record
 * 
 */
function checkAssignTicketStatus() : string {
    
    global $conn;
    $ticketStatus_id = mysqli_real_escape_string($conn,$_REQUEST['id']);
    $checkTicketStatus = $conn->query("SELECT COUNT(status) as `ticketStatus` FROM `ticket_record` WHERE status = '$ticketStatus_id'");
    $checkTicketStatus = mysqli_fetch_column($checkTicketStatus);
    if($checkTicketStatus > 0) {
        return json_encode(['status' => 400 , 'text' => 'Status Assign to Ticket' , 'title' => "Sorry.. Status can't delete"]);
    } else {
        return json_encode(['status' => 200 , 'text' => "Status can delete"]);
    }
}

function checkAssignTicketCategory() : string {

    global $conn;
    $ticketCategory_id = mysqli_real_escape_string($conn,$_REQUEST['id']);
    $checkTicketCategory = $conn->query("SELECT COUNT(category) FROM `ticket_record` WHERE category = '$ticketCategory_id'");
    $checkTicketCategory = mysqli_fetch_column($checkTicketCategory);
    if($checkTicketCategory > 0) {
        return json_encode(['status' => 400 , 'text' => 'Category Assign to Ticket' , 'title' => "Sorry..Category can't delete"]);
    } else {
        return json_encode(['status' => 200 , 'text' => "Category can delete"]);
    }
}
?>