<?php 

## Database configuration
include '../../includes/db-config.php';
session_start();

if (isset($_REQUEST['projection_id'])) {

    $projection_id = mysqli_real_escape_string($conn,$_REQUEST['projection_id']);
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
        $orderby = "ORDER BY Closure_details.$columnName $columnSortOrder";
    } else {
        $orderby = "ORDER BY Closure_details.ID ASC";
    }

    $searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']); // Search value

    $searchQuery = "";
    if (!empty($searchValue)) {
        $searchQuery = "AND (Closure_details.center_name LIKE '%$searchValue%' OR Closure_details.center_email LIKE '%$searchValue%')"; 
    }

    ## Total number of records without filtering
    $all_count = $conn->query("SELECT COUNT(ID) as `allcount` FROM Closure_details WHERE Closure_details.Projection_id = '$projection_id' AND Deleted_At IS NULL");
    $records = mysqli_fetch_assoc($all_count);
    $totalRecords = $records['allcount'];

    ## Total number of record with filtering
    $filter_count = $conn->query("SELECT COUNT(ID) as `filtered` FROM Closure_details WHERE Closure_details.Projection_id = '$projection_id' AND Deleted_At IS NULL $searchQuery");
    $records = mysqli_fetch_assoc($filter_count);
    $totalRecordwithFilter = $records['filtered'];

    ## Fetch Record
    $closure_details = $conn->query("SELECT Closure_details.* , Projection_type.Name as `projection_type` FROM Closure_details LEFT JOIN Projection_type ON Projection_type.ID = Closure_details.projectionType WHERE Closure_details.Projection_id = '$projection_id' AND Closure_details.Deleted_At IS NULL $searchQuery $orderby LIMIT $row , $rowperpage");

    $data = [];
    if ($closure_details->num_rows > 0 ) {
        while( $row = mysqli_fetch_assoc($closure_details)) {
            $status = ""; $last_updated_date = '';
            $doc_received_status = is_null($row['doc_received']) ?  false : true;
            if($doc_received_status) {
                $doc_colse_status = is_null($row['doc_closed']) ? false : true;
                if($doc_colse_status) {
                    $status = "Deal Closed";
                    $last_updated_date = $row['doc_closed'];
                } else {
                    $status = "Doc Received";
                    $last_updated_date = $row['doc_received'];    
                }
            } else {
                $status = "Doc Prepare";
                $last_updated_date = $row['doc_prepare'];
            }
            $last_updated_date = date_format(date_create($last_updated_date),'d-M-Y');
            $authorization_amount = empty($row['amount']) ? 'None' : number_format($row['amount'],2,'.',',');
            $data[] = array(
                'ID' => $row['id'],
                'center_name' => $row['center_name'],
                'center_email' => $row['center_email'],
                'contact' => $row['contact'],
                'country_code' => $row['country_code'],
                'projection_type' => $row['projection_type'], 
                'projection_id' => $row['projection_id'] , 
                'user_id' => $row['user_id'],
                'authorization_amount' => $authorization_amount,
                'doc_status' => $status , 
                'last_update_date' => $last_updated_date
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

}


echo json_encode($response);
?>