<?php 

include '../../includes/db-config.php';
session_start();

$userSearchQuery = '';

if(isset($_SESSION['role']) && $_SESSION['role'] == '2') {
    
    if(isset($_POST['user']) && !empty($_POST['user']) && $_POST['user'] != 'None') {
        $userSearchQuery .= "ID = '".$_POST['user']."'";
    }    

    if(empty($userSearchQuery)) {
        $userSearchQuery .= "ID IN (".implode(',',$_SESSION['allChildId']).")";
    }

} elseif (isset($_SESSION['role']) && $_SESSION['role'] == '3') {

    $userSearchQuery .= " Organization_id = '".$_SESSION['Organization_id']."'";

    if(isset($_POST['branch']) && !empty($_POST['branch']) && $_POST['branch'] != 'None') {
        $userSearchQuery .= " AND Branch_id = '".$_POST['branch']."'";
    }
    
    if(isset($_POST['vertical']) && !empty($_POST['vertical']) && $_POST['vertical'] != 'None') {
        $userSearchQuery .= " AND Vertical_id = '".$_POST['vertical']."'";
    }
    
    if(isset($_POST['department']) && !empty($_POST['department']) && $_POST['department'] != 'None') {
        $userSearchQuery .= " AND Department_id = '".$_POST['department']."'";
    }
} else {
   
    if(isset($_POST['organization']) && !empty($_POST['organization']) && $_POST['organization'] != 'None') {
        $userSearchQuery .= "Organization_id = '".$_POST['organization']."'";
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

}

$userQuery = $conn->query("SELECT GROUP_CONCAT(ID) as `user_id` FROM `users` WHERE $userSearchQuery AND Deleted_At IS NULL");
$userIds = mysqli_fetch_column($userQuery);

$centerDepositSearch = '';
if(isset($_POST['month']) && !empty($_POST['month']) && $_POST['month'] != 'None') {
    $centerDepositSearch .= "AND DATE_FORMAT(Created_At , '%c') = '".$_POST['month']."'";
}

if(isset($_POST['year']) && !empty($_POST['year']) && $_POST['year'] != 'None') {
    $centerDepositSearch .= "AND DATE_FORMAT(Created_At , '%Y') = '".$_POST['year']."'";
}

if (!empty($userIds)) {
    $centerDeposit = $conn->query("SELECT GROUP_CONCAT(id) FROM `center_deposite` WHERE user_id IN (".$userIds.") $centerDepositSearch AND Deleted_At IS NULL");
    $centerDeposit = mysqli_fetch_column($centerDeposit);
    if(!empty($centerDeposit)) {
        showResponse(true,$centerDeposit);
    } else {
        showResponse(false);
    }
}

function showResponse($response,$centerDeposit=null) {
    if ($response) {
        echo json_encode(['status' => 200, 'center_deposit' => $centerDeposit]);
    } else {
        echo json_encode(['status' => 400 ]);
    }
}

?>