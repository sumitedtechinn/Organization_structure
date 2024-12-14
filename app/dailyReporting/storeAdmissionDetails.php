<?php

require '../../includes/db-config.php';
session_start();

if(isset($_REQUEST['admission_center']) && isset($_REQUEST['numOfAdmission']) && isset($_REQUEST['admission_amount']) && isset($_REQUEST['id'])) {

    $admission_by = mysqli_real_escape_string($conn,$_REQUEST['admission_center']);
    $numOfAdmission = mysqli_real_escape_string($conn,$_REQUEST['numOfAdmission']);
    $admission_amount = mysqli_real_escape_string($conn,$_REQUEST['admission_amount']);
    $id = mysqli_real_escape_string($conn,$_REQUEST['id']); 

    $dbDetails = $conn->query("SELECT * FROM `admission_details` WHERE id = '$id'");
    $dbDetails = mysqli_fetch_assoc($dbDetails);

    $received_amount = (!empty($dbDetails['amount'])) ? $dbDetails['amount'] : 0;
    $deposit_amount = (!empty($dbDetails['deposit_amount'])) ? $dbDetails['deposit_amount'] : 0;

    $dbAdmAmount = $received_amount+$deposit_amount;

    $setClause = '';
    if($dbAdmAmount != $admission_amount) {
        list($received_amount,$deposit_amount) = checkReceivedAndDepositAmount($admission_amount,$received_amount,$deposit_amount,$dbDetails['admission_by']);
        $setClause = "amount = '$received_amount' , deposit_amount = '$deposit_amount'";
    } else {
        $setClause = "amount = '".$dbDetails['amount']."' , deposit_amount = '".$dbDetails['deposit_amount']."'";
    }
    $update = $conn->query("UPDATE admission_details SET admission_by = '$admission_by' , numofadmission = '$numOfAdmission' , $setClause WHERE id = '$id'");
    showResponse($update,'Updated');
}

function showResponse($response, $message = "Something went wrong!") {
    if ($response) {
        echo json_encode(['status' => 200, 'message' => "Admission  details $message successfully!"]);
    } else {
        echo json_encode(['status' => 400, 'message' => $message]);
    }
}

function checkReceivedAndDepositAmount($updateAmount,$received_amount,$deposit_amount,$center_id) {

    global $conn;
    if($updateAmount > ($received_amount+$deposit_amount)) {
        $received_amount = $updateAmount - $deposit_amount;
    } else if ($updateAmount < ($received_amount+$deposit_amount)) {
        $tempamount = $updateAmount - $deposit_amount;
        if($tempamount >= 0) {
            $received_amount = $tempamount;
        } else {
            $tempamount = abs($tempamount);
            $deposit_amount = $deposit_amount - $tempamount;
            $updateClosure = $conn->query("UPDATE Closure_details SET withdraw_amount = withdraw_amount - '$tempamount' WHERE id = '$center_id'");
        }
    }
    return [$received_amount,$deposit_amount];
}

?>