<?php 

## Database configuration
include '../../includes/db-config.php';
session_start();

if (isset($_REQUEST['center_deposit_ids'])) {

    $center_deposit_ids = mysqli_real_escape_string($conn,$_REQUEST['center_deposit_ids']);
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
        $orderby = "ORDER BY center_deposite.$columnName $columnSortOrder";
    } else {
        $orderby = "ORDER BY center_deposite.id ASC";
    }

    $searchQuery = "";

    ## Total number of records without filtering
    $all_count = $conn->query("SELECT COUNT(id) as `allcount` FROM center_deposite WHERE center_deposite.id IN ($center_deposit_ids) AND center_deposite.Deleted_At IS NULL");
    $records = mysqli_fetch_assoc($all_count);
    $totalRecords = $records['allcount'];

    ## Total number of record with filtering
    $filter_count = $conn->query("SELECT COUNT(id) as `filtered` FROM center_deposite WHERE center_deposite.id IN ($center_deposit_ids) $searchQuery AND center_deposite.Deleted_At IS NULL");
    $records = mysqli_fetch_assoc($filter_count);
    $totalRecordwithFilter = $records['filtered'];

    ## Fetch Record
    $center_deposite = $conn->query("SELECT center_deposite.* , Closure_details.center_name as `center` FROM `center_deposite` LEFT JOIN Closure_details ON Closure_details.id = center_deposite.center_id WHERE center_deposite.Deleted_At IS NULL AND center_deposite.id IN ($center_deposit_ids) $searchQuery $orderby LIMIT $row , $rowperpage");

    $data = [];$a = 1 ;
    if ($center_deposite->num_rows > 0 ) {
        while( $row = mysqli_fetch_assoc($center_deposite)) {
            $createDate = date_format(date_create($row['Created_At']),'d-M-Y');
            $data[] = array(
                'slno' => $a,
                'ID' => $row['id'],
                'user_id' => $row['user_id'],
                'center_id' => $row['center_id'], 
                'center_name' => $row['center'], 
                'deposit_amount' => number_format($row['deposit_amount'],2,".",","), 
                'deposit_date' => date_format(date_create($row['Created_At']),'d-M-Y'),
                'create_date' => date_format(date_create($row['Created_At']),'d/m/Y')
            );
            $a++;
        }
    }

    ## Response
    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    );
}

echo json_encode($response);
?>