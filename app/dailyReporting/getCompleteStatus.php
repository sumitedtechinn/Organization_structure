<?php 

## Database configuration
include '../../includes/db-config.php';
session_start();

$userSearchQuery = '';
$searchQuery = '';

if(isset($_SESSION['role']) && $_SESSION['role'] == '3') {
    $userSearchQuery .= " AND Organization_id = '".$_SESSION['Organization_id']."'";
}

if(isset($_POST['organization']) && !empty($_POST['organization']) && $_POST['organization'] != 'None') {
    $userSearchQuery .= " AND Organization_id = '".$_POST['organization']."'";
}

if(isset($_POST['branch']) && !empty($_POST['branch']) && $_POST['branch'] != 'None') {
    $userSearchQuery .= " AND Branch_id = '".$_POST['branch']."'";
}

if(isset($_POST['vertical']) && !empty($_POST['vertical']) && $_POST['vertical'] != 'None') {
    $userSearchQuery .= " AND Vertical_id = '".$_POST['vertical']."'";
}

if(isset($_POST['department']) && !empty($_POST['department']) && $_POST['department'] != 'None') {
    $userSearchQuery .= " AND Department_id = '".$_POST['department']."'";
}

if(isset($_POST['user']) && !empty($_POST['user']) && $_POST['user'] != 'None') {
    $userSearchQuery .= " AND ID = '".$_POST['user']."'";
}

$selected_user_id = [];
if(!empty($userSearchQuery)) {
    $users_id = $conn->query("SELECT ID FROM `users` WHERE Deleted_At IS NULL AND Assinged_Person_id IS NOT NULL $userSearchQuery");
    if( $users_id->num_rows > 0 ) {
        while($id = mysqli_fetch_assoc($users_id)) {
            $selected_user_id[] = $id['ID'];
        }
    }
}

if(isset($_SESSION['role']) && $_SESSION['role'] == '2') {
    $selected_user_id = $_SESSION['allChildId'];
}

if(isset($_POST['daterange']) && !empty($_POST['daterange']) && $_POST['daterange'] != 'None' ) {
    $daterange = $_POST['daterange'];
    $dates = array_map('createDate', explode('-',$daterange));
    $searchQuery .= " AND date BETWEEN '".$dates[0]."' AND '".$dates[1]."'";
}

if(!empty($selected_user_id)) {
    $searchQuery .= " AND user_id IN (".implode(',',$selected_user_id).")";
}

$closure_data = ['total_call'=> 0,'total_new_call'=> 0 ,'total_meeting'=> 0 ,'total_doc_prepare' => 0,'total_doc_received'=> 0 ,'total_deal_close'=> 0,'total_admission' => 0,'total_admission_amount' => 0,'total_deposit_amount' => 0,'total_dealclose_amount' => 0];

if(!empty($selected_user_id)) {
    
    $closures = $conn->query("SELECT * FROM `daily_reporting` WHERE Deleted_At IS NULL $searchQuery");  
    if($closures->num_rows > 0) {
        while($row = mysqli_fetch_assoc($closures)) {
            $closure_data['total_call'] += intval($row['total_call']);
            $closure_data['total_new_call'] += intval($row['new_call']);
            if(!empty($row['numofmeeting'])) {
                if(is_numeric($row['numofmeeting'])) {
                    $closure_data['total_meeting'] += intval($row['numofmeeting']); 
                } else {
                    $numofmeeting = json_decode($row['numofmeeting'],true);
                    $closure_data['total_meeting'] += count($numofmeeting);
                }
            }
            if (!empty($row['admission_ids'])) {
                $admission_ids = json_decode($row['admission_ids'],true);
                $adm_query = $conn->query("SELECT SUM(numofadmission) as `admission` , SUM(amount) as `received_amount` FROM `admission_details` WHERE id IN (".implode(',',$admission_ids).")");
                $admission_count = mysqli_fetch_assoc($adm_query);
                $closure_data['total_admission'] += $admission_count['admission'];
                $closure_data['total_admission_amount'] += $admission_count['received_amount'];
            }
            if (!empty($row['doc_prepare'])) {
                $row['doc_prepare'] = json_decode($row['doc_prepare'],true);
                $closure_data['total_doc_prepare'] += count($row['doc_prepare']);
            }
            if (!empty($row['doc_received'])) {
                $row['doc_received'] = json_decode($row['doc_received'],true);
                $closure_data['total_doc_received'] += count($row['doc_received']);
            }
            if (!empty($row['doc_close'])) {
                $row['doc_close'] = json_decode($row['doc_close'],true);
                $dealCloseAmount = $conn->query("SELECT SUM(amount) FROM `Closure_details` WHERE id IN (".implode(",",$row['doc_close']).") AND Deleted_At IS NULL");
                $dealCloseAmount = mysqli_fetch_column($dealCloseAmount);
                $totaldealCloseAmount = empty($dealCloseAmount) ? 0 : $dealCloseAmount;
                $closure_data['total_deal_close'] += count($row['doc_close']);
                $closure_data['total_dealclose_amount'] += $totaldealCloseAmount;
            }
            if (!empty($row['center_deposit_id'])) {
                $row['center_deposit_id'] = json_decode($row['center_deposit_id'],true);
                $centerDeposit = $conn->query("SELECT SUM(deposit_amount) FROM `center_deposite` WHERE id IN (".implode(',',$row['center_deposit_id']).") AND Deleted_At IS NULL");
                $centerDeposit = mysqli_fetch_column($centerDeposit);
                $closure_data['total_deposit_amount'] += $centerDeposit;
            }
        }
    }

}

echo json_encode($closure_data);

function createDate($date) {
    $date = trim($date);
    $date = date_format(date_create($date),'Y-m-d');
    return $date;
}
?>