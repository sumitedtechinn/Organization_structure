<?php 

## Database configuration
include '../../includes/db-config.php';
session_start();
## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$orderby = '';

if (isset($_POST['order'])) {
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name 
    $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
}

if (isset($columnSortOrder)) {
    $orderby = "ORDER BY daily_reporting.$columnName $columnSortOrder";
} else {
    $orderby = "ORDER BY daily_reporting.date DESC";
}

$searchQuery = "";
if(isset($_SESSION['allChildId'])) {
    $searchQuery .= "AND daily_reporting.user_id IN (".implode(',',$_SESSION['allChildId']).")";
}

$searchUserQuery = '';
if ($_SESSION['role'] == '3') {
    $searchUserQuery .= "AND users.organization_id= '".$_SESSION['Organization_id']."'";
}

if(isset($_POST['organizationFilter']) && !empty($_POST['organizationFilter'])) {
    $searchUserQuery .= " AND users.organization_id= '".$_POST['organizationFilter']."'";
}

if(isset($_POST['branchFilter']) && !empty($_POST['branchFilter'])) {
    $searchUserQuery .= " AND users.branch_id = '".$_POST['branchFilter']."'";
}

if(isset($_POST['verticalFilter']) && !empty($_POST['verticalFilter'])) {
    $searchUserQuery .= " AND users.vertical_id = '".$_POST['verticalFilter']."'";
}

if(isset($_POST['departmentFilter']) && !empty($_POST['departmentFilter'])) {
    $searchUserQuery .= " AND users.department_id = '".$_POST['departmentFilter']."'";
}

if(isset($_POST['selected_user']) && !empty($_POST['selected_user'])) {
    $searchUserQuery .= " AND users.ID = '".$_POST['selected_user']."'";
}

if(!empty($searchUserQuery)) {
    $user_id = [];
    $users = $conn->query("SELECT users.ID as `id` FROM `users` WHERE users.Deleted_At IS NULL $searchUserQuery");
    if($users->num_rows > 0 ) {
        while($user = mysqli_fetch_assoc($users)) {
            $user_id[] = $user['id'];
        }
    }
    if(!empty($user_id)) {
        $searchQuery .= " AND daily_reporting.user_id IN (".implode(',',$user_id).")";
    } else {
        $searchQuery .= " AND daily_reporting.user_id IS NULL";
    }    
}

if(isset($_POST['selected_date']) && !empty($_POST['selected_date'])) {
    $dates = explode('-',$_POST['selected_date']);
    $start_date = date_format(date_create(trim($dates[0])),'Y-m-d');
    $end_date = date_format(date_create(trim($dates[1])),'Y-m-d');
    $searchQuery .= " AND (daily_reporting.date BETWEEN '$start_date' AND '$end_date')"; 
}

$delete_query = "";
if (isset($_POST['deleteDailyReport'])) {
    $delete_query = "daily_reporting.Deleted_At IS NOT NULL";
} else {
    $delete_query = "daily_reporting.Deleted_At IS NULL";
}

## Total number of records without filtering
$all_count = $conn->query("SELECT COUNT(ID) as `allcount` FROM daily_reporting WHERE $delete_query $searchQuery");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$filter_count = $conn->query("SELECT COUNT(ID) as `filtered` FROM daily_reporting WHERE $delete_query $searchQuery");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

## Fetch Record 
$dailyReport = $conn->query("SELECT daily_reporting.* , users.Name as `user_name` , users.Photo as `user_image` , IF(users.Deleted_At IS NULL,'No','Yes') as `user_delete` FROM daily_reporting LEFT JOIN users ON users.ID = daily_reporting.user_id WHERE $delete_query $searchQuery $orderby LIMIT $row , $rowperpage");

$data = [];
if($dailyReport->num_rows > 0) {
    while ($row = mysqli_fetch_assoc($dailyReport)) {
        $report_date = date_format(date_create($row["date"]),'d-M-Y');
        $doc_preapre = !empty($row['doc_prepare']) ? json_decode($row['doc_prepare'],true) : 'None';
        $doc_received = !empty($row['doc_received']) ? json_decode($row['doc_received'],true) : 'None';
        $doc_closed = !empty($row['doc_close']) ? json_decode($row['doc_close'],true) : 'None';
        $center_deposit_id = !empty($row['center_deposit_id']) ? json_decode($row['center_deposit_id'],true) : 'None'; 
        $numofmeeting = '';
        if(is_null($row['numofmeeting'])) {
            $numofmeeting = 'None';
        } else {
            $meeting = json_decode($row['numofmeeting'],true);
            if(json_last_error() === JSON_ERROR_NONE) {
                $numofmeeting = json_decode($row['numofmeeting'],true);
            } else {
                $numofmeeting = $row['numofmeeting'];
            }
        }
        $admission_ids = '';
        $admission_count = '';
        if(!empty($row['admission_ids'])) {
            $admission_ids = json_decode($row['admission_ids'],true);
            $adm_query = $conn->query("SELECT SUM(numofadmission) FROM `admission_details` WHERE id IN (".implode(',',$admission_ids).")");
            $admission_count = mysqli_fetch_column($adm_query);
        } else {
            $admission_ids = 'None';
            $admission_count = "None";
        }
        $admission_ids = !empty($row['admission_ids']) ? json_decode($row['admission_ids'],true) : "None";
        $createDate = date_format(date_create($row['created_at']),'d/m/Y');
        $data[] = array(
            "id" => $row['id'] , 
            "user_id" => $row['user_id'],
            "user_name" => $row['user_name'] , 
            "user_image" => $row['user_image'] , 
            "user_delete" => $row['user_delete'],
            "total_call" => $row['total_call'],
            "new_call" => $row['new_call'] ,
            "numofmeeting" => $numofmeeting,
            "doc_prepare" => $doc_preapre, 
            "doc_received" => $doc_received, 
            "doc_close" => $doc_closed, 
            "date" => $report_date, 
            "createDate" => $createDate , 
            'admission_ids' => $admission_ids,
            'admission_count' => $admission_count,
            'center_deposit_ids' => $center_deposit_id
        );
    }
}

## Response
$response = array(
    "draw" => intval($draw),
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    "aaData" => $data
);

echo json_encode($response);

?>