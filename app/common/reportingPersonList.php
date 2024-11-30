<?php 

## Database configuration
include '../../includes/db-config.php';
session_start();

if(!isset($_REQUEST['designation_type'])) {
    $_REQUEST['designation_type'] = "insideDepartment";
}
 
if($_REQUEST['designation_type'] == "insideDepartment") {

    if (isset($_REQUEST['branch_id']) && !isset($_REQUEST['organization_id'])) {
        $organization = $conn->query("SELECT organization_id FROM `Branch` WHERE ID = '".$_REQUEST['branch_id']."'");
        $_REQUEST['organization_id'] = mysqli_fetch_column($organization);
    }
    
    if (isset($_REQUEST['branch_id']) && isset($_REQUEST['department_id']) && isset($_REQUEST['organization_id']) && isset($_REQUEST['hierarchy_value'])) {
    
        $hierarchy = mysqli_real_escape_string($conn,$_REQUEST['hierarchy_value']);
        $organization_id = mysqli_real_escape_string($conn,$_REQUEST['organization_id']);
        $department_id = mysqli_real_escape_string($conn,$_REQUEST['department_id']);
        $branch_id = mysqli_real_escape_string($conn,$_REQUEST['branch_id']);
        $assinged_Person_id = '';
        if (isset($_REQUEST['assing_person_id']) && !empty($_REQUEST['assing_person_id'])) {
            $assinged_Person_id = mysqli_real_escape_string($conn,$_REQUEST['assing_person_id']);
            if ($assinged_Person_id == 'head') {
                $assinged_Person_id = 0;
            }
        }
        if (isset($_REQUEST['raised_by']) && !empty($_REQUEST['raised_by'])) {
            $assinged_Person_id = mysqli_real_escape_string($conn,$_REQUEST['raised_by']);
            if ($assinged_Person_id == 'head') {
                $assinged_Person_id = 0;
            }
        }
    
        $option = '<option value="">Select</option>';
        $loop_count = 0;
        while ($hierarchy > 0) {
            $hierarchy -= 1;
            $checkSeniors = $conn->query("SELECT users.ID , users.Name , Designation.code as `designation_code` FROM `users` LEFT JOIN Designation ON Designation.ID = users.Designation_id WHERE users.Organization_id = '$organization_id' AND users.Branch_id = '$branch_id' AND users.Department_id = '$department_id' AND users.Hierarchy_value = '$hierarchy' AND users.Assinged_Person_id IS NOT NULL AND users.Deleted_At IS NULL");
            if ($checkSeniors->num_rows > 0) {
                $loop_count++;
                while($row = mysqli_fetch_assoc($checkSeniors)) {
                    if(!empty($assinged_Person_id) && $row['ID'] == $assinged_Person_id) {
                        $option .= '<option value="'.$row['ID'].'" selected>'.$row['Name'].'('.$row['designation_code'].')</option>';
                    } else {
                        $option .= '<option value="'.$row['ID'].'">'.$row['Name'].'('.$row['designation_code'].')</option>';
                    }
                }
                if ($checkSeniors->num_rows > 1 || $loop_count > 1) {
                    break;
                }
            }
        }
        if($loop_count <= 1) {
            if( '0' == $assinged_Person_id) {
                $option .= '<option value="0" selected>Head</option>';
            } else {
                $option .= '<option value="0">Head</option>';
            }
        }
        echo $option;
    
    } 
    
} elseif ($_REQUEST['designation_type'] == "insideOrganization") {
        
    if (isset($_REQUEST['organization_id']) && isset($_REQUEST['hierarchy_value'])) {
        $hierarchy = mysqli_real_escape_string($conn,$_REQUEST['hierarchy_value']);
        $organization_id = mysqli_real_escape_string($conn,$_REQUEST['organization_id']);
        $assinged_Person_id = '';
        if (isset($_REQUEST['assing_person_id']) && !empty($_REQUEST['assing_person_id'])) {
            $assinged_Person_id = mysqli_real_escape_string($conn,$_REQUEST['assing_person_id']);
            if ($assinged_Person_id == 'head') {
                $assinged_Person_id = 0;
            }
        }

        $option = '<option value="">Select</option>';
        $loop_count = 0;
        while ($hierarchy > 0) {
            $hierarchy -= 1;
            $checkSeniors = $conn->query("SELECT users.ID , users.Name , Designation.code as `designation_code` FROM `users` LEFT JOIN Designation ON Designation.ID = users.Designation_id WHERE users.Organization_id = '$organization_id' AND users.Branch_id IS NULL AND users.Department_id IS NULL AND Assinged_Person_id IS NOT NULL AND users.Hierarchy_value = '$hierarchy'");
            if ($checkSeniors->num_rows > 0) {
                $loop_count++;
                while($row = mysqli_fetch_assoc($checkSeniors)) {
                    if(!empty($assinged_Person_id) && $row['ID'] == $assinged_Person_id) {
                        $option .= '<option value="'.$row['ID'].'" selected>'.$row['Name'].'('.$row['designation_code'].')</option>';
                    } else {
                        $option .= '<option value="'.$row['ID'].'">'.$row['Name'].'('.$row['designation_code'].')</option>';
                    }
                }
                if ($checkSeniors->num_rows > 1 || $loop_count > 1) {
                    break;
                }
            }
        }
        if($loop_count <= 1) {
            if( '0' == $assinged_Person_id) {
                $option .= '<option value="0" selected>Head</option>';
            } else {
                $option .= '<option value="0">Head</option>';
            }
        }
        echo $option;
    }
} elseif ($_REQUEST['designation_type'] == "insideBranch") {

    if (isset($_REQUEST['branch_id']) && isset($_REQUEST['organization_id']) && isset($_REQUEST['hierarchy_value'])) {
        $hierarchy = mysqli_real_escape_string($conn,$_REQUEST['hierarchy_value']);
        $organization_id = mysqli_real_escape_string($conn,$_REQUEST['organization_id']);
        $branch_id = mysqli_real_escape_string($conn,$_REQUEST['branch_id']);
        $assinged_Person_id = '';
        if (isset($_REQUEST['assing_person_id']) && !empty($_REQUEST['assing_person_id'])) {
            $assinged_Person_id = mysqli_real_escape_string($conn,$_REQUEST['assing_person_id']);
            if ($assinged_Person_id == 'head') {
                $assinged_Person_id = 0;
            }
        }

        $option = '<option value="">Select</option>';
        $loop_count = 0;
        while ($hierarchy > 0) {
            $hierarchy -= 1;
            $checkSeniors = $conn->query("SELECT users.ID , users.Name , Designation.code as `designation_code` FROM `users` LEFT JOIN Designation ON Designation.ID = users.Designation_id WHERE users.Organization_id = '$organization_id' AND users.Branch_id = '$branch_id' AND users.Department_id IS NULL AND Assinged_Person_id IS NOT NULL AND users.Hierarchy_value = '$hierarchy' AND users.role = '3'");
            if ($checkSeniors->num_rows > 0) {
                $loop_count++;
                while($row = mysqli_fetch_assoc($checkSeniors)) {
                    if(!empty($assinged_Person_id) && $row['ID'] == $assinged_Person_id) {
                        $option .= '<option value="'.$row['ID'].'" selected>'.$row['Name'].'('.$row['designation_code'].')</option>';
                    } else {
                        $option .= '<option value="'.$row['ID'].'">'.$row['Name'].'('.$row['designation_code'].')</option>';
                    }
                }
                if ($checkSeniors->num_rows > 1 || $loop_count > 1) {
                    break;
                }
            }
        }
        if($loop_count <= 1) {
            if( '0' == $assinged_Person_id) {
                $option .= '<option value="0" selected>Head</option>';
            } else {
                $option .= '<option value="0">Head</option>';
            }
        }
        echo $option;
    }
} elseif ($_REQUEST['designation_type'] == "insideVertical") {
    
    if (isset($_REQUEST['branch_id']) && isset($_REQUEST['vertical_id']) && isset($_REQUEST['organization_id']) && isset($_REQUEST['hierarchy_value'])) {
        $hierarchy = mysqli_real_escape_string($conn,$_REQUEST['hierarchy_value']);
        $organization_id = mysqli_real_escape_string($conn,$_REQUEST['organization_id']);
        $branch_id = mysqli_real_escape_string($conn,$_REQUEST['branch_id']);
        $vertical_id = mysqli_real_escape_string($conn,$_REQUEST['vertical_id']);
        $assinged_Person_id = '';
        if (isset($_REQUEST['assing_person_id']) && !empty($_REQUEST['assing_person_id'])) {
            $assinged_Person_id = mysqli_real_escape_string($conn,$_REQUEST['assing_person_id']);
            if ($assinged_Person_id == 'head') {
                $assinged_Person_id = 0;
            }
        }

        $option = '<option value="">Select</option>';
        $loop_count = 0;
        while ($hierarchy > 0) {
            $hierarchy -= 1;
            $checkSeniors = $conn->query("SELECT users.ID , users.Name , Designation.code as `designation_code` FROM `users` LEFT JOIN Designation ON Designation.ID = users.Designation_id WHERE users.Organization_id = '$organization_id' AND users.Branch_id = '$branch_id' AND users.Vertical_id = '$vertical_id' AND users.Department_id IS NULL AND Assinged_Person_id IS NOT NULL AND  users.Hierarchy_value = '$hierarchy' AND users.role = '3'");
            if ($checkSeniors->num_rows > 0) {
                $loop_count++;
                while($row = mysqli_fetch_assoc($checkSeniors)) {
                    if(!empty($assinged_Person_id) && $row['ID'] == $assinged_Person_id) {
                        $option .= '<option value="'.$row['ID'].'" selected>'.$row['Name'].'('.$row['designation_code'].')</option>';
                    } else {
                        $option .= '<option value="'.$row['ID'].'">'.$row['Name'].'('.$row['designation_code'].')</option>';
                    }
                }
                if ($checkSeniors->num_rows > 1 || $loop_count > 1) {
                    break;
                }
            }
        }
        if($loop_count <= 1) {
            if( '0' == $assinged_Person_id) {
                $option .= '<option value="0" selected>Head</option>';
            } else {
                $option .= '<option value="0">Head</option>';
            }
        }
        echo $option;
    }
}

?>