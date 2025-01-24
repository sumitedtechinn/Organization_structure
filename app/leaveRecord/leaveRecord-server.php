<?php 

## Database configuration
include '../../includes/db-config.php';
session_start();

## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$orderby = '';
$data = [];

if (isset($_POST['order'])) {
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name 
    $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
}

$orderby = (isset($columnSortOrder)) ? "ORDER BY  leave_record.$columnName $columnSortOrder" : "ORDER BY leave_record.id ASC"; 
$searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']);
$searchQuery = (!empty($searchValue)) ? "AND (users.Name LIKE '%$searchValue%' OR leaveType.leaveName LIKE '%$searchValue%')" : "";

if(isset($_POST['selected_date']) && !empty($_POST['selected_date'])) {
    $dates = explode('-',$_POST['selected_date']);
    $start_date = date_format(date_create(trim($dates[0])),'Y-m-d');
    $end_date = date_format(date_create(trim($dates[1])),'Y-m-d');
    $searchQuery .= " AND ((leave_record.start_date BETWEEN '$start_date' AND '$end_date') OR (leave_record.end_date BETWEEN '$start_date' AND '$end_date'))"; 
}

$filterQuery = '';
if (isset($_REQUEST['leaveRecord']) && $_REQUEST['leaveRecord'] == 'myLeave') {
    $filterQuery .= "AND leave_record.user_id = '".$_SESSION['ID']."'";
}

if (isset($_REQUEST['leaveRecord']) && $_REQUEST['leaveRecord'] == 'requestedLeave') {
    $downTheLineUser = getDownTheLineUser();
    if (!empty($downTheLineUser)) {
        $filterQuery .= "AND leave_record.user_id IN ($downTheLineUser)";
    }
}



if(empty($filterQuery)) {
    if ($_SESSION['role'] == '1') {
        $response = getDataTableData();
    } elseif ($_SESSION['role'] == '2' && $_SESSION['Department_id'] == '8') {
        $response = getDataTableData();
    } else {
        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => 0,
            "iTotalDisplayRecords" => 0,
            "aaData" => $data
        );
    }
} else {
    $response = getDataTableData();
}  

echo json_encode($response);

function getDataTableData() : array{
    
    global $conn, $searchQuery, $filterQuery, $orderby, $row, $rowperpage, $draw, $data;
    ## Total number of records without filtering
    
    if (isset($_REQUEST['departmentFilter']) && !empty($_REQUEST['departmentFilter'])) {
        $department_id = mysqli_real_escape_string($conn,$_REQUEST['departmentFilter']);
        $departmentUser = departmentUserList($department_id);
        if(!empty($departmentUser)) {
            $filterQuery .= "AND leave_record.user_id IN ($departmentUser)";
        }
    }
    if (isset($_REQUEST['userFilter']) && !empty($_REQUEST['userFilter'])) {
        $filterQuery .= "AND leave_record.user_id = '".$_REQUEST['userFilter']."'";
    }
    
    $all_count = $conn->query("SELECT COUNT(leave_record.id) as `allcount` FROM leave_record WHERE leave_record.start_date IS NOT NULL $filterQuery");
    $records = mysqli_fetch_assoc($all_count);
    $totalRecords = $records['allcount'];

    ## Total number of record with filtering    
    $filter_count = $conn->query("SELECT COUNT(leave_record.id) as `filtered` FROM `leave_record` LEFT JOIN leaveType ON leaveType.id = leave_record.leave_type LEFT JOIN users ON users.ID = leave_record.user_id WHERE leave_record.start_date IS NOT NULL $filterQuery $searchQuery");
    $records = mysqli_fetch_assoc($filter_count);
    $totalRecordwithFilter = $records['filtered'];

    ## Fetch Record
    $leaveRecords = $conn->query("SELECT leave_record.* , leaveType.leaveName as `leave_type`, CONCAT(DATE_FORMAT(leave_record.start_date,'%d%b%Y'),' - ',DATE_FORMAT(leave_record.end_date,'%d%b%Y')) as `leave_date` , DATEDIFF(leave_record.end_date,leave_record.start_date) + 1 as `numOfDays` , DATE_FORMAT(DATE(leave_record.created_at),'%d%b%Y') as `applied_on` , users.Name as `user_name` , users.role as `user_role` , users.Photo as `image` FROM `leave_record` LEFT JOIN leaveType ON leaveType.id = leave_record.leave_type LEFT JOIN users ON users.ID = leave_record.user_id WHERE leave_record.start_date IS NOT NULL $filterQuery $searchQuery $orderby LIMIT $row , $rowperpage");
    if($leaveRecords->num_rows > 0) {
        while ($row = mysqli_fetch_assoc($leaveRecords)) {
            $approved_by = $row['approved_by'];
            $approved_by_user_name = '';
            if (!is_null($approved_by) && !empty($approved_by)) {
                $query = $conn->query("SELECT Name  FROM `users` WHERE ID = '$approved_by'");
                $approved_by_user_name = mysqli_fetch_column($query);
            }
            $data[] = array(
                "ID" => $row['id'],
                "leave_type" => $row['leave_type'],
                "leave_date" => $row['leave_date'],
                "numOfDays" => $row['numOfDays'],
                "applied_on" => $row['applied_on'],
                "user_name" => $row['user_name'],
                "user_role" => $row['user_role'],
                "image" => $row['image'],
                "approved_by" => $approved_by,
                "approved_by_user_name" => $approved_by_user_name ,
                "status" => $row['status'],
                "mail_to" => $row['status'],
                "mail_cc" => $row['mail_cc'],
                "mail_subject" => $row["mail_subject"],
                "mail_body" => $row['mail_body'],
                "supported_document" => $row['supported_document'],
            );
        }
    }

    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    );

    return $response;
}

function getDownTheLineUser() : string {

    global $conn;
    $downTheLineUser = '';
    if( $_SESSION['role'] == '2') {
        if ( $_SESSION['Department_id'] != '8' && isset($_SESSION['allChildId'])) {
            $downTheLineUserList = $_SESSION['allChildId'];
            unset($downTheLineUserList[0]);
            if (!empty($downTheLineUserList)) {
                $downTheLineUser = implode(',',$downTheLineUserList);
            }
        }
    } elseif ($_SESSION['role'] == '3') {
        $searchQuery = '';
        if ($_SESSION['added_inside'] == '3') {
            $searchQuery .= ($_SESSION['Vertical_id'] == '1' || $_SESSION['Vertical_id'] == '2' || $_SESSION['Vertical_id'] == '3') ? " AND users.Vertical_id IN ('1','2','3')" : " AND users.Vertical_id = '".$_SESSION['Vertical_id']."'";
        }
        if ($_SESSION['added_inside'] == '3' || $_SESSION['added_inside'] == '2') {
            $searchQuery .= "AND users.Branch_id = '".$_SESSION['Branch_id']."'";
        }
        $searchQuery .= "AND users.Organization_id = '".$_SESSION['Organization_id']."'";
        $searchQuery .= "AND Designation.added_inside > '".$_SESSION['added_inside']."'";
        $delete_query = "users.Deleted_At IS NULL";
        $downTheLineUserList = $conn->query("SELECT GROUP_CONCAT(users.ID) AS 'user_ids' FROM `users` LEFT JOIN Designation ON Designation.ID = users.Designation_id WHERE $delete_query $searchQuery");
        $downTheLineUser = mysqli_fetch_column($downTheLineUserList);
    }
    return $downTheLineUser;
}

function departmentUserList($department_id) {
    
    global $conn;
    $user_ids = $conn->query("SELECT GROUP_CONCAT(ID) FROM `users` WHERE users.Department_id = '$department_id'");
    $user_ids = mysqli_fetch_column($user_ids);
    return $user_ids;
}
?>