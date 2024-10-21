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
    $orderby = "ORDER BY daily_reporting.date ASC";
}

$searchQuery = "";
if(isset($_SESSION['allChildId'])) {
    $searchQuery .= "AND user_id IN (".implode(',',$_SESSION['allChildId']).")";
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
        $doc_preapre = [];
        if(!empty($row['doc_prepare'])) {
            $doc_prepare_ids = json_decode($row['doc_prepare'],true);
            $prepareDocCenterName = $conn->query("SELECT center_name , IF(Deleted_At IS NULL,'No','Yes') as `center_delete` FROM `Closure_details` WHERE id IN (".implode(',',$doc_prepare_ids).")");
            if($prepareDocCenterName->num_rows > 0) {
                $i= 0;
                while($name = mysqli_fetch_assoc($prepareDocCenterName)) {
                    $doc_preapre[$i]['center_name'] = $name['center_name'];
                    $doc_preapre[$i]['center_delete'] = $name['center_delete'];
                    $i++;
                }
            }
        } else {
            $doc_preapre = "None";
        }
        $doc_received = [];
        if (!empty($row['doc_received'])) {
            $doc_received_ids = json_decode($row['doc_received'],true);
            $receivedDocCenterName = $conn->query("SELECT center_name , IF(Deleted_At IS NULL,'No','Yes') as `center_delete` FROM `Closure_details` WHERE id IN (".implode(',',$doc_received_ids).")");
            if($receivedDocCenterName->num_rows > 0) {
                $i = 0 ;
                while($name = mysqli_fetch_assoc($receivedDocCenterName)) {
                    $doc_received[$i]['center_name'] = $name['center_name'];
                    $doc_received[$i]['center_delete'] = $name['center_delete'];
                    $i++;
                }
            }
        } else {
            $doc_received = 'None';
        }
        $doc_closed = [];
        if(!empty($row['doc_close'])) {
            $doc_closed_ids = json_decode($row['doc_close'],true);
            $closeDocCenterName = $conn->query("SELECT center_name , IF(Deleted_At IS NULL,'No','Yes') as `center_delete` FROM `Closure_details` WHERE id IN (".implode(',',$doc_closed_ids).")");
            if($closeDocCenterName->num_rows > 0) {
                $i = 0 ;
                while($name = mysqli_fetch_assoc($closeDocCenterName)) {
                    $doc_closed[$i]['center_name'] = $name['center_name'];
                    $doc_closed[$i]['center_delete'] = $name['center_delete'];
                    $i++;
                }
            }
        } else {
            $doc_closed = 'None';
        }
        $numofmeeting =  is_null($row['numofmeeting']) ? "None" : $row['numofmeeting'];
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
            "doc_prepare" => $doc_preapre , 
            "doc_received" => $doc_received , 
            "doc_close" => $doc_closed, 
            "date" => $report_date , 
            "createDate" => $createDate
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