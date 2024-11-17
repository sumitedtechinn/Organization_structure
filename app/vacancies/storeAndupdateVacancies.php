<?php 

require '../../includes/db-config.php';
session_start();


if (isset($_REQUEST['branch']) && isset($_REQUEST['department']) && isset($_REQUEST['numofvacancies']) && isset($_REQUEST['designation']) && isset($_REQUEST['raisedby']) ) {

    $branch = mysqli_real_escape_string($conn,$_POST['branch']);
    list($designation,$hierarchy) = explode("_",mysqli_real_escape_string($conn,$_POST['designation']));
    $numofvacancies = mysqli_real_escape_string($conn,$_POST['numofvacancies']);
    $department = mysqli_real_escape_string($conn,$_POST['department']);
    $raisedby = mysqli_real_escape_string($conn,$_POST['raisedby']);

    $department_details = $conn->query("SELECT organization_id , vertical_id FROM `Department` WHERE id = '$department'");
    $department_details = mysqli_fetch_assoc($department_details);

    $organization = $department_details['organization_id'];
    $vertical = $department_details['vertical_id'];

    $checkVacancy = $conn->query("SELECT COUNT(ID) FROM `Vacancies` WHERE Organization_id = '$organization' AND Branch_id = '$branch' AND Vertical_id = '$vertical' AND Department_id = '$department' AND Designation_id = '$designation'");
    $checkVacancy = mysqli_fetch_column($checkVacancy);
    
    if ($checkVacancy >= 1) {
        showResponse(false,'Duplicate Entry Found','Vacancy already generated for this Designation');
        die;
    }
    $checkColorOfNode = $conn->query("SELECT color FROM `Department` WHERE color IS NOT NULL LIMIT 1");
    if ($checkColorOfNode->num_rows > 0) {
        $checkColorOfNode = mysqli_fetch_column($checkColorOfNode);
        $insert_query = $conn->query("INSERT INTO `Vacancies`(`NumOfVacanciesRaised`, `Organization_id`, `Designation_id`, `Department_id`, `Branch_id`, `Vertical_id`, `Raised_by`,`color`) VALUES ('$numofvacancies','$organization','$designation','$department','$branch','$vertical','$raisedby','$checkColorOfNode')");
        showResponse($insert_query,'inserted');
    } else {
        $colorCode = "#F95454";
        $insert_query = $conn->query("INSERT INTO `Vacancies`(`NumOfVacanciesRaised`, `Organization_id`, `Designation_id`, `Department_id`, `Branch_id`, `Vertical_id`, `Raised_by`,`color`) VALUES ('$numofvacancies','$organization','$designation','$department','$branch','$vertical','$raisedby','$colorCode')");
        showResponse($insert_query,'inserted');
    }

} elseif (isset($_REQUEST['numofvacancies']) && isset($_REQUEST['ID']) && isset($_REQUEST['raisedby'])) {

    $vacacy_data = $conn->query("SELECT * FROM `Vacancies` WHERE ID = '".$_REQUEST['ID']."'");
    $vacacy_data = mysqli_fetch_assoc($vacacy_data);

    $vacancies_fill = $conn->query("SELECT COUNT(users.ID) as `allcount` FROM users where Branch_id = '".$vacacy_data['Branch_id']."' AND Vertical_id = '".$vacacy_data['Vertical_id']."' AND Organization_id = '".$vacacy_data['Organization_id']."' AND Department_id = '".$vacacy_data['Department_id']."' AND  Designation_id = '".$vacacy_data['Designation_id']."' AND Assinged_Person_id IS NOT NULL AND Deleted_At IS NULL");
    $numofvacanciesfill = mysqli_fetch_column($vacancies_fill);

    $numofvacancies = mysqli_real_escape_string($conn,$_POST['numofvacancies']);
    $raisedby = mysqli_real_escape_string($conn,$_POST['raisedby']);

    if ($numofvacancies - $numofvacanciesfill > 0) {
        $updated_time = date("Y-m-d h:i:s");
        $update_query = $conn->query("UPDATE `Vacancies` SET `NumOfVacanciesRaised`='$numofvacancies',`Raised_by`='$raisedby' WHERE `ID` = '".$_REQUEST['ID']."'");
        showResponse($update_query,'Updated');
    } else {
        showResponse(false,'Number of vacancy too less');
    }
}

function showResponse($response, $message = "Something went wrong!",$text = null) {
    if ($response) {
        echo json_encode(['status' => 200, 'message' => "Vacancies $message successfully!"]);
    } else {
        if(is_null($text)) {
            echo json_encode(['status' => 400, 'message' => $message]);
        } else {
            echo json_encode(['status' => 400, 'message' => $message, 'text' => $text]);
        }
    }
}

?>