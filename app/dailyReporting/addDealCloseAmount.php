<?php 
## Database configuration
include '../../includes/db-config.php';
session_start();

$deal_close_ids = mysqli_real_escape_string($conn,$_REQUEST['selectedOptions']);
$deal_close_id = [];
if(!empty($deal_close_ids)) {
    $deal_close_id = explode(',',$deal_close_ids);
}

$insert_center_id = []; $remove_center_id = [];
if(!isset($_SESSION['dealCloseCenterId'])) {
    $_SESSION['dealCloseCenterId'] = $deal_close_id;
    $insert_center_id = $deal_close_id;
} else {
    $insert_center_id = array_diff($deal_close_id,$_SESSION['dealCloseCenterId']);
    $remove_center_id = array_diff($_SESSION['dealCloseCenterId'],$deal_close_id);
    $_SESSION['dealCloseCenterId'] = $deal_close_id;
}

$insert_details = [];
if(!empty($insert_center_id)) {
    $i = 0;
    foreach ($insert_center_id as $value) {
        $centerName = $conn->query("SELECT center_name,amount FROM `Closure_details` WHERE id = '$value'");
        $center = mysqli_fetch_assoc($centerName);
        $insert_details[$i]['id'] = $value;
        $insert_details[$i]['center_name'] = $center['center_name'];
        $insert_details[$i]['amount'] = !is_null($center['amount']) ? $center['amount'] : '';
        $i++;
    }
}

$remove_details = [];
if(!empty($remove_center_id)) {
    $i = 0;
    foreach($remove_center_id as $value) {
        $remove_details[$i]['id'] = $value;
        $i++;
    }
}

echo json_encode(['insert'=> $insert_details , 'remove' => $remove_details]);
?>