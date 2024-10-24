<?php

require '../../includes/db-config.php';
session_start();

$layout = [];

$filter_record = file_get_contents('php://input'); // by this we get raw data
$filter_record = json_decode($filter_record,true);

$organization_id = ''; $branch_id = ''; $vertical_id = ''; $department_id = '';
if($_SESSION['role'] == '2') {
    $organization_id = mysqli_real_escape_string($conn,$_SESSION['Organization_id']);
    $branch_id = mysqli_real_escape_string($conn,$_SESSION['Branch_id']);
    $vertical_id = mysqli_real_escape_string($conn,$_SESSION['Vertical_id']);
    $department_id = mysqli_real_escape_string($conn,$_SESSION['Department_id']);
    generateDepartmentStructure();
} else {
    if(!empty($filter_record['organization_id']) && !empty($filter_record['branch_id']) && !empty($filter_record['vertical_id']) && !empty($filter_record['department_id'])) {
        $organization_id = mysqli_real_escape_string($conn,$filter_record['organization_id']);
        $branch_id = mysqli_real_escape_string($conn,$filter_record['branch_id']);
        $vertical_id = mysqli_real_escape_string($conn,$filter_record['vertical_id']);
        $department_id = mysqli_real_escape_string($conn,$filter_record['department_id']);
        generateDepartmentStructure();
    } elseif(!empty($filter_record['organization_id']) && !empty($filter_record['branch_id']) && !empty($filter_record['vertical_id'])) {
        $organization_id = mysqli_real_escape_string($conn,$filter_record['organization_id']);
        $branch_id = mysqli_real_escape_string($conn,$filter_record['branch_id']);
        $vertical_id = mysqli_real_escape_string($conn,$filter_record['vertical_id']);
        generateVerticalStructure();
    } elseif (!empty($filter_record['organization_id']) && !empty($filter_record['branch_id'])) {
        $organization_id = mysqli_real_escape_string($conn,$filter_record['organization_id']);
        $branch_id = mysqli_real_escape_string($conn,$filter_record['branch_id']);
        generateBranchStructure();
    } elseif(!empty($filter_record['organization_id'])) {
        $organization_id = mysqli_real_escape_string($conn,$filter_record['organization_id']);
        generateOrganizationStructure();
    } else {
        $organization_id = '';
        if ($_SESSION['role'] == '3') {
            $organization_id = mysqli_real_escape_string($conn,$_SESSION['Organization_id']);
        } else {
            $organization = $conn->query("SELECT id FROM `organization` LIMIT 1");
            $organization_id = mysqli_fetch_column($organization);
        }
        generateOrganizationStructure();
    }
}

echo json_encode($layout);

function generateOrganizationStructure() {
    if(getOrganizationData()) {
        getUserInsideOrganization();
        if (getBranchdata()) {
            getUserInsideBranch();
            if(getVerticalData()) {
                if(getDepartmentData()) {
                    getUserListDepartmentBasis();
                    getVacancyNode();
                } 
            }
        }    
    }
}

function generateBranchStructure() {
    if (getBranchdata()) {
        getUserInsideBranch();
        if(getVerticalData()) {
            if(getDepartmentData()) {
                getUserListDepartmentBasis();
                getVacancyNode();
            } 
        }
    }
}

function generateVerticalStructure() {
    if(getVerticalData()) {
        if(getDepartmentData()) {
            getUserListDepartmentBasis();
            getVacancyNode();
        } 
    }
}

function generateDepartmentStructure() {
    if(getDepartmentData()) {
        getUserListDepartmentBasis();
        getVacancyNode();
    }
}

function getOrganizationData() : bool {
    global $layout;
    global $conn;
    global $organization_id;
    $organization = $conn->query("SELECT * FROM `organization` WHERE id = '$organization_id' AND Deleted_At IS NULL");
    if($organization->num_rows > 0 ){
        $organization_data = mysqli_fetch_assoc($organization);
        $date1 = new DateTime($organization_data['start_date']);
        $date2 = new DateTime();
        $interval = $date1->diff($date2);
        $layout[] = array(
            "id" => "organization_". $organization_data['id'],
            "Name" => $organization_data['organization_name'] , 
            "Designation" => "Organization" , 
            "Image" => $organization_data['logo'],
            "Duration" => $interval->y ." Year " .$interval->m . " Month ".$interval->d. " Day",
            "Code" => 'organization' ,
            "tags" => ['organization'] ,
            "color" => $organization_data['color'],
        );
        return true;
    } else {
        return false;
    }
}

function getUserInsideOrganization() {

    global $layout;
    global $conn;
    global $organization_id;
    $organization_user = $conn->query("SELECT users.* , Designation.designation_name as `designation` , Designation.color as `color` , Designation.code as `designation_code` FROM users LEFT JOIN Designation ON Designation.ID = users.Designation_id WHERE role = '3' AND users.Branch_id IS NULL AND users.Organization_id = '$organization_id' AND users.Assinged_Person_id IS NOT NULL AND users.Deleted_At IS NULL");
    if($organization_user->num_rows > 0 ) {
        while ($row = mysqli_fetch_assoc($organization_user)) {
            $date1 = new DateTime($row['DOJ']);
            $date2 = new DateTime();
            $interval = $date1->diff($date2);
            $address = "country : ".$row['Country']." \n State : ".$row['State']." \n City : ".$row['City']." \n Locality : ".$row['Address'];
            if (is_null($row['Photo'])) {
                $image = "../../assets/images/sample_user.jpg";
            } else {
                $image = $row['Photo'];
            }
            $pid = '';
            if($row['Assinged_Person_id'] == '0') {
                $pid = "organization_".$row['Organization_id'];
            } else {
                $pid = "organizationUser_".$row['Assinged_Person_id'];
            }
            $tag = $row['designation_code'] . '_' .$row['Organization_id'];
            $layout[] = array(
                "id" => "organizationUser_".$row['ID'] , 
                "pid" => $pid , 
                "Name" => $row['Name'],
                "Designation" => $row['designation'],
                "Duration" => $interval->y ." Year " .$interval->m . " Month ".$interval->d. " Day",
                "Image" => $image,
                "Address" => $address ,
                "Code" =>  $row['designation_code'] . '_' .$row['Organization_id'],
                "tags" => ["$tag"], 
                "color" => $row['color']
            );    
        }
    }
}

function getBranchdata() {
    global $layout;
    global $conn;
    global $organization_id; global $branch_id;
    $searchQuery = '';
    if (!empty($branch_id)) {
        $searchQuery .= "AND ID = '$branch_id'";
    }
    $branch = $conn->query("SELECT * FROM `Branch` WHERE organization_id = '$organization_id' $searchQuery AND Deleted_At IS NULL");
    if ($branch->num_rows > 0) {
        $pid = '';
        if (count($layout) > 1) {
            $pid = $layout[count($layout)-1]['id'];
        }
        while($row = mysqli_fetch_assoc($branch)) {
            $date1 = new DateTime($row['Start_date']);
            $date2 = new DateTime();
            $interval = $date1->diff($date2);
            $address = "country : ".$row['Country']." \n State : ".$row['State']." \n City : ".$row['City']." \n Locality : ".$row['Address'];
            if (is_null($row['image'])) {
                $image = "../../assets/images/sample_branch.jpg";
            } else {
                $image = $row['image'];
            }
            if (empty($pid)) {
                $pid = "organization_".$row['organization_id'];
            }
            $layout[] = array(
                "id" => "branch_".$row['ID'], 
                "pid" => $pid , 
                "Name" => $row['Branch_name'],
                "Designation" => "Branch",
                "Duration" => $interval->y ." Year " .$interval->m . " Month ".$interval->d. " Day",
                "Image" => $image , 
                "Address" => $address ,
                "Code" => 'branch' ,
                "tags" => ['branch'],
                "color" => $row['color'],
            );
        }
        return true;
    } else {
        return false;
    }
}

function getUserInsideBranch() {

    global $layout;
    global $conn;
    global $organization_id; global $branch_id;
    $searchQuery = '';
    if (!empty($branch_id)) {
        $searchQuery .= "AND ID = '$branch_id'";
    }
    $branch_list = $conn->query("SELECT ID FROM Branch WHERE organization_id = '$organization_id' $searchQuery AND Deleted_At IS NULL");
    if($branch_list->num_rows > 0) {
        while ($branch = mysqli_fetch_assoc($branch_list)) {
            $organization_user = $conn->query("SELECT users.* , Designation.designation_name as `designation` , Designation.color as `color` , Designation.code as `designation_code` FROM users LEFT JOIN Designation ON Designation.ID = users.Designation_id WHERE role = '3' AND users.Branch_id = '".$branch['ID']."' AND users.Assinged_Person_id IS NOT NULL AND users.Organization_id = '$organization_id'");
            if($organization_user->num_rows > 0 ) {
                while ($row = mysqli_fetch_assoc($organization_user)) {
                    $date1 = new DateTime($row['DOJ']);
                    $date2 = new DateTime();
                    $interval = $date1->diff($date2);
                    $address = "country : ".$row['Country']." \n State : ".$row['State']." \n City : ".$row['City']." \n Locality : ".$row['Address'];
                    if (is_null($row['Photo'])) {
                        $image = "../../assets/images/sample_user.jpg";
                    } else {
                        $image = $row['Photo'];
                    }
                    $pid = '';
                    if($row['Assinged_Person_id'] == '0') {
                        $pid = "branch_".$row['Branch_id'];
                    } else {
                        $pid = "branchUser_".$row['Assinged_Person_id'];
                    }
                    $tag = $row['designation_code'] . '_' .$row['Branch_id'];
                    $layout[] = array(
                        "id" => "branchUser_".$row['ID'], 
                        "pid" => $pid , 
                        "Name" => $row['Name'],
                        "Designation" => $row['designation'],
                        "Duration" => $interval->y ." Year " .$interval->m . " Month ".$interval->d. " Day",
                        "Image" => $image,
                        "Address" => $address ,
                        "Code" =>  $row['designation_code'] . '_' .$row['Organization_id'],
                        "tags" => ["$tag"], 
                        "color" => $row['color'],
                        "orientation" => "OrgChart.orientation.left"
                    );    
                }
            }       
        }
    }
}

function getVerticalData() {
    global $layout;
    global $conn;
    global $organization_id; global $branch_id; global $vertical_id;
    $searchQuery = '';
    if (!empty($branch_id)) {
        $searchQuery .= "AND Branch_id LIKE '%$branch_id%'";
    }
    if (!empty($vertical_id)) {
        $searchQuery .= "AND ID = '$vertical_id'";
    }
    $vertical = $conn->query("SELECT * FROM `Vertical` WHERE organization_id = '$organization_id' $searchQuery AND Deleted_At IS NULL");
    if ($vertical->num_rows > 0) {
        while($row = mysqli_fetch_assoc($vertical)) {
            $branches = json_decode($row['Branch_id'],true);
            foreach ($branches as $branch) {
                if (!empty($branch_id)) {
                    if($branch == $branch_id) {
                        $vertical_pid = verticalPID($organization_id,$branch);
                        $pid = '';
                        if($vertical_pid) {
                            $pid = 'branchUser_'. $vertical_pid;
                        } else {
                            $pid = "branch_".$branch;
                        }
                        if (is_null($row['image'])) {
                            $image = "../../assets/images/sample_vertical.jpg";
                        } else {
                            $image = $row['image'];
                        }
                        $layout[] = array(
                            "id" => "vertical_".$row['ID']."_" . $branch , 
                            "pid" => $pid, 
                            "Name" => $row['Vertical_name'],
                            "Designation" => "Vertical",
                            "Image" => $image , 
                            "Code" => 'vertical' ,
                            "tags" => ['vertical'] , 
                            "color" => $row['color'],
                        );
                    }   
                } else {
                    $vertical_pid = verticalPID($organization_id,$branch);
                    $pid = '';
                    if($vertical_pid) {
                        $pid = 'branchUser_'. $vertical_pid;
                    } else {
                        $pid = "branch_".$branch;
                    }
                    if (is_null($row['image'])) {
                        $image = "../../assets/images/sample_vertical.jpg";
                    } else {
                        $image = $row['image'];
                    }
                    $layout[] = array(
                        "id" => "vertical_".$row['ID']."_" . $branch , 
                        "pid" => $pid, 
                        "Name" => $row['Vertical_name'],
                        "Designation" => "Vertical",
                        "Image" => $image , 
                        "Code" => 'vertical' ,
                        "tags" => ['vertical'] , 
                        "color" => $row['color'],
                    );
                }    
            }
        }
        return true;
    }  else {
        return false;
    } 
}

function verticalPID($organization_id,$branch_id){

    global $conn;
    $checkDesignationInsideBranch = $conn->query("SELECT ID , hierarchy_value FROM Designation WHERE branch_id = '$branch_id' AND organization_id = '$organization_id' AND department_id IS null ORDER BY hierarchy_value DESC");
    if($checkDesignationInsideBranch->num_rows > 0 ) {
        $userId = '';
        while($row = mysqli_fetch_assoc($checkDesignationInsideBranch)) {
            $checkUser = $conn->query("SELECT ID FROM users WHERE Designation_id = '".$row['ID']."' AND Hierarchy_value = '".$row['hierarchy_value']."' AND Deleted_At IS NULL LIMIT 1");
            if($checkUser->num_rows > 0) {
                $userId = mysqli_fetch_column($checkUser);
                break;
            } 
        }
        if(!empty($userId)) {
            return $userId;
        } else {
            return false;
        }
    } else {
        return false;
    }
    
}

function getDepartmentData() {

    global $layout;
    global $conn;
    global $organization_id; global $branch_id; global $vertical_id; global $department_id;
    $searchQuery = '';
    if (!empty($branch_id)) {
        $searchQuery .= "AND branch_id LIKE '%$branch_id%'";
    }
    if (!empty($vertical_id)) {
        $searchQuery .= "AND vertical_id = '$vertical_id'";
    }
    if(!empty($department_id)) {
        $searchQuery .= "AND id = '$department_id'";
    }
    $department = $conn->query("SELECT * FROM `Department` WHERE organization_id = '$organization_id' $searchQuery AND Deleted_At IS NULL");
    if($department->num_rows > 0 ) {
        while($row = mysqli_fetch_assoc($department)) {
            $image = '';
            if(is_null($row['logo'])) {
                $image = "../../assets/images/gallery/sample_department.png";
            } else {
                $image = $row['logo'];
            }
            $branchs = json_decode($row['branch_id'],true);
            foreach ($branchs as $branch) {
                if(!empty($branch_id)) {
                    if($branch == $branch_id) {
                        $layout[] = array(
                            "id" => "department_".$row['id']."_" . $row['vertical_id'] . "_" . $branch , 
                            "pid" => "vertical_".$row['vertical_id']."_" . $branch, 
                            "Name" => $row['department_name'],
                            "Designation" => "Department",
                            "Image" => $image , 
                            "Code" => 'department',
                            "tags" => ['department'], 
                            "color" => $row['color'],
                        );    
                    }
                } else {
                    $layout[] = array(
                        "id" => "department_".$row['id']."_" . $row['vertical_id'] . "_" . $branch , 
                        "pid" => "vertical_".$row['vertical_id']."_" . $branch, 
                        "Name" => $row['department_name'],
                        "Designation" => "Department",
                        "Image" => $image , 
                        "Code" => 'department',
                        "tags" => ['department'], 
                        "color" => $row['color'],
                    );
                }
            }
        }
        return true;
    }  else {
        return false;
    }
}

function getUserListDepartmentBasis() {

    global $conn;
    global $layout;
    global $organization_id; global $branch_id; global $vertical_id; global $department_id;
    $searchQuery = '';
    if (!empty($branch_id)) {
        $searchQuery .= "AND users.Branch_id = '$branch_id'";
    }
    if (!empty($vertical_id)) {
        $searchQuery .= "AND users.Vertical_id = '$vertical_id'";
    }
    if(!empty($department_id)) {
        $searchQuery .= "AND users.Department_id = '$department_id'";
    }
    $directUser = $conn->query("SELECT users.* , Designation.designation_name as `designation` , Designation.color as `color` , Designation.code as `designation_code` FROM users LEFT JOIN Designation ON Designation.ID = users.Designation_id WHERE users.Assinged_Person_id = '0' AND users.Organization_id = '$organization_id' $searchQuery  AND users.role = '2' AND users.Deleted_At IS NULL");
    if($directUser->num_rows > 0) {
        while ($row = mysqli_fetch_assoc($directUser)) {
            $date1 = new DateTime($row['DOJ']);
            $date2 = new DateTime();
            $interval = $date1->diff($date2);
            $address = "country : ".$row['Country']." \n State : ".$row['State']." \n City : ".$row['City']." \n Locality : ".$row['Address'];
            if (is_null($row['Photo'])) {
                $image = "../../assets/images/sample_user.jpg";
            } else {
                $image = $row['Photo'];
            }
            $tag = $row['designation_code'] . '_' .$row['Department_id'];
            $layout[] = array(
                "id" => "user_".$row['ID']."_" .$row['Department_id'] . "_". $row['Vertical_id'] . "_" . $row['Branch_id'] , 
                "pid" => "department_".$row['Department_id']."_" . $row['Vertical_id'] . "_" . $row['Branch_id'] , 
                "Name" => $row['Name'],
                "Designation" => $row['designation'],
                "Duration" => $interval->y ." Year " .$interval->m . " Month ".$interval->d. " Day",
                "Image" => $image,
                "Address" => $address ,
                "Code" =>  $row['designation_code'] . '_' .$row['Department_id'],
                "tags" => ["$tag"], 
                "color" => $row['color'],
            );    
        }
    }

    $allChildUsers = $conn->query("SELECT users.* , Designation.designation_name as `designation` , Designation.color as `color` , Designation.code as `designation_code` FROM users LEFT JOIN Designation ON Designation.ID = users.Designation_id WHERE users.Assinged_Person_id != '0' AND users.Organization_id = '$organization_id' $searchQuery  AND users.role = '2' AND users.Deleted_At IS NULL");

    if($allChildUsers->num_rows > 0) {
        while ($row = mysqli_fetch_assoc($allChildUsers)) {
            $date1 = new DateTime($row['DOJ']);
            $date2 = new DateTime();
            $interval = $date1->diff($date2);
            $address = "country : ".$row['Country']." \n State : ".$row['State']." \n City : ".$row['City']." \n Locality : ".$row['Address'];
            if (is_null($row['Photo'])) {
                $image = "../../assets/images/sample_user.jpg";
            } else {
                $image = $row['Photo'];
            }
            $tag = $row['designation_code'] . '_' .$row['Department_id'];
            $layout[] = array(
                "id" => "user_".$row['ID']."_" .$row['Department_id'] . "_". $row['Vertical_id'] . "_" . $row['Branch_id'] , 
                "pid" => "user_".$row['Assinged_Person_id']."_" .$row['Department_id'] . "_". $row['Vertical_id'] . "_" . $row['Branch_id']  , 
                "Name" => $row['Name'],
                "Designation" => $row['designation'],
                "Duration" => $interval->y ." Year " .$interval->m . " Month ".$interval->d. " Day",
                "Image" => $image,
                "Address" => $address ,
                "Code" =>  $row['designation_code']. '_' .$row['Department_id'],
                "tags" => ["$tag"], 
                "color" => $row['color'],
            );    
        }
    }

}

function getVacancyNode() {

    global $conn;
    global $layout;
    global $organization_id; global $branch_id; global $vertical_id; global $department_id;
    $searchQuery = '';
    if (!empty($branch_id)) {
        $searchQuery .= "AND Vacancies.Branch_id = '$branch_id'";
    }
    if (!empty($vertical_id)) {
        $searchQuery .= "AND Vacancies.Vertical_id = '$vertical_id'";
    }
    if(!empty($department_id)) {
        $searchQuery .= "AND Vacancies.Department_id = '$department_id'";
    }
    $getGenerateVacancy = $conn->query("SELECT Vacancies.* , Designation.designation_name as `designation` , Designation.code as `designation_code` FROM `Vacancies` LEFT JOIN Designation ON Designation.ID = Vacancies.Designation_id WHERE Vacancies.Deleted_At IS NULL AND Vacancies.Organization_id = '$organization_id' $searchQuery");
    if ($getGenerateVacancy->num_rows > 0) {
        while( $row = mysqli_fetch_assoc($getGenerateVacancy)) {
            $branch = $row['Branch_id'];
            $vertical = $row['Vertical_id'];
            $organization = $row['Organization_id'];
            $designation_id = $row['Designation_id']; // from hierarchy table
            $department = $row['Department_id'];
            $numofVacancy = $row['NumOfVacanciesRaised'];
            $raisedby = $row['Raised_by'];
            $vacancies_fill = $conn->query("SELECT COUNT(users.ID) as `allcount` FROM users where Vertical_id = '".$vertical."' AND Organization_id = '".$organization."' AND Department_id = '".$department."' AND  Designation_id = '".$designation_id."' AND Assinged_Person_id IS NOT NULL AND Deleted_At IS NULL");
            $numofvacanciesfill = mysqli_fetch_column($vacancies_fill);
            $numofVacancyVacanct = intval($numofVacancy - $numofvacanciesfill);
            while($numofVacancyVacanct > 0 ) {
                $id = "vacancy_".$row['ID']."_" .$department . "_". $vertical . "_" . $branch .'_'. $numofVacancyVacanct;
                if ($raisedby == "0") {
                    $pid = 'department_'.$department.'_'.$vertical.'_'.$branch ;
                } else {
                    $pid = 'user_'.$raisedby.'_'.$department.'_'.$vertical.'_'.$branch;
                }
                $layout[] = array(
                    "id" => $id , 
                    "pid" => $pid, 
                    "Name" => "N/A",
                    "Designation" => $row['designation'],
                    "Image" => "/../../assets/images/vacant_position.avif" , 
                    "Code" =>  'vacancy',
                    "tags" => ["vacancy"], 
                    "color" => $row['color'],
                );
                $numofVacancyVacanct -= 1;
            } 
        }
    }
}
?>