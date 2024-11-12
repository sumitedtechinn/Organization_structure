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

$closures = $conn->query("SELECT * FROM `daily_reporting` WHERE Deleted_At IS NULL $searchQuery");

$closure_data = ['total_call'=> 0,'total_new_call'=> 0 ,'total_meeting'=> 0 ,'total_doc_prepare' => 0,'total_doc_received'=> 0 ,'total_deal_close'=> 0];

if($closures->num_rows > 0) {
    while($row = mysqli_fetch_assoc($closures)) {
        $closure_data['total_call'] += intval($row['total_call']);
        $closure_data['total_new_call'] += intval($row['new_call']);
        if(!empty($row['numofmeeting'])) {
            $closure_data['total_meeting'] += intval($row['numofmeeting']);
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
            $closure_data['total_deal_close'] += count($row['doc_close']);
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