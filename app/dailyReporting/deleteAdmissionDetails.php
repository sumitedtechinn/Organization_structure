<?php 

require '../../includes/db-config.php';
session_start();

if(isset($_REQUEST['id'])) {
    $admId = mysqli_real_escape_string($conn,$_REQUEST['id']);
    
    $admDetails = $conn->query("SELECT * FROM admission_details WHERE id = '$admId'");
    $admDetails = mysqli_fetch_assoc($admDetails);

    $deposit_amount = $admDetails['deposit_amount'];
    $depositStatus = (is_null($deposit_amount) || empty($deposit_amount)) ? false : true;
    if($depositStatus) {
        $updateWidthrawAmount = $conn->query("UPDATE `Closure_details` SET withdraw_amount =  withdraw_amount - '$deposit_amount' WHERE id = '".$admDetails['admission_by']."'");
    }
    $updateAdmStatus = $conn->query("UPDATE admission_details SET Deleted_At = CURRENT_TIMESTAMP WHERE id = '$admId'");
    showResponse($updateAdmStatus,'Deleted');
}

function showResponse($response, $message = "Something went wrong!") {
    if ($response) {
        echo json_encode(['status' => 200, 'message' => "Admission $message successfully!"]);
    } else {
        echo json_encode(['status' => 400, 'message' => $message]);
    }
}

?>