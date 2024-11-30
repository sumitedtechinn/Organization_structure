<?php

## Database configuration
include '../../includes/db-config.php';
session_start();

if (isset($_REQUEST['user_id']) && isset($_REQUEST['parent_id'])) {

    $current_assign_parent = mysqli_real_escape_string($conn,$_REQUEST['parent_id']);
    $user_id = mysqli_real_escape_string($conn,$_REQUEST['user_id']);
    $user_details = $conn->query("SELECT * FROM users WHERE ID = '$user_id'");
    $user_details = mysqli_fetch_assoc($user_details);

    $checkvacncy = $conn->query("SELECT NumOfVacanciesRaised FROM `Vacancies` WHERE Branch_id = '".$user_details['Branch_id']."' AND Vertical_id = '".$user_details['Vertical_id']."' AND Designation_id = '".$user_details['Designation_id']."' AND Organization_id = '".$user_details['Organization_id']."' AND Department_id = '".$user_details['Department_id']."'");

    $vacancies_fill = $conn->query("SELECT COUNT(users.ID) as `allcount` FROM users where Branch_id = '".$user_details['Branch_id']."' AND Vertical_id = '".$user_details['Vertical_id']."' AND Organization_id = '".$user_details['Organization_id']."' AND Department_id = '".$user_details['Department_id']."' AND  Designation_id = '".$user_details['Designation_id']."' AND Assinged_Person_id IS NOT NULL AND Deleted_At IS NULL");
    $numofvacanciesfill = mysqli_fetch_column($vacancies_fill);

    if ($checkvacncy->num_rows > 0) {
        $numofvacancy = mysqli_fetch_column($checkvacncy); 
        if( ($numofvacancy - $numofvacanciesfill) > 0 ) {
            echo json_encode(['status' => 200 , 'message' => "Vacancy Present"]);
        } else {
            if ((!empty($user_details['Assinged_Person_id']) || $user_details['Assinged_Person_id'] == '0' ) &&  $current_assign_parent == $user_details['Assinged_Person_id'] ) {
                echo json_encode(['status' => 200 , 'message' => "Same parent"]);
            } elseif((!empty($user_details['Assinged_Person_id']) || $user_details['Assinged_Person_id'] == '0' )) {
                echo json_encode(['status' => 200 , 'message' => "Parent is change"]);
            } else {
                echo json_encode(['status' => 400 , 'message' => "Vacancy is full please update"]);
            }
        }
    } else {
        echo json_encode(['status' => 400 , 'message' => "Please generate the vacancy"]);
    }
}

?>