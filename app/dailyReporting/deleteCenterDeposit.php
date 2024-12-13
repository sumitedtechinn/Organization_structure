<?php 
## Database configuration
include '../../includes/db-config.php';
session_start();

if(isset($_REQUEST['id']) && isset($_REQUEST['center_id']) && isset($_REQUEST['dailyReport_id'])) {

    $id = mysqli_real_escape_string($conn,$_REQUEST['id']);
    $center_id = mysqli_real_escape_string($conn,$_REQUEST['center_id']);
    $daily_report_id = mysqli_real_escape_string($conn,$_REQUEST['dailyReport_id']);

    $dbDepositAmount = $conn->query("SELECT deposit_amount FROM center_deposite WHERE id = '$id'");
    $dbDepositAmount = mysqli_fetch_column($dbDepositAmount);

    $dbClosure = $conn->query("SELECT deposit_amount , withdraw_amount FROM `Closure_details` WHERE id = '$center_id'");
    $dbClosure = mysqli_fetch_assoc($dbClosure);

    $dbClosureDepositAmount = $dbClosure['deposit_amount'];
    $dbClosureWithdrawAmount = $dbClosure['withdraw_amount'];

    $dbClosureDifferAmount = !is_null($dbClosureWithdrawAmount) ? intval($dbClosureDepositAmount-$dbClosureWithdrawAmount) : intval($dbClosureDepositAmount);
 
    if ($dbClosureDifferAmount >= $dbDepositAmount) {
        $updateCenter_deposit = $conn->query("UPDATE center_deposite SET Deleted_At = CURRENT_TIMESTAMP WHERE id = '$id'");
        $dbClosureDepositAmount -= $dbDepositAmount;
        $updateClosureDetails = $conn->query("UPDATE Closure_details SET deposit_amount = '$dbClosureDepositAmount' WHERE id = '$center_id'");

        $dailyreportCenterDepositId = $conn->query("SELECT center_deposit_id FROM `daily_reporting` WHERE id = '$daily_report_id'");
        $dailyreportCenterDepositId = mysqli_fetch_column($dailyreportCenterDepositId);
        $dailyreportCenterDepositId_arr = json_decode($dailyreportCenterDepositId,true);
        $key = array_search($center_id,$dailyreportCenterDepositId_arr);
        unset($dailyreportCenterDepositId_arr[$key]);
        $updatedDailyreportCenterDepositId = !empty($dailyreportCenterDepositId_arr) ? json_encode($dailyreportCenterDepositId_arr) : null;
        $updateQueryForDailyReport = $conn->query("UPDATE daily_reporting SET center_deposit_id = '$updatedDailyreportCenterDepositId' WHERE id = '$daily_report_id'");
        showResponse($updateQueryForDailyReport,'Deleted');
    } else {
        showResponse(false,'Amount Already Use','Can`t delete');
    }
}

function showResponse($response, $message = "Something went wrong!",$title = null) {
    if ($response) {
        echo json_encode(['status' => 200, 'message' => "Deposit details $message successfully!"]);
    } else {
        echo json_encode(['status' => 400, 'message' => $message , 'title' => $title]);
    }
}

?>