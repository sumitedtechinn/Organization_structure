<?php

## Database configuration
include '../../includes/db-config.php';
session_start();

if (isset($_REQUEST['ID']) && isset($_REQUEST['branch']) && isset($_REQUEST['department']) && isset($_REQUEST['designation'])) {
    
    $department  = mysqli_real_escape_string($conn,$_REQUEST['department']);
    list($designation_id,$hierarchy_value) = explode("_",mysqli_real_escape_string($conn,$_REQUEST['designation']));
    $branch_id = mysqli_real_escape_string($conn,$_POST['branch']);
    $id = mysqli_real_escape_string($conn,$_POST['ID']);

    $userOrganizationAndVertical = $conn->query("SELECT organization_id , vertical_id FROM Department WHERE id = '$department'");
    $userOrganizationAndVertical = mysqli_fetch_assoc($userOrganizationAndVertical);
    $organization_id = $userOrganizationAndVertical['organization_id'];
    $vertical_id = $userOrganizationAndVertical['vertical_id'];
    
    $update_query = $conn->query("UPDATE users SET Department_id = '$department', Designation_id = '$designation_id', Hierarchy_value = '$hierarchy_value', Vertical_id = '$vertical_id', Branch_id = '$branch_id', Organization_id = '$organization_id', Assinged_Person_id = null WHERE ID = $id");
    showResponse($update_query,'updated'); 

} elseif (isset($_REQUEST['ID']) && isset($_REQUEST['organization']) && isset($_REQUEST['branch']) && isset($_REQUEST['designation'])) {

    $organization_id = mysqli_real_escape_string($conn,$_REQUEST['organization']);
    list($designation_id,$hierarchy_value) = explode("_",mysqli_real_escape_string($conn,$_REQUEST['designation']));
    $branch_id = mysqli_real_escape_string($conn,$_POST['branch']);
    $id = mysqli_real_escape_string($conn,$_POST['ID']);
    $update_query = $conn->query("UPDATE users SET Designation_id = '$designation_id', Hierarchy_value = '$hierarchy_value' , Branch_id = '$branch_id', Organization_id = '$organization_id', Assinged_Person_id = null WHERE ID = $id");
    showResponse($update_query,'updated');

} elseif (isset($_REQUEST['ID']) && isset($_REQUEST['organization']) && isset($_REQUEST['designation'])) {

    $organization_id = mysqli_real_escape_string($conn,$_REQUEST['organization']);
    list($designation_id,$hierarchy_value) = explode("_",mysqli_real_escape_string($conn,$_REQUEST['designation']));
    $id = mysqli_real_escape_string($conn,$_POST['ID']);
    $update_query = $conn->query("UPDATE users SET Designation_id = '$designation_id', Hierarchy_value = '$hierarchy_value', Organization_id = '$organization_id', Assinged_Person_id = null WHERE ID = $id");
    showResponse($update_query,'updated');

} elseif (isset($_REQUEST['ID']) && isset($_REQUEST['reporting_person'])) {
    $id = mysqli_real_escape_string($conn,$_POST['ID']);
    if ($_REQUEST['reporting_person'] == '0' || !empty($_REQUEST['reporting_person'])) {
        $assign_person = mysqli_real_escape_string($conn,$_POST['reporting_person']);
        $update_query = $conn->query("UPDATE users SET Assinged_Person_id = '$assign_person' WHERE ID =  $id");
    } else {
        $update_query = $conn->query("UPDATE users SET Assinged_Person_id = NULL WHERE ID =  $id");
    }    
    showResponse($update_query,'updated');
}


function showResponse($response, $message = "Something went wrong!") {
    if ($response) {
        echo json_encode(['status' => 200, 'message' => "Assing details $message successfully!"]);
    } else {
        echo json_encode(['status' => 400, 'message' => $message]);
    }
}


?>